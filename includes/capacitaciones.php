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
    $q = $pdo->prepare("SELECT 1 FROM usuarios WHERE id=:id AND rol IN('docente','administracion_docente','administrador_docente') AND estado='activo' AND deleted_at IS NULL FOR UPDATE");
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
    $q = $pdo->prepare("SELECT da.docente_id FROM docentes_asignaturas da INNER JOIN usuarios u ON u.id=da.docente_id AND u.rol IN('docente','administracion_docente','administrador_docente') AND u.estado='activo' AND u.deleted_at IS NULL WHERE da.asignatura_id=:a AND da.estado='activo' AND (SELECT COUNT(DISTINCT da2.asignatura_id) FROM docentes_asignaturas da2 INNER JOIN asignaturas a2 ON a2.id=da2.asignatura_id WHERE da2.docente_id=da.docente_id AND da2.estado='activo' AND a2.activo=1 AND a2.estado_capacitacion IN('publicada','cerrada') AND a2.deleted_at IS NULL)<=2");
    $q->execute(['a' => $asignaturaId]);
    return array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
}

function elementoAleatorioSeguro(array $ids): ?int
{
    if (!$ids) return null;
    return (int) $ids[random_int(0, count($ids) - 1)];
}

function registrarHistorialSeccionCapacitacion(PDO $pdo,int $seccionId,string $accion,?array $antes,?array $despues,?int $adminId=null,?string $motivo=null): void
{
    $permitidas=['creada','editada','abierta','cerrada','docente_cambiado','asignacion_automatica','asignacion_manual','estudiante_movido'];
    if(!in_array($accion,$permitidas,true))throw new DomainException('La acción de historial no es válida.');
    $q=$pdo->prepare('INSERT INTO capacitacion_seccion_historial(seccion_id,accion,datos_anteriores,datos_nuevos,motivo,realizado_por) VALUES(:seccion,:accion,:antes,:despues,:motivo,:admin)');
    $q->execute(['seccion'=>$seccionId,'accion'=>$accion,'antes'=>$antes?json_encode($antes,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR):null,'despues'=>$despues?json_encode($despues,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR):null,'motivo'=>$motivo?mb_substr(trim($motivo),0,500):null,'admin'=>$adminId]);
}

function buscarSeccionDisponibleCapacitacion(PDO $pdo,int $asignaturaId): ?array
{
    $q=$pdo->prepare("SELECT s.* FROM capacitacion_secciones s JOIN usuarios u ON u.id=s.docente_id AND u.rol IN('docente','administracion_docente','administrador_docente') AND u.estado='activo' AND u.deleted_at IS NULL JOIN docentes_asignaturas da ON da.docente_id=s.docente_id AND da.asignatura_id=s.asignatura_id AND da.estado='activo' WHERE s.asignatura_id=:a AND s.estado='abierta' AND s.cantidad_actual<s.capacidad_maxima ORDER BY (s.cantidad_actual/NULLIF(s.capacidad_maxima,0)),COALESCE(s.fecha_inicio,'9999-12-31'),s.id FOR UPDATE");
    $q->execute(['a'=>$asignaturaId]);return$q->fetch()?:null;
}

function encolarCorreoAsignacionCapacitacion(PDO $pdo,int $inscripcionId): bool
{
    $q=$pdo->prepare("SELECT i.id,u.id usuario_id,u.nombre,u.apellido,u.correo,a.nombre capacitacion,s.codigo,s.nombre seccion,s.horario,s.fecha_inicio,CONCAT_WS(' ',d.nombre,d.apellido) docente FROM inscripciones_capacitacion i JOIN usuarios u ON u.id=i.usuario_id JOIN asignaturas a ON a.id=i.asignatura_id JOIN capacitacion_secciones s ON s.id=i.seccion_id JOIN usuarios d ON d.id=i.docente_id WHERE i.id=:id AND i.estado IN('inscrito','finalizado')");$q->execute(['id'=>$inscripcionId]);$datos=$q->fetch();if(!$datos)return false;
    try{enviarPlantillaCorreoAtenea('aviso_administrativo',(string)$datos['correo'],trim($datos['nombre'].' '.$datos['apellido']),['asunto'=>'Asignación académica completada · '.$datos['capacitacion'],'resumen'=>'Tu sección y docente ya fueron asignados.','mensaje'=>'Sección: '.$datos['seccion'].' ('.$datos['codigo'].'). Docente: '.$datos['docente'].'. Horario: '.($datos['horario']?:'por confirmar').'. Inicio: '.($datos['fecha_inicio']?date('d/m/Y',strtotime($datos['fecha_inicio'])):'por confirmar').'.','enlace'=>atenea_url_absoluta('src/estudiantes/clase.php'),'texto_boton'=>'Ver mi clase'],['usuario_id'=>(int)$datos['usuario_id'],'idempotency_key'=>'capacitacion-asignada:inscripcion:'.$inscripcionId]);return true;}catch(Throwable $e){error_log('Correo asignación '.$inscripcionId.': '.sanitizarErrorCorreoAtenea($e));return false;}
}

