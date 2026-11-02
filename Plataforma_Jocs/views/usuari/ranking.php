<?php include 'views/layout/header.php'; ?>

<?php $count = 0 ?>

<div class="jocs-container">
    <h1>Ranking</h1>

    <table>
        <tr>
            <th>Posició</th>
            <th>Nom d'usuari</th>
            <th>Nom complet</th>
            <th>Puntuació</th>
        </tr>
         <?php foreach ($ranking as $r): ?>
            <?php $count++ ?>
            <tr>
                <td><?php echo $count ?></td>
                <td><?php echo $r['nom_usuari'] ?></td>
                <td><?php echo $r['nom_complet'] ?></td>
                <td><?php echo $r['puntuacio_total'] ?? '0' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include 'views/layout/footer.php'; ?>