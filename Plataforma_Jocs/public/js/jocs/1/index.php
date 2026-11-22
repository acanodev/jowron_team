<!DOCTYPE html>
<html lang="ca">
  <head>
    <meta charset="UTF-8" />
    <meta name="game-joc-id" content="">
    <meta name="game-nivell" content="">
    <meta name="game-total-nivells" content="">
    <meta name="game-player-name" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Nau vs Ovnis</title>
    <meta name="description" content="Joc JS per treballar la manipulació del DOM, la gestió d'esdeveniments i la POO." />
    <meta name="author" content="Xavi Garcia @xavig-icv" />
    <meta name="copyright" content="Xavi Garcia @xavig-icv" />
    <link rel="stylesheet" href="./index.css" type="text/css"/>
  </head>
  <body>
    <div id="pantalla"></div>
    <div id="infoPartida"></div>
    <script src="./classes.js" type="text/javascript"></script>
    <script type="module">
    const id = new URLSearchParams(location.search).get('joc_id') || '1';
    const url = new URL('../../../../?route=/jugar&id=' + encodeURIComponent(id), location.href);
    let dades = null;
    try {
      const res = await fetch(url.href, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
      dades = (await res.json()).data;
    } catch (e) { }

    document.querySelector('meta[name="game-joc-id"]').content = dades?.joc?.id || id;
    document.querySelector('meta[name="game-nivell"]').content = dades?.nivellActual || 1;
    document.querySelector('meta[name="game-total-nivells"]').content = dades?.totalNivells || 1;
    document.querySelector('meta[name="game-player-name"]').content = dades?.nomJugador || 'Jowron';

    const base = document.createElement('base');
    base.href = dades?.gameBaseUrl || new URL('./', location.href).href;
    document.head.prepend(base);

    await import('./main.js');
    </script>
  </body>
</html>
