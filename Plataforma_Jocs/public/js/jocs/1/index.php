<?php
$jocId = $jocId ?? 1;
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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Nau vs Ovnis</title>
    <meta name="description" content="Joc JS per treballar la manipulació del DOM, la gestió d'esdeveniments i la POO." />
    <meta name="author" content="Xavi Garcia @xavig-icv" />
    <meta name="copyright" content="Xavi Garcia @xavig-icv" />
    <?php if (!empty($gameBaseUrl)): ?>
      <base href="<?= $gameBaseUrl ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="./index.css" type="text/css"/>
  </head>
  <body>
    <div id="pantalla"></div>
    <div id="infoPartida"></div>
    <script src="./classes.js" type="text/javascript"></script>
    <script src="./main.js" type="module"></script>
  </body>
</html>