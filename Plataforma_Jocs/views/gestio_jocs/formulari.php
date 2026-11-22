<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="auth-container auth-ample" id="contenidor-formulari"></div>

<script type="module">
const resposta = await window.appFetch(location.href);
window.renderLayout(resposta.meta);
window.renderFlash(resposta.meta);
if (window.mostraError(resposta)) throw new Error(resposta.data.error);

const joc = resposta.data.joc || {};
const edicio = !!joc.id;
const h = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const error = resposta.data.error || '';
const action = '/mvc/?route=/gestio-jocs/' + (edicio ? 'actualitzar' : 'crear');

document.getElementById('contenidor-formulari').innerHTML = `
    <h2>${edicio ? 'Editar joc' : 'Publicar un joc nou'}</h2>
    ${error ? `<div class="error">${h(error)}</div>` : ''}

    <form method="POST" enctype="multipart/form-data" action="${action}" data-json>
        ${edicio ? `<input type="hidden" name="id" value="${Number(joc.id)}">` : ''}
        <div class="form-group">
            <label for="nom_joc">Nom del joc:</label>
            <input type="text" id="nom_joc" name="nom_joc" maxlength="50" required value="${h(joc.nom_joc || '')}">
        </div>
        <div class="form-group">
            <label for="descripcio">Descripció:</label>
            <textarea id="descripcio" name="descripcio" rows="3">${h(joc.descripcio || '')}</textarea>
        </div>
        <div class="form-group">
            <label for="puntuacio_maxima">Puntuació màxima:</label>
            <input type="text" id="puntuacio_maxima" name="puntuacio_maxima" value="${Number(joc.puntuacio_maxima || 0)}">
        </div>
        <div class="form-group">
            <label for="nivells_totals">Nivells totals:</label>
            <input type="text" id="nivells_totals" name="nivells_totals" value="${Number(joc.nivells_totals || 1)}">
        </div>
        <div class="form-group">
            <label for="zip">Fitxer ZIP del joc (ha de contenir un index.html)${edicio ? ' — deixa-ho buit per no canviar-lo' : ''}:</label>
            <input type="file" id="zip" name="zip" accept=".zip" ${edicio ? '' : 'required'}>
        </div>
        <div class="form-group">
            <input type="checkbox" id="actiu" name="actiu" value="1" ${!joc.actiu && joc.actiu !== undefined ? '' : 'checked'}>
            <label for="actiu" class="label-inline">Joc visible a la plataforma</label>
        </div>
        <button type="submit">${edicio ? 'Desar canvis' : 'Publicar joc'}</button>
        <a class="btn-secundari" href="/mvc/?route=/gestio-jocs">Cancel·lar</a>
    </form>`;

document.querySelectorAll('form[data-json]').forEach(form => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const resultat = await window.appForm(form);
    if (!resultat.ok) {
      const dades = resultat.data || {};
      document.getElementById('contenidor-formulari').querySelector('.error')?.remove();
      const missatge = document.createElement('div');
      missatge.className = 'error';
      missatge.textContent = dades.error || 'Hi ha hagut un error';
      document.getElementById('contenidor-formulari').prepend(missatge);
      if (dades.joc) {
        for (const [name, value] of Object.entries(dades.joc)) {
          const camp = form.elements[name];
          if (camp && camp.type !== 'file' && camp.type !== 'checkbox') camp.value = value;
        }
      }
      return;
    }
    if (resultat.meta.redirect) location.href = resultat.meta.redirect;
  });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
