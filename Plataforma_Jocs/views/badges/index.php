<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="perfil-container" id="contenidor-badges"></div>

<script type="module">
const resposta = await window.appFetch(location.href);
window.renderLayout(resposta.meta);
window.renderFlash(resposta.meta);
if (window.mostraError(resposta)) throw new Error(resposta.data.error);

const badges = resposta.data.badges || [];
const obtinguts = Number(resposta.data.obtinguts);
const total = Number(resposta.data.total);
const pct = total ? Math.floor(100 * obtinguts / total) : 0;
const h = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const unitat = {
  partides: 'partides', victories: 'victòries', puntuacio_total: 'punts',
  puntuacio_partida: 'punts', nivell: 'nivell', joc_completat: 'jocs',
  jocs_diferents: 'jocs', temps_total: 's', amics: 'amics',
};

document.getElementById('contenidor-badges').innerHTML = `
    <h1>Assoliments</h1>
    <p class="text-muted">Els badges es guanyen jugant: per partides, victòries, puntuacions, nivells i amistats.</p>

    <section class="perfil-card">
        <h2>${obtinguts} de ${total} assoliments</h2>
        <div class="barra-progres" role="progressbar" aria-valuenow="${pct}" aria-valuemin="0" aria-valuemax="100">
            <div class="barra-progres-omplert" style="width: ${pct}%"></div>
        </div>
    </section>

    <section class="perfil-card">
        <div class="insignies insignies-grans">
            ${badges.map(b => `
                <div class="insignia ${b.obtingut ? 'insignia-ok' : 'insignia-bloquejada'}">
                    <span class="insignia-icona">${h(b.icona)}</span>
                    <span class="insignia-nom">${h(b.nom)}</span>
                    <span class="insignia-desc">${h(b.descripcio)}</span>
                    ${b.joc_id && !b.nom_joc ? '<span class="insignia-joc">Joc no disponible</span>' : ''}
                    ${b.nom_joc ? `<span class="insignia-joc">${h(b.nom_joc)}</span>` : ''}
                    ${b.obtingut
                      ? `<span class="insignia-data">Obtingut el ${h(String(b.obtingut).slice(0, 10))}</span>`
                      : `<div class="barra-progres barra-progres-petita">
                           <div class="barra-progres-omplert" style="width: ${Number(b.percentatge)}%"></div>
                         </div>
                         <span class="insignia-data">${Number(b.progres)} / ${Number(b.llindar)} ${h(unitat[b.tipus] || '')}</span>`}
                </div>`).join('')}
        </div>
    </section>`;
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
