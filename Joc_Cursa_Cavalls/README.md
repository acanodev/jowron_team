# Pràctica: Cursa de Cavalls amb Manipulació del DOM

Aquest exercici proposa la creació d'un joc senzill basat en la generació d'elements HTML dinàmics, la gestió d'esdeveniments de ratolí (`click`) i la interacció amb camps de formulari.

---

## 1. Objectiu de la pràctica

L'objectiu és implementar la lògica necessària perquè, en prémer un botó:
1. S'esculli un cavall aleatòriament (de l'1 al 5).
2. S'afegeixi un element visual (una caixa) al seu carril corresponent.
3. S'actualitzi el panell d'estat superior amb el nom de l'apostador indicat al camp de text.
4. El primer cavall que obtingui 5 caixes guanyi la cursa, desactivant el botó i mostrant una alerta amb el guanyador.

Conceptes treballats:
- Selecció d'elements amb `document.querySelector`.
- Lectura de valors d'un camp de formulari mitjançant la propietat `value`.
- Creació de nous nodes de tipus `div` amb `document.createElement`.
- Assignació de classes CSS amb `classList.add`.
- Inserció de nodes a l'arbre del document amb `append`.
- Generació de nombres pseudoaleatoris en un rang amb `Math.random` i `Math.floor`.
- Control d'estat i condició de victòria mitjançant `alert` i desactivació del botó amb la propietat `disabled`.

---

## 2. Estructura dels fitxers

| Fitxer | Descripció |
| :--- | :--- |
| `index.html` | Conté l'estructura de la capçalera amb el formulari de l'apostador, els botons i les 5 columnes (carrils). |
| `index.css` | Defineix l'estil de les columnes, la línia de meta i les classes `.caixa-avanc` amb colors específics per a cada carril. |
| `main.js` | Conté l'esquelet del programa amb les funcions buides i comentaris pas a pas per implementar la lògica. |

---

## 3. Instruccions per a la resolució autònoma

Has d'editar el fitxer `main.js` i completar els dos blocs de codi indicats amb comentaris `TODO`.

### Bloc 1: Funció `afegirCaixa(numeroCavall)`
Aquesta funció rep com a argument un número del 1 al 5 i ha de reflectir l'avanç al DOM:
1. Obtenir la referència de la zona d'avanç del cavall corresponent mitjançant el selector `#carrer-${numeroCavall} .zona-avanc`.
2. Crear un nou element `div` amb `document.createElement("div")`.
3. Afegir la classe `caixa-avanc` al nou element amb `element.classList.add("caixa-avanc")`.
4. Assignar el text o posició de la caixa mitjançant `innerHTML`.
5. Inserir el node creat dins de la zona d'avanç amb `zona.append(element)`.

### Bloc 2: Esdeveniment de clic al botó `#boto-avancar`
Dins de la funció d'escolta del botó:
1. Comprovar si la variable `cursaFinalitzada` és certa. Si ho és, aturar l'execució amb `return`.
2. Generar un valor enter aleatori entre 1 i 5 utilitzant l'expressió:
   ```javascript
   const cavallAleatori = Math.floor(Math.random() * 5) + 1;
   ```
3. Incrementar el valor corresponent a l'array `posicionsCavalls` (tingues en compte que l'índex és `cavallAleatori - 1`).
4. Cridar la funció `afegirCaixa(cavallAleatori)` per actualitzar la interfície.
5. Actualitzar el contingut de l'element `#missatge-estat` fent servir `innerHTML`:
   ```javascript
   missatgeEstat.innerHTML = "Últim moviment: Cavall " + cavallAleatori + " (Apostador: " + inputApostador.value + ")";
   ```
6. Comprovar si el cavall ha assolit les 5 posicions:
   - Assignar `cursaFinalitzada = true`.
   - Desactivar el botó per impedir continuar la partida: `botoAvancar.disabled = true`.
   - Actualitzar el missatge final amb `innerHTML`.
   - Mostrar una alerta al navegador indicant el cavall guanyador: `alert("El Cavall " + cavallAleatori + " ha guanyat la cursa!")`.

---

## 4. Nota de Ciberseguretat: Vulnerabilitat DOM-based XSS (CWE-79)

En aquest exercici, la concatenació directa del valor d'un camp d'entrada d'usuari (`inputApostador.value`) dins de la propietat `innerHTML` de `#missatge-estat` introdueix una vulnerabilitat crítica de tipus **DOM-based Cross-Site Scripting (XSS)**.

Si un usuari introdueix com a nom d'apostador una càrrega útil basada en esdeveniments, com per exemple:
```html
<img src="error" onerror="alert('Vulnerabilitat XSS detectada en la cursa!')">
```
El navegador interpretarà aquesta cadena com a codi HTML ejecutable en prémer el botó d'avançar torn.

> **Important:** Aquesta vulnerabilitat s'ha dissenyat expressament d'aquesta manera. **No l'has de corregir en aquest tema**, ja que servirà com a cas d'estudi per a la pràctica d'auditoria de seguretat web del Tema 3.

---

## 5. Verificació del funcionament

1. Obre el fitxer `index.html` amb el navegador.
2. Escriu un nom al camp "Nom de l'apostador".
3. Prem repetidament el botó "Avançar torn".
4. Comprova que a cada clic apareix una caixa al carril del cavall seleccionat aleatòriament i que el missatge superior mostra el teu nom.
5. Quan un cavall acumuli 5 caixes, ha d'aparèixer l'alerta del navegador indicant quin cavall ha guanyat i el botó d'avançar ha de quedar desactivat.
6. Prem el botó "Reiniciar cursa" per verificar que el tauler es buida i la cursa es pot tornar a iniciar.
