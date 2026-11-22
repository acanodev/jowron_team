<?php
require_once __DIR__ . '/../models/JocModel.php';
require_once __DIR__ . '/../models/ProgressModel.php';
require_once __DIR__ . '/../models/PartidaModel.php';
require_once __DIR__ . '/../models/BadgeModel.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../config/database.php';

class JocController
{
    private $jocModel;
    private $progressModel;

    public function __construct()
    {
        $this->jocModel = new JocModel();
        $this->progressModel = new ProgressModel();
    }

    private function nivellsDe($jocId)
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare(
                'SELECT nivell, configuracio_json FROM nivells_joc WHERE joc_id = :id ORDER BY nivell'
            );
            $stmt->execute(['id' => $jocId]);
            return array_map(static fn($f) => [
                'nivell' => $f['nivell'],
                'configuracio' => json_decode($f['configuracio_json'], true),
            ], $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (Throwable $e) {
            return [];
        }
    }

    private function configuracioDe($jocId, $nivell)
    {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT configuracio_json FROM nivells_joc WHERE joc_id = $jocId AND nivell = $nivell";
            $fila = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
            return $fila ? json_decode($fila['configuracio_json'], true) : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function llistaJocs()
    {
        $jocs = $this->jocModel->obtenirJocsActius();
        json_ok(['jocs' => $jocs]);
    }

    public function jugar()
    {
        // VULNERABILITAT: IDOR - no verifica el jugador
        $jocId = $_GET['id'] ?? 0;
        $joc = $this->jocModel->obtenirJoc($jocId);

        if (!$joc) {
            json_error('Joc no trobat', 404);
        }

        $totalNivells = max(1, (int) $joc['nivells_totals']);
        $nivellActual = 1;

        if (Session::isLoggedIn()) {
            $nivellActual = $this->progressModel->obtenirNivellActual(
                Session::get('usuari_id'),
                $jocId
            );
            $nivellActual = max(1, min($nivellActual, $totalNivells));
        }

        $nomJugador = Session::get('nom_usuari') ?? 'Jowron';

        $indexFile = __DIR__ . '/../public/js/jocs/' . $joc['id'] . '/index.php';
        $indexHtmlFile = __DIR__ . '/../public/js/jocs/' . $joc['id'] . '/index.html';
        if (!file_exists($indexFile) && empty($joc['ruta_index']) && file_exists($indexHtmlFile)) {
            $joc['ruta_index'] = 'index.html';
        }
        $esJocHtml = !file_exists($indexFile) && !empty($joc['ruta_index']);
        $baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
        $gameBaseUrl = $baseDir . '/public/js/jocs/' . $joc['id'] . '/';
        $segments = array_map('rawurlencode', explode('/', $joc['ruta_index'] ?? ''));
        $urlJoc = $gameBaseUrl . implode('/', $segments);

        json_ok([
            'joc' => $joc,
            'esJocHtml' => $esJocHtml,
            'urlJoc' => $esJocHtml ? $urlJoc : $gameBaseUrl . 'index.php',
            'gameBaseUrl' => $gameBaseUrl,
            'nivellActual' => $nivellActual,
            'totalNivells' => $totalNivells,
            'nomJugador' => $nomJugador,
            'nivells' => $this->nivellsDe($jocId),
            'configuracio' => $this->configuracioDe($jocId, $nivellActual),
        ]);
    }

    public function guardarPuntuacio()
    {
        $dades = json_input();
        if (!$dades || !Session::isLoggedIn()) {
            json_send([
                'ok' => false,
                'data' => ['error' => 'Petició no vàlida'],
                'meta' => json_meta(),
                'success' => false,
            ], 401);
        }

        // VULNERABILITAT: Sense validació de dades
        $usuariId = Session::get('usuari_id');
        $jocId = (int) ($dades['joc_id'] ?? 0);
        $nivell = (int) ($dades['nivell'] ?? 1);
        $puntuacio = (int) ($dades['puntuacio'] ?? 0);
        $durada = (int) ($dades['durada'] ?? 0);

        $joc = $this->jocModel->obtenirJoc($jocId);

        if (!$joc) {
            json_send([
                'ok' => false,
                'data' => ['error' => 'Joc no trobat'],
                'meta' => json_meta(),
                'success' => false,
                'error' => 'Joc no trobat',
            ], 404);
        }

        $totalNivells = max(1, (int) $joc['nivells_totals']);
        $nivell = max(1, min($nivell, $totalNivells));

        // Els jocs poden informar d'una derrota amb resultat=derrota (estadístiques)
        if (($dades['resultat'] ?? 'victoria') === 'derrota') {
            (new PartidaModel())->registrarDerrota($usuariId, $jocId, $nivell, max(0, $puntuacio), max(0, $durada));
            $resposta = ['success' => true, 'resultat' => 'derrota'] + $this->badgesNous($usuariId);
            json_send(['ok' => true, 'data' => $resposta, 'meta' => json_meta()] + $resposta);
        }

        $resultat = $this->progressModel->registrarNivellSuperat(
            $usuariId,
            $jocId,
            $nivell,
            max(0, $puntuacio),
            max(0, $durada),
            $totalNivells
        );

        $resposta = ['success' => true] + $resultat + $this->badgesNous($usuariId);
        json_send(['ok' => true, 'data' => $resposta, 'meta' => json_meta()] + $resposta);
    }

    // Avalua els badges després de desar una partida. Retorna ['badgesNous' => [...]]
    // (nom, descripció i icona de cada assoliment desbloquejat) perquè el joc ho pugui mostrar.
    // Un error aquí no ha d'impedir que la partida quedi desada.
    private function badgesNous($usuariId)
    {
        try {
            $nous = (new BadgeModel())->avaluar($usuariId);
        } catch (Throwable $e) {
            error_log('[Badges] ' . $e->getMessage());
            $nous = [];
        }
        return ['badgesNous' => array_map(fn($b) => [
            'codi' => $b['codi'], 'nom' => $b['nom'], 'descripcio' => $b['descripcio'], 'icona' => $b['icona'],
        ], $nous)];
    }
}
