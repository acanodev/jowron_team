
<?php
// mvc/views/usuari/perfil.php

/**
 * Variables rebudes des de UsuariController::perfil()
 *
 * @var array $usuari
 * @var array $partides
 */

require 'views/layout/header.php';
?>

<link rel="stylesheet" href="/mvc/public/css/perfil.css">

<div class="perfil-container">

    <h1>El meu perfil</h1>

    <!-- Dades personals -->
    <section class="perfil-card">
        <h2>Dades personals</h2>

        <div class="dades-usuari">
            <p>
                <strong>Nom d'usuari:</strong>
                <?php echo $usuari['nom_usuari']; ?>
            </p>

            <p>
                <strong>Nom complet:</strong>
                <?php echo $usuari['nom_complet']; ?>
            </p>

            <p>
                <strong>Correu electrònic:</strong>
                <?php echo $usuari['email']; ?>
            </p>

            <p>
                <strong>Data de registre:</strong>
                <?php echo $usuari['data_registre']; ?>
            </p>
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
                    <th>Durada (segons)</th>
                    <th>Data de la partida</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($partides)): ?>

                    <?php foreach ($partides as $partida): ?>
                        <tr>
                            <td><?php echo $partida['nom_joc']; ?></td>
                            <td><?php echo $partida['puntuacio_obtinguda']; ?></td>
                            <td><?php echo $partida['durada_segons']; ?></td>
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
