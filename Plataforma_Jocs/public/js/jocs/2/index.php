<?php
$jocId = $jocId ?? 2;
$nivellActual = $nivellActual ?? 1;
$totalNivells = $totalNivells ?? 1;
$nomJugador = $nomJugador ?? 'Jowron';
?>
<!DOCTYPE html>
<html lang="ca">
  <head>
    <meta charset="UTF-8" />
    <meta name="game-joc-id" content="<?= (int) $jocId ?>">
    <meta name="game-nivell" content="<?= (int) $nivellActual ?>">
    <meta name="game-total-nivells" content="<?= (int) $totalNivells ?>">
    <meta name="game-player-name" content="<?= htmlspecialchars($nomJugador, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cursa de Cavalls - Manipulació del DOM</title>
    <?php if (!empty($gameBaseUrl)): ?>
      <base href="<?= $gameBaseUrl ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="./index.css" />
  </head>
  <body>
    <header id="panell-superior">
      <h1>Cursa de Cavalls</h1>
      <p>Prem el botó per avançar un torn. Es generarà un número aleatori entre 1 i 5 i s'afegirà una caixa a la columna del cavall corresponent. El primer que obtingui 5 caixes guanya la cursa.</p>

      <div id="formulari-apostador">
        <h2>Per quin cavall vols apostar?</h2>
        <select name="cavall" id="cavall">
          <option value="1">Cavall 1</option>
          <option value="2">Cavall 2</option>
          <option value="3">Cavall 3</option>
          <option value="4">Cavall 4</option>
          <option value="5">Cavall 5</option>
        </select>
      </div>

      <div id="controls">
        <button id="boto-avancar">Avançar torn</button>
        <button id="boto-reiniciar">Reiniciar cursa</button>
      </div>

      <!-- Zona de notificacions d'estat (vulnerable a injecció mitjançant innerHTML) -->
      <div id="missatge-estat">Cursa a punt per començar. Introdueix el teu nom i prem "Avançar torn".</div>
    </header>

    <main id="pista-carrera">
      <!-- Línia d'arribada -->
      <div id="linia-meta">META (5 posicions)</div>

      <!-- Les 5 columnes que representen els carrils dels cavalls -->
      <div id="carrils-contenidor">
        <div class="carrer" id="carrer-1" data-cavall="1">
          <div class="zona-avanc"></div>
          <div class="etiqueta-cavall">Cavall 1</div>
        </div>

        <div class="carrer" id="carrer-2" data-cavall="2">
          <div class="zona-avanc"></div>
          <div class="etiqueta-cavall">Cavall 2</div>
        </div>

        <div class="carrer" id="carrer-3" data-cavall="3">
          <div class="zona-avanc"></div>
          <div class="etiqueta-cavall">Cavall 3</div>
        </div>

        <div class="carrer" id="carrer-4" data-cavall="4">
          <div class="zona-avanc"></div>
          <div class="etiqueta-cavall">Cavall 4</div>
        </div>

        <div class="carrer" id="carrer-5" data-cavall="5">
          <div class="zona-avanc"></div>
          <div class="etiqueta-cavall">Cavall 5</div>
        </div>
      </div>
    </main>

    <script src="./main.js" type="module"></script>
  </body>
</html>
