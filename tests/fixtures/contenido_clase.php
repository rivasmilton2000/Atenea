<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__, 2) . '/includes/auth.php';

$pdo = obtenerConexion();
$accion = $argv[1] ?? '';
$correos = [
    'docente' => 'etapa5.docente@example.invalid',
    'ajeno' => 'etapa5.ajeno@example.invalid',
    'estudiante' => 'etapa5.estudiante@example.invalid',
    'sin_matricula' => 'etapa5.sinmatricula@example.invalid',
];
$codigoAsignatura = 'E5-CONTENIDO-TEST';

$limpiar = static function () use ($pdo, $correos, $codigoAsignatura): void {
    $marcas = implode(',', array_fill(0, count($correos), '?'));
    $consulta = $pdo->prepare("SELECT id FROM usuarios WHERE correo IN ({$marcas})");
    $consulta->execute(array_values($correos));
    $usuarios = array_map('intval', $consulta->fetchAll(PDO::FETCH_COLUMN));
    $consulta = $pdo->prepare('SELECT id FROM asignaturas WHERE codigo = :codigo');
    $consulta->execute(['codigo' => $codigoAsignatura]);
    $asignaturaId = (int) $consulta->fetchColumn();

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    try {
        if ($asignaturaId) {
            $consulta = $pdo->prepare('SELECT id, archivo_relpath FROM contenidos WHERE asignatura_id = :id');
            $consulta->execute(['id' => $asignaturaId]);
            foreach ($consulta->fetchAll() as $contenido) {
                if (!empty($contenido['archivo_relpath'])) {
                    $base = rtrim(entornoAtenea('ACADEMIC_STORAGE_PATH', dirname(ATENEA_ROOT) . '/atenea-private/academico'), '/\\');
                    $ruta = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $contenido['archivo_relpath']);
                    if (is_file($ruta)) {
                        unlink($ruta);
                    }
                }
                $pdo->prepare('DELETE FROM progreso_contenido WHERE contenido_id = :id')->execute(['id' => $contenido['id']]);
                $pdo->prepare('DELETE FROM entrega_evidencias WHERE entrega_id IN (SELECT id FROM entregas_contenido WHERE contenido_id = :id)')->execute(['id' => $contenido['id']]);
                $pdo->prepare('DELETE FROM entregas_contenido WHERE contenido_id = :id')->execute(['id' => $contenido['id']]);
                $pdo->prepare('DELETE FROM contenido_comentarios WHERE contenido_id = :id')->execute(['id' => $contenido['id']]);
                $pdo->prepare('DELETE FROM admin_notices WHERE action_url LIKE :url')->execute(['url' => '%contenido.php?id=' . $contenido['id'] . '%']);
            }
            foreach (['contenidos', 'inscripciones_capacitacion', 'capacitacion_pagos', 'capacitacion_secciones', 'docentes_asignaturas'] as $tabla) {
                $pdo->prepare("DELETE FROM `{$tabla}` WHERE asignatura_id = :id")->execute(['id' => $asignaturaId]);
            }
            $pdo->prepare('DELETE FROM asignaturas WHERE id = :id')->execute(['id' => $asignaturaId]);
        }
        if ($usuarios) {
            $marcasUsuarios = implode(',', array_fill(0, count($usuarios), '?'));
            $pdo->prepare("DELETE FROM audit_logs WHERE actor_user_id IN ({$marcasUsuarios}) OR target_user_id IN ({$marcasUsuarios})")
                ->execute([...$usuarios, ...$usuarios]);
            $pdo->prepare("DELETE FROM admin_notices WHERE user_id IN ({$marcasUsuarios}) OR created_by IN ({$marcasUsuarios})")
                ->execute([...$usuarios, ...$usuarios]);
            $pdo->prepare("DELETE FROM usuarios WHERE id IN ({$marcasUsuarios})")->execute($usuarios);
        }
    } finally {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
};

if ($accion === 'cleanup') {
    $limpiar();
    echo "OK cleanup\n";
    exit;
}
if ($accion !== 'setup') {
    fwrite(STDERR, "Uso: setup|cleanup\n");
    exit(2);
}

$limpiar();
$adminId = (int) $pdo->query("SELECT id FROM usuarios WHERE rol = 'admin' AND estado = 'activo' ORDER BY id LIMIT 1")->fetchColumn();
if (!$adminId) {
    throw new RuntimeException('No existe un administrador activo.');
}

