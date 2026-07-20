<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

/**
 * Conexión PDO compartida. En producción configure ATENEA_DB_* en el entorno.
 */
function obtenerConexion(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = entornoAtenea('ATENEA_DB_HOST', 'localhost');
    $database = entornoAtenea('ATENEA_DB_NAME', 'db_atenea');
    $user = entornoAtenea('ATENEA_DB_USER', 'root');
    $password = entornoAtenea('ATENEA_DB_PASSWORD');

    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch(PDOException $error) {
        if(PHP_SAPI!=='cli'&&function_exists('idSeguimientoAtenea')&&function_exists('mostrarPaginaErrorAtenea')){
            $incidente=idSeguimientoAtenea();$usuario=isset($_SESSION['usuario_id'])?(int)$_SESSION['usuario_id']:0;$url=mb_substr((string)($_SERVER['REQUEST_URI']??''),0,500);
            error_log('Atenea incidente='.$incidente.' http=503 usuario='.$usuario.' url='.$url.' mensaje=Conexión a la base de datos no disponible.');
            mostrarPaginaErrorAtenea(503,$incidente,'base_datos');
        }
        throw $error;
    }

    // El Salvador does not observe daylight-saving time.
    $pdo->exec("SET time_zone = '-06:00'");

    return $pdo;
}
