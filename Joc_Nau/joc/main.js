document.addEventListener("DOMContentLoaded", () => { // Recomendable cargar el codigo cuando los elementos el DOM esten totalmente cargado.
  // --------- Pantalla del Joc ---------
  const pantalla = document.querySelector("#pantalla"); // el # es per referirse a un ID del html (busca el element de # pantalla i sera la teua pantalla)
  const infoPartida = document.querySelector("#infoPartida");

  // --------- Objecte Jugador ---------
  const jugador = new Nau({ x: 100, y: 300 }, 150, 100);
  jugador.elementHTML.classList.add("nau", "jugador");
  pantalla.append(jugador.elementHTML);

  // --------- Objecte Enemic ---------
  const enemic = new Entitat({ x: 500, y: 200 }, 75, 75);
  enemic.elementHTML.classList.add("nau", "enemic");
  pantalla.append(enemic.elementHTML);

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
  elementVides.innerHTML = `Vides: 2`;
  infoPartida.append(elementVides);

  window.addEventListener("keydown", (e) => {
    const estilsPantalla = window.getComputedStyle(pantalla);
    const maxAltPantalla = parseFloat(estilsPantalla.height);

    if (e.key === "ArrowUp") {
      jugador.moureAmunt();
    }

    if (e.key === "ArrowDown") {
      jugador.moureAvall(maxAltPantalla);
    }
  });
});