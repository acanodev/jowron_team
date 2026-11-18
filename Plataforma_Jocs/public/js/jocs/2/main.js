// Snake Classic: orquestra motor, interfície i integració amb Jowron Team.
import { snakeConf as conf } from "./conf.js";
import { MotorSnake } from "./motor.js";
import { detectarEntorn, crearApi, ApiNoDisponible } from "./api-jowron.js";
import { InterficieSnake, crearElement as el } from "./snake-ui.js";

const entorn = detectarEntorn(conf);
const api = crearApi(conf, entorn);

const motor = new MotorSnake({
  columnes: conf.graella.columnes,
  files: conf.graella.files,
  longitudInicial: conf.inicial.longitud,
  direccioInicial: conf.inicial.direccio,
  puntsPerPoma: conf.puntsPerPoma,
  velocitat: conf.velocitat,
});

// --------------------------------------------------------------- Estat de la UI
let estat = "carregant"; // carregant | preparat | jugant | pausat | acabat
let record = 0;
let origenRecord = "local"; // api | local | proves
let tempsActiu = 0; // ms de joc efectiu (sense comptar pauses)
let acumulat = 0;
let ultimTs = null;
let raf = null;
let seqPartida = 0; // evita que una resposta antiga pinti una partida nova
let ultimaPartida = null;

const CLAU_ALIES = "snake_classic.alies";
const CLAU_RECORD = `snake_classic.record.${entorn.jocId ?? "local"}`;

const ui = new InterficieSnake(conf, {
  girar: (dir) => { if (estat === "jugant") motor.canviarDireccio(dir); },
  principal: accioPrincipal,
  pausa: alternarPausa,
  reiniciar: reiniciarIComencar,
  perdreFocus: () => { if (estat === "jugant") pausar(); },
});

// ------------------------------------------------------------- Emmagatzem local
function llegirLocal(clau, defecte) {
  try {
    const v = localStorage.getItem(clau);
    return v === null ? defecte : v;
  } catch {
    return defecte;
  }
}
function escriureLocal(clau, valor) {
  try { localStorage.setItem(clau, String(valor)); } catch { /* mode privat: s'ignora */ }
}

// ----------------------------------------------------------------- Rècord
async function carregarRecord() {
  try {
    record = Number(await api.obtenirRecord()) || 0;
    origenRecord = api.mode === "proves" ? "proves" : "api";
  } catch (e) {
    // PENDENT DE L'API: sense endpoint de lectura, s'usa el rècord d'aquest navegador
    if (!(e instanceof ApiNoDisponible)) console.warn("[Snake] Rècord:", e);
    record = Number(llegirLocal(CLAU_RECORD, 0)) || 0;
    origenRecord = "local";
  }
  ui.etiquetaRecord({ api: "Rècord", local: "Rècord (local)", proves: "Rècord (proves)" }[origenRecord]);
}

// ---------------------------------------------------------------- Flux de joc
function segons() { return Math.floor(tempsActiu / 1000); }

function pintaHud() {
  ui.actualitzarHud({ puntuacio: motor.puntuacio, record: Math.max(record, motor.puntuacio), segons: segons(), nivell: motor.nivell });
}

function canviarEstat(nou) {
  estat = nou;
  ui.mostrarEstat(nou);
}

function accioPrincipal() {
  if (estat === "preparat") comencar();
  else if (estat === "pausat") reprendre();
  else if (estat === "jugant") pausar();
  else if (estat === "acabat") reiniciarIComencar();
}

function comencar() {
  motor.iniciar();
  acumulat = 0;
  ultimTs = null;
  ui.amagarOverlay();
  canviarEstat("jugant");
  engegarBucle();
}

function reiniciarIComencar() {
  if (estat === "carregant") return;
  seqPartida++;
  motor.reiniciar();
  tempsActiu = 0;
  acumulat = 0;
  pintaHud();
  comencar();
}

function pausar() {
  if (estat !== "jugant") return;
  motor.pausar();
  canviarEstat("pausat");
  ui.overlayPausa();
}

function reprendre() {
  if (estat !== "pausat") return;
  motor.reprendre();
  ultimTs = null;
  ui.amagarOverlay();
  canviarEstat("jugant");
  engegarBucle();
}

function alternarPausa() {
  if (estat === "jugant") pausar();
  else if (estat === "pausat") reprendre();
}

function engegarBucle() {
  if (raf === null) raf = requestAnimationFrame(bucle);
}

