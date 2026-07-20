<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/notificaciones.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/website_versionado.php';

function slugCapacitacion(string $valor): string
{
    $valor = trim(mb_strtolower($valor));
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor);
    $valor = preg_replace('/[^a-z0-9]+/', '-', is_string($ascii) ? strtolower($ascii) : $valor) ?: '';
    return trim(substr($valor, 0, 190), '-');
}

function capacitacionPublicaPorSlug(string $slug): ?array
{
    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) return null;
    foreach(filasEstadoWebsite('asignaturas') as$a)if($a['slug']===$slug&&$a['estado_capacitacion']==='publicada'&&(int)$a['activo']===1&&$a['estado']==='activo'&&empty($a['deleted_at']))return$a;return null;
}

function capacitacionesPublicasWebsite():array{$filas=array_values(array_filter(filasEstadoWebsite('asignaturas'),fn($a)=>$a['estado_capacitacion']==='publicada'&&(int)$a['activo']===1&&$a['estado']==='activo'&&empty($a['deleted_at'])));usort($filas,fn($a,$b)=>[(int)$a['orden'],$a['nombre']]<=>[(int)$b['orden'],$b['nombre']]);return$filas;}

function docentePuedeAsumirCapacitacion(PDO $pdo, int $docenteId, int $asignaturaId, ?int $excluirRelacion = null): bool
{
    $q = $pdo->prepare("SELECT 1 FROM usuarios WHERE id=:id AND rol='docente' AND estado='activo' AND deleted_at IS NULL FOR UPDATE");
    $q->execute(['id' => $docenteId]);
    if (!$q->fetchColumn()) return false;
    $sql = "SELECT COUNT(DISTINCT da.asignatura_id) FROM docentes_asignaturas da INNER JOIN asignaturas a ON a.id=da.asignatura_id WHERE da.docente_id=:docente AND da.estado='activo' AND a.activo=1 AND a.estado_capacitacion IN('publicada','cerrada') AND a.deleted_at IS NULL AND da.asignatura_id<>:asignatura";
    $params = ['docente' => $docenteId, 'asignatura' => $asignaturaId];
    if ($excluirRelacion) { $sql .= ' AND da.id<>:relacion'; $params['relacion'] = $excluirRelacion; }
    $q = $pdo->prepare($sql);
    $q->execute($params);
    return (int) $q->fetchColumn() < 2;
}

function docentesElegiblesCapacitacion(PDO $pdo, int $asignaturaId): array
{
    $q = $pdo->prepare("SELECT da.docente_id FROM docentes_asignaturas da INNER JOIN usuarios u ON u.id=da.docente_id AND u.rol='docente' AND u.estado='activo' AND u.deleted_at IS NULL WHERE da.asignatura_id=:a AND da.estado='activo' AND (SELECT COUNT(DISTINCT da2.asignatura_id) FROM docentes_asignaturas da2 INNER JOIN asignaturas a2 ON a2.id=da2.asignatura_id WHERE da2.docente_id=da.docente_id AND da2.estado='activo' AND a2.activo=1 AND a2.estado_capacitacion IN('publicada','cerrada') AND a2.deleted_at IS NULL)<=2");
    $q->execute(['a' => $asignaturaId]);
    return array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
}

function elementoAleatorioSeguro(array $ids): ?int
{
    if (!$ids) return null;
    return (int) $ids[random_int(0, count($ids) - 1)];
}

