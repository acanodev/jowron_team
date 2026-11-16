// =============================================================================
// CURSA DE CAVALLS - CONTROL DEL DOM
// =============================================================================

// --- 1. SELECCIÓ D'ELEMENTS DEL DOM ---
const botoAvancar = document.querySelector("#boto-avancar");
const botoReiniciar = document.querySelector("#boto-reiniciar");
const inputApostador = document.querySelector("#nom-apostador");
const missatgeEstat = document.querySelector("#missatge-estat");

// --- 2. VARIABLES D'ESTAT DEL JOC ---
// Nombre de posicions que cal assolir per guanyar
const POSICIONS_PER_GUANYAR = 5;

// Array per portar el recompte de les posicions de cada cavall (índex 0 = cavall 1, etc.)
const posicionsCavalls = [0, 0, 0, 0, 0];

// Indicador de si la cursa ha finalitzat
let cursaFinalitzada = false;

// =============================================================================
// FUNCIÓ 1: afegirCaixa(numeroCavall)
// =============================================================================
// Objectiu: Crear un nou element <div> i afegir-lo al carril del cavall indicat.
// Paràmetre: numeroCavall (un número enter de l'1 al 5)
function afegirCaixa(numeroCavall) {
  // TODO: Implementa aquí el codi seguint aquests passos:
  //
  // Pas 1: Selecciona la zona d'avanç del cavall corresponent.
  //        Pots fer servir el selector `#carrer-${numeroCavall} .zona-avanc` amb document.querySelector.
  //
  // Pas 2: Crea un nou element <div> mitjançant document.createElement("div").
  //
  // Pas 3: Afegeix la classe CSS "caixa-avanc" al nou element amb classList.add("caixa-avanc").
  //
  // Pas 4: Assigna el contingut visual de la caixa mitjançant innerHTML mostrant la posició o el nom de l'apostador:
  //        Exemple: novaCaixa.innerHTML = "<span>Posició " + posicionsCavalls[numeroCavall - 1] + "</span>";
  //        (Nota: L'ús d'innerHTML en aquest exercici és intencionat per a futures pràctiques d'auditoria).
  //
  // Pas 5: Afegeix el nou element a la zona d'avanç utilitzant el mètode append().

  const carrerCavall = document.querySelector(
    `#carrer-${numeroCavall} .zona-avanc`,
  );
  const elementCavall = document.createElement("div");
  elementCavall.classList.add("caixa-avanc");
  elementCavall.innerHTML = `<span>Posició ${posicionsCavalls[numeroCavall - 1] + 1}</span>`;
  carrerCavall.append(elementCavall);
}

// =============================================================================
// ESDEVENIMENT: Pulsació del botó "Avançar torn"
// =============================================================================
// Objectiu: Generar un cavall aleatori (de l'1 al 5), fer-lo avançar i comprovar si guanya.
botoAvancar.addEventListener("click", () => {
  // TODO: Implementa aquí la lògica del torn seguint aquests passos:
  //
  // Pas 1: Si la variable `cursaFinalitzada` és true, surt de la funció amb `return` per no continuar.
  //
  // Pas 2: Genera un número enter aleatori entre 1 i 5.
  //        Fórmula: const cavallAleatori = Math.floor(Math.random() * 5) + 1;
  //
  // Pas 3: Incrementa el comptador de posicions del cavall generat.
  //        Nota: índex = cavallAleatori - 1 (perquè l'array comença a 0).
  //
  // Pas 4: Crida la funció `afegirCaixa(cavallAleatori)` per reflectir l'avanç al DOM.
  //
  // Pas 5: Actualitza el missatge d'estat de la cursa al panell superior usant la propietat innerHTML:
  //        missatgeEstat.innerHTML = "Últim moviment: Cavall " + cavallAleatori + " (Apostador: " + inputApostador.value + ")";
  //
  //        ATENCIÓ (CIBERSEGURETAT):
  //        En assignar directament el valor d'un camp de formulari (inputApostador.value) a innerHTML,
  //        s'introdueix una vulnerabilitat de tipus DOM-based XSS (CWE-79). Si l'usuari introdueix un
  //        vector d'atac com <img src=x onerror=alert(1)>, el navegador l'executarà.
  //        Aquest mètode s'ha de mantenir així expressament per a la pràctica d'auditoria del Tema 3.
  //
  // Pas 6: Comprova si la posició d'aquest cavall ha arribat a `POSICIONS_PER_GUANYAR` (5).
  //        Si ha arribat a 5:
  //        a) Canvia `cursaFinalitzada = true;`
  //        b) Desactiva el botó d'avançar: `botoAvancar.disabled = true;`
  //        c) Actualitza el panell d'estat amb innerHTML indicant el guanyador:
  //           missatgeEstat.innerHTML = "Cursa finalitzada! Enhorabona <strong>" + inputApostador.value + "</strong>, el Cavall " + cavallAleatori + " ha guanyat!";
  //        d) Mostra un missatge alert() indicant el cavall guanyador:
  //           alert("El Cavall " + cavallAleatori + " ha guanyat la cursa!");
  if (cursaFinalitzada) {
    return;
  }

  const cavallAleatori = Math.floor(Math.random() * 5) + 1;
  const missatgeEstat = document.querySelector("#missatge-estat");
  const inputNomApostador = document.querySelector("#nom-apostador");
  const nomApostador = inputNomApostador.value;
  
  missatgeEstat.innerHTML = `Últim moviment: Cavall ${cavallAleatori} Apostador: ${nomApostador}`;

  afegirCaixa(cavallAleatori);
  posicionsCavalls[cavallAleatori - 1]++;
  if (posicionsCavalls[cavallAleatori - 1] === 5) {
    cursaFinalitzada = true;
    alert(`Ha guanyat el cavall: ${cavallAleatori}`);
  }
});

// =============================================================================
// ESDEVENIMENT: Botó "Reiniciar cursa" (Codi d'ajuda)
// =============================================================================
botoReiniciar.addEventListener("click", () => {
  // Buidem totes les caixes de totes les columnes
  const zones = document.querySelectorAll(".zona-avanc");
  zones.forEach((zona) => {
    zona.innerHTML = "";
  });

  // Reiniciem les posicions
  for (let i = 0; i < posicionsCavalls.length; i++) {
    posicionsCavalls[i] = 0;
  }

  // Reactivem la cursa
  cursaFinalitzada = false;
  botoAvancar.disabled = false;
  missatgeEstat.textContent =
    "Cursa a punt per començar. Introdueix el teu nom i prem 'Avançar torn'.";
});
