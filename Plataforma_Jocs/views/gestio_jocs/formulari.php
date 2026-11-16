<?php
include 'views/layout/header.php';
$edicio = !empty($joc['id']);
?>

<div class="auth-container auth-ample">
    <h2><?= $edicio ? 'Editar joc' : 'Publicar un joc nou' ?></h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data"
        action="/mvc/?route=/gestio-jocs/<?= $edicio ? 'actualitzar' : 'crear' ?>">
        <?php if ($edicio): ?>
            <input type="hidden" name="id" value="<?= (int) $joc['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="nom_joc">Nom del joc:</label>
            <input type="text" id="nom_joc" name="nom_joc" maxlength="50" required
                value="<?= htmlspecialchars($joc['nom_joc'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="descripcio">Descripció:</label>
            <textarea id="descripcio" name="descripcio" rows="3"><?= htmlspecialchars($joc['descripcio'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="puntuacio_maxima">Puntuació màxima:</label>
            <input type="text" id="puntuacio_maxima" name="puntuacio_maxima" value="<?= (int) ($joc['puntuacio_maxima'] ?? 0) ?>">
        </div>

        <div class="form-group">
            <label for="nivells_totals">Nivells totals:</label>
            <input type="text" id="nivells_totals" name="nivells_totals" value="<?= (int) ($joc['nivells_totals'] ?? 1) ?>">
        </div>

        <div class="form-group">
            <label for="zip">Fitxer ZIP del joc (ha de contenir un index.html)<?= $edicio ? ' — deixa-ho buit per no canviar-lo' : '' ?>:</label>
            <input type="file" id="zip" name="zip" accept=".zip" <?= $edicio ? '' : 'required' ?>>
        </div>

        <div class="form-group">
            <input type="checkbox" id="actiu" name="actiu" value="1" <?= !isset($joc['actiu']) || $joc['actiu'] ? 'checked' : '' ?>>
            <label for="actiu" class="label-inline">Joc visible a la plataforma</label>
        </div>

        <button type="submit"><?= $edicio ? 'Desar canvis' : 'Publicar joc' ?></button>
        <a class="btn-secundari" href="/mvc/?route=/gestio-jocs">Cancel·lar</a>
    </form>
</div>

<?php include 'views/layout/footer.php'; ?>