function asignarInscripcionAutomatica(PDO $pdo, int $pagoId): array
{
    $q = $pdo->prepare('SELECT cp.*,a.cupo_seccion,a.fecha_inicio,a.fecha_finalizacion,a.nombre FROM capacitacion_pagos cp INNER JOIN asignaturas a ON a.id=cp.asignatura_id WHERE cp.id=:id FOR UPDATE');
    $q->execute(['id' => $pagoId]);
    $pago = $q->fetch();
    if (!$pago || $pago['estado'] !== 'pagado') throw new RuntimeException('El pago académico no está confirmado.');

    $q = $pdo->prepare('SELECT * FROM inscripciones_capacitacion WHERE pago_id=:pago OR (usuario_id=:usuario AND asignatura_id=:asignatura) LIMIT 1 FOR UPDATE');
    $q->execute(['pago' => $pagoId, 'usuario' => $pago['usuario_id'], 'asignatura' => $pago['asignatura_id']]);
    if ($existente = $q->fetch()) return $existente;

    $pdo->prepare('SELECT id FROM asignaturas WHERE id=:id FOR UPDATE')->execute(['id' => $pago['asignatura_id']]);
    $docentes = docentesElegiblesCapacitacion($pdo, (int) $pago['asignatura_id']);
    $seccionId = null;
    $docenteId = null;

    if ($docentes) {
        $marcas = implode(',', array_fill(0, count($docentes), '?'));
        $q = $pdo->prepare("SELECT id,docente_id FROM capacitacion_secciones WHERE asignatura_id=? AND estado='abierta' AND cantidad_actual<capacidad_maxima AND capacidad_maxima<=30 AND docente_id IN($marcas) ORDER BY id FOR UPDATE");
        $q->execute(array_merge([(int) $pago['asignatura_id']], $docentes));
        $secciones = $q->fetchAll();
        if ($secciones) {
            $elegida = $secciones[random_int(0, count($secciones) - 1)];
            $seccionId = (int) $elegida['id'];
            $docenteId = (int) $elegida['docente_id'];
        } else {
            $docenteId = elementoAleatorioSeguro($docentes);
            $q = $pdo->prepare('SELECT COUNT(*)+1 FROM capacitacion_secciones WHERE asignatura_id=:a');
            $q->execute(['a' => $pago['asignatura_id']]);
            $numero = (int) $q->fetchColumn();
            $codigo = 'CAP-' . (int) $pago['asignatura_id'] . '-S' . str_pad((string) $numero, 2, '0', STR_PAD_LEFT);
            $q = $pdo->prepare("INSERT INTO capacitacion_secciones(asignatura_id,docente_id,codigo,nombre,fecha_inicio,fecha_finalizacion,capacidad_maxima,estado) VALUES(:a,:d,:codigo,:nombre,:inicio,:fin,:cupo,'abierta')");
            $q->execute(['a' => $pago['asignatura_id'], 'd' => $docenteId, 'codigo' => $codigo, 'nombre' => 'Sección ' . $numero, 'inicio' => $pago['fecha_inicio'], 'fin' => $pago['fecha_finalizacion'], 'cupo' => min(30, max(1, (int) $pago['cupo_seccion']))]);
            $seccionId = (int) $pdo->lastInsertId();
        }
    }

    $estado = $seccionId ? 'inscrito' : 'pendiente_asignacion';
    $q = $pdo->prepare('INSERT INTO inscripciones_capacitacion(usuario_id,asignatura_id,pago_id,seccion_id,docente_id,estado,assigned_at) VALUES(:u,:a,:p,:s,:d,:estado,:fecha)');
    $q->execute(['u' => $pago['usuario_id'], 'a' => $pago['asignatura_id'], 'p' => $pagoId, 's' => $seccionId, 'd' => $docenteId, 'estado' => $estado, 'fecha' => $seccionId ? date('Y-m-d H:i:s') : null]);
    $inscripcionId = (int) $pdo->lastInsertId();

    if ($seccionId && $docenteId) {
        $q = $pdo->prepare('UPDATE capacitacion_secciones SET cantidad_actual=cantidad_actual+1 WHERE id=:id AND cantidad_actual<capacidad_maxima AND capacidad_maxima<=30');
        $q->execute(['id' => $seccionId]);
        if ($q->rowCount() !== 1) throw new RuntimeException('La sección seleccionada se quedó sin cupo.');
        $pdo->prepare("INSERT INTO estudiantes_docentes(estudiante_id,docente_id,asignatura_id,estado) VALUES(:e,:d,:a,'activo') ON DUPLICATE KEY UPDATE estado='activo'")
            ->execute(['e' => $pago['usuario_id'], 'd' => $docenteId, 'a' => $pago['asignatura_id']]);
    }

    crearNotificacionAtenea(['usuario_id' => (int) $pago['usuario_id'], 'tipo' => 'inscripcion_confirmada', 'categoria' => 'capacitaciones', 'nivel' => $seccionId ? 'exito' : 'advertencia', 'titulo' => $seccionId ? 'Inscripción confirmada' : 'Pago confirmado, asignación pendiente', 'descripcion' => $seccionId ? 'Ya estás inscrito en ' . $pago['nombre'] . '.' : 'Tu pago está confirmado. Atenea asignará docente y sección en cuanto exista disponibilidad.', 'url' => atenea_url('src/estudiantes/cursos.php'), 'idempotency_key' => 'capacitacion:inscripcion:' . $inscripcionId], $pdo);
    crearNotificacionAtenea(['rol' => 'admin', 'tipo' => $seccionId ? 'inscripcion_nueva' : 'inscripcion_pendiente', 'categoria' => 'capacitaciones', 'nivel' => $seccionId ? 'informacion' : 'error', 'titulo' => $seccionId ? 'Nueva inscripción' : 'Inscripción sin docente disponible', 'descripcion' => $seccionId ? 'Se creó una inscripción automática en ' . $pago['nombre'] . '.' : 'El pago fue confirmado, pero la inscripción requiere asignación manual.', 'url' => atenea_url('src/dashboard/capacitaciones/inscripciones.php?capacitacion_id=' . (int) $pago['asignatura_id']), 'idempotency_key' => 'capacitacion:admin:' . $inscripcionId], $pdo);

    $q = $pdo->prepare('SELECT * FROM inscripciones_capacitacion WHERE id=:id');
    $q->execute(['id' => $inscripcionId]);
    return $q->fetch();
}

