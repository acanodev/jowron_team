document.addEventListener("DOMContentLoaded", () => {
  // Recomendable cargar el codigo cuando los elementos el DOM esten totalmente cargado.
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
  );
  jugador.elementHTML.classList.add("nau", "jugador");
  pantalla.append(jugador.elementHTML);

  // --------- Objecte i llista Enemic ---------

  let enemic;
  let enemics = [];

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
    );
    enemic.elementHTML.classList.add("nau", "enemic");
    enemics.push(enemic);
  }

  enemics.forEach((e, i) => {
    setTimeout(
      () => {
        pantalla.append(e.elementHTML);
        e.moure();
      },
      gameConf.intervalAparicioMs * (i + 1),
    ); // Fraccionem l'interval d'aparició amb les iteracions de forEach per tal de que no surtin tots els enemics de cop, per exemple el primer surt al cap de 5 segons i el segon després de 10 segons.
  });

  // ------- Objecte asteroide -------
  const asteroides = [];
  for (let i = 0; i < 100; i++) {
    let posX = Math.floor(Math.random() * 1200);
    let posY = Math.floor(Math.random() * 800);
    const asteroide = new Entitat({ x: posX, y: posY }, 5, 5);
    asteroide.elementHTML.classList.add("asteroide");
    pantalla.append(asteroide.elementHTML);
    asteroides.push(asteroide);
  }

  // ------- Informació de la partida -------
  const elementNom = document.createElement("p");
  const elementPunts = document.createElement("p");
  const elementDerribats = document.createElement("p");
  const elementVides = document.createElement("p");
  // Ús d'un mètode vulnerable (innerHTML), l'usuari pot injectar codi a l'introduir el seu nom
  elementNom.innerHTML = `Jugador: Pepet`;
  infoPartida.append(elementNom);
  elementPunts.innerHTML = `Punts: 100`;
  infoPartida.append(elementPunts);
  elementDerribats.innerHTML = `Kills: 12`;
  infoPartida.append(elementDerribats);
  elementVides.innerHTML = `Vides: 3`;
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
});

function getRandomNumber(min, max) {
  return Math.random() * (max - min) + min;
}