function notificarAsignacionCapacitacion(PDO $pdo,array $inscripcion,array $seccion,?int $actorId=null): void
{
    $q=$pdo->prepare('SELECT nombre FROM asignaturas WHERE id=:id');$q->execute(['id'=>$inscripcion['asignatura_id']]);$capacitacion=(string)$q->fetchColumn();
    $q=$pdo->prepare('SELECT CONCAT_WS(\' \',nombre,apellido) FROM usuarios WHERE id=:id');$q->execute(['id'=>$seccion['docente_id']]);$docente=(string)$q->fetchColumn();
    crearNotificacionAtenea(['usuario_id'=>(int)$inscripcion['usuario_id'],'created_by'=>$actorId,'tipo'=>'asignacion_completada','categoria'=>'capacitaciones','nivel'=>'exito','titulo'=>'Sección y docente asignados','descripcion'=>'Ya perteneces a '.$seccion['nombre'].' ('.$seccion['codigo'].') con '.$docente.'. Horario: '.($seccion['horario']?:'por confirmar').'.','url'=>atenea_url('src/estudiantes/clase.php'),'idempotency_key'=>'capacitacion:asignada:'.(int)$inscripcion['id']],$pdo);
    encolarCorreoAsignacionCapacitacion($pdo,(int)$inscripcion['id']);
}

function asignarInscripcionPendiente(PDO $pdo,int $inscripcionId,?int $adminId=null,string $motivo='Asignación automática después del pago'): array
{
    $q=$pdo->prepare('SELECT * FROM inscripciones_capacitacion WHERE id=:id FOR UPDATE');$q->execute(['id'=>$inscripcionId]);$inscripcion=$q->fetch();if(!$inscripcion)throw new DomainException('La inscripción no existe.');
    if($inscripcion['estado']!=='pendiente_asignacion')return['asignada'=>false,'estado'=>$inscripcion['estado'],'inscripcion'=>$inscripcion];
    $seccion=buscarSeccionDisponibleCapacitacion($pdo,(int)$inscripcion['asignatura_id']);$pdo->prepare('UPDATE inscripciones_capacitacion SET ultimo_intento_asignacion_at=NOW() WHERE id=:id')->execute(['id'=>$inscripcionId]);
    if(!$seccion){$q=$pdo->prepare('SELECT nombre FROM asignaturas WHERE id=:id');$q->execute(['id'=>$inscripcion['asignatura_id']]);$nombre=(string)$q->fetchColumn();crearNotificacionAtenea(['rol'=>'admin','tipo'=>'inscripcion_pendiente','categoria'=>'capacitaciones','nivel'=>'error','titulo'=>'Asignación académica pendiente','descripcion'=>'No existe una sección abierta con docente y cupo para '.$nombre.'.','url'=>atenea_url('src/dashboard/capacitaciones/inscripciones.php?estado=pendiente_asignacion&capacitacion_id='.(int)$inscripcion['asignatura_id']),'idempotency_key'=>'capacitacion:sin-cupo:'.$inscripcionId],$pdo);return['asignada'=>false,'estado'=>'pendiente_asignacion','inscripcion'=>$inscripcion];}
    $q=$pdo->prepare('UPDATE capacitacion_secciones SET cantidad_actual=cantidad_actual+1 WHERE id=:id AND estado=\'abierta\' AND cantidad_actual<capacidad_maxima');$q->execute(['id'=>$seccion['id']]);if($q->rowCount()!==1)throw new RuntimeException('La sección seleccionada se quedó sin cupo.');
    $metodo='automatica';$q=$pdo->prepare("UPDATE inscripciones_capacitacion SET seccion_id=:seccion,docente_id=:docente,estado='inscrito',asignado_por=:admin,metodo_asignacion=:metodo,assigned_at=NOW() WHERE id=:id AND estado='pendiente_asignacion'");$q->execute(['seccion'=>$seccion['id'],'docente'=>$seccion['docente_id'],'admin'=>$adminId,'metodo'=>$metodo,'id'=>$inscripcionId]);if($q->rowCount()!==1)throw new RuntimeException('La inscripción cambió mientras se procesaba.');
    $pdo->prepare('INSERT INTO inscripcion_movimientos(inscripcion_id,seccion_origen_id,seccion_destino_id,docente_origen_id,docente_destino_id,motivo,realizado_por) VALUES(:i,NULL,:destino,NULL,:docente,:motivo,:admin)')->execute(['i'=>$inscripcionId,'destino'=>$seccion['id'],'docente'=>$seccion['docente_id'],'motivo'=>mb_substr(trim($motivo),0,500),'admin'=>$adminId]);
    $pdo->prepare("INSERT INTO estudiantes_docentes(estudiante_id,docente_id,asignatura_id,estado,matriculado_por) VALUES(:e,:d,:a,'activo',:admin) ON DUPLICATE KEY UPDATE estado='activo',matriculado_por=VALUES(matriculado_por)")->execute(['e'=>$inscripcion['usuario_id'],'d'=>$seccion['docente_id'],'a'=>$inscripcion['asignatura_id'],'admin'=>$adminId]);
    $inscripcion['seccion_id']=$seccion['id'];$inscripcion['docente_id']=$seccion['docente_id'];$inscripcion['estado']='inscrito';$inscripcion['metodo_asignacion']=$metodo;registrarHistorialSeccionCapacitacion($pdo,(int)$seccion['id'],'asignacion_automatica',null,['inscripcion_id'=>$inscripcionId,'usuario_id'=>$inscripcion['usuario_id']],$adminId,$motivo);notificarAsignacionCapacitacion($pdo,$inscripcion,$seccion,$adminId);
    return['asignada'=>true,'estado'=>'inscrito','inscripcion'=>$inscripcion,'seccion'=>$seccion];
}

