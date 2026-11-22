<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="jocs-container" id="contenidor-ranking"></div>

<script type="module">
const resposta = await window.appFetch(location.href);
window.renderLayout(resposta.meta);
window.renderFlash(resposta.meta);
if (window.mostraError(resposta)) throw new Error(resposta.data.error);

const llista = Object.values(resposta.data.ranking || {});
const podi = llista.slice(0, 3);
const maxScore = llista.length ? Number(llista[0].puntuacio_total || 0) : 0;
const avatarHtml = (r, cls) => {
  const inicial = String(r.nom_usuari || '').slice(0, 1);
  if (r.avatar) {
    return `<img class="${cls}" src="../../${r.avatar}" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">`
      + `<span class="${cls} rank-fallback">${inicial}</span>`;
  }
  return `<span class="${cls} rank-fallback rank-fallback-on">${inicial}</span>`;
};

let fila = 0;
document.getElementById('contenidor-ranking').innerHTML = `
    <h1>Ranking</h1>
    <p class="rank-subtitol">Els millors jugadors de la plataforma segons la puntuació total.</p>

    ${podi.length ? `
      <div class="podi">
        ${podi.map((p, i) => `
          <div class="podi-card podi-${i + 1}">
            <span class="rank-pos rank-pos-${i + 1}">${i + 1}</span>
            <div class="podi-avatar-wrap">${avatarHtml(p, 'podi-avatar')}</div>
            <div class="podi-nom">${p.nom_usuari}</div>
            <div class="podi-real">${p.nom_complet}</div>
            <div class="podi-score">${p.puntuacio_total ?? '0'} <small>punts</small></div>
          </div>`).join('')}
      </div>` : ''}

    <div class="table-scroll">
      <table class="ranking-table">
        <thead>
          <tr><th class="col-pos">#</th><th>Jugador</th><th class="col-nom-complet">Nom complet</th><th class="col-score">Puntuació</th></tr>
        </thead>
        <tbody>
          ${llista.map(r => {
            fila++;
            const score = Number(r.puntuacio_total || 0);
            const pct = maxScore > 0 ? Math.round(score / maxScore * 100) : 0;
            let classes = 'rank-row';
            if (fila <= 3) classes += ' rank-top-' + fila;
            if (r.nom_usuari === resposta.meta.nomUsuari) classes += ' rank-yo';
            return `
              <tr class="${classes}">
                <td class="col-pos"><span class="rank-pos ${fila <= 3 ? 'rank-pos-' + fila : ''}">${fila}</span></td>
                <td><div class="rank-user">${avatarHtml(r, 'rank-avatar')}<span class="rank-nom">${r.nom_usuari}</span></div></td>
                <td class="col-nom-complet">${r.nom_complet}</td>
                <td class="col-score"><span class="score-num">${r.puntuacio_total ?? '0'}</span><div class="score-bar"><span style="width: ${pct}%"></span></div></td>
              </tr>`;
          }).join('')}
        </tbody>
      </table>
    </div>`;
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
