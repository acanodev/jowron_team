<?php
// Incloure els fitxers per gestionar les rutes i controlar les sessions d'usuari
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Session.php';
require_once __DIR__ . '/core/Roles.php';
Session::start();

// FUNCIONS JSON PER L'API

function json_input(): array
{
    static $dades = null;
    if ($dades === null) {
        $cos = file_get_contents('php://input');
        $json = json_decode($cos, true);
        $dades = is_array($json) ? array_merge($_POST, $json) : $_POST;
    }
    return $dades;
}

function json_meta(array $extra = []) {
    return array_merge([
        'loggedIn' => Session::isLoggedIn(),
        'nomUsuari' => Session::get('nom_usuari'),
        'rol' => $_COOKIE['rol'] ?? Roles::codificar(Roles::USUARI),
        'potGestionarJocs' => Roles::potGestionarJocs(),
        'flash' => Session::flash(),
    ], $extra);
}

function json_send(array $payload, int $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function json_ok(array $data = [], array $meta = [], int $status = 200) {
    json_send(['ok' => true, 'data' => $data, 'meta' => json_meta($meta)], $status);
}

function json_error(string $message, int $status = 400, array $data = [], array $meta = []) {
    json_send(['ok' => false, 'data' => ['error' => $message] + $data, 'meta' => json_meta($meta)], $status);
}

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/JocController.php';
require_once __DIR__ . '/controllers/UsuariController.php';
require_once __DIR__ . '/controllers/GestioJocsController.php';
require_once __DIR__ . '/controllers/AmicsController.php';
require_once __DIR__ . '/controllers/EstadistiquesController.php';
require_once __DIR__ . '/controllers/BadgesController.php';

// VULNERABILITAT: Headers de seguretat absents

$router = new Router(); // Emmagatzema les rutes i redirigeix peticions GET i POST al controlador corresponent

$router->get('/login', 'AuthController', 'mostrarLogin');
$router->post('/login', 'AuthController', 'login');
$router->get('/registre', 'AuthController', 'mostrarRegistre');
$router->post('/registre', 'AuthController', 'registre');
$router->get('/logout', 'AuthController', 'logout');

$router->get('/', 'JocController', 'llistaJocs');
$router->get('/jocs', 'JocController', 'llistaJocs');
$router->get('/jugar', 'JocController', 'jugar');
$router->post('/guardar-puntuacio', 'JocController', 'guardarPuntuacio');

$router->get('/perfil', 'UsuariController', 'perfil');
$router->get('/ranking', 'UsuariController', 'ranking');
$router->post('/actualitzar-perfil', 'UsuariController', 'actualitzarPerfil');

$router->get('/gestio-jocs', 'GestioJocsController', 'llista');
$router->get('/gestio-jocs/nou', 'GestioJocsController', 'nou');
$router->post('/gestio-jocs/crear', 'GestioJocsController', 'crear');
$router->get('/gestio-jocs/editar', 'GestioJocsController', 'editar');
$router->post('/gestio-jocs/actualitzar', 'GestioJocsController', 'actualitzar');
$router->post('/gestio-jocs/eliminar', 'GestioJocsController', 'eliminar');

$router->get('/amics', 'AmicsController', 'index');
$router->post('/amics/solicitar', 'AmicsController', 'solicitar');
$router->post('/amics/respondre', 'AmicsController', 'respondre');
$router->post('/amics/eliminar', 'AmicsController', 'eliminar');
$router->get('/usuari', 'AmicsController', 'perfilPublic');

$router->get('/estadistiques', 'EstadistiquesController', 'index');
$router->get('/badges', 'BadgesController', 'index');

$router->view('/', 'views/jocs/llista.php');
$router->view('/jocs', 'views/jocs/llista.php');
$router->view('/jugar', 'views/jocs/joc.php');
$router->view('/login', 'views/auth/login.php');
$router->view('/registre', 'views/auth/registre.php');
$router->view('/perfil', 'views/usuari/perfil.php');
$router->view('/ranking', 'views/usuari/ranking.php');
$router->view('/amics', 'views/amics/index.php');
$router->view('/usuari', 'views/amics/perfil_public.php');
$router->view('/estadistiques', 'views/estadistiques/index.php');
$router->view('/badges', 'views/badges/index.php');
$router->view('/gestio-jocs', 'views/gestio_jocs/llista.php');
$router->view('/gestio-jocs/nou', 'views/gestio_jocs/formulari.php');
$router->view('/gestio-jocs/editar', 'views/gestio_jocs/formulari.php');

// REP PETICIONS DE L'USUARI: Detecta el mètode HTTP i la ruta i redirigeix al controlador corresponent, sinó mostra un error.
$router->dispatch();
