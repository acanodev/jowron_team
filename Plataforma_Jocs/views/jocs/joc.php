<?php include __DIR__ . '/../layout/header.php'; ?>

<div id="contenidor-joc"></div>

<script type="module">
const resposta = await window.appFetch(location.href);
window.renderLayout(resposta.meta);
window.renderFlash(resposta.meta);
if (window.mostraError(resposta)) throw new Error(resposta.data.error);

const dades = resposta.data;
const id = new URLSearchParams(location.search).get('id');
const cont = document.getElementById('contenidor-joc');
const titol = dades.joc.nom_joc;
const descripcio = dades.joc.descripcio || '';
const src = dades.esJocHtml
  ? dades.urlJoc
  : dades.gameBaseUrl + 'index.php?joc_id=' + encodeURIComponent(id || dades.joc.id);

cont.innerHTML = `
  <div class="jocs-container">
    <h2>${titol}</h2>
    <p class="text-muted">${descripcio}</p>
    <iframe class="joc-frame" src="${src}" title="${titol}" allowfullscreen></iframe>
    <p>
      <a class="btn-secundari" href="${src}" target="_blank" rel="noopener">Obrir en una pestanya nova</a>
      <a class="btn-secundari" href="/mvc/?route=/jocs">Tornar als jocs</a>
    </p>
  </div>`;
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
