// ============================================================================
// Capa d'integració amb Jowron Team. ÚNIC mòdul que fa comunicacions externes.
//
// El joc no coneix ni la BD ni les credencials: només parla amb la plataforma
// pels camins que aquesta ofereix. Estat d'aquest checkout (vegeu README.md):
//
//   FUNCIONA (existeix al codi de la plataforma):
//     - desarPartida()      POST ?route=/guardar-puntuacio  (JocController)
//                           -> registra la partida de l'usuari amb sessió,
//                              avalua badges i retorna `badgesNous`.
//
//   PENDENT DE L'API (no hi ha cap endpoint de lectura en aquest ZIP):
//     - obtenirRecord(), obtenirEstadistiques(), obtenirHistorial(),
//       obtenirAssoliments()  -> en mode "real" llancen ApiNoDisponible i el
//       joc usa un fallback local i enllaços a les pàgines HTML existents.
//
// Modes: "real" (plataforma) i "proves" (simulació local, sense xarxa).
// ============================================================================

export class ApiNoDisponible extends Error {
  constructor(operacio) {
    super(`Operació pendent de l'API: ${operacio}`);
    this.name = "ApiNoDisponible";
    this.operacio = operacio;
  }
}

/**
 * Detecta on s'executa el joc: id del joc, URL base de la plataforma i mode.
 * Dins la plataforma la URL és  <base>/public/js/jocs/<id>/index.html
 */
