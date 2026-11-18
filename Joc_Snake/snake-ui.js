// Interfície de Snake: dibuix del tauler, HUD, pantalles superposades i entrades.
// No coneix l'API ni la lògica del joc: rep dades i emet callbacks.

const COLORS = {
  fons: "#0d1f14", graella: "#123221", serp: "#4fd266", serpCap: "#b6ff8a",
  serpMort: "#ff8a8a", poma: "#ff5c5c", tija: "#8b5a2b",
};

const TECLES = {
  ArrowUp: "amunt", ArrowDown: "avall", ArrowLeft: "esquerra", ArrowRight: "dreta",
  w: "amunt", s: "avall", a: "esquerra", d: "dreta",
  W: "amunt", S: "avall", A: "esquerra", D: "dreta",
};

export function formatTemps(segons) {
  const s = Math.max(0, Math.floor(segons));
  return String(Math.floor(s / 60)).padStart(2, "0") + ":" + String(s % 60).padStart(2, "0");
}

// Crea un element amb text segur (textContent)
function el(etiqueta, classe, text) {
  const e = document.createElement(etiqueta);
  if (classe) e.className = classe;
  if (text !== undefined) e.textContent = text;
  return e;
}

export class InterficieSnake {
  constructor(conf, callbacks) {
    this.conf = conf;
    this.cb = callbacks; // { girar, iniciar, pausa, reiniciar }
    const g = conf.graella;
    this.canvas = document.getElementById("tauler");
    this.canvas.width = g.columnes * g.mida;
    this.canvas.height = g.files * g.mida;
    this.ctx = this.canvas.getContext("2d");
    this.app = document.getElementById("app");
    this.wrap = document.getElementById("taulerWrap");
    this.overlay = document.getElementById("overlay");
    this.q = (id) => document.getElementById(id);
    this._lligarEntrades();
    this._sincronitzarTema();
  }

  // ------------------------------------------------------------------ Tema
  // Segueix el tema dia/nit de la plataforma (iframe del mateix origen)
  _sincronitzarTema() {
    try {
      const arrel = window.parent.document.documentElement;
      const aplicar = () => {
        const t = arrel.getAttribute("data-theme");
        if (t) document.documentElement.setAttribute("data-theme", t);
      };
      aplicar();
      new MutationObserver(aplicar).observe(arrel, { attributes: true, attributeFilter: ["data-theme"] });
    } catch {
      /* fora de la plataforma o d'un altre origen: es queda el tema per defecte */
    }
  }

  // ---------------------------------------------------------------- Entrades
  _lligarEntrades() {
    window.addEventListener("keydown", (e) => {
      if (e.ctrlKey || e.metaKey || e.altKey) return;
      if (e.target && /^(INPUT|TEXTAREA)$/.test(e.target.tagName)) return;
      const dir = TECLES[e.key];
      if (dir) {
        e.preventDefault();
        this.cb.girar(dir);
      } else if (e.key === " " || e.key === "Enter") {
        if (e.target && e.target.tagName === "BUTTON") return; // deixa que el botó actuï
        e.preventDefault();
        this.cb.principal();
      } else if (e.key === "p" || e.key === "P" || e.key === "Escape") {
        this.cb.pausa();
      } else if (e.key === "r" || e.key === "R") {
        this.cb.reiniciar();
      }
    });

    // Després d'un clic de ratolí es treu el focus del botó: així Espai/Enter del
    // teclat no tornen a activar un botó equivocat (p. ex. Reiniciar).
    const clic = (id, accio) => this.q(id).addEventListener("click", (e) => {
      if (e.detail > 0) e.currentTarget.blur();
      accio();
    });
    clic("btnIniciar", () => this.cb.principal());
    clic("btnPausa", () => this.cb.pausa());
    clic("btnReiniciar", () => this.cb.reiniciar());

    // Creueta tàctil
    document.querySelectorAll(".dpad-btn").forEach((b) => {
      b.addEventListener("pointerdown", (e) => {
        e.preventDefault();
        this.cb.girar(b.dataset.dir);
      });
    });

    // Lliscar el dit sobre el tauler
    let inici = null;
    this.wrap.addEventListener("touchstart", (e) => {
      const t = e.changedTouches[0];
      inici = { x: t.clientX, y: t.clientY };
    }, { passive: true });
    this.wrap.addEventListener("touchend", (e) => {
      if (!inici) return;
      const t = e.changedTouches[0];
      const dx = t.clientX - inici.x;
      const dy = t.clientY - inici.y;
      inici = null;
      if (Math.max(Math.abs(dx), Math.abs(dy)) < 24) return;
      this.cb.girar(Math.abs(dx) > Math.abs(dy) ? (dx > 0 ? "dreta" : "esquerra") : (dy > 0 ? "avall" : "amunt"));
    }, { passive: true });

    window.addEventListener("blur", () => this.cb.perdreFocus());
    document.addEventListener("visibilitychange", () => {
      if (document.hidden) this.cb.perdreFocus();
    });
  }

