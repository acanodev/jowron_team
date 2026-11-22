<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="perfil-container" id="contenidor-perfil"></div>

<script type="module">
const resposta = await window.appFetch(location.href);
window.renderLayout(resposta.meta);
window.renderFlash(resposta.meta);
if (window.mostraError(resposta)) throw new Error(resposta.data.error);

const u = resposta.data.usuari;
const partides = resposta.data.partides || [];
const badges = resposta.data.badges || [];
const totalPartides = partides.length;
const millorPuntuacio = totalPartides ? Math.max(...partides.map(p => Number(p.puntuacio_obtinguda) || 0)) : 0;
const avatar = u.avatar
  ? `<img class="perfil-avatar" src="../../${u.avatar}" alt="Imatge de perfil">`
  : `<div class="avatar-placeholder">${String(u.nom_usuari || '').slice(0, 1)}</div>`;

document.getElementById('contenidor-perfil').innerHTML = `
    <h1>El meu perfil</h1>

    <section class="perfil-card perfil-card-hero">
        <h2>Dades personals</h2>
        <div class="perfil-banner"></div>
        <div class="perfil-hero-body">
            <div class="avatar-container">${avatar}</div>
            <div class="perfil-info">
                <h2 class="perfil-nom">${u.nom_usuari}</h2>
                <p class="perfil-real">${u.nom_complet}</p>
                <div class="dades-usuari">
                    <div class="dada"><span class="dada-label">Nom d'usuari</span><span class="dada-valor">${u.nom_usuari}</span></div>
                    <div class="dada"><span class="dada-label">Nom complet</span><span class="dada-valor">${u.nom_complet}</span></div>
                    <div class="dada"><span class="dada-label">Correu electrònic</span><span class="dada-valor">${u.email}</span></div>
                    <div class="dada"><span class="dada-label">Data de registre</span><span class="dada-valor">${u.data_registre}</span></div>
                </div>
            </div>
            <div class="perfil-stats">
                <div class="stat"><span class="stat-num">${totalPartides}</span><span class="stat-txt">Partides</span></div>
                <div class="stat"><span class="stat-num">${millorPuntuacio}</span><span class="stat-txt">Millor punt.</span></div>
            </div>
        </div>
    </section>

    <section class="perfil-card">
        <h2>Actualitzar perfil</h2>
        <form class="perfil-form" method="POST" action="/mvc/?route=/actualitzar-perfil" enctype="multipart/form-data" data-json>
            <label for="nom_complet">Nom complet</label>
            <input type="text" id="nom_complet" name="nom_complet" value="${u.nom_complet || ''}">
            <label for="email">Correu electrònic</label>
            <input type="text" id="email" name="email" value="${u.email || ''}">
            <label for="avatar">Imatge de perfil</label>
            <input type="file" id="avatar" name="avatar">
            <button type="submit">Desar canvis</button>
        </form>
    </section>

    <section class="perfil-card">
        <h2>Assoliments (${badges.length})</h2>
        ${badges.length ? `
          <div class="insignies">
            ${badges.map(b => `
              <div class="insignia insignia-ok" title="${b.descripcio}">
                <span class="insignia-icona">${b.icona}</span>
                <span class="insignia-nom">${b.nom}</span>
              </div>`).join('')}
          </div>` : '<p class="text-muted">Encara no tens cap assoliment. Juga una partida per començar a desbloquejar-ne!</p>'}
        <p><a class="btn-petit" href="/mvc/?route=/badges">Veure tots els assoliments</a></p>
    </section>

    <section class="perfil-card">
        <h2>Historial de partides</h2>
        <table class="taula-partides">
            <thead><tr><th>Joc</th><th>Puntuació</th><th>Data de la partida</th></tr></thead>
            <tbody>
              ${partides.length
                ? partides.map(p => `<tr><td>${p.nom_joc}</td><td>${p.puntuacio_obtinguda}</td><td>${p.data_partida}</td></tr>`).join('')
                : '<tr><td colspan="4" class="sense-partides">Encara no tens cap partida registrada.</td></tr>'}
            </tbody>
        </table>
    </section>`;

document.querySelectorAll('form[data-json]').forEach(form => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const resultat = await window.appForm(form);
    if (window.mostraError(resultat)) return;
    if (resultat.meta.redirect) location.href = resultat.meta.redirect;
  });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
