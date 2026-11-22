<?php
require_once __DIR__ . '/../models/JocModel.php';
require_once __DIR__ . '/../core/GameUploader.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Roles.php';

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

    private function requerirGestor()
    {
        if (!Session::isLoggedIn()) {
            json_error('Cal iniciar sessió', 401, [], ['redirect' => '/mvc/?route=/login']);
        }
        if (!Roles::potGestionarJocs()) {
            json_error('Accés denegat: aquesta secció és només per a desenvolupadors i administradors.', 403);
        }
    }

    private function potModificar($joc)
    {
        if (Roles::esAdmin()) {
            return true;
        }
        return $joc['developer_id'] !== null
            && (int) $joc['developer_id'] === (int) Session::get('usuari_id');
    }

    private function carregarJocPropi($id)
    {
        $joc = $this->jocModel->obtenirJoc((int) $id);
        if (!$joc) {
            Session::flash('Joc no trobat.', 'error');
            json_error('Joc no trobat', 404);
        }
        if (!$this->potModificar($joc)) {
            json_error('No tens permís per modificar aquest joc.', 403);
        }
        return $joc;
    }

    private function llegirCamps()
    {
        $dades = json_input();
        return [
            'nom' => trim($dades['nom_joc'] ?? ''),
            'descripcio' => trim($dades['descripcio'] ?? ''),
            'puntuacio' => max(0, (int) ($dades['puntuacio_maxima'] ?? 0)),
            'nivells' => max(1, (int) ($dades['nivells_totals'] ?? 1)),
            'actiu' => isset($dades['actiu']) ? 1 : 0,
        ];
    }

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

    public function llista()
    {
        $this->requerirGestor();
        $jocs = $this->jocModel->obtenirJocsGestio(Session::get('usuari_id'), Roles::esAdmin());
        json_ok(['jocs' => $jocs]);
    }

    public function nou()
    {
        $this->requerirGestor();
        json_ok(['joc' => null, 'error' => null]);
    }

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
            json_error($error, 422, ['joc' => $this->campsDeVolta($camps)]);
        }

        $id = $this->jocModel->crearJoc(
            $camps['nom'], $camps['descripcio'], $camps['puntuacio'], $camps['nivells'], $camps['actiu'],
            Session::get('usuari_id')
        );

        try {
            $this->jocModel->establirRutaIndex($id, GameUploader::desplegar($zip['tmp_name'], $id));
        } catch (Exception $e) {
            GameUploader::eliminar($id);
            $this->jocModel->eliminarJoc($id);
            json_error($e->getMessage(), 422, ['joc' => $this->campsDeVolta($camps)]);
        }

        Session::flash('Joc "' . $camps['nom'] . "\" publicat correctament (id $id).");
        json_ok([], ['redirect' => '/mvc/?route=/gestio-jocs']);
    }

    public function editar()
    {
        $this->requerirGestor();
        $joc = $this->carregarJocPropi($_GET['id'] ?? 0);
        json_ok(['joc' => $joc, 'error' => null]);
    }

    public function actualitzar()
    {
        $this->requerirGestor();
        $dades = json_input();
        $joc = $this->carregarJocPropi($dades['id'] ?? 0);
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
            json_error($error, 422, ['joc' => array_merge($joc, $this->campsDeVolta($camps))]);
        }

        if ($zip !== null) {
            if (!empty($joc['ruta_index'])) {
                GameUploader::eliminar($joc['id']);
            }
            try {
                $this->jocModel->establirRutaIndex($joc['id'], GameUploader::desplegar($zip['tmp_name'], $joc['id']));
            } catch (Exception $e) {
                json_error($e->getMessage(), 422, ['joc' => array_merge($joc, $this->campsDeVolta($camps))]);
            }
        }

        $this->jocModel->actualitzarJoc(
            $joc['id'], $camps['nom'], $camps['descripcio'], $camps['puntuacio'], $camps['nivells'], $camps['actiu']
        );
        Session::flash('Joc actualitzat correctament.');
        json_ok([], ['redirect' => '/mvc/?route=/gestio-jocs']);
    }

    public function eliminar()
    {
        $this->requerirGestor();
        $dades = json_input();
        $joc = $this->carregarJocPropi($dades['id'] ?? 0);

        if (!empty($joc['ruta_index'])) {
            GameUploader::eliminar($joc['id']);
        }
        $this->jocModel->eliminarJoc($joc['id']);

        Session::flash('Joc "' . $joc['nom_joc'] . '" eliminat.');
        json_ok([], ['redirect' => '/mvc/?route=/gestio-jocs']);
    }
}
