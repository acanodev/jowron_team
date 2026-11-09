<!DOCTYPE html>
<html lang="ca">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Joc interactiu HTML, CSS i JavaScript</title>
    <meta name="description" content="Joc JS per treballar la manipulació del DOM, la gestió d'esdeveniments i la POO." />
    <meta name="author" content="Xavi Garcia @xavig-icv" />
    <meta name="copyright" content="Xavi Garcia @xavig-icv" />
    <?php if (!empty($gameBaseUrl)): ?>
      <base href="<?php htmlspecialchars($gameBaseUrl) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="./index.css" type="text/css"/>
  </head>
  <body>
    <div id="pantalla"></div>
    <div id="infoPartida"></div>
    <script type="text/javascript" src="conf.js"></script>
    <script src="./classes.js" type="text/javascript"></script>
    <script src="./main.js" type="text/javascript"></script>
  </body>
</html>