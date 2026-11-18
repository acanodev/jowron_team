<?php
include 'views/layout/header.php';
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$jo = (int) Session::get('usuari_id');
?>
<div class="perfil-container">
    <?php if (!empty($flash)): ?>
        <div class="<?= $flash['tipus'] === 'error' ? 'error' : 'missatge-ok' ?>"><?= $h($flash['text']) ?></div>
    <?php endif; ?>

    <section class="perfil-card perfil-card-hero">
        <h2>Perfil de <?= $h($usuari['nom_usuari']) ?></h2>
        <div class="perfil-banner"></div>
        <div class="perfil-hero-body">
            <div class="avatar-container">
                <?php if (!empty($usuari['avatar'])): ?>
                    <img class="perfil-avatar" src="../../<?= $h($usuari['avatar']) ?>" alt="Avatar">
                <?php else: ?>
                    <div class="avatar-placeholder"><?= $h(substr($usuari['nom_usuari'], 0, 1)) ?></div>
                <?php endif; ?>
            </div>
            <div class="perfil-info">
                <h2 class="perfil-nom"><?= $h($usuari['nom_usuari']) ?></h2>
                <p class="perfil-real"><?= $h($usuari['nom_complet']) ?></p>
                <p class="text-muted">Membre des de <?= $h($usuari['data_registre']) ?></p>
                <div class="accions">
                    <?php if ((int) $usuari['id'] === $jo): ?>
                        <a class="btn-petit" href="/mvc/?route=/perfil">El meu perfil</a>
                    <?php elseif (!$relacio || $relacio['estat'] === 'rebutjada'): ?>
                        <form method="POST" action="/mvc/?route=/amics/solicitar" class="form-inline">
                            <input type="hidden" name="receptor_id" value="<?= (int) $usuari['id'] ?>">
                            <input type="hidden" name="tornar" value="/usuari&id=<?= (int) $usuari['id'] ?>">
                            <button type="submit" class="btn-petit">Afegir amic</button>
                        </form>
                    <?php elseif ($relacio['estat'] === 'pendent'): ?>
                        <span class="badge badge-pend">Sol·licitud pendent</span>
                    <?php else: ?>
                        <span class="badge badge-ok">Amics</span>
                    <?php endif; ?>
                    <a class="btn-petit" href="/mvc/?route=/estadistiques&usuari_id=<?= (int) $usuari['id'] ?>">Veure estadístiques</a>
                </div>
            </div>
            <div class="perfil-stats">
                <div class="stat"><span class="stat-num"><?= (int) $resum['partides'] ?></span><span class="stat-txt">Partides</span></div>
                <div class="stat"><span class="stat-num"><?= (int) $resum['puntuacio_total'] ?></span><span class="stat-txt">Punts</span></div>
            </div>
        </div>
    </section>

    <section class="perfil-card">
        <h2>Assoliments (<?= count($badges) ?>)</h2>
        <?php if (!$badges): ?>
            <p class="text-muted">Encara no ha aconseguit cap assoliment.</p>
        <?php else: ?>
            <div class="insignies">
                <?php foreach ($badges as $b): ?>
                    <div class="insignia insignia-ok" title="<?= $h($b['descripcio']) ?>">
                        <span class="insignia-icona"><?= $h($b['icona']) ?></span>
                        <span class="insignia-nom"><?= $h($b['nom']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="perfil-card">
        <h2>Últimes partides</h2>
        <table class="taula-partides">
            <thead><tr><th>Joc</th><th>Puntuació</th><th>Data</th></tr></thead>
            <tbody>
            <?php if (!$partides): ?><tr><td colspan="3" class="sense-partides">Cap partida.</td></tr><?php endif; ?>
            <?php foreach ($partides as $p): ?>
                <tr><td><?= $h($p['nom_joc']) ?></td><td><?= (int) $p['puntuacio_obtinguda'] ?></td><td><?= $h($p['data_partida']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
<?php include 'views/layout/footer.php'; ?>
