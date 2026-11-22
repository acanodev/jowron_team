<?php
require_once __DIR__ . '/../models/EstadistiquesModel.php';
require_once __DIR__ . '/../models/UsuariModel.php';
require_once __DIR__ . '/../models/AmistatModel.php';
require_once __DIR__ . '/../models/BadgeModel.php';
require_once __DIR__ . '/../core/Session.php';

/**
 * Estadístiques avançades amb gràfics.
 *
 * VULNERABILITATS INTENCIONADES:
 *  - V5 (SQL Injection): el filtre "q" de l'historial (EstadistiquesModel::historial).
 *  - V6 (Exposició d'informació): el paràmetre ?usuari_id= permet veure les
 *    estadístiques de qualsevol usuari sense comprovar permisos ni amistat.
 */
class EstadistiquesController
{
    public function index()
    {
        if (!Session::isLoggedIn()) {
            json_error('Cal iniciar sessió', 401, [], ['redirect' => '/mvc/?route=/login']);
        }
        $jo = (int) Session::get('usuari_id');

        // VULNERABILITAT V6: no es comprova que $usuariId sigui l'usuari autenticat
        $usuariId = (int) ($_GET['usuari_id'] ?? $jo);

        $estadistiques = new EstadistiquesModel();
        $usuaris = new UsuariModel();

        $usuari = $usuaris->obtenirUsuari($usuariId);
        if (!$usuari) {
            json_error('Usuari no trobat', 404);
        }

        $esMeu = ($usuariId === $jo);
        $amics = (new AmistatModel())->obtenirAmics($usuariId);
        $idsComparar = array_merge([$usuariId], array_column($amics, 'id'));

        $cerca = $_GET['q'] ?? '';
        $ordre = $_GET['ordre'] ?? 'data';

        $historial = [];
        $errorSql = null;
        try {
            // VULNERABILITAT V5: $cerca arriba sense filtrar a la consulta SQL
            $historial = $estadistiques->historial($usuariId, $cerca, $ordre);
        } catch (PDOException $e) {
            $errorSql = $e->getMessage();
        }

        $resum = $estadistiques->resum($usuariId);
        $jocsMesJugats = $estadistiques->jocsMesJugats($usuariId);
        $evolucio = $estadistiques->evolucio($usuariId);
        $ranking = array_slice($usuaris->obtenirRanking(), 0, 10);
        $comparativa = $estadistiques->comparativa($idsComparar);
        $numBadges = count((new BadgeModel())->obtingutsDetall($usuariId));

        json_ok([
            'usuari' => $usuari,
            'usuariId' => $usuariId,
            'esMeu' => $esMeu,
            'resum' => $resum,
            'cerca' => $cerca,
            'ordre' => $ordre,
            'jocsMesJugats' => $jocsMesJugats,
            'evolucio' => $evolucio,
            'ranking' => $ranking,
            'comparativa' => $comparativa,
            'historial' => $historial,
            'errorSql' => $errorSql,
            'numBadges' => $numBadges,
            'datasets' => [
                'victoriesDerrotes' => [
                    ['etiqueta' => 'Victòries', 'valor' => (int) $resum['victories'], 'color' => '#4cc38a'],
                    ['etiqueta' => 'Derrotes', 'valor' => (int) $resum['derrotes'], 'color' => '#fa5c5c'],
                ],
                'jocsMesJugats' => array_map(fn($r) => [
                    'etiqueta' => $r['nom_joc'], 'valor' => (int) $r['partides'],
                ], $jocsMesJugats),
                'evolucio' => array_map(fn($r) => [
                    'etiqueta' => substr($r['data_partida'], 5, 5), 'valor' => (int) $r['puntuacio_obtinguda'],
                ], $evolucio),
                'ranking' => array_map(fn($r) => [
                    'etiqueta' => $r['nom_usuari'], 'valor' => (int) $r['puntuacio_total'],
                ], $ranking),
                'comparativa' => array_map(fn($r) => [
                    'etiqueta' => $r['nom_usuari'], 'valor' => (int) $r['puntuacio_total'],
                ], $comparativa),
            ],
        ]);
    }
}
