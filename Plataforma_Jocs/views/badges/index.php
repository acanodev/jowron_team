<?php
/**
 * Variables rebudes des de BadgesController::index()
 *
 * @var array $badges     catàleg amb obtingut / progres / llindar / percentatge
 * @var int   $obtinguts
 * @var int   $total
 */
include 'views/layout/header.php';
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$pct = $total ? (int) floor(100 * $obtinguts / $total) : 0;

// Unitats del progrés segons el tipus de criteri
$unitat = [
    'partides' => 'partides', 'victories' => 'victòries', 'puntuacio_total' => 'punts',
    'puntuacio_partida' => 'punts', 'nivell' => 'nivell', 'joc_completat' => 'jocs',
    'jocs_diferents' => 'jocs', 'temps_total' => 's', 'amics' => 'amics',
];
?>
<div class="perfil-container">
    <h1>Assoliments</h1>
    <p class="text-muted">Els badges es guanyen jugant: per partides, victòries, puntuacions, nivells i amistats.</p>

    <section class="perfil-card">
        <h2><?= (int) $obtinguts ?> de <?= (int) $total ?> assoliments</h2>
        <div class="barra-progres" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
            <div class="barra-progres-omplert" style="width: <?= $pct ?>%"></div>
        </div>
    </section>

    <section class="perfil-card">
        <div class="insignies insignies-grans">
            <?php foreach ($badges as $b): ?>
                <div class="insignia <?= $b['obtingut'] ? 'insignia-ok' : 'insignia-bloquejada' ?>">
                    <span class="insignia-icona"><?= $h($b['icona']) ?></span>
                    <span class="insignia-nom"><?= $h($b['nom']) ?></span>
                    <span class="insignia-desc"><?= $h($b['descripcio']) ?></span>
                    <?php if (!empty($b['joc_id']) && empty($b['nom_joc'])): ?>
                        <span class="insignia-joc">Joc no disponible</span>
                    <?php elseif (!empty($b['nom_joc'])): ?>
                        <span class="insignia-joc"><?= $h($b['nom_joc']) ?></span>
                    <?php endif; ?>
                    <?php if ($b['obtingut']): ?>
                        <span class="insignia-data">Obtingut el <?= $h(substr($b['obtingut'], 0, 10)) ?></span>
                    <?php else: ?>
                        <div class="barra-progres barra-progres-petita">
                            <div class="barra-progres-omplert" style="width: <?= (int) $b['percentatge'] ?>%"></div>
                        </div>
                        <span class="insignia-data"><?= (int) $b['progres'] ?> / <?= (int) $b['llindar'] ?> <?= $h($unitat[$b['tipus']] ?? '') ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<?php include 'views/layout/footer.php'; ?>