function bucle(ts) {
  raf = null;
  if (estat !== "jugant") return;

  const dt = ultimTs === null ? 0 : Math.min(ts - ultimTs, 250);
  ultimTs = ts;
  acumulat += dt;
  tempsActiu += dt;

  while (estat === "jugant" && acumulat >= motor.intervalMs) {
    acumulat -= motor.intervalMs;
    const ev = motor.pas();
    if (ev.tipus === "poma") ui.animarPunts();
    if (ev.tipus === "fi") { acabarPartida(); return; }
  }

  ui.render(motor, Math.min(1, acumulat / motor.intervalMs), ts);
  pintaHud();
  raf = requestAnimationFrame(bucle);
}

// ------------------------------------------------------------- Fi de partida
function acabarPartida() {
  canviarEstat("acabat");
  ui.render(motor, 1, performance.now());

  const nouRecord = motor.puntuacio > record;
  if (nouRecord) {
    record = motor.puntuacio;
    if (origenRecord === "local") escriureLocal(CLAU_RECORD, record);
  }
  pintaHud();

  ultimaPartida = {
    puntuacio: motor.puntuacio,
    durada: segons(),
    derrota: motor.causaFi !== "completat",
  };
  const refs = ui.overlayGameOver(
    { puntuacio: motor.puntuacio, record, nouRecord, segons: segons(), pomes: motor.pomes, causa: motor.causaFi },
    () => desarUltimaPartida(refs, seqPartida)
  );
  desarUltimaPartida(refs, seqPartida);
}

async function desarUltimaPartida(refs, seq) {
  const resultat = await api.desarPartida(ultimaPartida);
  if (seq !== seqPartida) return; // ja s'ha iniciat una altra partida
  resultat.proves = api.mode === "proves";
  ui.resultatDesat(refs, resultat);
  refrescarPanell();
}

// ----------------------------------------------------- Panell "Les teves partides"
async function refrescarPanell() {
  try {
    const [resum, historial] = await Promise.all([api.obtenirEstadistiques(), api.obtenirHistorial()]);
    const taula = document.createElement("table");
    const cap = taula.createTHead().insertRow();
    ["Data", "Punts", "Temps"].forEach((t) => cap.appendChild(el("th", "", t)));
    const cos = taula.createTBody();
    historial.forEach((p) => {
      const f = cos.insertRow();
      f.append(el("td", "", new Date(p.data).toLocaleString("ca")), el("td", "", String(p.puntuacio)), el("td", "", `${p.durada}s`));
    });
    ui.panell([
      el("p", "", `Partides: ${resum.partides} · Millor: ${resum.millor} · Total: ${resum.total} punts`),
      historial.length ? taula : el("p", "nota", "Encara no has jugat cap partida."),
      el("p", "nota", "Dades simulades del mode proves."),
    ]);
  } catch (e) {
    // PENDENT DE L'API: s'envia el jugador a les pàgines que la plataforma ja té
    const nodes = [el("p", "nota", "L'historial i les estadístiques d'aquest joc encara no estan disponibles des del joc (pendent de l'API). Consulta'ls a la plataforma:")];
    if (entorn.baseUrl) {
      for (const [text, ruta] of [["Les meves estadístiques", conf.enllacos.estadistiques], ["Els meus assoliments", conf.enllacos.assoliments]]) {
        const a = el("a", "", text);
        a.href = new URL(ruta, entorn.baseUrl).href;
        a.target = "_top";
        nodes.push(a);
      }
    }
    ui.panell(nodes);
  }
}

// ------------------------------------------------------------------ Arrencada
function aliesActual() {
  // VULNERABILITAT (SNK-V1): ?jugador= té prioritat i no es valida (vegeu snake-ui.js)
  return entorn.params.get("jugador") ?? llegirLocal(CLAU_ALIES, "Jugador");
}

async function iniciar() {
  canviarEstat("carregant");
  ui.overlayCarregant();
  ui.mostrarJugador(aliesActual());
  if (entorn.mode === "proves") {
    ui.mostrarBannerProves(`MODE PROVES (${entorn.motiuProves}): les partides NO s'envien a Jowron Team.`);
  }
  await carregarRecord();
  motor.reiniciar();
  pintaHud();
  ui.render(motor, 1, 0);
  refrescarPanell();

  canviarEstat("preparat");
  ui.overlayInici(llegirLocal(CLAU_ALIES, ""), (valor) => {
    escriureLocal(CLAU_ALIES, valor.trim());
    ui.mostrarJugador(aliesActual());
  });
  window.focus();
}

iniciar().catch((e) => {
  console.error("[Snake] Error d'inicialització:", e);
  canviarEstat("acabat");
  ui.overlayError("No s'ha pogut iniciar el joc. Recarrega la pàgina.");
});
