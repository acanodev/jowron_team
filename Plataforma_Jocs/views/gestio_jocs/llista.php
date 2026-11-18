<?php include 'views/layout/header.php'; ?>

<div class="jocs-container">
    <div class="titol-accions">
        <h2>Gestió de jocs</h2>
        <a class="btn-jugar" href="/mvc/?route=/gestio-jocs/nou">+ Publicar un joc nou</a>
    </div>
    <p class="text-muted">
        Rol actual: <strong><?= htmlspecialchars(Roles::actual()) ?></strong>
        <?= Roles::esAdmin() ? '(es mostren tots els jocs)' : '(es mostren només els teus jocs)' ?>
    </p>

    <?php if (!empty($flash)): ?>
        <div class="<?= $flash['tipus'] === 'error' ? 'error' : 'missatge-ok' ?>"><?= htmlspecialchars($flash['text']) ?></div>
    <?php endif; ?>

    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Nom</th>
                    <th>Tipus</th>
                    <th>Developer</th>
                    <th>Nivells</th>
                    <th>Estat</th>
                    <th>Accions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jocs)): ?>
                    <tr><td colspan="7" class="sense-partides">Encara no has publicat cap joc.</td></tr>
                <?php endif; ?>
                <?php foreach ($jocs as $j): ?>
                    <tr>
                        <td><?= (int) $j['id'] ?></td>
                        <td><?= htmlspecialchars($j['nom_joc']) ?></td>
                        <td><?= !empty($j['ruta_index']) ? 'ZIP (' . htmlspecialchars($j['ruta_index']) . ')' : 'Plataforma' ?></td>
                        <td><?= htmlspecialchars($j['nom_developer'] ?? '—') ?></td>
                        <td><?= (int) $j['nivells_totals'] ?></td>
                        <td><span class="badge <?= $j['actiu'] ? 'badge-ok' : 'badge-off' ?>"><?= $j['actiu'] ? 'Actiu' : 'Inactiu' ?></span></td>
                        <td class="accions">
                            <a class="btn-petit" href="/mvc/?route=/jugar&id=<?= (int) $j['id'] ?>">Jugar</a>
                            <a class="btn-petit" href="/mvc/?route=/gestio-jocs/editar&id=<?= (int) $j['id'] ?>">Editar</a>
                            <form method="POST" action="/mvc/?route=/gestio-jocs/eliminar" class="form-inline"
                                onsubmit="return confirm('Eliminar aquest joc?');">
                                <input type="hidden" name="id" value="<?= (int) $j['id'] ?>">
                                <button type="submit" class="btn-petit btn-perill">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>