function asignarInscripcionAutomatica(PDO $pdo, int $pagoId): array
{
    $q = $pdo->prepare('SELECT cp.*,a.nombre,a.asignacion_automatica FROM capacitacion_pagos cp INNER JOIN asignaturas a ON a.id=cp.asignatura_id WHERE cp.id=:id FOR UPDATE');
    $q->execute(['id' => $pagoId]);
    $pago = $q->fetch();
    if (!$pago || $pago['estado'] !== 'pagado') throw new RuntimeException('El pago académico no está confirmado.');

    $q = $pdo->prepare('SELECT * FROM inscripciones_capacitacion WHERE pago_id=:pago OR (usuario_id=:usuario AND asignatura_id=:asignatura) LIMIT 1 FOR UPDATE');
    $q->execute(['pago' => $pagoId, 'usuario' => $pago['usuario_id'], 'asignatura' => $pago['asignatura_id']]);
    $existente=$q->fetch();
    if($existente){if($existente['estado']==='pendiente_asignacion'&&(int)$pago['asignacion_automatica']===1){$resultado=asignarInscripcionPendiente($pdo,(int)$existente['id']);return$resultado['inscripcion'];}return$existente;}
    $q=$pdo->prepare("INSERT INTO inscripciones_capacitacion(usuario_id,asignatura_id,pago_id,estado,asignacion_limite_at) VALUES(:u,:a,:p,'pendiente_asignacion',DATE_ADD(NOW(),INTERVAL 3 DAY))");
    $q->execute(['u'=>$pago['usuario_id'],'a'=>$pago['asignatura_id'],'p'=>$pagoId]);$inscripcionId=(int)$pdo->lastInsertId();
    crearNotificacionAtenea(['usuario_id'=>(int)$pago['usuario_id'],'tipo'=>'pago_academico_confirmado','categoria'=>'capacitaciones','nivel'=>'informacion','titulo'=>'Pago confirmado, asignación en proceso','descripcion'=>'Recibimos tu pago de '.$pago['nombre'].'. La asignación de sección y docente puede tardar hasta 3 días.','url'=>atenea_url('src/estudiantes/cursos.php'),'idempotency_key'=>'capacitacion:pago-confirmado:'.$inscripcionId],$pdo);
    $resultado=(int)$pago['asignacion_automatica']===1?asignarInscripcionPendiente($pdo,$inscripcionId):['inscripcion'=>['id'=>$inscripcionId,'usuario_id'=>$pago['usuario_id'],'asignatura_id'=>$pago['asignatura_id'],'pago_id'=>$pagoId,'estado'=>'pendiente_asignacion']];
    if((int)$pago['asignacion_automatica']!==1)crearNotificacionAtenea(['rol'=>'admin','tipo'=>'inscripcion_pendiente','categoria'=>'capacitaciones','nivel'=>'advertencia','titulo'=>'Asignación manual requerida','descripcion'=>'La asignación automática está desactivada para '.$pago['nombre'].'.','url'=>atenea_url('src/dashboard/capacitaciones/inscripciones.php?estado=pendiente_asignacion&capacitacion_id='.(int)$pago['asignatura_id']),'idempotency_key'=>'capacitacion:manual-requerida:'.$inscripcionId],$pdo);
    return$resultado['inscripcion'];
}

