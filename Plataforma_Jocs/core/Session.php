<?php
class Session
{
    public static function start()
    {
        if (session_status() == PHP_SESSION_NONE) {
            // VULNERABILITAT: Sessions insegures
            ini_set('session.cookie_httponly', '0'); // JavaScript pot accedir
            ini_set('session.cookie_secure', '0');   // HTTP (no HTTPS)
            session_start();
        }
    }

    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get($key)
    {
        self::start();
        return $_SESSION[$key] ?? null;
    }

    public static function isLoggedIn()
    {
        return self::get('usuari_id') !== null;
    }

    // Redirigeix al login si no hi ha sessió (evita repetir el mateix bloc als controladors)
    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            header("Location: /mvc/?route=/login");
            exit;
        }
    }

    // Missatges d'una sola lectura (flash) per mostrar-los després d'una redirecció
    public static function flash($missatge = null, $tipus = 'ok')
    {
        self::start();
        if ($missatge !== null) {
            $_SESSION['flash'] = ['text' => $missatge, 'tipus' => $tipus];
            return null;
        }
        $f = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $f;
    }

    // VULNERABILITAT: Logout insegur
    public static function logout()
    {
        self::start();
        unset($_SESSION['usuari_id']); // No destrueix completament la sessió
    }
}
