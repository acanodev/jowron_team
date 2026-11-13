<?php include 'views/layout/header.php'; ?>

<?php
$count = 0;
$llista = array_values($ranking);
$podi = array_slice($llista, 0, 3);
$maxScore = !empty($llista) ? (float)($llista[0]['puntuacio_total'] ?? 0) : 0;

// Avatar amb ruta relativa (igual que al perfil) + inicial de reserva si la imatge no carrega
$avatarHtml = function ($r, $cls) {
    $inicial = substr($r['nom_usuari'], 0, 1);
    if (!empty($r['avatar'])) {
        return '<img class="' . $cls . '" src="../../' . $r['avatar'] . '" alt="" '
             . 'onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\'">'
             . '<span class="' . $cls . ' rank-fallback">' . $inicial . '</span>';
    }
    return '<span class="' . $cls . ' rank-fallback rank-fallback-on">' . $inicial . '</span>';
};
?>

<div class="jocs-container">
    <h1>Ranking</h1>
    <p class="rank-subtitol">Els millors jugadors de la plataforma segons la puntuació total.</p>

    <?php if (!empty($podi)): ?>
        <!-- Podi Top 3 -->
        <div class="podi">
            <?php foreach ($podi as $i => $p): ?>
                <div class="podi-card podi-<?php echo $i + 1; ?>">
                    <span class="rank-pos rank-pos-<?php echo $i + 1; ?>"><?php echo $i + 1; ?></span>
                    <div class="podi-avatar-wrap">
                        <?php echo $avatarHtml($p, 'podi-avatar'); ?>
                    </div>
                    <div class="podi-nom"><?php echo $p['nom_usuari']; ?></div>
                    <div class="podi-real"><?php echo $p['nom_complet']; ?></div>
                    <div class="podi-score">
                        <?php echo $p['puntuacio_total'] ?? '0'; ?>
                        <small>punts</small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Classificació completa -->
    <div class="table-scroll">
        <table class="ranking-table">
            <thead>
                <tr>
                    <th class="col-pos">#</th>
                    <th>Jugador</th>
                    <th class="col-nom-complet">Nom complet</th>
                    <th class="col-score">Puntuació</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($llista as $r): ?>
                    <?php
                    $count++;
                    $score = (float)($r['puntuacio_total'] ?? 0);
                    $pct = $maxScore > 0 ? round($score / $maxScore * 100) : 0;
                    $classes = 'rank-row';
                    if ($count <= 3) { $classes .= ' rank-top-' . $count; }
                    if ($r['nom_usuari'] === Session::get('nom_usuari')) { $classes .= ' rank-yo'; }
                    ?>
                    <tr class="<?php echo $classes; ?>">
                        <td class="col-pos">
                            <span class="rank-pos <?php echo $count <= 3 ? 'rank-pos-' . $count : ''; ?>"><?php echo $count ?></span>
                        </td>
                        <td>
                            <div class="rank-user">
                                <?php echo $avatarHtml($r, 'rank-avatar'); ?>
                                <span class="rank-nom"><?php echo $r['nom_usuari'] ?></span>
                            </div>
                        </td>
                        <td class="col-nom-complet"><?php echo $r['nom_complet'] ?></td>
                        <td class="col-score">
                            <span class="score-num"><?php echo $r['puntuacio_total'] ?? '0' ?></span>
                            <div class="score-bar"><span style="width: <?php echo $pct; ?>%"></span></div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>