function enviarConfirmacionInscripcion(int $pagoId): bool
{
    $q = obtenerConexion()->prepare('SELECT cp.id pago_id,cp.importe,cp.moneda,u.id usuario_id,u.nombre,u.apellido,u.correo,a.nombre capacitacion,i.estado,s.codigo seccion FROM capacitacion_pagos cp INNER JOIN usuarios u ON u.id=cp.usuario_id INNER JOIN asignaturas a ON a.id=cp.asignatura_id INNER JOIN inscripciones_capacitacion i ON i.pago_id=cp.id LEFT JOIN capacitacion_secciones s ON s.id=i.seccion_id WHERE cp.id=:id AND cp.estado=\'pagado\'');
    $q->execute(['id' => $pagoId]);
    $datos = $q->fetch();
    if (!$datos) return false;
    try {
        enviarPlantillaCorreoAtenea('aviso_administrativo', (string) $datos['correo'], trim($datos['nombre'] . ' ' . $datos['apellido']), [
            'asunto' => 'Pago e inscripción confirmados · ' . $datos['capacitacion'],
            'resumen' => 'Tu pago de capacitación fue confirmado por Stripe.',
            'mensaje' => $datos['estado'] === 'inscrito' ? 'Tu pago fue confirmado y quedaste inscrito en la sección ' . $datos['seccion'] . '.' : 'Tu pago fue confirmado. La asignación de docente y sección está pendiente; no necesitas pagar nuevamente.',
            'enlace' => atenea_url_absoluta('src/estudiantes/cursos.php'),
            'texto_boton' => 'Ver mis capacitaciones',
        ], ['usuario_id' => (int) $datos['usuario_id'], 'idempotency_key' => 'capacitacion-confirmada:pago:' . $pagoId]);
        return true;
    } catch (Throwable $e) {
        error_log('Correo inscripción ' . $pagoId . ': ' . sanitizarErrorCorreoAtenea($e));
        return false;
    }
}

function errorTransitorioMysqlAtenea(Throwable $error): bool
{
    if (!$error instanceof PDOException) return false;
    $codigoDriver = (int) ($error->errorInfo[1] ?? 0);
    return in_array($codigoDriver, [1205, 1213], true) || (string) $error->getCode() === '40001';
}

function procesarWebhookCapacitacion(object $evento, object $sesion): int
{
    $ultimoError = null;
    for ($intento = 1; $intento <= 3; $intento++) {
        try {
            return procesarWebhookCapacitacionUnaVez($evento, $sesion);
        } catch (Throwable $error) {
            $ultimoError = $error;
            if (!errorTransitorioMysqlAtenea($error) || $intento === 3) throw $error;
            usleep(random_int(20000, 80000) * $intento);
        }
    }
    throw $ultimoError ?? new RuntimeException('No fue posible procesar el webhook académico.');
}

