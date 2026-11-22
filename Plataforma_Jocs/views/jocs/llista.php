<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="jocs-container">
    <h2>Jocs Disponibles</h2>
    <div class="jocs-grid" id="jocs-grid"></div>
</div>

<script type="module">
const resposta = await window.appFetch(location.href);
window.renderLayout(resposta.meta);
window.renderFlash(resposta.meta);
if (window.mostraError(resposta)) throw new Error(resposta.data.error);

document.getElementById('jocs-grid').innerHTML = resposta.data.jocs.map(j => `
  <div class="joc-card">
    <!-- VULNERABILITAT: XSS -->
    <h3>${j.nom_joc}</h3>
    <p>${j.descripcio}</p>
    <p>Puntuació màxima: ${j.puntuacio_maxima}</p>
    ${resposta.meta.loggedIn
      ? `<a href="/mvc/?route=/jugar&id=${j.id}" class="btn-jugar">Jugar</a>`
      : '<p><a href="/mvc/?route=/login">Inicia sessió per jugar</a></p>'}
  </div>`).join('');

if (resposta.meta.redirect) location.href = resposta.meta.redirect;
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
