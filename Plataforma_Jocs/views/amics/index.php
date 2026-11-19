<?php
include 'views/layout/header.php';
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$boto = function ($accio, $id, $text, $classe = '') use ($h) {
    return '<form method="POST" action="/mvc/?route=' . $accio . '" class="form-inline">'
        . '<input type="hidden" name="id" value="' . (int) $id . '">'
        . '<button type="submit" class="btn-petit ' . $classe . '">' . $h($text) . '</button></form>';
};
?>
<div class="perfil-container">
    <h1>Amics</h1>

    <?php if (!empty($flash)): ?>
        <div class="<?= $flash['tipus'] === 'error' ? 'error' : 'missatge-ok' ?>"><?= $h($flash['text']) ?></div>
    <?php endif; ?>

    <section class="perfil-card">
        <h2>Cercar usuaris</h2>
        <form method="GET" action="/mvc/" class="form-cerca">
            <input type="hidden" name="route" value="/amics">
            <input type="text" name="q" placeholder="Nom d'usuari o nom complet" value="<?= $h($cerca) ?>">
            <button type="submit">Cercar</button>
        </form>

        <?php if ($cerca !== ''): ?>
            <table>
                <thead><tr><th>Usuari</th><th>Nom complet</th><th>Estat</th><th></th></tr></thead>
                <tbody>
                <?php if (!$resultats): ?><tr><td colspan="4" class="sense-partides">Cap usuari trobat.</td></tr><?php endif; ?>
                <?php foreach ($resultats as $r): ?>
                    <tr>
                        <td><a href="/mvc/?route=/usuari&id=<?= (int) $r['id'] ?>"><?= $h($r['nom_usuari']) ?></a></td>
                        <td><?= $h($r['nom_complet']) ?></td>
                        <td>
                            <?php if (!$r['relacio']): ?>—
                            <?php elseif ($r['relacio']['estat'] === 'acceptada'): ?><span class="badge badge-ok">Amics</span>
                            <?php elseif ($r['relacio']['estat'] === 'pendent'): ?><span class="badge badge-pend">Pendent</span>
                            <?php else: ?><span class="badge badge-off">Rebutjada</span><?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$r['relacio'] || $r['relacio']['estat'] === 'rebutjada'): ?>
                                <form method="POST" action="/mvc/?route=/amics/solicitar" class="form-inline">
                                    <input type="hidden" name="receptor_id" value="<?= (int) $r['id'] ?>">
                                    <button type="submit" class="btn-petit">Afegir amic</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="perfil-card">
        <h2>Sol·licituds rebudes (<?= count($rebudes) ?>)</h2>
        <?php if (!$rebudes): ?><p class="text-muted">No tens sol·licituds pendents.</p><?php endif; ?>
        <?php foreach ($rebudes as $r): ?>
            <div class="fila-usuari">
                <a href="/mvc/?route=/usuari&id=<?= (int) $r['id'] ?>"><?= $h($r['nom_usuari']) ?></a>
                <span class="text-muted"><?= $h($r['data_solicitud']) ?> · sol·licitud #<?= (int) $r['amistat_id'] ?></span>
                <span class="accions">
                    <form method="POST" action="/mvc/?route=/amics/respondre" class="form-inline">
                        <input type="hidden" name="id" value="<?= (int) $r['amistat_id'] ?>">
                        <input type="hidden" name="accio" value="acceptar">
                        <button type="submit" class="btn-petit">Acceptar</button>
                    </form>
                    <form method="POST" action="/mvc/?route=/amics/respondre" class="form-inline">
                        <input type="hidden" name="id" value="<?= (int) $r['amistat_id'] ?>">
                        <input type="hidden" name="accio" value="rebutjar">
                        <button type="submit" class="btn-petit btn-perill">Rebutjar</button>
                    </form>
                </span>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="perfil-card">
        <h2>Sol·licituds enviades</h2>
        <?php if (!$enviades): ?><p class="text-muted">No has enviat cap sol·licitud pendent.</p><?php endif; ?>
        <?php foreach ($enviades as $r): ?>
            <div class="fila-usuari">
                <a href="/mvc/?route=/usuari&id=<?= (int) $r['id'] ?>"><?= $h($r['nom_usuari']) ?></a>
                <span class="badge <?= $r['estat'] === 'pendent' ? 'badge-pend' : 'badge-off' ?>"><?= $r['estat'] === 'pendent' ? 'Pendent' : 'Rebutjada' ?></span>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="perfil-card">
        <h2>Els meus amics (<?= count($amics) ?>)</h2>
        <?php if (!$amics): ?><p class="text-muted">Encara no tens amics. Cerca usuaris per afegir-ne.</p><?php endif; ?>
        <?php foreach ($amics as $a): ?>
            <div class="fila-usuari">
                <a href="/mvc/?route=/usuari&id=<?= (int) $a['id'] ?>"><?= $h($a['nom_usuari']) ?></a>
                <span class="text-muted"><?= $h($a['nom_complet']) ?> · amistat #<?= (int) $a['amistat_id'] ?></span>
                <span class="accions">
                    <a class="btn-petit" href="/mvc/?route=/estadistiques&usuari_id=<?= (int) $a['id'] ?>">Estadístiques</a>
                    <?= $boto('/amics/eliminar', $a['amistat_id'], 'Eliminar', 'btn-perill') ?>
                </span>
            </div>
        <?php endforeach; ?>
    </section>
</div>
<?php include 'views/layout/footer.php'; ?>
