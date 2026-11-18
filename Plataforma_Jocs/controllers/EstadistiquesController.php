<?php
require_once 'models/EstadistiquesModel.php';
require_once 'models/UsuariModel.php';
require_once 'models/AmistatModel.php';
require_once 'models/BadgeModel.php';
require_once 'core/Session.php';

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
        Session::requireLogin();
        $jo = (int) Session::get('usuari_id');

        // VULNERABILITAT V6: no es comprova que $usuariId sigui l'usuari autenticat
        $usuariId = (int) ($_GET['usuari_id'] ?? $jo);

        $estadistiques = new EstadistiquesModel();
        $usuaris = new UsuariModel();

        $usuari = $usuaris->obtenirUsuari($usuariId);
        if (!$usuari) {
            die("Usuari no trobat");
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
            // Es mostra l'error SQL tal qual (exposició d'informació, laboratori)
            $errorSql = $e->getMessage();
        }

        $resum = $estadistiques->resum($usuariId);
        $jocsMesJugats = $estadistiques->jocsMesJugats($usuariId);
        $evolucio = $estadistiques->evolucio($usuariId);
        $ranking = array_slice($usuaris->obtenirRanking(), 0, 10);
        $comparativa = $estadistiques->comparativa($idsComparar);
        $numBadges = count((new BadgeModel())->obtingutsDetall($usuariId));

        include 'views/estadistiques/index.php';
    }
}
