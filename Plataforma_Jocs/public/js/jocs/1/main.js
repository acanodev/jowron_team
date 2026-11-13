import { gameConf } from "./conf.js";

const appBaseUrl = new URL("../../../../", document.baseURI);
const progressUrl = new URL("?route=/guardar-puntuacio", appBaseUrl).href;
const gamesListUrl = new URL("?route=/jocs", appBaseUrl).href;

let nivellFinalitzat = false;
const iniciPartida = Date.now();

// --------- Pantalla del Joc ---------
const pantalla = document.querySelector("#pantalla"); // el # es per referirse a un ID del html (busca el element de # pantalla i sera la teua pantalla)
const infoPartida = document.querySelector("#infoPartida");
const estilsPantalla = window.getComputedStyle(pantalla);
const maxPantallaWidth = parseFloat(estilsPantalla.width);
const maxAltPantalla = parseFloat(estilsPantalla.height);

// --------- Objecte Jugador ---------
const jugador = new Jugador(
  { x: 100, y: 300 },
  gameConf.ampleJugador,
  gameConf.altJugador,
  gameConf.velocitatJugador,
  gameConf.maxVides,
  gameConf.puntsJugador,
  gameConf.killsJugador,
);
jugador.elementHTML.classList.add("nau", "jugador");
pantalla.append(jugador.elementHTML);

// --------- Objecte i llista Enemic ---------

let enemic;
const enemics = [];

for (let i = 0; i < gameConf.maxEnemics; i++) {
  enemic = new Enemic(
    {
      x: maxPantallaWidth,
      y: getRandomNumber(0, maxAltPantalla - gameConf.altEnemics),
    },
    gameConf.ampleEnemics,
    gameConf.altEnemics,
    gameConf.velocitatEnemics,
    gameConf.puntsPerEnemic,
    maxPantallaWidth,
    maxAltPantalla,
    gameConf.fotogramesEnemics,
  );
  enemic.elementHTML.classList.add("nau", "enemic");
  enemics.push(enemic);
}

enemics.forEach((e, i) => {
  setTimeout(
    () => {
      pantalla.append(e.elementHTML);
      e.moure(perdreVida);
    },
    gameConf.intervalAparicioMs * (i + 1),
  ); // Fraccionem l'interval d'aparició amb les iteracions de forEach per tal de que no surtin tots els enemics de cop, per exemple el primer surt al cap de 5 segons i el segon després de 10 segons.
});

// ------- Objecte asteroide -------
const asteroides = [];
for (let i = 0; i < gameConf.maxAsteroides; i++) {
  const asteroide = new Asteroide(
    {
      x: getRandomNumber(0, maxPantallaWidth),
      y: getRandomNumber(0, maxAltPantalla),
    },
    5,
    5,
    gameConf.velocitatAsteroides,
    maxPantallaWidth,
    gameConf.fotogramesAsteroides,
  );
  asteroide.elementHTML.classList.add("asteroide");
  pantalla.append(asteroide.elementHTML);
  asteroides.push(asteroide);
}

asteroides.forEach((a) => {
  a.moure();
});

// ------- Informació de la partida -------
const elementNom = document.createElement("p");
const elementNivell = document.createElement("p");
const elementPunts = document.createElement("p");
const elementDerribats = document.createElement("p");
const elementVides = document.createElement("p");
// Ús d'un mètode vulnerable (innerHTML), l'usuari pot injectar codi a l'introduir el seu nom
// Es manté perquè en activitats posteriors explotarem aquesta vulnerabilitat.
elementNom.innerHTML = `Jugador: ${gameConf.nomJugador}`;
infoPartida.append(elementNom);
elementNivell.innerHTML = `Nivell: ${gameConf.nivell}/${gameConf.totalNivells}`;
infoPartida.append(elementNivell);
elementPunts.innerHTML = `Punts: ${jugador.punts}`;
infoPartida.append(elementPunts);
elementDerribats.innerHTML = `Kills: ${jugador.kills}`;
infoPartida.append(elementDerribats);
elementVides.innerHTML = `Vides: ${jugador.vides}`;
infoPartida.append(elementVides);

// Event listener amb arrow function
window.addEventListener("keydown", (e) => {
  if (e.key === "ArrowUp") {
    jugador.moureAmunt();
  }

  if (e.key === "ArrowDown") {
    jugador.moureAvall(maxAltPantalla);
  }
});

/**
 * Retorna un número aleatori a partir d'un mínim i un màxim.
 */
function getRandomNumber(min, max) {
  return Math.random() * (max - min) + min;
}

/**
 * Actualitza la informació de la partida.
 */
function actualitzarInfo() {
  elementPunts.innerHTML = `Punts: ${jugador.punts}`;
  elementDerribats.innerHTML = `Kills: ${jugador.kills}`;
  elementVides.innerHTML = `Vides: ${jugador.vides}`;
}

/**
 * Controla com el jugador perd vides quan els enemics arriben a l'esquerra
 * de la pantalla i desapareixen.
 */
function perdreVida() {
  jugador.vides--;
  actualitzarInfo();
  if (jugador.vides <= 0) {
    nivellFinalitzat = true;
    clearInterval(intervalColisions);
    alert("Game over!");
    window.location.reload();
  }
}

async function superarNivell() {
  if (nivellFinalitzat) {
    return;
  }

  nivellFinalitzat = true;
  clearInterval(intervalColisions);

  const durada = Math.round((Date.now() - iniciPartida) / 1000);
  const dades = new URLSearchParams({
    joc_id: gameConf.jocId,
    nivell: gameConf.nivell,
    puntuacio: jugador.punts,
    durada: durada,
  });

  try {
    const resposta = await fetch(progressUrl, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: dades,
    });
    const resultat = await resposta.json();

    if (!resultat.success) {
      throw new Error(resultat.error ?? "No s'ha pogut desar el progrés");
    }

    if (resultat.completed) {
      alert(`Has completat el joc! Punts: ${jugador.punts}`);
      window.location.href = gamesListUrl;
      return;
    }

    alert(`Nivell superat! Punts: ${jugador.punts}`);
    window.location.reload();
  } catch (e) {
    console.error("No s'ha pogut desar el progrés:", e);
    alert(`Nivell superat! Punts: ${jugador.punts}`);
    window.location.reload();
  }
}

function detectarCollisions() {
  enemics.forEach((e) => {
    if (jugador.collisiona(e) && e.elementHTML.isConnected) {
      e.destruir();
      jugador.kills++;
      jugador.punts += e.punts;
    }
  });

  actualitzarInfo();

  if (jugador.punts >= gameConf.maxPunts) {
    superarNivell();
  }
}

const intervalColisions = setInterval(detectarCollisions, 100);
