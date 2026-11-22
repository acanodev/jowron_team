<?php
require_once __DIR__ . '/../models/UsuariModel.php';
require_once __DIR__ . '/../models/PartidaModel.php';
require_once __DIR__ . '/../models/BadgeModel.php';
require_once __DIR__ . '/../core/Session.php';

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
            json_error('Cal iniciar sessió', 401, [], ['redirect' => '/mvc/?route=/login']);
        }

        // VULNERABILITAT: IDOR - es pot veure perfil d'altres usuaris via URL
        $usuariId = $_GET['id'] ?? Session::get('usuari_id');
        $usuari = $this->usuariModel->obtenirUsuari($usuariId);

        if (!$usuari) {
            json_error('Usuari no trobat', 404);
        }

        $partides = $this->partidaModel->obtenirPartides($usuariId);

        $badgeModel = new BadgeModel();
        if ((int) $usuariId === (int) Session::get('usuari_id')) {
            $badgeModel->avaluar($usuariId);
        }
        $badges = $badgeModel->obtingutsDetall((int) $usuariId);

        json_ok([
            'usuari' => $usuari,
            'partides' => $partides,
            'badges' => $badges,
        ]);
    }

    public function ranking()
    {
        $ranking = $this->usuariModel->obtenirRanking();
        json_ok(['ranking' => $ranking]);
    }

    public function actualitzarPerfil()
    {
        if (!Session::isLoggedIn()) {
            json_error('Cal iniciar sessió', 401, [], ['redirect' => '/mvc/?route=/login']);
        }

        $dades = json_input();
        // VULNERABILITAT: Sense protecció CSRF i validació insuficient
        $usuariId = Session::get('usuari_id');
        $usuari = $this->usuariModel->obtenirUsuari($usuariId);

        $nomComplet = $dades['nom_complet'] ?? '';
        $email = $dades['email'] ?? '';
        $avatar = $dades['avatar'] ?? '';

        if (!empty($usuari['avatar'])) {
            unlink($usuari['avatar']);
        }

        // VULNERABILITAT: Upload insegur
        $info = pathinfo($_FILES['avatar']['name'] ?? '');
        $ext = $info['extension'] ?? '';
        $newname = "$usuariId." . $ext;

        $target = 'public/uploads/' . $newname;
        move_uploaded_file($_FILES['avatar']['tmp_name'] ?? '', $target);

        // VULNERABILITAT: SQL Injection
        $sql = "UPDATE usuaris SET nom_complet = '$nomComplet', email = '$email', avatar = '$target' WHERE id = $usuariId";
        $this->usuariModel->pdo->exec($sql);

        // VULNERABILITAT: Open Redirect
        $redirect = $_GET['redirect'] ?? '/mvc/?route=/perfil';
        json_ok([], ['redirect' => $redirect]);
    }
}
