<?php
// VULNERABILITAT: Credencials hardcoded i exposades
class Database
{
    private static $host = 'localhost';
    private static $dbname = 'plataforma_videojocs';
    private static $username = 'plataforma_user';
    private static $password = '123456789a';

    public static function getConnection()
    {
        // Llista d'amfitrions a provar per garantir compatibilitat multi-SO (socket UNIX o TCP 'db'/'IP_DE_LA_VM')
        $hostsToTry = array_unique([
            self::$host,
            getenv('DB_HOST') ?: 'db',
            'IP_DE_LA_VM',
            '127.0.0.1'
        ]);

        $lastException = null;

        foreach ($hostsToTry as $h) {
            try {
                // Si usem localhost i el socket MariaDB esta disponible, fem servir el socket
                $dsn = ($h === 'localhost' && file_exists('/var/run/mysqld/mysqld.sock'))
                    ? "mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=" . self::$dbname . ";charset=utf8mb4"
                    : "mysql:host=" . $h . ";port=3306;dbname=" . self::$dbname . ";charset=utf8mb4";

                $pdo = new PDO(
                    $dsn,
                    self::$username,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                return $pdo;
            } catch (PDOException $e) {
                $lastException = $e;
            }
        }

        // VULNERABILITAT: Exposició d'informació sensible
        die("Error de connexió: " . ($lastException ? $lastException->getMessage() : 'Desconegut'));
    }
}
