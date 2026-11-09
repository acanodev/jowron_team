<?php

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Mètode no suportat']);
    exit;
}

$segments = obtenirSegmentsApi();

if (count($segments) !== 4 || $segments[0] !== 'jocs' || $segments[2] !== 'nivells') {
    http_response_code(400);
    echo json_encode(['error' => 'Ruta no vàlida. Exemple: /api/jocs/1/nivells/3']);
    exit;
}

$jocId = $segments[1];
$nivell = $segments[3];

$pdo = Database::getConnection();

$sql = "SELECT configuracio_json FROM nivells_joc WHERE joc_id = $jocId AND nivell = $nivell";

try {
    $resultat = $pdo->query($sql);

    if ($resultat && $fila = $resultat->fetch(PDO::FETCH_ASSOC)) {
        echo $fila['configuracio_json'];
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Joc o nivell no trobat']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error a la consulta SQL. Joc o nivell inexistent.']);
} finally {
    $pdo = null;
}

function obtenirSegmentsApi(): array
{
    $path = $_SERVER['PATH_INFO'] ?? '';

    if ($path === '' && isset($_SERVER['REQUEST_URI'])) {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
        $script = $_SERVER['SCRIPT_NAME'] ?? '';

        if ($script !== '' && str_starts_with($uri, $script)) {
            $path = substr($uri, strlen($script));
        }
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
