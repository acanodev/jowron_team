<?php

define('API_MODE', true);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$token = '';
$authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
    $token = trim($matches[1]);
}
if ($token !== '' && preg_match('/^[A-Za-z0-9,\-]+$/', $token) && empty($_COOKIE['PHPSESSID'])) {
    session_id($token);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../index.php';

$method = $_SERVER['REQUEST_METHOD'];
$segments = obtenirSegmentsApi();

if ($method === 'GET' && count($segments) === 4 && $segments[0] === 'jocs' && $segments[2] === 'nivells') {
    $jocId = $segments[1];
    $nivell = $segments[3];
    $pdo = Database::getConnection();

    try {
        $sql = "SELECT configuracio_json FROM nivells_joc WHERE joc_id = $jocId AND nivell = $nivell";
        $resultat = $pdo->query($sql);
        $fila = $resultat ? $resultat->fetch(PDO::FETCH_ASSOC) : false;

        if ($fila) {
            echo $fila['configuracio_json'];
        } else {
            json_error('Joc o nivell no trobat', 404);
        }
    } catch (Throwable $e) {
        json_error('Error a la consulta SQL. Joc o nivell inexistent.', 500);
    }
    exit;
}

if (($segments[0] ?? '') === 'reptes') {
    json_error('Endpoint /api/reptes no implementat: no hi ha model ni taula de reptes.', 501);
}

$router->delete('/api/amics/{id}', 'AmicsController', 'eliminar');
$router->delete('/api/gestio/jocs/{id}', 'GestioJocsController', 'eliminar');

$router->get('/api/auth/me', 'AuthController', 'me');
$router->post('/api/auth/login', 'AuthController', 'login');
$router->post('/api/auth/registre', 'AuthController', 'registre');
$router->post('/api/auth/logout', 'AuthController', 'logout');

$router->get('/api/jocs', 'JocController', 'llistaJocs');
$router->get('/api/jocs/{id}', 'JocController', 'jugar');
$router->get('/api/partides', 'JocController', 'partides');
$router->post('/api/partides', 'JocController', 'guardarPuntuacio');

$router->get('/api/usuaris', 'UsuariController', 'ranking');
$router->get('/api/ranking', 'UsuariController', 'ranking');
$router->get('/api/usuaris/me', 'UsuariController', 'perfil');
$router->get('/api/usuaris/{id}', 'UsuariController', 'perfil');
$router->get('/api/usuaris/{id}/public', 'AmicsController', 'perfilPublic');
$router->post('/api/usuaris/me', 'UsuariController', 'actualitzarPerfil');

$router->get('/api/amics', 'AmicsController', 'index');
$router->post('/api/amics/solicitar', 'AmicsController', 'solicitar');
$router->post('/api/amics/{id}/respondre', 'AmicsController', 'respondre');

$router->get('/api/estadistiques', 'EstadistiquesController', 'index');
$router->get('/api/badges', 'BadgesController', 'index');

$router->get('/api/gestio/jocs', 'GestioJocsController', 'llista');
$router->get('/api/gestio/jocs/nou', 'GestioJocsController', 'nou');
$router->post('/api/gestio/jocs', 'GestioJocsController', 'crear');
$router->get('/api/gestio/jocs/{id}', 'GestioJocsController', 'editar');
$router->post('/api/gestio/jocs/{id}', 'GestioJocsController', 'actualitzar');

$_GET['route'] = '/api/' . ltrim(prepararRutaApi($segments), '/');
$router->dispatch();

function obtenirSegmentsApi(): array
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
    $prefixes = ['/mvc/api/api.php', '/api/api.php', '/mvc/api', '/api'];
    $path = $uri;

    foreach ($prefixes as $prefix) {
        if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
            $path = substr($path, strlen($prefix));
            break;
        }
    }

    if ($path === $uri && !empty($_SERVER['PATH_INFO'])) {
        $path = $_SERVER['PATH_INFO'];
    }

    $segments = array_values(array_filter(
        explode('/', trim($path, '/')),
        static fn($segment) => $segment !== ''
    ));

    if ($segments === [] && isset($_GET['joc_id'], $_GET['nivell'])) {
        $segments = ['jocs', (string) $_GET['joc_id'], 'nivells', (string) $_GET['nivell']];
    }

    return $segments;
}

function prepararRutaApi(array $segments): string
{
    $parts = $segments;
    $total = count($parts);

    if ($total >= 2 && $parts[0] === 'jocs' && ctype_digit($parts[1])) {
        $_GET['id'] = $parts[1];
        $parts[1] = '{id}';
        if ($total >= 4 && $parts[2] === 'nivells' && ctype_digit($parts[3])) {
            $parts[3] = '{nivell}';
        }
    }

    if ($total >= 2 && $parts[0] === 'usuaris' && ctype_digit($parts[1])) {
        $_GET['id'] = $parts[1];
        $parts[1] = '{id}';
    }

    if ($total >= 1 && $parts[0] === 'amics' && isset($parts[1]) && ctype_digit($parts[1])) {
        $_POST['id'] = $parts[1];
        $parts[1] = '{id}';
    }

    if ($total >= 2 && $parts[0] === 'gestio' && $parts[1] === 'jocs' && isset($parts[2]) && ctype_digit($parts[2])) {
        $_GET['id'] = $parts[2];
        $_POST['id'] = $parts[2];
        $parts[2] = '{id}';
    }

    return implode('/', $parts);
}