function procesarWebhookCapacitacionUnaVez(object $evento, object $sesion): int
{
    $pagoId = (int) ($sesion->metadata->capacitacion_pago_id ?? 0);
    if ($pagoId < 1) throw new RuntimeException('Referencia de pago académico inválida.');
    $pdo = obtenerConexion();
    $pdo->beginTransaction();
    try {
        $pdo->prepare('INSERT IGNORE INTO stripe_eventos(stripe_event_id,tipo) VALUES(:id,:tipo)')->execute(['id' => (string) $evento->id, 'tipo' => (string) $evento->type]);
        $q = $pdo->prepare('SELECT procesado FROM stripe_eventos WHERE stripe_event_id=:id FOR UPDATE');
        $q->execute(['id' => (string) $evento->id]);
        if ((int) $q->fetchColumn() === 1) { $pdo->commit(); enviarConfirmacionInscripcion($pagoId); return $pagoId; }
        $q = $pdo->prepare('SELECT * FROM capacitacion_pagos WHERE id=:id FOR UPDATE');
        $q->execute(['id' => $pagoId]);
        $pago = $q->fetch();
        if (!$pago) throw new RuntimeException('Pago académico no localizado.');
        $monto = (int) round((float) $pago['importe'] * 100);
        if (!hash_equals((string) $pago['stripe_checkout_session_id'], (string) ($sesion->id ?? '')) ||
            !hash_equals((string) $pagoId, (string) ($sesion->client_reference_id ?? '')) ||
            (int) ($sesion->amount_total ?? -1) !== $monto ||
            strtolower((string) ($sesion->currency ?? '')) !== strtolower((string) $pago['moneda']) ||
            (string) ($sesion->payment_status ?? '') !== 'paid') {
            throw new RuntimeException('La sesión, referencia, importe, moneda o estado del pago académico no coincide.');
        }
        if ($pago['estado'] !== 'pagado') {
            $intent = substr((string) ($sesion->payment_intent ?? ''), 0, 255) ?: null;
            $pdo->prepare("UPDATE capacitacion_pagos SET estado='pagado',stripe_payment_intent_id=:intent,last_stripe_event_id=:evento,paid_at=COALESCE(paid_at,NOW()) WHERE id=:id")
                ->execute(['intent' => $intent, 'evento' => (string) $evento->id, 'id' => $pagoId]);
        }
        asignarInscripcionAutomatica($pdo, $pagoId);
        $pdo->prepare('UPDATE stripe_eventos SET procesado=1,error_mensaje=NULL,procesado_at=NOW() WHERE stripe_event_id=:id')->execute(['id' => (string) $evento->id]);
        $pdo->commit();
        enviarConfirmacionInscripcion($pagoId);
        return $pagoId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        try { $pdo->prepare('INSERT INTO stripe_eventos(stripe_event_id,tipo,procesado,error_mensaje) VALUES(:id,:tipo,0,:error) ON DUPLICATE KEY UPDATE procesado=0,error_mensaje=VALUES(error_mensaje)')->execute(['id' => (string) $evento->id, 'tipo' => (string) $evento->type, 'error' => mb_substr($e->getMessage(), 0, 500)]); } catch (Throwable) {}
        throw $e;
    }
}

function procesarEstadoWebhookCapacitacion(object $evento, object $objeto): void
{
    $pagoId = (int) ($objeto->metadata->capacitacion_pago_id ?? 0);
    if ($pagoId < 1) throw new RuntimeException('Referencia de pago académico inválida.');
    $mapa = ['checkout.session.expired' => 'expirado', 'checkout.session.async_payment_failed' => 'fallido', 'payment_intent.payment_failed' => 'fallido', 'charge.refunded' => 'reembolsado'];
    if (!isset($mapa[(string) $evento->type])) return;
    $pdo = obtenerConexion();$pdo->beginTransaction();
    try {
        $pdo->prepare('INSERT IGNORE INTO stripe_eventos(stripe_event_id,tipo) VALUES(:id,:tipo)')->execute(['id' => (string) $evento->id, 'tipo' => (string) $evento->type]);
        $q=$pdo->prepare('SELECT procesado FROM stripe_eventos WHERE stripe_event_id=:id FOR UPDATE');$q->execute(['id'=>(string)$evento->id]);if((int)$q->fetchColumn()===1){$pdo->commit();return;}
        $q=$pdo->prepare('SELECT * FROM capacitacion_pagos WHERE id=:id FOR UPDATE');$q->execute(['id'=>$pagoId]);$pago=$q->fetch();if(!$pago)throw new RuntimeException('Pago académico no localizado.');
        if($pago['estado']!=='pagado'||$mapa[(string)$evento->type]==='reembolsado')$pdo->prepare('UPDATE capacitacion_pagos SET estado=:estado,last_stripe_event_id=:evento,stripe_payment_intent_id=COALESCE(stripe_payment_intent_id,:intent) WHERE id=:id')->execute(['estado'=>$mapa[(string)$evento->type],'evento'=>(string)$evento->id,'intent'=>str_starts_with((string)$evento->type,'payment_intent.')?substr((string)($objeto->id??''),0,255):null,'id'=>$pagoId]);
        $pdo->prepare('UPDATE stripe_eventos SET procesado=1,error_mensaje=NULL,procesado_at=NOW() WHERE stripe_event_id=:id')->execute(['id'=>(string)$evento->id]);$pdo->commit();
    } catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw$e;}
}

