<?php include 'views/layout/header.php'; ?>
<div class="auth-container">
    <h2>Accés denegat</h2>
    <div class="error"><?= htmlspecialchars($error ?? 'No tens permís per accedir a aquesta secció.') ?></div>
    <p><a href="/mvc/?route=/jocs">Tornar als jocs</a></p>
</div>
<?php include 'views/layout/footer.php'; ?>
