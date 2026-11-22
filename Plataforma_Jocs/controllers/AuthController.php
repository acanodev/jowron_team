<?php
require_once __DIR__ . '/../models/UsuariModel.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Roles.php';

class AuthController
{
    private $usuariModel;

    public function __construct()
    {
        $this->usuariModel = new UsuariModel();
    }

    public function mostrarLogin()
    {
        json_ok(['error' => null]);
    }

    public function login()
    {
        $dades = json_input();
        // VULNERABILITAT: Sense protecció CSRF
        $email = $dades['email'] ?? '';
        $password = $dades['password'] ?? '';

        $usuari = $this->usuariModel->login($email, $password);

        if (!$usuari) {
            json_error('Credencials incorrectes', 401);
        }

        Session::set('usuari_id', $usuari['id']);
        Session::set('nom_usuari', $usuari['nom_usuari']);

        // Rol (Base64) llegit de la BD i enviat a la cookie "rol" (vegeu core/Roles.php)
        Roles::establirCookie($usuari['rol'] ?? Roles::codificar(Roles::USUARI));

        // VULNERABILITAT: Open Redirect
        $redirect = $_GET['redirect'] ?? '/mvc/';
        json_ok(['usuari' => $usuari], ['redirect' => $redirect]);
    }

    public function mostrarRegistre()
    {
        json_ok(['valors' => json_input()]);
    }

    public function registre()
    {
        $dades = json_input();
        // VULNERABILITAT: Validació insuficient
        $nomUsuari = $dades['nom_usuari'] ?? '';
        $email = $dades['email'] ?? '';
        $password = $dades['password'] ?? '';
        $nomComplet = $dades['nom_complet'] ?? '';

        // Crear usuari sense validacions
        $this->usuariModel->crear($nomUsuari, $email, $password, $nomComplet);

        json_ok([], ['redirect' => '/mvc/?route=/login']);
    }

    public function logout()
    {
        Session::logout();
        Roles::esborrarCookie();
        json_ok([], ['redirect' => '/mvc/']);
    }

    public function me()
    {
        if (!Session::isLoggedIn()) {
            json_error('Cal iniciar sessió', 401);
        }

        $usuari = $this->usuariModel->obtenirUsuari(Session::get('usuari_id'));
        json_ok(['usuari' => $usuari]);
    }
}
