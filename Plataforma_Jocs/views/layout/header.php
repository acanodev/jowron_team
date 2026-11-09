<?php
// Tema día/noche guardado en la sesión del usuario ('fosc' = noche, 'clar' = día)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_GET['tema']) && in_array($_GET['tema'], ['clar', 'fosc'], true)) {
    $_SESSION['tema'] = $_GET['tema'];
}
$tema = $_SESSION['tema'] ?? 'fosc';
$temaOposat = ($tema === 'fosc') ? 'clar' : 'fosc';
$urlTema = '?' . http_build_query(array_merge($_GET, ['tema' => $temaOposat]));
?>
<!DOCTYPE html>
<html lang="ca" data-theme="<?= $tema ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma de Videojocs</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
</head>

<body>
    <header class="site-header">
        <nav class="site-nav">
            <a class="logo" href="/mvc/?route=/">ArJol<span>.cat</span></a>

            <div class="menu">
                <a href="/mvc/?route=/">Inici</a>
                <a href="/mvc/?route=/jocs">Jocs</a>
                <?php if (Session::isLoggedIn()): ?>
                    <a href="/mvc/?route=/ranking">Ranking</a>
                    <!-- VULNERABILITAT: XSS -->
                    <a class="menu-user" href="/mvc/?route=/perfil">Hola, <?= Session::get('nom_usuari') ?></a>
                    <a class="menu-cta menu-cta-outline" href="/mvc/?route=/logout">Sortir</a>
                <?php else: ?>
                    <a href="/mvc/?route=/login">Accedir</a>
                    <a class="menu-cta" href="/mvc/?route=/registre">Registrar-se</a>
                <?php endif; ?>

                <a id="theme-toggle" class="theme-toggle"
                   href="<?= htmlspecialchars($urlTema) ?>"
                   title="Canviar tema dia/nit" aria-label="Canviar tema dia/nit">
                    <!-- Lluna (visible en mode dia, per passar a nit) -->
                    <svg class="icon-moon" viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
                        <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>
                    </svg>
                    <!-- Sol (visible en mode nit, per passar a dia) -->
                    <svg class="icon-sun" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="4" fill="currentColor"/>
                        <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
                    </svg>
                </a>
            </div>
        </nav>
    </header>
    <main>
