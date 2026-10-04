// --------- Classe Base ---------
class Entitat {
  constructor(posicio = { x: 0, y: 0 }, ample = 50, alt = 50, velocitat = 1) {
    this.x = posicio.x;
    this.y = posicio.y;
    this.ample = ample;
    this.alt = alt;
    this.velocitat = velocitat;
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
    return this.x < -(this.ample);
  }
}

class Jugador extends Entitat {
  constructor(
    posicio = { x: 100, y: 300 },
    ample = 150,
    alt = 100,
    velocitat = 1,
    vides = 3,
  ) {
    super(posicio, ample, alt, velocitat);
    this.vides = vides;
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
  ) {
    super(posicio, ample, alt, velocitat);
    this.punts = punts;
    this.pantallaWidth = pantallaWidth;
    this.pantallaHeight = pantallaHeight;
  }

  moure(accioDesaparicio) { // El paràmetre serà una funció de callback que executarà quan l'enemic desapareixi de la pantalla.
    this.interval = setInterval(() => { // Assigna una id propia a l'interval executat per cada enemic.
      this.x -= this.velocitat;
      this.elementHTML.style.left = this.x + "px";
      if (this.esFora()) {
        clearInterval(this.interval); // Netejem l'interval per evitar eliminar el div i mantenir intervals actius.
        // Ens servirà també per notificar quan el jugador perd una vida.
        this.elementHTML.remove();
        accioDesaparicio?.(); // Executa l'acció de desaparició, treu vides al jugador.
      }
    }, 100);
  }
}

class Asteroide extends Entitat {
  constructor(
    posicio = { x: 500, y: 200 },
    ample = 50,
    alt = 50,
    velocitat = 1,
    pantallaWidth
  ) {
    super(posicio, ample, alt, velocitat);
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
    }, 100);
  }
}
