<?php
require_once 'models/UsuariModel.php';
require_once 'models/PartidaModel.php';
require_once 'core/Session.php';

class UsuariController
{
    private $usuariModel;
    private $partidaModel;

    public function __construct()
    {
        $this->usuariModel = new UsuariModel();
        $this->partidaModel = new PartidaModel();
    }

    public function perfil()
    {
        if (!Session::isLoggedIn()) {
            header("Location: /mvc/?route=/login");
            exit;
        }

        // VULNERABILITAT: IDOR - es pot veure perfil d'altres usuaris via URL
        $usuariId = $_GET['id'] ?? Session::get('usuari_id');
        $usuari = $this->usuariModel->obtenirUsuari($usuariId);

        if (!$usuari) {
            die("Usuari no trobat");
        }

        // Obtenir partides de l'usuari
        $partides = $this->partidaModel->obtenirPartides($usuariId);

        include 'views/usuari/perfil.php';
    }

    public function ranking()
    {
        $ranking = $this->usuariModel->obtenirRanking();
        include 'views/usuari/ranking.php';
    }

    public function actualitzarPerfil()
    {
        if (!Session::isLoggedIn()) {
            header("Location: /mvc/?route=/login");
            exit;
        }

        if ($_POST) {
            // VULNERABILITAT: Sense protecció CSRF i validació insuficient
            $usuariId = Session::get('usuari_id');
            $usuari = $this->usuariModel->obtenirUsuari($usuariId);

            $nomComplet = $_POST['nom_complet'] ?? '';
            $email = $_POST['email'] ?? '';
            $avatar = $_POST['avatar'] ?? '';
            
            if(!empty($usuari['avatar'])) {
                // Eliminar l'avatar anterior si existeix
                unlink($usuari['avatar']);
            }

            // VULNERABILITAT: Upload insegur
            $info = pathinfo($_FILES['avatar']['name']);
            $ext = $info['extension'];
            $newname = "$usuariId." . $ext;

            // Ruta relativa del directori images
            $target = 'public/uploads/' . $newname;
            move_uploaded_file($_FILES['avatar']['tmp_name'], $target);

            // VULNERABILITAT: SQL Injection
            $sql = "UPDATE usuaris SET nom_complet = '$nomComplet', email = '$email', avatar = '$target' WHERE id = $usuariId";
            $this->usuariModel->pdo->exec($sql);

            // VULNERABILITAT: Open Redirect
            $redirect = $_GET['redirect'] ?? '/mvc/?route=/perfil';
            header("Location: $redirect");
            exit;
        }
    }
}