export function detectarEntorn(conf, loc = window.location) {
  const params = new URLSearchParams(loc.search);
  const m = loc.pathname.match(/^(.*)\/public\/js\/jocs\/(\d+)\//);

  let mode = conf.api.mode;
  let motiuProves = mode === "proves" ? "configuració" : null;
  if (conf.api.permetreModeProvesPerUrl && params.get("mode") === "proves") {
    mode = "proves";
    motiuProves = "paràmetre ?mode=proves";
  }

  let jocId = m ? parseInt(m[2], 10) : null;
  // VULNERABILITAT (SNK-V3): l'id del joc es pot sobreescriure des de la URL
  // (?joc_id=N) i s'envia tal qual a la plataforma, sense comprovar que sigui
  // l'id d'aquest joc. Vegeu docs/LABORATORI_VULNERABILITATS_SNAKE.md.
  if (params.has("joc_id")) {
    jocId = parseInt(params.get("joc_id"), 10);
  }

  const baseUrl = m ? loc.origin + m[1] + "/" : null;

  if (mode === "real" && (!baseUrl || !jocId)) {
    mode = "proves";
    motiuProves = "el joc s'executa fora de la plataforma";
  }
  return { mode, motiuProves, jocId, baseUrl, params };
}

export function crearApi(conf, entorn) {
  return entorn.mode === "proves"
    ? new ApiProves(conf, entorn)
    : new ApiReal(conf, entorn);
}

// ----------------------------------------------------------------------------
// MODE REAL
// ----------------------------------------------------------------------------
class ApiReal {
  constructor(conf, entorn) {
    this.conf = conf;
    this.entorn = entorn;
    this.mode = "real";
  }

  /**
   * Desa una partida acabada.
   * @param {{puntuacio:number, durada:number, derrota:boolean}} partida
   * @returns {Promise<{desada:boolean, motiu?:string, badgesNous:Array}>}
   *   motiu: "sessio" (no autenticat) | "error" (xarxa/servidor)
   */
  async desarPartida({ puntuacio, durada, derrota }) {
    const { transport, endpoints, timeoutMs } = this.conf.api;
    const url = new URL(endpoints[transport].desarPartida, this.entorn.baseUrl).href;
    const camps = {
      joc_id: this.entorn.jocId,
      nivell: 1, // Snake no té nivells: la plataforma exigeix el camp
      puntuacio,
      durada,
    };
    if (derrota) camps.resultat = "derrota"; // el servidor només coneix aquest valor

    let opcions;
    if (transport === "rest") {
      // NO VERIFICAT: ruta del document de canvis; no existeix en aquest ZIP.
      opcions = {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(camps),
      };
    } else {
      opcions = {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams(camps),
      };
    }

    const ctrl = new AbortController();
    const t = setTimeout(() => ctrl.abort(), timeoutMs);
    try {
      const resp = await fetch(url, { ...opcions, credentials: "same-origin", signal: ctrl.signal });
      if (resp.status === 401) return { desada: false, motiu: "sessio", badgesNous: [] };

      const text = await resp.text();
      let dades;
      try {
        dades = JSON.parse(text);
      } catch {
        throw new Error("Resposta no vàlida de la plataforma");
      }

      if (!dades.success) {
        // El controlador respon {success:false} sense missatge quan no hi ha sessió
        return { desada: false, motiu: dades.error ? "error" : "sessio",
                 detall: dades.error, badgesNous: [] };
      }
      return {
        desada: true,
        badgesNous: Array.isArray(dades.badgesNous) ? dades.badgesNous : [],
      };
    } catch (e) {
      console.error("[Snake] No s'ha pogut desar la partida:", e);
      return { desada: false, motiu: "error", detall: e.message, badgesNous: [] };
    } finally {
      clearTimeout(t);
    }
  }

  // --- PENDENT DE L'API: cal que el teu company exposi aquests endpoints ----
  async obtenirRecord() { throw new ApiNoDisponible("record personal"); }
  async obtenirEstadistiques() { throw new ApiNoDisponible("estadístiques del jugador"); }
  async obtenirHistorial() { throw new ApiNoDisponible("partides anteriors"); }
  async obtenirAssoliments() { throw new ApiNoDisponible("assoliments"); }
}

// ----------------------------------------------------------------------------
// MODE PROVES: simula la plataforma amb localStorage. No fa cap petició.
// El catàleg d'assoliments reprodueix database/snake_classic.sql.
// ----------------------------------------------------------------------------
const CLAU_PROVES = "snake_classic.proves.partides";
const CATALEG_PROVES = [
  { codi: "snake_primera", nom: "Primera mossegada", descripcio: "Juga la teva primera partida de Snake.", icona: "🐍", valor: (s) => s.partides, llindar: 1 },
  { codi: "snake_10_pomes", nom: "Gana de pomes", descripcio: "Menja 10 pomes en una partida.", icona: "🍎", valor: (s) => s.millor, llindar: 10 },
  { codi: "snake_50", nom: "Mig centenar", descripcio: "Fes 50 punts en una partida.", icona: "⭐", valor: (s) => s.millor, llindar: 50 },
  { codi: "snake_100", nom: "Centenari", descripcio: "Fes 100 punts en una partida.", icona: "🏆", valor: (s) => s.millor, llindar: 100 },
  { codi: "snake_habitual", nom: "Serp habitual", descripcio: "Juga 10 partides de Snake.", icona: "🔁", valor: (s) => s.partides, llindar: 10 },
];

class ApiProves {
  constructor(conf, entorn) {
    this.conf = conf;
    this.entorn = entorn;
    this.mode = "proves";
  }

  _llegir() {
    try {
      return JSON.parse(localStorage.getItem(CLAU_PROVES)) || [];
    } catch {
      return [];
    }
  }

  _resum(partides) {
    return {
      partides: partides.length,
      millor: partides.reduce((m, p) => Math.max(m, p.puntuacio), 0),
      total: partides.reduce((s, p) => s + p.puntuacio, 0),
      temps: partides.reduce((s, p) => s + p.durada, 0),
    };
  }

  async desarPartida({ puntuacio, durada }) {
    const abans = this._resum(this._llegir());
    const partides = this._llegir();
    partides.unshift({ puntuacio, durada, data: new Date().toISOString() });
    try {
      localStorage.setItem(CLAU_PROVES, JSON.stringify(partides.slice(0, 50)));
    } catch (e) {
      return { desada: false, motiu: "error", detall: e.message, badgesNous: [] };
    }
    const despres = this._resum(partides);
    const badgesNous = CATALEG_PROVES
      .filter((b) => b.valor(abans) < b.llindar && b.valor(despres) >= b.llindar)
      .map(({ codi, nom, descripcio, icona }) => ({ codi, nom, descripcio, icona }));
    return { desada: true, badgesNous };
  }

  async obtenirRecord() { return this._resum(this._llegir()).millor; }
  async obtenirEstadistiques() { return this._resum(this._llegir()); }
  async obtenirHistorial() { return this._llegir().slice(0, 10); }
  async obtenirAssoliments() {
    const s = this._resum(this._llegir());
    return CATALEG_PROVES.map((b) => ({ ...b, obtingut: b.valor(s) >= b.llindar }));
  }
}
