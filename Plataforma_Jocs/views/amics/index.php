<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="perfil-container" id="contenidor-amics"></div>

<script type="module">
const resposta = await window.appFetch(location.href);
window.renderLayout(resposta.meta);
window.renderFlash(resposta.meta);
if (window.mostraError(resposta)) throw new Error(resposta.data.error);

const d = resposta.data;
const h = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const boto = (accio, id, text, classe = '') =>
  `<form method="POST" action="/mvc/?route=${accio}" class="form-inline" data-json>
     <input type="hidden" name="id" value="${Number(id)}">
     <button type="submit" class="btn-petit ${classe}">${h(text)}</button>
   </form>`;
const estat = (relacio) => {
  if (!relacio) return '—';
  if (relacio.estat === 'acceptada') return '<span class="badge badge-ok">Amics</span>';
  if (relacio.estat === 'pendent') return '<span class="badge badge-pend">Pendent</span>';
  return '<span class="badge badge-off">Rebutjada</span>';
};

document.getElementById('contenidor-amics').innerHTML = `
    <h1>Amics</h1>

    <section class="perfil-card">
        <h2>Cercar usuaris</h2>
        <form method="GET" action="/mvc/" class="form-cerca">
            <input type="hidden" name="route" value="/amics">
            <input type="text" name="q" placeholder="Nom d'usuari o nom complet" value="${h(d.cerca)}">
            <button type="submit">Cercar</button>
        </form>

        ${d.cerca !== '' ? `
          <table>
            <thead><tr><th>Usuari</th><th>Nom complet</th><th>Estat</th><th></th></tr></thead>
            <tbody>
              ${d.resultats.length ? d.resultats.map(r => `
                <tr>
                  <td><a href="/mvc/?route=/usuari&id=${Number(r.id)}">${h(r.nom_usuari)}</a></td>
                  <td>${h(r.nom_complet)}</td>
                  <td>${estat(r.relacio)}</td>
                  <td>${!r.relacio || r.relacio.estat === 'rebutjada'
                    ? `<form method="POST" action="/mvc/?route=/amics/solicitar" class="form-inline" data-json>
                         <input type="hidden" name="receptor_id" value="${Number(r.id)}">
                         <button type="submit" class="btn-petit">Afegir amic</button>
                       </form>` : ''}</td>
                </tr>`).join('') : '<tr><td colspan="4" class="sense-partides">Cap usuari trobat.</td></tr>'}
            </tbody>
          </table>` : ''}
    </section>

    <section class="perfil-card">
        <h2>Sol·licituds rebudes (${d.rebudes.length})</h2>
        ${d.rebudes.length ? d.rebudes.map(r => `
          <div class="fila-usuari">
            <a href="/mvc/?route=/usuari&id=${Number(r.id)}">${h(r.nom_usuari)}</a>
            <span class="text-muted">${h(r.data_solicitud)} · sol·licitud #${Number(r.amistat_id)}</span>
            <span class="accions">
              <form method="POST" action="/mvc/?route=/amics/respondre" class="form-inline" data-json>
                <input type="hidden" name="id" value="${Number(r.amistat_id)}">
                <input type="hidden" name="accio" value="acceptar">
                <button type="submit" class="btn-petit">Acceptar</button>
              </form>
              <form method="POST" action="/mvc/?route=/amics/respondre" class="form-inline" data-json>
                <input type="hidden" name="id" value="${Number(r.amistat_id)}">
                <input type="hidden" name="accio" value="rebutjar">
                <button type="submit" class="btn-petit btn-perill">Rebutjar</button>
              </form>
            </span>
          </div>`).join('') : '<p class="text-muted">No tens sol·licituds pendents.</p>'}
    </section>

    <section class="perfil-card">
        <h2>Sol·licituds enviades</h2>
        ${d.enviades.length ? d.enviades.map(r => `
          <div class="fila-usuari">
            <a href="/mvc/?route=/usuari&id=${Number(r.id)}">${h(r.nom_usuari)}</a>
            <span class="badge ${r.estat === 'pendent' ? 'badge-pend' : 'badge-off'}">${r.estat === 'pendent' ? 'Pendent' : 'Rebutjada'}</span>
          </div>`).join('') : '<p class="text-muted">No has enviat cap sol·licitud pendent.</p>'}
    </section>

    <section class="perfil-card">
        <h2>Els meus amics (${d.amics.length})</h2>
        ${d.amics.length ? d.amics.map(a => `
          <div class="fila-usuari">
            <a href="/mvc/?route=/usuari&id=${Number(a.id)}">${h(a.nom_usuari)}</a>
            <span class="text-muted">${h(a.nom_complet)} · amistat #${Number(a.amistat_id)}</span>
            <span class="accions">
              <a class="btn-petit" href="/mvc/?route=/estadistiques&usuari_id=${Number(a.id)}">Estadístiques</a>
              ${boto('/amics/eliminar', a.amistat_id, 'Eliminar', 'btn-perill')}
            </span>
          </div>`).join('') : '<p class="text-muted">Encara no tens amics. Cerca usuaris per afegir-ne.</p>'}
    </section>`;

document.querySelectorAll('form[data-json]').forEach(form => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = Object.fromEntries(new FormData(form));
    const resultat = await window.appPost(form.action, body);
    if (window.mostraError(resultat)) return;
    window.renderFlash(resultat.meta);
    if (resultat.meta.redirect) location.href = resultat.meta.redirect;
  });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
