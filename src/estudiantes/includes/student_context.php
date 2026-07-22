<?php
declare(strict_types=1);

require_once __DIR__.'/auth.php';
require_once __DIR__.'/student_data.php';
require_once ATENEA_ROOT.'/includes/portal_estudiante_aula.php';

function datosPortalEstudiante(int $usuarioId): array
{
    $pdo=obtenerConexion();
    $q=$pdo->prepare("SELECT COUNT(*) pedidos,COALESCE(SUM(estado='pagado'),0) pagados,COALESCE(SUM(CASE WHEN estado='pagado' THEN total ELSE 0 END),0) invertido FROM pedidos WHERE usuario_id=:u");
    $q->execute(['u'=>$usuarioId]);
    $resumen=$q->fetch()?:['pedidos'=>0,'pagados'=>0,'invertido'=>0];
    $q=$pdo->prepare('SELECT id,numero,total,moneda,estado,created_at FROM pedidos WHERE usuario_id=:u ORDER BY created_at DESC LIMIT 8');
    $q->execute(['u'=>$usuarioId]);
    $pedidos=$q->fetchAll();
    $q=$pdo->prepare("SELECT COUNT(*) FROM admin_notices WHERE user_id=:u AND status='pendiente'");
    $q->execute(['u'=>$usuarioId]);
    $avisos=(int)$q->fetchColumn();
    $q=$pdo->prepare("SELECT COUNT(*) FROM inscripciones_capacitacion WHERE usuario_id=:u AND estado IN('pendiente_asignacion','inscrito','finalizado')");
    $q->execute(['u'=>$usuarioId]);
    $capacitaciones=(int)$q->fetchColumn();
    $q=$pdo->prepare("SELECT COUNT(*) FROM certificados_capacitacion WHERE estudiante_id=:u AND estado='emitido'");
    $q->execute(['u'=>$usuarioId]);
    $certificados=(int)$q->fetchColumn();
    return ['resumen'=>$resumen,'pedidos'=>$pedidos,'capacitaciones'=>$capacitaciones,'certificados'=>$certificados,'avisos_pendientes'=>$avisos];
}

function cargarContextoEstudianteAtenea(bool $permitirPerfilIncompleto=false): array
{
    $perfil=autorizarPortalEstudianteAtenea($permitirPerfilIncompleto);
    $usuarioId=(int)$perfil['id'];
    $pdo=obtenerConexion();
    $inscripciones=aulaInscripcionesEstudiante($pdo,$usuarioId);
    $claseActiva=null;
    foreach($inscripciones as $inscripcion){
        if(($inscripcion['estado']??'')==='inscrito'&&!empty($inscripcion['seccion_id'])){$claseActiva=$inscripcion;break;}
    }
    $hora=(int)date('G');
    return [
        'perfil'=>$perfil,
        'usuario_id'=>$usuarioId,
        'datos'=>datosPortalEstudiante($usuarioId),
        'inscripciones'=>$inscripciones,
        'clase_activa'=>$claseActiva,
        'cantidad_carrito'=>cantidadCarrito($pdo,$usuarioId),
        'notificaciones'=>notificacionesUsuarioResumen($usuarioId,5),
        'mensajes_no_leidos'=>mensajesNoLeidosEstudianteAtenea($pdo,$usuarioId),
        'logo'=>logoPersonalizacionVisualAtenea('estudiantes',obtenerConfiguracionPortalEstudiante('portal_logo')),
        'avatar'=>rutaFotoPerfil($perfil),
        'saludo'=>$hora<12?'Buenos días':($hora<18?'Buenas tardes':'Buenas noches'),
    ];
}
