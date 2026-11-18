// Motor de Snake: lògica pura, sense DOM ni xarxa (es pot provar amb Node).

export const DIRECCIONS = {
  amunt: { x: 0, y: -1 },
  avall: { x: 0, y: 1 },
  esquerra: { x: -1, y: 0 },
  dreta: { x: 1, y: 0 },
};

const OPOSADA = { amunt: "avall", avall: "amunt", esquerra: "dreta", dreta: "esquerra" };

// Estats: preparat -> jugant <-> pausat -> acabat
export class MotorSnake {
  constructor({ columnes, files, longitudInicial = 3, direccioInicial = "dreta",
                puntsPerPoma = 1, velocitat, rng = Math.random }) {
    this.columnes = columnes;
    this.files = files;
    this.longitudInicial = longitudInicial;
    this.direccioInicial = direccioInicial;
    this.puntsPerPoma = puntsPerPoma;
    this.velocitat = velocitat;
    this.rng = rng;
    this.reiniciar();
  }

  reiniciar() {
    const d = DIRECCIONS[this.direccioInicial];
    const cx = Math.floor(this.columnes / 2);
    const cy = Math.floor(this.files / 2);
    this.serp = [];
    for (let i = 0; i < this.longitudInicial; i++) {
      this.serp.push({ x: cx - d.x * i, y: cy - d.y * i }); // cap primer
    }
    this.anterior = this.serp.map((s) => ({ ...s }));
    this.direccio = this.direccioInicial;
    this.cua = []; // girs pendents (màxim 2) per no perdre pulsacions ràpides
    this.pomes = 0;
    this.puntuacio = 0;
    this.causaFi = null; // "paret" | "cos" | "completat"
    this.estat = "preparat";
    this.poma = this._novaPoma();
  }

  get nivell() {
    return 1 + Math.floor(this.pomes / this.velocitat.pomesPerNivell);
  }

  get intervalMs() {
    const v = this.velocitat;
    return Math.max(v.minimMs, v.inicialMs - (this.nivell - 1) * v.reduccioMs);
  }

  iniciar() { if (this.estat === "preparat") this.estat = "jugant"; }
  pausar() { if (this.estat === "jugant") this.estat = "pausat"; }
  reprendre() { if (this.estat === "pausat") this.estat = "jugant"; }

  // Retorna true si el gir s'ha acceptat. Rebutja la direcció contrària i la repetida
  // comparant amb l'ÚLTIMA direcció (o l'última encuada), no amb l'actual: així dues
  // pulsacions seguides dins d'un mateix tick no poden fer un gir de 180º.
  canviarDireccio(nom) {
    if (!DIRECCIONS[nom] || this.estat === "acabat") return false;
    const ultima = this.cua.length ? this.cua[this.cua.length - 1] : this.direccio;
    if (nom === ultima || nom === OPOSADA[ultima]) return false;
    if (this.cua.length >= 2) return false;
    this.cua.push(nom);
    return true;
  }

  // Avança un pas. Retorna { tipus: "cap" | "moviment" | "poma" | "fi", causa? }
  pas() {
    if (this.estat !== "jugant") return { tipus: "cap" };

    this.anterior = this.serp.map((s) => ({ ...s }));
    if (this.cua.length) this.direccio = this.cua.shift();

    const d = DIRECCIONS[this.direccio];
    const cap = { x: this.serp[0].x + d.x, y: this.serp[0].y + d.y };

    if (cap.x < 0 || cap.y < 0 || cap.x >= this.columnes || cap.y >= this.files) {
      return this._acabar("paret");
    }

    const menja = !!this.poma && cap.x === this.poma.x && cap.y === this.poma.y;
    // Si no menja, la cua es mou i la seva cel·la queda lliure en aquest pas
    const cos = menja ? this.serp : this.serp.slice(0, -1);
    if (cos.some((s) => s.x === cap.x && s.y === cap.y)) {
      return this._acabar("cos");
    }

    this.serp.unshift(cap);
    if (!menja) {
      this.serp.pop();
      return { tipus: "moviment" };
    }

    this.pomes++;
    this.puntuacio += this.puntsPerPoma;
    this.poma = this._novaPoma();
    if (!this.poma) return this._acabar("completat"); // tauler ple: guanya
    return { tipus: "poma" };
  }

  _acabar(causa) {
    this.anterior = this.serp.map((s) => ({ ...s })); // sense interpolació en morir
    this.estat = "acabat";
    this.causaFi = causa;
    return { tipus: "fi", causa };
  }

  // Tria una cel·la entre les LLIURES (mai dins de la serp)
  _novaPoma() {
    const ocupades = new Set(this.serp.map((s) => s.x + "," + s.y));
    const lliures = [];
    for (let y = 0; y < this.files; y++) {
      for (let x = 0; x < this.columnes; x++) {
        if (!ocupades.has(x + "," + y)) lliures.push({ x, y });
      }
    }
    if (lliures.length === 0) return null;
    return lliures[Math.floor(this.rng() * lliures.length)];
  }
}
