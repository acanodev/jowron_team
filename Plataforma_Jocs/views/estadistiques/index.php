<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="perfil-container" id="contenidor-estadistiques"></div>

<script src="../../public/js/charts.js"></script>
<script type="module">
const resposta = await window.appFetch(location.href);
window.renderLayout(resposta.meta);
window.renderFlash(resposta.meta);
if (window.mostraError(resposta)) throw new Error(resposta.data.error);

const d = resposta.data;
const h = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const format = (segons) => {
  const s = Number(segons) || 0;
  return `${Math.floor(s / 3600)}h ${String(Math.floor((s % 3600) / 60)).padStart(2, '0')}m ${String(s % 60).padStart(2, '0')}s`;
};
const grafic = (id, tipus, dades) =>
  `<div class="grafic" id="${id}" data-tipus="${tipus}" data-dades="${h(JSON.stringify(dades || []))}"></div>`;

const resum = d.resum || {};
const historial = d.historial || [];
const ranking = d.ranking || [];
const comparativa = d.comparativa || [];

document.getElementById('contenidor-estadistiques').innerHTML = `
    <h1>Estadístiques${d.esMeu ? '' : ' de ' + h(d.usuari.nom_usuari)}</h1>
    ${d.esMeu ? '' : '<p class="text-muted">Estàs veient les estadístiques d\'un altre usuari. <a href="/mvc/?route=/estadistiques">Tornar a les meves</a></p>'}

    <section class="targetes-resum">
        <div class="targeta-num"><span>${Number(resum.partides)}</span>Partides jugades</div>
        <div class="targeta-num"><span>${Number(resum.victories)} / ${Number(resum.derrotes)}</span>Victòries / derrotes</div>
        <div class="targeta-num"><span>${Number(resum.puntuacio_total)}</span>Puntuació total</div>
        <div class="targeta-num"><span>${format(resum.temps_total)}</span>Temps de joc</div>
        <div class="targeta-num"><span>${Number(d.numBadges)}</span>Assoliments</div>
    </section>

    <div class="graelles-2">
        <section class="perfil-card">
            <h2>Victòries i derrotes</h2>
            ${grafic('grafic-vd', 'donut', d.datasets.victoriesDerrotes)}
        </section>
        <section class="perfil-card">
            <h2>Jocs més jugats</h2>
            ${grafic('grafic-jocs', 'barres', d.datasets.jocsMesJugats)}
        </section>
    </div>

    <section class="perfil-card">
        <h2>Evolució de les puntuacions</h2>
        ${grafic('grafic-evolucio', 'linia', d.datasets.evolucio)}
    </section>

    <div class="graelles-2">
        <section class="perfil-card">
            <h2>Ranking general (top 10)</h2>
            ${grafic('grafic-ranking', 'barres', d.datasets.ranking)}
            <table>
                <thead><tr><th>#</th><th>Jugador</th><th>Punts</th></tr></thead>
                <tbody>${ranking.map((r, i) => `<tr><td>${i + 1}</td><td>${h(r.nom_usuari)}</td><td>${Number(r.puntuacio_total)}</td></tr>`).join('')}</tbody>
            </table>
        </section>
        <section class="perfil-card">
            <h2>Comparativa amb amics</h2>
            ${grafic('grafic-comparativa', 'barres', d.datasets.comparativa)}
            <table>
                <thead><tr><th>Jugador</th><th>Partides</th><th>Victòries</th><th>Punts</th><th>Temps</th></tr></thead>
                <tbody>${comparativa.map(r => `<tr>
                    <td>${h(r.nom_usuari)}</td><td>${Number(r.partides)}</td><td>${Number(r.victories)}</td>
                    <td>${Number(r.puntuacio_total)}</td><td>${format(r.temps_total)}</td>
                </tr>`).join('')}</tbody>
            </table>
            ${comparativa.length < 2 ? '<p class="text-muted">Afegeix amics per poder comparar-te.</p>' : ''}
        </section>
    </div>

    <section class="perfil-card">
        <h2>Historial de partides</h2>
        <form method="GET" action="/mvc/" class="form-cerca">
            <input type="hidden" name="route" value="/estadistiques">
            <input type="hidden" name="usuari_id" value="${Number(d.usuariId)}">
            <input type="text" name="q" placeholder="Filtrar per nom de joc" value="${h(d.cerca)}">
            <select name="ordre">
                ${['data', 'puntuacio', 'durada'].map(k => `<option value="${k}" ${d.ordre === k ? 'selected' : ''}>${k === 'data' ? 'Data' : k === 'puntuacio' ? 'Puntuació' : 'Durada'}</option>`).join('')}
            </select>
            <button type="submit">Filtrar</button>
        </form>

        ${d.errorSql ? `<div class="error">Error SQL: ${h(d.errorSql)}</div>` : ''}

        <div class="table-scroll">
            <table class="taula-partides">
                <thead><tr><th>Id</th><th>Joc</th><th>Nivell</th><th>Puntuació</th><th>Durada (s)</th><th>Resultat</th><th>Data</th></tr></thead>
                <tbody>
                  ${historial.length
                    ? historial.map(p => `<tr>${Object.values(p).map(v => `<td>${h(v)}</td>`).join('')}</tr>`).join('')
                    : '<tr><td colspan="7" class="sense-partides">Cap partida trobada.</td></tr>'}
                </tbody>
            </table>
        </div>
    </section>`;

window.renderGrafics();
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
