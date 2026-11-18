<?php
require_once 'models/UsuariModel.php';
require_once 'core/Session.php';
require_once 'core/Roles.php';

class AuthController
{
    private $usuariModel;

    public function __construct()
    {
        $this->usuariModel = new UsuariModel();
    }

    public function mostrarLogin()
    {
        include 'views/auth/login.php';
    }

    public function login()
    {
        if ($_POST) {
            // VULNERABILITAT: Sense protecció CSRF
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $usuari = $this->usuariModel->login($email, $password);

            if ($usuari) {
                Session::set('usuari_id', $usuari['id']);
                Session::set('nom_usuari', $usuari['nom_usuari']);

                // Rol (Base64) llegit de la BD i enviat a la cookie "rol" (vegeu core/Roles.php)
                Roles::establirCookie($usuari['rol'] ?? Roles::codificar(Roles::USUARI));

                // VULNERABILITAT: Open Redirect
                $redirect = $_GET['redirect'] ?? '/mvc/';
                header("Location: $redirect");
                exit;
            } else {
                $error = "Credencials incorrectes";
                include 'views/auth/login.php';
            }
        }
    }

    public function mostrarRegistre()
    {
        include 'views/auth/registre.php';
    }

    public function registre()
    {
        if ($_POST) {
            // VULNERABILITAT: Validació insuficient
            $nomUsuari = $_POST['nom_usuari'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $nomComplet = $_POST['nom_complet'] ?? '';

            // Crear usuari sense validacions
            $this->usuariModel->crear($nomUsuari, $email, $password, $nomComplet);

            header("Location: /mvc/?route=/login");
            exit;
        }
    }

    public function logout()
    {
        Session::logout();
        Roles::esborrarCookie();
        header("Location: /mvc/");
        exit;
    }
}