  // --------------------------------------------------------------------- HUD
  actualitzarHud({ puntuacio, record, segons, nivell }) {
    this.q("hudPunts").textContent = String(puntuacio);
    this.q("hudRecord").textContent = String(record);
    this.q("hudTemps").textContent = formatTemps(segons);
    this.q("hudNivell").textContent = String(nivell);
  }

  animarPunts() {
    const e = this.q("hudPunts");
    e.classList.remove("pop");
    void e.offsetWidth; // reinicia l'animació
    e.classList.add("pop");
  }

  etiquetaRecord(text) { this.q("hudRecordEtiqueta").textContent = text; }

  // VULNERABILITAT (SNK-V1): el nom del jugador (?jugador= o l'àlies desat) es
  // insereix amb innerHTML sense escapar -> DOM XSS. Vegeu
  // docs/LABORATORI_VULNERABILITATS_SNAKE.md. La correcció és textContent.
  mostrarJugador(nom) {
    this.q("hudJugador").innerHTML = nom;
  }

  mostrarEstat(estat) {
    const text = { carregant: "Carregant", preparat: "Preparat", jugant: "Jugant", pausat: "Pausa", acabat: "Game Over" }[estat] || estat;
    this.q("hudEstat").textContent = text;
    this.app.dataset.estat = estat;
    const q = (id) => this.q(id);
    q("btnIniciar").disabled = estat === "jugant" || estat === "carregant";
    q("btnIniciar").textContent = estat === "acabat" ? "Tornar a jugar" : (estat === "pausat" ? "Reprendre" : "Iniciar");
    q("btnPausa").disabled = !(estat === "jugant" || estat === "pausat");
    q("btnPausa").textContent = estat === "pausat" ? "Reprendre" : "Pausa";
    q("btnReiniciar").disabled = estat === "carregant" || estat === "preparat";
  }

  mostrarBannerProves(text) {
    const b = this.q("bannerProves");
    b.hidden = !text;
    b.textContent = text || "";
  }

  // ----------------------------------------------------------- Superposicions
  _overlay(classe, ...fills) {
    this.overlay.className = "overlay visible " + classe;
    this.overlay.replaceChildren(...fills);
  }

  amagarOverlay() {
    this.overlay.className = "overlay";
    this.overlay.replaceChildren();
  }

  overlayCarregant() {
    this._overlay("ov-carregant", el("p", "ov-titol", "Carregant…"), el("div", "spinner"));
  }

  overlayError(missatge) {
    this._overlay("ov-error", el("p", "ov-titol", "Error"), el("p", "ov-text", missatge));
  }

  overlayInici(alies, alSetAlies) {
    const camp = document.createElement("input");
    camp.type = "text";
    camp.maxLength = 40;
    camp.value = alies || "";
    camp.placeholder = "Àlies (opcional)";
    camp.className = "camp-alies";
    camp.setAttribute("aria-label", "Àlies del jugador");
    camp.addEventListener("change", () => alSetAlies(camp.value));
    const boto = el("button", "btn-joc", "Iniciar partida");
    boto.type = "button";
    boto.addEventListener("click", () => this.cb.principal());
    this._overlay("ov-inici",
      el("p", "ov-titol", "SNAKE CLASSIC"),
      el("p", "ov-text", "Menja pomes, creix i no toquis les parets ni el teu cos."),
      camp, boto);
  }

  overlayPausa() {
    this._overlay("ov-pausa", el("p", "ov-titol", "PAUSA"), el("p", "ov-text", "Prem Espai o P per continuar"));
  }

  /**
   * Pantalla de Game Over. `desat` és un objecte actualitzable: { text, classe }
   * i retorna elements per poder-los refrescar quan arribi la resposta de l'API.
   */
  overlayGameOver({ puntuacio, record, nouRecord, segons, pomes, causa }, alReintentar) {
    const causaText = { paret: "Has xocat contra la paret", cos: "T'has mossegat la cua", completat: "Has omplert el tauler!" }[causa] || "";
    const estatDesat = el("p", "ov-desat", "Desant la partida…");
    const badges = el("ul", "ov-badges");
    const reintentar = el("button", "btn-joc btn-sec btn-petit", "Tornar a desar");
    reintentar.type = "button";
    reintentar.hidden = true;
    reintentar.addEventListener("click", alReintentar);

    const taula = el("dl", "ov-resum");
    const fila = (k, v) => { taula.append(el("dt", "", k), el("dd", "", v)); };
    fila("Punts", String(puntuacio));
    fila("Pomes", String(pomes));
    fila("Temps", formatTemps(segons));
    fila("Rècord", String(record));

    const boto = el("button", "btn-joc", "Tornar a jugar");
    boto.type = "button";
    boto.addEventListener("click", () => this.cb.reiniciar());

    this._overlay("ov-gameover",
      el("p", "ov-titol ov-titol-mal", "GAME OVER"),
      el("p", "ov-text", causaText),
      ...(nouRecord ? [el("p", "ov-record", "★ NOU RÈCORD ★")] : []),
      taula, estatDesat, badges, reintentar, boto);

    this.wrap.classList.remove("sacseig");
    void this.wrap.offsetWidth;
    this.wrap.classList.add("sacseig");
    return { estatDesat, badges, reintentar };
  }