$password = 'Etapa5Contenido!2026';
$ubicacion = $pdo->query(
    'SELECT d.id AS distrito_id, m.id AS municipio_id, m.departamento_id
     FROM distritos d INNER JOIN municipios m ON m.id = d.municipio_id
     ORDER BY d.id LIMIT 1'
)->fetch();
if (!$ubicacion) {
    throw new RuntimeException('No existen ubicaciones para completar los perfiles de prueba.');
}
$crearUsuario = static function (string $correo, string $rol, string $nombre) use ($pdo, $password, $ubicacion): int {
    $numero = abs(crc32($correo));
    $dui = str_pad((string) ($numero % 100000000), 8, '0', STR_PAD_LEFT) . '-' . ($numero % 10);
    $telefono = '7' . str_pad((string) ($numero % 10000000), 7, '0', STR_PAD_LEFT);
    $consulta = $pdo->prepare(
        "INSERT INTO usuarios
            (nombre, apellido, nombre_usuario, correo, password, proveedor, email_verificado,
             rol, estado, perfil_estado, terminos_aceptados_at, fecha_nacimiento, dui,
             codigo_telefono, telefono, departamento_id, municipio_id, distrito_id, direccion)
         VALUES (:nombre, 'Prueba', :usuario, :correo, :password, 'local', 1,
                 :rol, 'activo', 'completo', NOW(), '1990-01-15', :dui,
                 '+503', :telefono, :departamento, :municipio, :distrito,
                 'Dirección temporal completa para las pruebas de contenido de clase')"
    );
    $consulta->execute([
        'nombre' => $nombre,
        'usuario' => str_replace(['@example.invalid', '.'], ['', '_'], $correo),
        'correo' => $correo,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'rol' => $rol,
        'dui' => $dui,
        'telefono' => $telefono,
        'departamento' => (int) $ubicacion['departamento_id'],
        'municipio' => (int) $ubicacion['municipio_id'],
        'distrito' => (int) $ubicacion['distrito_id'],
    ]);
    return (int) $pdo->lastInsertId();
};

$docenteId = $crearUsuario($correos['docente'], 'docente', 'Docente');
$docenteAjenoId = $crearUsuario($correos['ajeno'], 'docente', 'Docente ajeno');
$estudianteId = $crearUsuario($correos['estudiante'], 'usuario', 'Estudiante');
$estudianteAjenoId = $crearUsuario($correos['sin_matricula'], 'usuario', 'Sin matrícula');

$consulta = $pdo->prepare(
    "INSERT INTO asignaturas
        (codigo, nombre, slug, descripcion, tipo, estado_capacitacion, activo, estado, creado_por)
     VALUES (:codigo, 'Clase de contenido etapa 5', 'clase-contenido-etapa-5',
             'Clase temporal para pruebas automatizadas', 'capacitacion', 'publicada', 1, 'activo', :admin)"
);
$consulta->execute(['codigo' => $codigoAsignatura, 'admin' => $adminId]);
$asignaturaId = (int) $pdo->lastInsertId();

$pdo->prepare(
    "INSERT INTO docentes_asignaturas (docente_id, asignatura_id, estado, asignado_por)
     VALUES (:docente, :asignatura, 'activo', :admin)"
)->execute(['docente' => $docenteId, 'asignatura' => $asignaturaId, 'admin' => $adminId]);

$consulta = $pdo->prepare(
    "INSERT INTO capacitacion_secciones
        (asignatura_id, docente_id, codigo, nombre, estado, creada_por, cantidad_actual)
     VALUES (:asignatura, :docente, 'E5-S1', 'Sección contenido', 'abierta', :admin, 1)"
);
$consulta->execute(['asignatura' => $asignaturaId, 'docente' => $docenteId, 'admin' => $adminId]);
$seccionId = (int) $pdo->lastInsertId();

$consulta = $pdo->prepare(
    "INSERT INTO capacitacion_pagos
        (usuario_id, asignatura_id, checkout_key, importe, estado, paid_at)
     VALUES (:usuario, :asignatura, :clave, 0, 'pagado', NOW())"
);
$consulta->execute([
    'usuario' => $estudianteId,
    'asignatura' => $asignaturaId,
    'clave' => hash('sha256', 'etapa5-contenido-fixture'),
]);
$pagoId = (int) $pdo->lastInsertId();
$pdo->prepare(
    "INSERT INTO inscripciones_capacitacion
        (usuario_id, asignatura_id, pago_id, seccion_id, docente_id, estado,
         asignado_por, metodo_asignacion, assigned_at)
     VALUES (:usuario, :asignatura, :pago, :seccion, :docente, 'inscrito', :admin, 'manual', NOW())"
)->execute([
    'usuario' => $estudianteId,
    'asignatura' => $asignaturaId,
    'pago' => $pagoId,
    'seccion' => $seccionId,
    'docente' => $docenteId,
    'admin' => $adminId,
]);

echo json_encode([
    'password' => $password,
    'docente' => ['id' => $docenteId, 'correo' => $correos['docente']],
    'docente_ajeno' => ['id' => $docenteAjenoId, 'correo' => $correos['ajeno']],
    'estudiante' => ['id' => $estudianteId, 'correo' => $correos['estudiante']],
    'sin_matricula' => ['id' => $estudianteAjenoId, 'correo' => $correos['sin_matricula']],
    'asignatura_id' => $asignaturaId,
    'seccion_id' => $seccionId,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
