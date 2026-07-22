<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__, 2) . '/includes/contenido_clase.php';

$pdo = obtenerConexion();
$marca = 'e5_' . bin2hex(random_bytes(5));
$ids = ['usuarios' => [], 'pagos' => [], 'inscripciones' => []];
$temporales = [];
$pruebas = [];

$asegurar = static function (bool $condicion, string $mensaje) use (&$pruebas): void {
    if (!$condicion) {
        throw new RuntimeException('FALLO: ' . $mensaje);
    }
    $pruebas[] = $mensaje;
};

try {
    $adminId = (int) $pdo->query(
        "SELECT id FROM usuarios WHERE rol = 'admin' AND estado = 'activo' ORDER BY id LIMIT 1"
    )->fetchColumn();
    if (!$adminId) {
        throw new RuntimeException('Se necesita un administrador activo para ejecutar la prueba.');
    }

    $crearUsuario = static function (string $rol, string $sufijo) use ($pdo, $marca, &$ids): int {
        $consulta = $pdo->prepare(
            "INSERT INTO usuarios
                (nombre, apellido, correo, password, rol, estado, email_verificado, perfil_estado)
             VALUES (:nombre, 'Etapa 5', :correo, :password, :rol, 'activo', 1, 'completo')"
        );
        $consulta->execute([
            'nombre' => ucfirst($sufijo),
            'correo' => $marca . '.' . $sufijo . '@example.invalid',
            'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            'rol' => $rol,
        ]);
        $id = (int) $pdo->lastInsertId();
        $ids['usuarios'][] = $id;
        return $id;
    };

    $docente = $crearUsuario('docente', 'docente');
    $docenteAjeno = $crearUsuario('docente', 'ajeno');
    $estudiante = $crearUsuario('usuario', 'estudiante');
    $estudianteDos = $crearUsuario('usuario', 'estudiante2');
    $estudianteAjeno = $crearUsuario('usuario', 'sinmatricula');

    $consulta = $pdo->prepare(
        "INSERT INTO asignaturas
            (codigo, nombre, slug, descripcion, tipo, estado_capacitacion, activo, estado, creado_por)
         VALUES (:codigo, :nombre, :slug, 'Prueba automática etapa 5', 'capacitacion', 'publicada', 1, 'activo', :admin)"
    );
    $consulta->execute([
        'codigo' => strtoupper($marca),
        'nombre' => 'Contenido ' . $marca,
        'slug' => $marca,
        'admin' => $adminId,
    ]);
    $asignaturaId = (int) $pdo->lastInsertId();
    $ids['asignatura'] = $asignaturaId;

    $pdo->prepare(
        "INSERT INTO docentes_asignaturas (docente_id, asignatura_id, estado, asignado_por)
         VALUES (:docente, :asignatura, 'activo', :admin)"
    )->execute(['docente' => $docente, 'asignatura' => $asignaturaId, 'admin' => $adminId]);

    $consulta = $pdo->prepare(
        "INSERT INTO capacitacion_secciones
            (asignatura_id, docente_id, codigo, nombre, estado, creada_por, cantidad_actual)
         VALUES (:asignatura, :docente, :codigo, 'Sección etapa 5', 'abierta', :admin, 2)"
    );
    $consulta->execute([
        'asignatura' => $asignaturaId,
        'docente' => $docente,
        'codigo' => strtoupper($marca) . '-S1',
        'admin' => $adminId,
    ]);
    $seccionId = (int) $pdo->lastInsertId();
    $ids['seccion'] = $seccionId;

    foreach ([$estudiante, $estudianteDos] as $indice => $usuarioId) {
        $consulta = $pdo->prepare(
            "INSERT INTO capacitacion_pagos
                (usuario_id, asignatura_id, checkout_key, importe, estado, paid_at)
             VALUES (:usuario, :asignatura, :clave, 0, 'pagado', NOW())"
        );
        $consulta->execute([
            'usuario' => $usuarioId,
            'asignatura' => $asignaturaId,
            'clave' => hash('sha256', $marca . ':' . $indice),
        ]);
        $pagoId = (int) $pdo->lastInsertId();
        $ids['pagos'][] = $pagoId;
        $consulta = $pdo->prepare(
            "INSERT INTO inscripciones_capacitacion
                (usuario_id, asignatura_id, pago_id, seccion_id, docente_id, estado, asignado_por, metodo_asignacion, assigned_at)
             VALUES (:usuario, :asignatura, :pago, :seccion, :docente, 'inscrito', :admin, 'manual', NOW())"
        );
        $consulta->execute([
            'usuario' => $usuarioId,
            'asignatura' => $asignaturaId,
            'pago' => $pagoId,
            'seccion' => $seccionId,
            'docente' => $docente,
            'admin' => $adminId,
        ]);
        $ids['inscripciones'][] = (int) $pdo->lastInsertId();
    }

    $consulta = $pdo->prepare(
        "INSERT INTO contenidos
            (asignatura_id, seccion_id, docente_id, modulo, tipo, titulo, descripcion,
             video_url, fecha_publicacion, publicado_at, estado, activo, obligatorio, peso_progreso)
         VALUES
            (:asignatura, :seccion, :docente, 'Unidad segura', 'video', :titulo, :descripcion,
             :url, NOW(), NOW(), 'activo', 1, 0, 0)"
    );
    $consulta->execute([
        'asignatura' => $asignaturaId,
        'seccion' => $seccionId,
        'docente' => $docente,
        'titulo' => 'Publicación ' . $marca,
        'descripcion' => 'Explicación de prueba sin HTML.',
        'url' => 'https://youtu.be/dQw4w9WgXcQ',
    ]);
    $contenidoId = (int) $pdo->lastInsertId();
    $ids['contenido'] = $contenidoId;
    $contenido = [
        'id' => $contenidoId,
        'asignatura_id' => $asignaturaId,
        'seccion_id' => $seccionId,
        'docente_id' => $docente,
        'titulo' => 'Publicación ' . $marca,
    ];

    $youtube = analizarUrlRecursoContenido('https://youtu.be/dQw4w9WgXcQ');
    $asegurar(
        $youtube['proveedor'] === 'youtube'
        && $youtube['embed'] === 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
        'YouTube se convierte al reproductor sin cookies'
    );
    $drive = analizarUrlRecursoContenido('https://drive.google.com/file/d/1234567890AbCdEf/view');
    $asegurar(
        $drive['proveedor'] === 'google_drive'
        && str_ends_with((string) $drive['embed'], '/preview'),
        'Google Drive se convierte a una vista previa controlada'
    );
    $externo = analizarUrlRecursoContenido('https://example.com/recurso?id=1');
    $asegurar($externo['proveedor'] === 'externo' && $externo['embed'] === null, 'Un proveedor desconocido nunca se inserta en iframe');

    foreach (['javascript:alert(1)', 'data:text/html,test', 'http://example.com/recurso'] as $urlPeligrosa) {
        $rechazada = false;
        try {
            analizarUrlRecursoContenido($urlPeligrosa);
        } catch (DomainException) {
            $rechazada = true;
        }
        $asegurar($rechazada, 'Se rechaza el enlace peligroso ' . strtok($urlPeligrosa, ':'));
    }
    $asegurar(
        analizarUrlRecursoContenido('http://localhost/Atenea/recurso')['proveedor'] === 'externo',
        'HTTP solo se tolera en localhost para desarrollo'
    );

    $exclusividad = false;
    try {
        validarEntradaRecursoContenido('youtube', 'https://youtu.be/dQw4w9WgXcQ', true);
    } catch (DomainException) {
        $exclusividad = true;
    }
    $asegurar($exclusividad, 'No se acepta archivo y enlace al mismo tiempo');

    $asegurar(docentePoseeSeccion($pdo, $docente, $seccionId), 'El docente asignado posee la sección');
    $asegurar(!docentePoseeSeccion($pdo, $docenteAjeno, $seccionId), 'Otro docente no puede usar la sección');
    $asegurar(contenidoClasePuedeAdministrar($pdo, $contenido, $docente, 'docente'), 'El autor puede administrar la publicación');
    $asegurar(!contenidoClasePuedeAdministrar($pdo, $contenido, $docenteAjeno, 'docente'), 'Un docente ajeno no puede administrar la publicación');
    $asegurar(contenidoClasePuedeAdministrar($pdo, $contenido, $adminId, 'admin'), 'Administración puede moderar la publicación');
    $asegurar(contenidoClasePuedeVerEstudiante($pdo, $contenidoId, $estudiante), 'El estudiante matriculado puede ver la publicación');
    $asegurar(!contenidoClasePuedeVerEstudiante($pdo, $contenidoId, $estudianteAjeno), 'El estudiante no matriculado no puede verla');

    notificarPublicacionContenido($pdo, $contenido);
    notificarPublicacionContenido($pdo, $contenido);
    $consulta = $pdo->prepare(
        "SELECT COUNT(*) FROM admin_notices
         WHERE idempotency_key LIKE :clave AND user_id IN (:estudiante_uno, :estudiante_dos)"
    );
    $consulta->execute([
        'clave' => 'contenido-publicado:' . $contenidoId . ':u:%',
        'estudiante_uno' => $estudiante,
        'estudiante_dos' => $estudianteDos,
    ]);
    $asegurar((int) $consulta->fetchColumn() === 2, 'La notificación llega una sola vez a cada estudiante inscrito');

    $pdo->prepare(
        'INSERT INTO contenido_comentarios (contenido_id, usuario_id, cuerpo) VALUES (:contenido, :usuario, :cuerpo)'
    )->execute(['contenido' => $contenidoId, 'usuario' => $estudiante, 'cuerpo' => '¿Podría ampliar este tema?']);
    $comentarioId = (int) $pdo->lastInsertId();
    $pdo->prepare(
        'INSERT INTO contenido_comentarios (contenido_id, usuario_id, parent_id, cuerpo) VALUES (:contenido, :usuario, :parent, :cuerpo)'
    )->execute([
        'contenido' => $contenidoId,
        'usuario' => $docente,
        'parent' => $comentarioId,
        'cuerpo' => 'Sí, agregaré un ejemplo adicional.',
    ]);
    $conversacion = comentariosContenidoClase($pdo, $contenidoId);
    $asegurar(
        $conversacion['total'] === 1
        && count($conversacion['comentarios']) === 1
        && count($conversacion['comentarios'][0]['respuestas']) === 1,
        'La conversación conserva un comentario principal y respuestas de un nivel'
    );

    $pdo->prepare('UPDATE contenidos SET eliminado_at = NOW(), eliminado_por = :admin WHERE id = :id')
        ->execute(['admin' => $adminId, 'id' => $contenidoId]);
    $asegurar(!contenidoClasePuedeVerEstudiante($pdo, $contenidoId, $estudiante), 'El contenido eliminado deja de ser visible inmediatamente');

    $temporal = tempnam(sys_get_temp_dir(), 'atenea_e5_');
    file_put_contents($temporal, 'material educativo');
    $temporales[] = $temporal;
    $_FILES['_etapa5'] = [
        'name' => 'material.php.txt',
        'type' => 'text/plain',
        'tmp_name' => $temporal,
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($temporal),
    ];
    $dobleExtension = false;
    try {
        guardarArchivoAcademico('_etapa5', 'contenidos', 'contenido');
    } catch (DomainException) {
        $dobleExtension = true;
    }
    $asegurar($dobleExtension, 'Los nombres con doble extensión ejecutable se bloquean');

    echo 'OK ' . count($pruebas) . " pruebas\n";
    foreach ($pruebas as $prueba) {
        echo '- ' . $prueba . "\n";
    }
} finally {
    unset($_FILES['_etapa5']);
    foreach ($temporales as $temporal) {
        if (is_file($temporal)) {
            unlink($temporal);
        }
    }
    try {
        $pdo->beginTransaction();
        if (!empty($ids['contenido'])) {
            $pdo->prepare('DELETE FROM admin_notices WHERE idempotency_key LIKE :clave')
                ->execute(['clave' => 'contenido-publicado:' . $ids['contenido'] . ':%']);
            $pdo->prepare('DELETE FROM contenido_comentarios WHERE contenido_id = :id')
                ->execute(['id' => $ids['contenido']]);
            $pdo->prepare('DELETE FROM contenidos WHERE id = :id')->execute(['id' => $ids['contenido']]);
        }
        if (!empty($ids['asignatura'])) {
            $pdo->prepare('DELETE FROM inscripciones_capacitacion WHERE asignatura_id = :id')->execute(['id' => $ids['asignatura']]);
            $pdo->prepare('DELETE FROM capacitacion_pagos WHERE asignatura_id = :id')->execute(['id' => $ids['asignatura']]);
            $pdo->prepare('DELETE FROM capacitacion_secciones WHERE asignatura_id = :id')->execute(['id' => $ids['asignatura']]);
            $pdo->prepare('DELETE FROM docentes_asignaturas WHERE asignatura_id = :id')->execute(['id' => $ids['asignatura']]);
            $pdo->prepare('DELETE FROM asignaturas WHERE id = :id')->execute(['id' => $ids['asignatura']]);
        }
        if ($ids['usuarios']) {
            $marcas = implode(',', array_fill(0, count($ids['usuarios']), '?'));
            $pdo->prepare("DELETE FROM admin_notices WHERE user_id IN ({$marcas}) OR created_by IN ({$marcas})")
                ->execute([...$ids['usuarios'], ...$ids['usuarios']]);
            $pdo->prepare("DELETE FROM usuarios WHERE id IN ({$marcas})")->execute($ids['usuarios']);
        }
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fwrite(STDERR, 'Limpieza: ' . $error->getMessage() . "\n");
    }
}
