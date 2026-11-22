<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="perfil-container" id="contenidor-perfil-public"></div>

<script type="module">
const resposta = await window.appFetch(location.href);
window.renderLayout(resposta.meta);
window.renderFlash(resposta.meta);
if (window.mostraError(resposta)) throw new Error(resposta.data.error);

const d = resposta.data;
const h = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const u = d.usuari;
const esMeu = Number(u.id) === Number(d.jo);
const avatar = u.avatar
  ? `<img class="perfil-avatar" src="../../${h(u.avatar)}" alt="Avatar">`
  : `<div class="avatar-placeholder">${h(String(u.nom_usuari || '').slice(0, 1))}</div>`;

let accio = '';
if (esMeu) {
  accio = '<a class="btn-petit" href="/mvc/?route=/perfil">El meu perfil</a>';
} else if (!d.relacio || d.relacio.estat === 'rebutjada') {
  accio = `<form method="POST" action="/mvc/?route=/amics/solicitar" class="form-inline" data-json>
             <input type="hidden" name="receptor_id" value="${Number(u.id)}">
             <input type="hidden" name="tornar" value="/usuari&id=${Number(u.id)}">
             <button type="submit" class="btn-petit">Afegir amic</button>
           </form>`;
} else if (d.relacio.estat === 'pendent') {
  accio = '<span class="badge badge-pend">Sol·licitud pendent</span>';
} else {
  accio = '<span class="badge badge-ok">Amics</span>';
}

document.getElementById('contenidor-perfil-public').innerHTML = `
    <section class="perfil-card perfil-card-hero">
        <h2>Perfil de ${h(u.nom_usuari)}</h2>
        <div class="perfil-banner"></div>
        <div class="perfil-hero-body">
            <div class="avatar-container">${avatar}</div>
            <div class="perfil-info">
                <h2 class="perfil-nom">${h(u.nom_usuari)}</h2>
                <p class="perfil-real">${h(u.nom_complet)}</p>
                <p class="text-muted">Membre des de ${h(u.data_registre)}</p>
                <div class="accions">
                    ${accio}
                    <a class="btn-petit" href="/mvc/?route=/estadistiques&usuari_id=${Number(u.id)}">Veure estadístiques</a>
                </div>
            </div>
            <div class="perfil-stats">
                <div class="stat"><span class="stat-num">${Number(d.resum.partides)}</span><span class="stat-txt">Partides</span></div>
                <div class="stat"><span class="stat-num">${Number(d.resum.puntuacio_total)}</span><span class="stat-txt">Punts</span></div>
            </div>
        </div>
    </section>

    <section class="perfil-card">
        <h2>Assoliments (${d.badges.length})</h2>
        ${d.badges.length ? `
          <div class="insignies">
            ${d.badges.map(b => `
              <div class="insignia insignia-ok" title="${h(b.descripcio)}">
                <span class="insignia-icona">${h(b.icona)}</span>
                <span class="insignia-nom">${h(b.nom)}</span>
              </div>`).join('')}
          </div>` : '<p class="text-muted">Encara no ha aconseguit cap assoliment.</p>'}
    </section>

    <section class="perfil-card">
        <h2>Últimes partides</h2>
        <table class="taula-partides">
            <thead><tr><th>Joc</th><th>Puntuació</th><th>Data</th></tr></thead>
            <tbody>
              ${d.partides.length
                ? d.partides.map(p => `<tr><td>${h(p.nom_joc)}</td><td>${Number(p.puntuacio_obtinguda)}</td><td>${h(p.data_partida)}</td></tr>`).join('')
                : '<tr><td colspan="3" class="sense-partides">Cap partida.</td></tr>'}
            </tbody>
        </table>
    </section>`;

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
