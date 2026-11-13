<?php
require_once 'models/JocModel.php';
require_once 'models/ProgressModel.php';
require_once 'core/Session.php';

class JocController
{
    private $jocModel;
    private $progressModel;

    public function __construct()
    {
        $this->jocModel = new JocModel();
        $this->progressModel = new ProgressModel();
    }

    public function llistaJocs()
    {
        $jocs = $this->jocModel->obtenirJocsActius();
        include 'views/jocs/llista.php';
    }

    public function jugar()
    {
        // VULNERABILITAT: IDOR - no verifica el jugador
        $jocId = $_GET['id'] ?? 0;
        $joc = $this->jocModel->obtenirJoc($jocId);

        if (!$joc) {
            die("Joc no trobat");
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

        include 'views/jocs/joc.php';
    }

    public function guardarPuntuacio()
    {
        if (!$_POST || !Session::isLoggedIn()) {
            echo json_encode(['success' => false]);
            return;
        }

        // VULNERABILITAT: Sense validació de dades
        $usuariId = Session::get('usuari_id');
        $jocId = (int) ($_POST['joc_id'] ?? 0);
        $nivell = (int) ($_POST['nivell'] ?? 1);
        $puntuacio = (int) ($_POST['puntuacio'] ?? 0);
        $durada = (int) ($_POST['durada'] ?? 0);

        $joc = $this->jocModel->obtenirJoc($jocId);

        if (!$joc) {
            echo json_encode(['success' => false, 'error' => 'Joc no trobat']);
            return;
        }

        $totalNivells = max(1, (int) $joc['nivells_totals']);
        $nivell = max(1, min($nivell, $totalNivells));

        $resultat = $this->progressModel->registrarNivellSuperat(
            $usuariId,
            $jocId,
            $nivell,
            max(0, $puntuacio),
            max(0, $durada),
            $totalNivells
        );

        echo json_encode(['success' => true] + $resultat);
    }
}
