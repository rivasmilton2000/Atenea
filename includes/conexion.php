<?php
declare(strict_types=1);

/**
 * Conexión PDO compartida. En producción configure ATENEA_DB_* en el entorno.
 */
function obtenerConexion(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('ATENEA_DB_HOST') ?: 'localhost';
    $database = getenv('ATENEA_DB_NAME') ?: 'db_atenea';
    $user = getenv('ATENEA_DB_USER') ?: 'root';
    $password = getenv('ATENEA_DB_PASSWORD');
    $password = $password === false ? '' : $password;

    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // El Salvador does not observe daylight-saving time.
    $pdo->exec("SET time_zone = '-06:00'");

    return $pdo;
}
