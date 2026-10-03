// --------- Classe Base ---------
class Entitat {
  constructor(posicio = { x: 0, y: 0 }, ample = 50, alt = 50) {
    this.x = posicio.x;
    this.y = posicio.y;
    this.ample = ample;
    this.alt = alt;
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
}

class Jugador extends Entitat {
  // creas una classe a a partir de una clase existente (Hereda las funciones anteriores)
  moureAmunt() {
    if (this.y > 0) {
      //limit superior
      //funcio moureamunt (inventat)
      let newY = this.y - 10; // Calcula la nova posició de Y per pujar l'element amunt.
      // Propietat top negativa puja l'element amunt del DOM.
      this.y = newY; // Sobreescriu la posició de Y amb la posició calculada anteriorment.
      this.elementHTML.style.top = this.y + "px"; //el element fa que mogui amunt
    }
  }

  moureAvall(pantallaHeight) {
    // limit inferior
    if (this.y < pantallaHeight - this.alt) {
      let newY = this.y + 10;
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
    super(posicio, ample, alt);
    this.velocitat = velocitat;
    this.punts = punts;
    this.pantallaWidth = pantallaWidth;
    this.pantallaHeight = pantallaHeight;
  }

  moure() {
    setInterval(() => {
      this.x -= this.velocitat;
      this.elementHTML.style.left = this.x + "px";
      if (this.x < -(this.ample)) {
        this.elementHTML.remove();
      }
    }, 200);
  }
}
