<?php
require_once 'models/AmistatModel.php';
require_once 'models/UsuariModel.php';
require_once 'models/PartidaModel.php';
require_once 'models/EstadistiquesModel.php';
require_once 'models/BadgeModel.php';
require_once 'core/Session.php';

/**
 * Sistema d'amics: cerca, sol·licituds, llista, perfil d'altres usuaris.
 *
 * VULNERABILITAT INTENCIONADA V3 (IDOR): respondre() i eliminar() actuen sobre
 * l'id d'amistat rebut per POST sense comprovar que pertanyi a l'usuari
 * autenticat (vegeu AmistatModel).
 */
class AmicsController
{
    private $amistats;

    public function __construct()
    {
        $this->amistats = new AmistatModel();
    }

    public function index()
    {
        Session::requireLogin();
        $jo = (int) Session::get('usuari_id');

        $cerca = trim($_GET['q'] ?? '');
        $resultats = [];
        if ($cerca !== '') {
            $resultats = $this->amistats->cercarUsuaris($cerca, $jo);
            foreach ($resultats as &$r) {
                $r['relacio'] = $this->amistats->obtenirRelacio($jo, $r['id']);
            }
            unset($r);
        }

        $amics = $this->amistats->obtenirAmics($jo);
        $rebudes = $this->amistats->obtenirPendentsRebudes($jo);
        $enviades = $this->amistats->obtenirEnviades($jo);
        $flash = Session::flash();

        include 'views/amics/index.php';
    }

    public function solicitar()
    {
        Session::requireLogin();
        $jo = (int) Session::get('usuari_id');

        $codi = $this->amistats->enviarSolicitud($jo, (int) ($_POST['receptor_id'] ?? 0));
        $missatges = [
            'jo_mateix' => "No et pots afegir a tu mateix.",
            'ja_amics' => "Ja sou amics.",
            'ja_pendent' => "Ja hi ha una sol·licitud pendent entre vosaltres.",
            'no_existeix' => "L'usuari no existeix.",
        ];
        if (isset($missatges[$codi])) {
            Session::flash($missatges[$codi], 'error');
        } else {
            Session::flash("Sol·licitud d'amistat enviada.");
        }

        header("Location: " . $this->tornarA('/amics'));
        exit;
    }

    // VULNERABILITAT V3 (IDOR): $_POST['id'] pot ser d'una sol·licitud d'un altre usuari
    public function respondre()
    {
        Session::requireLogin();
        $id = (int) ($_POST['id'] ?? 0);
        $accio = $_POST['accio'] ?? '';

        if (!in_array($accio, ['acceptar', 'rebutjar'], true)) {
            Session::flash('Acció no vàlida.', 'error');
        } elseif (!$this->amistats->respondre($id, $accio)) {
            Session::flash("No s'ha trobat cap sol·licitud pendent amb aquest id.", 'error');
        } else {
            Session::flash($accio === 'acceptar' ? 'Sol·licitud acceptada.' : 'Sol·licitud rebutjada.');
            if ($accio === 'acceptar') {
                $this->avaluarBadgesAmistat($id);
            }
        }

        header("Location: /mvc/?route=/amics");
        exit;
    }

    // VULNERABILITAT V3 (IDOR): $_POST['id'] pot ser d'una amistat aliena
    public function eliminar()
    {
        Session::requireLogin();
        $id = (int) ($_POST['id'] ?? 0);

        if (!$this->amistats->eliminar($id)) {
            Session::flash("No s'ha trobat l'amistat.", 'error');
        } else {
            Session::flash('Amistat eliminada.');
        }

        header("Location: /mvc/?route=/amics");
        exit;
    }

    // Perfil públic d'un altre usuari (sense email ni dades privades)
    public function perfilPublic()
    {
        Session::requireLogin();
        $jo = (int) Session::get('usuari_id');
        $id = (int) ($_GET['id'] ?? 0);

        $usuari = (new UsuariModel())->obtenirUsuari($id);
        if (!$usuari) {
            die("Usuari no trobat");
        }

        $resum = (new EstadistiquesModel())->resum($id);
        $partides = array_slice((new PartidaModel())->obtenirPartides($id), 0, 5);
        $relacio = ($id !== $jo) ? $this->amistats->obtenirRelacio($jo, $id) : false;
        $badges = (new BadgeModel())->obtingutsDetall($id);
        $flash = Session::flash();

        include 'views/amics/perfil_public.php';
    }

    // Una amistat acceptada pot desbloquejar badges ("Sociable", "Popular") als dos usuaris
    private function avaluarBadgesAmistat($amistatId)
    {
        try {
            $amistat = $this->amistats->obtenirAmistat($amistatId);
            if ($amistat) {
                $badges = new BadgeModel();
                $badges->avaluar((int) $amistat['solicitant_id']);
                $badges->avaluar((int) $amistat['receptor_id']);
            }
        } catch (Throwable $e) {
            error_log('[Badges] ' . $e->getMessage());
        }
    }

    // Només es permeten redireccions internes a /mvc/?route=...
    private function tornarA($per_defecte)
    {
        $ruta = $_POST['tornar'] ?? $per_defecte;
        if (strpos($ruta, '/') !== 0 || strpos($ruta, '//') === 0) {
            $ruta = $per_defecte;
        }
        return '/mvc/?route=' . $ruta;
    }
}
