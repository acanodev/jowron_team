<?php
// Variables: $joc (fila de la BD), $gameBaseUrl (URL base del directori del joc)
include 'views/layout/header.php';

// Ruta de l'index.html dins del directori del joc, amb cada segment codificat per a URL
$segments = array_map('rawurlencode', explode('/', $joc['ruta_index']));
$urlJoc = $gameBaseUrl . implode('/', $segments);
?>

<div class="jocs-container">
    <h2><?= htmlspecialchars($joc['nom_joc']) ?></h2>
    <p class="text-muted"><?= htmlspecialchars($joc['descripcio'] ?? '') ?></p>

    <!-- El joc s'executa directament des de la plataforma, sense descarregar-lo -->
    <iframe class="joc-frame" src="<?= htmlspecialchars($urlJoc) ?>" title="<?= htmlspecialchars($joc['nom_joc']) ?>"
        allowfullscreen></iframe>

    <p><a class="btn-jugar" href="<?= htmlspecialchars($urlJoc) ?>" target="_blank" rel="noopener">Obrir en una pestanya nova</a>
        <a class="btn-secundari" href="/mvc/?route=/jocs">Tornar als jocs</a></p>
</div>

<?php include 'views/layout/footer.php'; ?>
