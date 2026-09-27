---
tipus: exercici
codi: "LAB-02"
titol: "Interactivitat del DOM i POO pel Videojoc en JavaScript"
bloc: "01_Desenvolupament_Web"
ra:
  - "RA1"
dificultat: "Mitjana"
entorn_requerit: "Navegador Web + VS Code"
avaluable: true
pes_nota: "4%"
temps_estimat_min: 120
tags:
  - docencia/exercici
  - ra/ra1
  - stack/js
---

# 🧪 LAB-02: Interactivitat del DOM i POO pel Videojoc en JavaScript

> **Codi**: `LAB-02` | **Mòdul**: 5023 | **Durada**: 120 minuts | **Qualificació**: Avaluable (4%)

---

## 🎯 Objectius
* Crear dinàmicament elements visuals a la pàgina mitjançant el DOM de JavaScript.
* Gestionar esdeveniments de teclat per controlar el moviment d'un personatge interactiu.
* Estructurar el codi seguint els principis de la Programació Orientada a Objectes (classes ES6).

---

## 📋 Tasques Pas a Pas

### Tasca 1: Estructura Base de l'Escenari
Crea una carpeta `joc/` a `/var/www/html/` amb tres fitxers: `index.html`, `estils.css` i `app.js`.
* L'escenari ha de ser un contenidor de 800 × 600 px amb posició relativa i desbordament ocult (`overflow: hidden`).

### Tasca 2: Classe `Nau`
Dins d'un fitxer `classes.js`, defineix la classe `Nau`:
* Constructor que rebi la posició inicial (x, y).
* Mètode `dibuixar()` que creï o actualitzi un element `div` al DOM.
* Mètodes `moureAmunt()` i `moureAvall()` limitant el desplaçament perquè la nau no surti dels marges de l'escenari.

### Tasca 3: Gestió d'Esdeveniments de Teclat
A `app.js`, subscriu-te als esdeveniments `keydown` per controlar la nau:
```javascript
window.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowUp') nau.moureAmunt();
    if (e.key === 'ArrowDown') nau.moureAvall();
});
```

### Tasca 4: Generació d'Enemics i Configuració JSON
1. Defineix una classe `Enemic` que aparegui per la dreta de l'escenari i es mogui automàticament cap a l'esquerra.
2. Crea un objecte JSON amb la configuració de la partida:
```json
{
  "nivell": 1,
  "velocitatEnemics": 3,
  "intervalAparicioMs": 1500,
  "puntsPerEnemic": 100
}
```
3. Inicialitza la partida llegint aquests paràmetres.

---

## 📤 Evidències a Lliurar
1. Enllaç al teu repositori personal de GitHub amb el codi complet i comentat.
2. Petit vídeo en format GIF o MP4 (màx. 15 segons) mostrant la nau movent-se i els enemics apareixent.