function enviarConfirmacionInscripcion(int $pagoId): bool
{
    $pdo=obtenerConexion();$q = $pdo->prepare("SELECT i.id,i.estado FROM capacitacion_pagos cp INNER JOIN inscripciones_capacitacion i ON i.pago_id=cp.id WHERE cp.id=:id AND cp.estado='pagado'");
    $q->execute(['id' => $pagoId]);
    $datos = $q->fetch();
    return $datos&&in_array($datos['estado'],['inscrito','finalizado'],true)?encolarCorreoAsignacionCapacitacion($pdo,(int)$datos['id']):false;
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
            (isset($sesion->metadata->asignatura_id) && !hash_equals((string) $pago['asignatura_id'], (string) $sesion->metadata->asignatura_id)) ||
            (int) ($sesion->amount_total ?? -1) !== $monto ||
            strtolower((string) ($sesion->currency ?? '')) !== strtolower((string) $pago['moneda']) ||
            (string) ($sesion->payment_status ?? '') !== 'paid') {
            throw new RuntimeException('La sesión, referencia, importe, moneda o estado del pago académico no coincide.');
        }
        if ($pago['estado'] !== 'pagado') {
            $intent = substr((string) ($sesion->payment_intent ?? ''), 0, 255) ?: null;
            $pdo->prepare("UPDATE capacitacion_pagos SET estado='pagado',es_intencion_checkout=0,oficializado_at=COALESCE(oficializado_at,NOW()),stripe_payment_intent_id=:intent,last_stripe_event_id=:evento,paid_at=COALESCE(paid_at,NOW()) WHERE id=:id")
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
    $q=$pdo->prepare('UPDATE capacitacion_secciones SET cantidad_actual=cantidad_actual+1 WHERE id=:id AND cantidad_actual<capacidad_maxima');$q->execute(['id' => $seccionDestinoId]);if($q->rowCount()!==1)throw new DomainException('La sección de destino se quedó sin cupo.');
    $q = $pdo->prepare("UPDATE inscripciones_capacitacion SET seccion_id=:seccion,docente_id=:docente,estado='inscrito',asignado_por=:admin,metodo_asignacion='manual',assigned_at=NOW() WHERE id=:id");
    $q->execute(['seccion' => $seccionDestinoId, 'docente' => $destino['docente_id'], 'admin' => $adminId, 'id' => $inscripcionId]);
    $q=$pdo->prepare('INSERT INTO inscripcion_movimientos(inscripcion_id,seccion_origen_id,seccion_destino_id,docente_origen_id,docente_destino_id,motivo,realizado_por) VALUES(:i,:origen,:destino,:docente_origen,:docente_destino,:motivo,:admin)');
    $q->execute(['i' => $inscripcionId, 'origen' => $inscripcion['seccion_id'], 'destino' => $seccionDestinoId, 'docente_origen' => $inscripcion['docente_id'], 'docente_destino' => $destino['docente_id'], 'motivo' => $motivo, 'admin' => $adminId]);$movimientoId=(int)$pdo->lastInsertId();
    if ($inscripcion['docente_id']) $pdo->prepare("UPDATE estudiantes_docentes SET estado='retirado' WHERE estudiante_id=:e AND docente_id=:d AND asignatura_id=:a AND estado='activo'")->execute(['e' => $inscripcion['usuario_id'], 'd' => $inscripcion['docente_id'], 'a' => $inscripcion['asignatura_id']]);
    $pdo->prepare("INSERT INTO estudiantes_docentes(estudiante_id,docente_id,asignatura_id,estado,matriculado_por) VALUES(:e,:d,:a,'activo',:admin) ON DUPLICATE KEY UPDATE estado='activo',matriculado_por=VALUES(matriculado_por)")
        ->execute(['e' => $inscripcion['usuario_id'], 'd' => $destino['docente_id'], 'a' => $inscripcion['asignatura_id'], 'admin' => $adminId]);
    registrarHistorialSeccionCapacitacion($pdo,$seccionDestinoId,$inscripcion['seccion_id']?'estudiante_movido':'asignacion_manual',['seccion_id'=>$inscripcion['seccion_id'],'docente_id'=>$inscripcion['docente_id']],['inscripcion_id'=>$inscripcionId,'seccion_id'=>$seccionDestinoId,'docente_id'=>$destino['docente_id']],$adminId,$motivo);
    if($inscripcion['estado']==='pendiente_asignacion'){$inscripcion['estado']='inscrito';notificarAsignacionCapacitacion($pdo,$inscripcion,$destino,$adminId);}else crearNotificacionAtenea(['usuario_id' => (int) $inscripcion['usuario_id'], 'created_by'=>$adminId,'tipo' => 'seccion_actualizada', 'categoria' => 'capacitaciones', 'nivel' => 'informacion', 'titulo' => 'Tu sección fue actualizada', 'descripcion' => 'Administración actualizó tu sección académica. Tu progreso y notas se conservaron.', 'url' => atenea_url('src/estudiantes/clase.php'), 'idempotency_key' => 'capacitacion:movimiento:' . $movimientoId], $pdo);
}
