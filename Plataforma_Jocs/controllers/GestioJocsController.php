<?php
require_once 'models/JocModel.php';
require_once 'core/GameUploader.php';
require_once 'core/Session.php';
require_once 'core/Roles.php';

/**
 * CRUD de jocs. Accés: rol "admin" (tots els jocs) o "developer" (només els seus).
 * Els usuaris amb rol "usuari" reben un 403.
 *
 * VULNERABILITATS INTENCIONADES:
 *  - V1: el rol s'obté de la cookie "rol" (Base64, sense signar), vegeu core/Roles.php.
 *  - V2: pujada de ZIP insegura / Zip Slip, vegeu core/GameUploader.php.
 */
class GestioJocsController
{
    private $jocModel;

    public function __construct()
    {
        $this->jocModel = new JocModel();
    }

    // Exigeix sessió i rol admin/developer. Sense sessió -> login; sense rol -> 403.
    private function requerirGestor()
    {
        Session::requireLogin();
        if (!Roles::potGestionarJocs()) {
            http_response_code(403);
            $error = 'Accés denegat: aquesta secció és només per a desenvolupadors i administradors.';
            include 'views/gestio_jocs/denegat.php';
            exit;
        }
    }

    // Admin: qualsevol joc. Developer: només si és el propietari.
    private function potModificar($joc)
    {
        if (Roles::esAdmin()) {
            return true;
        }
        return $joc['developer_id'] !== null
            && (int) $joc['developer_id'] === (int) Session::get('usuari_id');
    }

    // Carrega un joc i comprova que l'usuari el pot modificar (404 / 403)
    private function carregarJocPropi($id)
    {
        $joc = $this->jocModel->obtenirJoc((int) $id);
        if (!$joc) {
            Session::flash("Joc no trobat.", 'error');
            header("Location: /mvc/?route=/gestio-jocs");
            exit;
        }
        if (!$this->potModificar($joc)) {
            http_response_code(403);
            $error = 'No tens permís per modificar aquest joc.';
            include 'views/gestio_jocs/denegat.php';
            exit;
        }
        return $joc;
    }

    private function llegirCamps()
    {
        return [
            'nom' => trim($_POST['nom_joc'] ?? ''),
            'descripcio' => trim($_POST['descripcio'] ?? ''),
            'puntuacio' => max(0, (int) ($_POST['puntuacio_maxima'] ?? 0)),
            'nivells' => max(1, (int) ($_POST['nivells_totals'] ?? 1)),
            'actiu' => isset($_POST['actiu']) ? 1 : 0,
        ];
    }

    // Camps rebuts, per tornar-los al formulari quan hi ha un error de validació
    private function campsDeVolta(array $camps)
    {
        return [
            'nom_joc' => $camps['nom'], 'descripcio' => $camps['descripcio'],
            'puntuacio_maxima' => $camps['puntuacio'], 'nivells_totals' => $camps['nivells'], 'actiu' => $camps['actiu'],
        ];
    }

