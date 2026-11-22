<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="jocs-container" id="contenidor-gestio"></div>

<script type="module">
const resposta = await window.appFetch(location.href);
window.renderLayout(resposta.meta);
window.renderFlash(resposta.meta);
if (window.mostraError(resposta)) throw new Error(resposta.data.error);

const jocs = resposta.data.jocs || [];
const h = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

document.getElementById('contenidor-gestio').innerHTML = `
    <div class="titol-accions">
        <h2>Gestió de jocs</h2>
        <a class="btn-jugar" href="/mvc/?route=/gestio-jocs/nou">+ Publicar un joc nou</a>
    </div>
    <p class="text-muted">
        Rol actual: <strong>${resposta.meta.rol}</strong>
        ${resposta.meta.rol === 'YWRtaW4=' ? '(es mostren tots els jocs)' : '(es mostren només els teus jocs)'}
    </p>

    <div class="table-scroll">
        <table>
            <thead><tr><th>Id</th><th>Nom</th><th>Tipus</th><th>Developer</th><th>Nivells</th><th>Estat</th><th>Accions</th></tr></thead>
            <tbody>
              ${jocs.length ? jocs.map(j => `
                <tr>
                  <td>${Number(j.id)}</td>
                  <td>${h(j.nom_joc)}</td>
                  <td>${j.ruta_index ? 'ZIP (' + h(j.ruta_index) + ')' : 'Plataforma'}</td>
                  <td>${h(j.nom_developer || '—')}</td>
                  <td>${Number(j.nivells_totals)}</td>
                  <td><span class="badge ${j.actiu ? 'badge-ok' : 'badge-off'}">${j.actiu ? 'Actiu' : 'Inactiu'}</span></td>
                  <td class="accions">
                    <a class="btn-petit" href="/mvc/?route=/jugar&id=${Number(j.id)}">Jugar</a>
                    <a class="btn-petit" href="/mvc/?route=/gestio-jocs/editar&id=${Number(j.id)}">Editar</a>
                    <form method="POST" action="/mvc/?route=/gestio-jocs/eliminar" class="form-inline" data-json
                          onsubmit="return confirm('Eliminar aquest joc?');">
                      <input type="hidden" name="id" value="${Number(j.id)}">
                      <button type="submit" class="btn-petit btn-perill">Eliminar</button>
                    </form>
                  </td>
                </tr>`).join('') : '<tr><td colspan="7" class="sense-partides">Encara no has publicat cap joc.</td></tr>'}
            </tbody>
        </table>
    </div>`;

document.querySelectorAll('form[data-json]').forEach(form => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = Object.fromEntries(new FormData(form));
    const resultat = await window.appPost(form.action, body);
    if (window.mostraError(resultat)) return;
    if (resultat.meta.redirect) location.href = resultat.meta.redirect;
  });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
