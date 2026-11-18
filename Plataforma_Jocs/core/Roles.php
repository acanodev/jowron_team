<?php
/**
 * Gestió de rols: admin | developer | usuari
 *
 * ============================ LABORATORI EDUCATIU ============================
 * VULNERABILITAT INTENCIONADA (V1 - Escalada de privilegis per cookie Base64)
 *
 *  - El rol es desa a la BD (columna usuaris.rol) codificat en Base64.
 *  - En fer login s'envia al navegador a la cookie "rol" (també en Base64).
 *  - Els permisos es calculen NOMÉS a partir del valor de la cookie.
 *  - No hi ha signatura, HMAC ni cap mena de comprovació d'autenticitat.
 *  - No es compara mai el valor de la cookie amb el rol real de la BD.
 *
 * Amb Burp Suite es pot canviar la cookie "rol" (dXN1YXJp -> YWRtaW4=) i
 * obtenir permisos d'administrador.
 * ============================================================================
 */
class Roles
{
    const ADMIN = 'admin';
    const DEVELOPER = 'developer';
    const USUARI = 'usuari';

    const COOKIE = 'rol';

    public static function codificar($rol)
    {
        return base64_encode($rol);
    }

    // Envia la cookie amb el rol (ja codificat en Base64, tal com és a la BD)
    public static function establirCookie($rolBase64)
    {
        // VULNERABILITAT: sense HttpOnly, sense Secure i sense signatura
        setcookie(self::COOKIE, $rolBase64, [
            'expires' => 0,
            'path' => '/',
            'httponly' => false,
            'secure' => false,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[self::COOKIE] = $rolBase64;
    }

    public static function esborrarCookie()
    {
        setcookie(self::COOKIE, '', ['expires' => time() - 3600, 'path' => '/']);
        unset($_COOKIE[self::COOKIE]);
    }

    // VULNERABILITAT: el rol surt de la cookie decodificada, sense verificar res
    public static function actual()
    {
        if (!isset($_COOKIE[self::COOKIE])) {
            return self::USUARI;
        }
        return base64_decode($_COOKIE[self::COOKIE]);
    }

    public static function esAdmin()
    {
        return self::actual() === self::ADMIN;
    }

    public static function esDeveloper()
    {
        return self::actual() === self::DEVELOPER;
    }

    // Admin i developer poden accedir al CRUD de jocs
    public static function potGestionarJocs()
    {
        return Session::isLoggedIn() && in_array(self::actual(), [self::ADMIN, self::DEVELOPER], true);
    }
}