    private function zipPujat()
    {
        if (!isset($_FILES['zip']) || $_FILES['zip']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        return $_FILES['zip'];
    }

    // READ: llistat
    public function llista()
    {
        $this->requerirGestor();
        $jocs = $this->jocModel->obtenirJocsGestio(Session::get('usuari_id'), Roles::esAdmin());
        $flash = Session::flash();
        include 'views/gestio_jocs/llista.php';
    }

    // CREATE: formulari
    public function nou()
    {
        $this->requerirGestor();
        $joc = null;
        $error = null;
        include 'views/gestio_jocs/formulari.php';
    }

    // CREATE: processa el formulari + ZIP
    public function crear()
    {
        $this->requerirGestor();
        $camps = $this->llegirCamps();
        $zip = $this->zipPujat();

        $error = null;
        if ($camps['nom'] === '') {
            $error = 'El nom del joc és obligatori.';
        } elseif ($zip === null || $zip['error'] !== UPLOAD_ERR_OK) {
            $error = 'Cal seleccionar un fitxer .zip (error de pujada: ' . ($zip['error'] ?? 'cap fitxer') . ').';
        } elseif (strtolower(pathinfo($zip['name'], PATHINFO_EXTENSION)) !== 'zip') {
            $error = 'El fitxer ha de tenir extensió .zip.';
        }
        if ($error) {
            $joc = $this->campsDeVolta($camps);
            include 'views/gestio_jocs/formulari.php';
            return;
        }

        // 1. Es crea el joc i s'obté el seu identificador únic autogenerat
        $id = $this->jocModel->crearJoc(
            $camps['nom'], $camps['descripcio'], $camps['puntuacio'], $camps['nivells'], $camps['actiu'],
            Session::get('usuari_id')
        );

        // 2-6. Directori public/js/jocs/<id>/, extracció del ZIP i localització de l'index.html
        try {
            $this->jocModel->establirRutaIndex($id, GameUploader::desplegar($zip['tmp_name'], $id));
        } catch (Exception $e) {
            GameUploader::eliminar($id);
            $this->jocModel->eliminarJoc($id);
            $error = $e->getMessage();
            $joc = $this->campsDeVolta($camps);
            include 'views/gestio_jocs/formulari.php';
            return;
        }

        Session::flash('Joc "' . $camps['nom'] . "\" publicat correctament (id $id).");
        header("Location: /mvc/?route=/gestio-jocs");
        exit;
    }

    // UPDATE: formulari
    public function editar()
    {
        $this->requerirGestor();
        $joc = $this->carregarJocPropi($_GET['id'] ?? 0);
        $error = null;
        include 'views/gestio_jocs/formulari.php';
    }

    // UPDATE: processa (opcionalment substitueix el ZIP)
    public function actualitzar()
    {
        $this->requerirGestor();
        $joc = $this->carregarJocPropi($_POST['id'] ?? 0);
        $camps = $this->llegirCamps();
        $zip = $this->zipPujat();

        $error = null;
        if ($camps['nom'] === '') {
            $error = 'El nom del joc és obligatori.';
        } elseif ($zip !== null && $zip['error'] !== UPLOAD_ERR_OK) {
            $error = 'Error en pujar el ZIP (codi ' . $zip['error'] . ').';
        } elseif ($zip !== null && strtolower(pathinfo($zip['name'], PATHINFO_EXTENSION)) !== 'zip') {
            $error = 'El fitxer ha de tenir extensió .zip.';
        }
        if ($error) {
            $joc = array_merge($joc, $this->campsDeVolta($camps));
            include 'views/gestio_jocs/formulari.php';
            return;
        }

        if ($zip !== null) {
            // Si el joc ja era un ZIP, es substitueix el seu directori. Els jocs de la
            // plataforma (ruta_index NULL, p.ex. el joc 1) no s'esborren: s'hi extreu per sobre.
            if (!empty($joc['ruta_index'])) {
                GameUploader::eliminar($joc['id']);
            }
            try {
                $this->jocModel->establirRutaIndex($joc['id'], GameUploader::desplegar($zip['tmp_name'], $joc['id']));
            } catch (Exception $e) {
                $error = $e->getMessage();
                $joc = array_merge($joc, $this->campsDeVolta($camps));
                include 'views/gestio_jocs/formulari.php';
                return;
            }
        }

        $this->jocModel->actualitzarJoc(
            $joc['id'], $camps['nom'], $camps['descripcio'], $camps['puntuacio'], $camps['nivells'], $camps['actiu']
        );
        Session::flash('Joc actualitzat correctament.');
        header("Location: /mvc/?route=/gestio-jocs");
        exit;
    }

    // DELETE
    public function eliminar()
    {
        $this->requerirGestor();
        $joc = $this->carregarJocPropi($_POST['id'] ?? 0);

        if (!empty($joc['ruta_index'])) {
            GameUploader::eliminar($joc['id']);
        }
        $this->jocModel->eliminarJoc($joc['id']);

        Session::flash('Joc "' . $joc['nom_joc'] . '" eliminat.');
        header("Location: /mvc/?route=/gestio-jocs");
        exit;
    }
}