function moverInscripcionCapacitacion(PDO $pdo, int $inscripcionId, int $seccionDestinoId, int $adminId, string $motivo): void
{
    $motivo = trim($motivo);
    if ($motivo === '' || mb_strlen($motivo) > 500) throw new DomainException('Indica un motivo válido para el movimiento.');
    $q = $pdo->prepare('SELECT * FROM inscripciones_capacitacion WHERE id=:id FOR UPDATE');
    $q->execute(['id' => $inscripcionId]);
    $inscripcion = $q->fetch();
    if (!$inscripcion) throw new DomainException('La inscripción no existe.');
    $q = $pdo->prepare("SELECT s.*,da.estado autorizacion FROM capacitacion_secciones s INNER JOIN docentes_asignaturas da ON da.docente_id=s.docente_id AND da.asignatura_id=s.asignatura_id WHERE s.id=:id AND s.asignatura_id=:a FOR UPDATE");
    $q->execute(['id' => $seccionDestinoId, 'a' => $inscripcion['asignatura_id']]);
    $destino = $q->fetch();
    if (!$destino || $destino['estado'] !== 'abierta' || $destino['autorizacion'] !== 'activo') throw new DomainException('La sección de destino no está abierta o su docente no está autorizado.');
    if ((int) $destino['capacidad_maxima'] > 30 || (int) $destino['cantidad_actual'] >= (int) $destino['capacidad_maxima']) throw new DomainException('La sección de destino no tiene cupo disponible.');
    if ((int) ($inscripcion['seccion_id'] ?? 0) === $seccionDestinoId) throw new DomainException('El estudiante ya pertenece a esa sección.');
    if ($inscripcion['seccion_id']) {
        $pdo->prepare('SELECT id FROM capacitacion_secciones WHERE id=:id FOR UPDATE')->execute(['id' => $inscripcion['seccion_id']]);
        $pdo->prepare('UPDATE capacitacion_secciones SET cantidad_actual=GREATEST(cantidad_actual-1,0) WHERE id=:id')->execute(['id' => $inscripcion['seccion_id']]);
    }
    $pdo->prepare('UPDATE capacitacion_secciones SET cantidad_actual=cantidad_actual+1 WHERE id=:id AND cantidad_actual<capacidad_maxima')->execute(['id' => $seccionDestinoId]);
    $q = $pdo->prepare("UPDATE inscripciones_capacitacion SET seccion_id=:seccion,docente_id=:docente,estado='inscrito',asignado_por=:admin,assigned_at=NOW() WHERE id=:id");
    $q->execute(['seccion' => $seccionDestinoId, 'docente' => $destino['docente_id'], 'admin' => $adminId, 'id' => $inscripcionId]);
    $pdo->prepare('INSERT INTO inscripcion_movimientos(inscripcion_id,seccion_origen_id,seccion_destino_id,docente_origen_id,docente_destino_id,motivo,realizado_por) VALUES(:i,:origen,:destino,:docente_origen,:docente_destino,:motivo,:admin)')
        ->execute(['i' => $inscripcionId, 'origen' => $inscripcion['seccion_id'], 'destino' => $seccionDestinoId, 'docente_origen' => $inscripcion['docente_id'], 'docente_destino' => $destino['docente_id'], 'motivo' => $motivo, 'admin' => $adminId]);
    if ($inscripcion['docente_id']) $pdo->prepare("UPDATE estudiantes_docentes SET estado='retirado' WHERE estudiante_id=:e AND docente_id=:d AND asignatura_id=:a AND estado='activo'")->execute(['e' => $inscripcion['usuario_id'], 'd' => $inscripcion['docente_id'], 'a' => $inscripcion['asignatura_id']]);
    $pdo->prepare("INSERT INTO estudiantes_docentes(estudiante_id,docente_id,asignatura_id,estado,matriculado_por) VALUES(:e,:d,:a,'activo',:admin) ON DUPLICATE KEY UPDATE estado='activo',matriculado_por=VALUES(matriculado_por)")
        ->execute(['e' => $inscripcion['usuario_id'], 'd' => $destino['docente_id'], 'a' => $inscripcion['asignatura_id'], 'admin' => $adminId]);
    crearNotificacionAtenea(['usuario_id' => (int) $inscripcion['usuario_id'], 'tipo' => 'seccion_actualizada', 'categoria' => 'capacitaciones', 'nivel' => 'informacion', 'titulo' => 'Tu sección fue actualizada', 'descripcion' => 'Administración actualizó tu sección académica. Tu progreso y notas se conservaron.', 'url' => atenea_url('src/estudiantes/cursos.php'), 'idempotency_key' => 'capacitacion:movimiento:' . $inscripcionId . ':' . $seccionDestinoId . ':' . time()], $pdo);
}
