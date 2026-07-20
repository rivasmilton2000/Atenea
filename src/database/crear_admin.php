<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script solo puede ejecutarse desde la terminal.');
}

require_once dirname(__DIR__, 2) . '/includes/conexion.php';

$passwordTemporal = entornoAtenea('ATENEA_ADMIN_PASSWORD');
$crearPruebas = in_array('--crear-pruebas', $argv, true);

if (strlen($passwordTemporal) < 12) {
    fwrite(STDERR, "La contraseña temporal debe tener al menos 12 caracteres.\n");
    exit(1);
}

$usuarios = [
    ['Administrador', 'Atenea', 'admin@atenea.local', 'admin', 'activo'],
];

if ($crearPruebas) {
    $usuarios[] = ['Estudiante', 'Prueba', 'usuario@atenea.local', 'usuario', 'activo'];
    $usuarios[] = ['Docente', 'Prueba', 'docente@atenea.local', 'docente', 'activo'];
    $usuarios[] = ['Usuario', 'Inactivo', 'inactivo@atenea.local', 'usuario', 'inactivo'];
}

try {
    $pdo = obtenerConexion();
    $consulta = $pdo->prepare(
        'INSERT INTO usuarios (nombre, apellido, correo, password, rol, estado)
         VALUES (:nombre, :apellido, :correo, :password, :rol, :estado)
         ON DUPLICATE KEY UPDATE
           nombre = VALUES(nombre), apellido = VALUES(apellido), password = VALUES(password),
           rol = VALUES(rol), estado = VALUES(estado), updated_at = CURRENT_TIMESTAMP'
    );

    foreach ($usuarios as [$nombre, $apellido, $correo, $rol, $estado]) {
        $consulta->execute([
            'nombre' => $nombre,
            'apellido' => $apellido,
            'correo' => $correo,
            'password' => password_hash($passwordTemporal, PASSWORD_DEFAULT),
            'rol' => $rol,
            'estado' => $estado,
        ]);
        echo "Usuario preparado: {$correo} ({$rol}, {$estado})\n";
    }
    echo "Contraseña temporal configurada mediante variable de entorno.\n";
    echo "Cámbiela antes de utilizar el sistema fuera del entorno local.\n";
} catch (Throwable $error) {
    fwrite(STDERR, "No fue posible crear los usuarios. Verifique que db_atenea.sql esté importado y la conexión configurada.\n");
    exit(1);
}
