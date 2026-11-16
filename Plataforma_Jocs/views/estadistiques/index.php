<?php
include 'views/layout/header.php';

$baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$format = function ($segons) {
    $segons = (int) $segons;
    return sprintf('%dh %02dm %02ds', intdiv($segons, 3600), intdiv($segons % 3600, 60), $segons % 60);
};
$json = fn($d) => htmlspecialchars(json_encode($d, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

$dMesJugats = array_map(fn($r) => ['etiqueta' => $r['nom_joc'], 'valor' => (int) $r['partides']], $jocsMesJugats);
$dEvolucio = array_map(fn($r) => ['etiqueta' => substr($r['data_partida'], 5, 5), 'valor' => (int) $r['puntuacio_obtinguda']], $evolucio);
$dVD = [
    ['etiqueta' => 'Victòries', 'valor' => (int) $resum['victories'], 'color' => '#4cc38a'],
    ['etiqueta' => 'Derrotes', 'valor' => (int) $resum['derrotes'], 'color' => '#fa5c5c'],
];
$dRanking = array_map(fn($r) => ['etiqueta' => $r['nom_usuari'], 'valor' => (int) $r['puntuacio_total']], $ranking);
$dComparativa = array_map(fn($r) => ['etiqueta' => $r['nom_usuari'], 'valor' => (int) $r['puntuacio_total']], $comparativa);
?>

<div class="perfil-container">
    <h1>Estadístiques<?= $esMeu ? '' : ' de ' . $h($usuari['nom_usuari']) ?></h1>
    <?php if (!$esMeu): ?>
        <p class="text-muted">Estàs veient les estadístiques d'un altre usuari. <a href="/mvc/?route=/estadistiques">Tornar a les meves</a></p>
    <?php endif; ?>

    <section class="targetes-resum">
        <div class="targeta-num"><span><?= (int) $resum['partides'] ?></span>Partides jugades</div>
        <div class="targeta-num"><span><?= (int) $resum['victories'] ?> / <?= (int) $resum['derrotes'] ?></span>Victòries / derrotes</div>
        <div class="targeta-num"><span><?= (int) $resum['puntuacio_total'] ?></span>Puntuació total</div>
        <div class="targeta-num"><span><?= $format($resum['temps_total']) ?></span>Temps de joc</div>
        <div class="targeta-num"><span><?= (int) $numBadges ?></span>Assoliments</div>
    </section>

    <div class="graelles-2">
        <section class="perfil-card">
            <h2>Victòries i derrotes</h2>
            <div class="grafic" data-tipus="donut" data-dades="<?= $json($dVD) ?>"></div>
        </section>
        <section class="perfil-card">
            <h2>Jocs més jugats</h2>
            <div class="grafic" data-tipus="barres" data-dades="<?= $json($dMesJugats) ?>"></div>
        </section>
    </div>

    <section class="perfil-card">
        <h2>Evolució de les puntuacions</h2>
        <div class="grafic" data-tipus="linia" data-dades="<?= $json($dEvolucio) ?>"></div>
    </section>

    <div class="graelles-2">
        <section class="perfil-card">
            <h2>Ranking general (top 10)</h2>
            <div class="grafic" data-tipus="barres" data-dades="<?= $json($dRanking) ?>"></div>
            <table>
                <thead><tr><th>#</th><th>Jugador</th><th>Punts</th></tr></thead>
                <tbody>
                <?php foreach ($ranking as $i => $r): ?>
                    <tr><td><?= $i + 1 ?></td><td><?= $h($r['nom_usuari']) ?></td><td><?= (int) $r['puntuacio_total'] ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <section class="perfil-card">
            <h2>Comparativa amb amics</h2>
            <div class="grafic" data-tipus="barres" data-dades="<?= $json($dComparativa) ?>"></div>
            <table>
                <thead><tr><th>Jugador</th><th>Partides</th><th>Victòries</th><th>Punts</th><th>Temps</th></tr></thead>
                <tbody>
                <?php foreach ($comparativa as $r): ?>
                    <tr>
                        <td><?= $h($r['nom_usuari']) ?></td><td><?= (int) $r['partides'] ?></td><td><?= (int) $r['victories'] ?></td>
                        <td><?= (int) $r['puntuacio_total'] ?></td><td><?= $format($r['temps_total']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (count($comparativa) < 2): ?><p class="text-muted">Afegeix amics per poder comparar-te.</p><?php endif; ?>
        </section>
    </div>

    <section class="perfil-card">
        <h2>Historial de partides</h2>
        <form method="GET" action="/mvc/" class="form-cerca">
            <input type="hidden" name="route" value="/estadistiques">
            <input type="hidden" name="usuari_id" value="<?= (int) $usuariId ?>">
            <input type="text" name="q" placeholder="Filtrar per nom de joc" value="<?= $h($cerca) ?>">
            <select name="ordre">
                <?php foreach (['data' => 'Data', 'puntuacio' => 'Puntuació', 'durada' => 'Durada'] as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $ordre === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Filtrar</button>
        </form>

        <?php if ($errorSql): ?>
            <div class="error">Error SQL: <?= $h($errorSql) ?></div>
        <?php endif; ?>

        <div class="table-scroll">
            <table class="taula-partides">
                <thead><tr><th>Id</th><th>Joc</th><th>Nivell</th><th>Puntuació</th><th>Durada (s)</th><th>Resultat</th><th>Data</th></tr></thead>
                <tbody>
                <?php if (!$historial): ?>
                    <tr><td colspan="7" class="sense-partides">Cap partida trobada.</td></tr>
                <?php endif; ?>
                <?php foreach ($historial as $p): ?>
                    <tr>
                        <?php foreach (array_values($p) as $v): ?><td><?= $h($v) ?></td><?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script src="<?= $h($baseDir) ?>/public/js/charts.js"></script>
<?php include 'views/layout/footer.php'; ?>
