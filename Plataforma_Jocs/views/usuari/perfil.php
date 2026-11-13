<?php
// mvc/views/usuari/perfil.php

/**
 * Variables rebudes des de UsuariController::perfil()
 *
 * @var array $usuari
 * @var array $partides
 */

require 'views/layout/header.php';

// Estadístiques per a la capçalera del perfil
$totalPartides = is_array($partides ?? null) ? count($partides) : 0;
$millorPuntuacio = $totalPartides ? max(array_column($partides, 'puntuacio_obtinguda')) : 0;
?>

<div class="perfil-container">

    <h1>El meu perfil</h1>

    <!-- Dades personals -->
    <section class="perfil-card perfil-card-hero">
        <h2>Dades personals</h2>

        <div class="perfil-banner"></div>

        <div class="perfil-hero-body">

            <div class="avatar-container">
                <?php if (!empty($usuari['avatar'])): ?>
                    <img class="perfil-avatar" src="../../<?php echo $usuari['avatar']; ?>" alt="Imatge de perfil">
                <?php else: ?>
                    <div class="avatar-placeholder"><?php echo substr($usuari['nom_usuari'], 0, 1); ?></div>
                <?php endif; ?>
            </div>

            <div class="perfil-info">
                <h2 class="perfil-nom"><?php echo $usuari['nom_usuari']; ?></h2>
                <p class="perfil-real"><?php echo $usuari['nom_complet']; ?></p>

                <div class="dades-usuari">
                    <div class="dada">
                        <span class="dada-label">Nom d'usuari</span>
                        <span class="dada-valor"><?php echo $usuari['nom_usuari']; ?></span>
                    </div>

                    <div class="dada">
                        <span class="dada-label">Nom complet</span>
                        <span class="dada-valor"><?php echo $usuari['nom_complet']; ?></span>
                    </div>

                    <div class="dada">
                        <span class="dada-label">Correu electrònic</span>
                        <span class="dada-valor"><?php echo $usuari['email']; ?></span>
                    </div>

                    <div class="dada">
                        <span class="dada-label">Data de registre</span>
                        <span class="dada-valor"><?php echo $usuari['data_registre']; ?></span>
                    </div>
                </div>
            </div>

            <div class="perfil-stats">
                <div class="stat">
                    <span class="stat-num"><?php echo $totalPartides; ?></span>
                    <span class="stat-txt">Partides</span>
                </div>
                <div class="stat">
                    <span class="stat-num"><?php echo $millorPuntuacio; ?></span>
                    <span class="stat-txt">Millor punt.</span>
                </div>
            </div>

        </div>
    </section>

    <!-- Formulari d'actualització del perfil -->
    <section class="perfil-card">
        <h2>Actualitzar perfil</h2>

        <form
            class="perfil-form"
            method="POST"
            action="/mvc/?route=/actualitzar-perfil"
            enctype="multipart/form-data"
        >
            <label for="nom_complet">Nom complet</label>
            <input
                type="text"
                id="nom_complet"
                name="nom_complet"
                value="<?php echo $usuari['nom_complet']; ?>"
            >

            <label for="email">Correu electrònic</label>
            <input
                type="text"
                id="email"
                name="email"
                value="<?php echo $usuari['email']; ?>"
            >

            <label for="avatar">Imatge de perfil</label>
            <input
                type="file"
                id="avatar"
                name="avatar"
            >

            <button type="submit">Desar canvis</button>
        </form>

    </section>

    <!-- Historial de partides -->
    <section class="perfil-card">
        <h2>Historial de partides</h2>

        <table class="taula-partides">
            <thead>
                <tr>
                    <th>Joc</th>
                    <th>Puntuació</th>
                    <th>Data de la partida</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($partides)): ?>

                    <?php foreach ($partides as $partida): ?>
                        <tr>
                            <td><?php echo $partida['nom_joc']; ?></td>
                            <td><?php echo $partida['puntuacio_obtinguda']; ?></td>
                            <td><?php echo $partida['data_partida']; ?></td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="4" class="sense-partides">
                            Encara no tens cap partida registrada.
                        </td>
                    </tr>

                <?php endif; ?>
            </tbody>
        </table>
    </section>

</div>

<?php
require 'views/layout/footer.php';
?>
