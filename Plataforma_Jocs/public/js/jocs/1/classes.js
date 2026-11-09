// --------- Classe Base ---------
class Entitat {
  constructor(posicio = { x: 0, y: 0 }, ample = 50, alt = 50, velocitat = 1, fotogrames = 10) {
    this.x = posicio.x;
    this.y = posicio.y;
    this.ample = ample;
    this.alt = alt;
    this.velocitat = velocitat;
    this.fotogrames = fotogrames;
    //Crear l'element HTML
    this.elementHTML = document.createElement("div");
    this.elementHTML.style.left = this.x + "px";
    this.elementHTML.style.top = this.y + "px";
    this.elementHTML.style.width = this.ample + "px";
    this.elementHTML.style.height = this.alt + "px";
  }

  // Modifica la posició de l'element a la pantalla
  dibuixar() {
    this.elementHTML.style.left = this.x + "px";
    this.elementHTML.style.top = this.y + "px";
  }

  esFora() {
    return this.x < -this.ample;
  }

  /**
   * Col·lisiona amb una altre entitat si:
   *
   * - La vora esquerra (this.x, recordem que modifica la propietat CSS left)
   * està més a l'esquerra que la vora dreta de l'altra entitat.
   *
   * - La vora dreta està més a la dreta que la vora esquerra de l'altra entitat.
   *
   * - La vora superior està més a dalt que la vora inferior de l'altra entitat.
   *
   * - La vora inferior està més avall que la vora superior de l'altra entitat
   */
  collisiona(altraEntitat) {
    return (
      this.x < altraEntitat.x + altraEntitat.ample &&
      this.x + this.ample > altraEntitat.x &&
      this.y < altraEntitat.y + altraEntitat.alt &&
      this.y + this.alt > altraEntitat.y
    );
  }
}

class Jugador extends Entitat {
  constructor(
    posicio = { x: 100, y: 300 },
    ample = 150,
    alt = 100,
    velocitat = 1,
    vides = 3,
    punts = 0,
    kills = 0,
  ) {
    super(posicio, ample, alt, velocitat);
    this.vides = vides;
    this.punts = punts;
    this.kills = kills;
  }
  // creas una classe a a partir de una clase existente (Hereda las funciones anteriores)
  moureAmunt() {
    if (this.y > 0) {
      //limit superior
      //funcio moureamunt (inventat)
      let newY = this.y - this.velocitat; // Calcula la nova posició de Y per pujar l'element amunt.
      // Propietat top negativa puja l'element amunt del DOM.
      this.y = newY; // Sobreescriu la posició de Y amb la posició calculada anteriorment.
      this.elementHTML.style.top = this.y + "px"; //el element fa que mogui amunt
    }
  }

  moureAvall(pantallaHeight) {
    // limit inferior
    if (this.y < pantallaHeight - this.alt) {
      let newY = this.y + this.velocitat;
      this.y = newY;
      this.elementHTML.style.top = this.y + "px"; // el element fa que es mogui avall
    }
  }
}

class Enemic extends Entitat {
  constructor(
    posicio = { x: 500, y: 200 },
    ample = 50,
    alt = 50,
    velocitat = 1,
    punts = 1,
    pantallaWidth,
    pantallaHeight,
    fotogrames = 10,
  ) {
    super(posicio, ample, alt, velocitat, fotogrames);
    this.punts = punts;
    this.pantallaWidth = pantallaWidth;
    this.pantallaHeight = pantallaHeight;
  }

  moure(accioDesaparicio) {
    // El paràmetre serà una funció de callback que executarà quan l'enemic desapareixi de la pantalla.
    this.interval = setInterval(() => {
      // Assigna una id propia a l'interval executat per cada enemic.
      this.x -= this.velocitat;
      this.elementHTML.style.left = this.x + "px";
      if (this.esFora()) {
        this.destruir();
        accioDesaparicio?.(); // Executa l'acció de desaparició, treu vides al jugador.
      }
    }, this.fotogrames);
  }

  destruir() {
    clearInterval(this.interval); // Netejem l'interval per evitar eliminar el div i mantenir intervals actius.
    // Ens servirà també per notificar quan el jugador perd una vida.
    this.elementHTML.remove();
  }
}

class Asteroide extends Entitat {
  constructor(
    posicio = { x: 500, y: 200 },
    ample = 50,
    alt = 50,
    velocitat = 1,
    pantallaWidth,
    fotogrames = 10,
  ) {
    super(posicio, ample, alt, velocitat, fotogrames);
    this.pantallaWidth = pantallaWidth;
  }

  moure() {
    setInterval(() => {
      this.x -= this.velocitat;
      this.elementHTML.style.left = this.x + "px";
      if (this.esFora()) {
        this.x = this.pantallaWidth;
        this.elementHTML.style.left = this.x + "px";
      }
    }, this.fotogrames);
  }
}