  // Actualitza l'estat de desat i els assoliments a la pantalla de Game Over
  resultatDesat(refs, resultat) {
    const { estatDesat, badges, reintentar } = refs;
    estatDesat.className = "ov-desat";
    reintentar.hidden = true;
    if (resultat.proves) {
      estatDesat.textContent = "Mode proves: partida simulada (no s'ha enviat a Jowron Team).";
      estatDesat.classList.add("ov-desat-proves");
    } else if (resultat.desada) {
      estatDesat.textContent = "Partida desada a Jowron Team.";
      estatDesat.classList.add("ov-desat-ok");
    } else if (resultat.motiu === "sessio") {
      estatDesat.textContent = "Cal iniciar sessió a Jowron Team per desar la partida.";
      estatDesat.classList.add("ov-desat-error");
    } else {
      estatDesat.textContent = "No s'ha pogut desar la partida.";
      estatDesat.classList.add("ov-desat-error");
      reintentar.hidden = false;
    }
    badges.replaceChildren(...(resultat.badgesNous || []).map((b) => {
      const li = el("li", "ov-badge");
      li.append(el("span", "ov-badge-icona", b.icona || "🏆"), el("span", "", `${b.nom}: ${b.descripcio}`));
      return li;
    }));
  }

  // ------------------------------------------------------------------ Dibuix
  render(motor, alfa, temps) {
    const { ctx } = this;
    const { mida, columnes, files } = this.conf.graella;
    const W = columnes * mida;
    const H = files * mida;

    ctx.fillStyle = COLORS.fons;
    ctx.fillRect(0, 0, W, H);

    ctx.strokeStyle = COLORS.graella;
    ctx.lineWidth = 1;
    ctx.beginPath();
    for (let x = 1; x < columnes; x++) { ctx.moveTo(x * mida + 0.5, 0); ctx.lineTo(x * mida + 0.5, H); }
    for (let y = 1; y < files; y++) { ctx.moveTo(0, y * mida + 0.5); ctx.lineTo(W, y * mida + 0.5); }
    ctx.stroke();

    // Poma (pulsa suaument)
    if (motor.poma) {
      const p = motor.poma;
      const pols = 1 + 0.08 * Math.sin(temps / 180);
      const r = (mida / 2 - 3) * pols;
      const cx = p.x * mida + mida / 2;
      const cy = p.y * mida + mida / 2 + 1;
      ctx.fillStyle = COLORS.poma;
      ctx.beginPath();
      ctx.arc(cx, cy, r, 0, Math.PI * 2);
      ctx.fill();
      ctx.fillStyle = COLORS.tija;
      ctx.fillRect(cx - 1, cy - r - 4, 2, 5);
      ctx.fillStyle = "rgba(255,255,255,0.35)";
      ctx.fillRect(cx - r / 2, cy - r / 2, 3, 3);
    }

    // Serp: cada segment s'interpola entre la posició anterior i l'actual
    const mort = motor.estat === "acabat" && motor.causaFi !== "completat";
    const ant = motor.anterior;
    for (let i = motor.serp.length - 1; i >= 0; i--) {
      const s = motor.serp[i];
      const a = ant[Math.min(i, ant.length - 1)] || s;
      const x = (a.x + (s.x - a.x) * alfa) * mida;
      const y = (a.y + (s.y - a.y) * alfa) * mida;
      ctx.fillStyle = mort ? COLORS.serpMort : (i === 0 ? COLORS.serpCap : COLORS.serp);
      ctx.fillRect(x + 1, y + 1, mida - 2, mida - 2);
      if (i === 0) this._ulls(motor.direccio, x, y, mida);
    }
  }

  _ulls(direccio, x, y, mida) {
    const { ctx } = this;
    const c = mida / 2;
    const dx = { amunt: 0, avall: 0, esquerra: -1, dreta: 1 }[direccio];
    const dy = { amunt: -1, avall: 1, esquerra: 0, dreta: 0 }[direccio];
    // Dos ulls desplaçats cap a la direcció de moviment
    const perp = { x: -dy, y: dx };
    ctx.fillStyle = "#0d1f14";
    for (const sgn of [-1, 1]) {
      ctx.fillRect(x + c + dx * 4 + perp.x * 4 * sgn - 2, y + c + dy * 4 + perp.y * 4 * sgn - 2, 4, 4);
    }
  }

  // ------------------------------------------------------- Panell de partides
  panell(contingutNodes) {
    this.q("panellContingut").replaceChildren(...contingutNodes);
  }
}

export { el as crearElement };
