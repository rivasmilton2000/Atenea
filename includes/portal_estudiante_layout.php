<?php
declare(strict_types=1);

require_once __DIR__.'/auth.php';
require_once __DIR__.'/portal_estudiante.php';
require_once __DIR__.'/contenido.php';
require_once __DIR__.'/perfil_modal.php';
require_once __DIR__.'/alerts.php';
require_once __DIR__.'/audit.php';
require_once __DIR__.'/carrito.php';
require_once __DIR__.'/notificaciones.php';
require_once __DIR__.'/personalizacion_visual.php';
require_once ATENEA_ROOT.'/src/estudiantes/includes/auth.php';
require_once ATENEA_ROOT.'/src/estudiantes/includes/student_data.php';

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
    return ['resumen'=>$resumen,'pedidos'=>$pedidos,'capacitaciones'=>$capacitaciones,'certificados'=>$certificados,'avisos_pendientes'=>$avisos];
}

function estadoPedidoEstudiante(string $estado): string{return match($estado){'pagado'=>'Pagado','pendiente_pago'=>'Pendiente de pago','preparando'=>'Preparando','enviado'=>'Enviado','entregado'=>'Entregado','pago_fallido'=>'Pago fallido','cancelado'=>'Cancelado','reembolsado'=>'Reembolsado',default=>ucfirst(str_replace('_',' ',$estado))};}
function claseEstadoPedido(string $estado): string{return match($estado){'pagado','entregado'=>'bg-success','pago_fallido','cancelado'=>'bg-danger','reembolsado'=>'bg-info','enviado'=>'bg-primary',default=>'bg-warning'};}

function portalEstudianteCabecera(string $titulo,string $activo='inicio',string $descripcion='',bool $permitirIncompleto=false): array
{
    $perfil=autorizarPortalEstudianteAtenea($permitirIncompleto);
    $datos=datosPortalEstudiante((int)$perfil['id']);
    $cantidadCarrito=cantidadCarrito(obtenerConexion(),(int)$perfil['id']);
    $notificacionesPortal=notificacionesUsuarioResumen((int)$perfil['id'],5);
    $mensajesNoLeidos=mensajesNoLeidosEstudianteAtenea(obtenerConexion(),(int)$perfil['id']);
    $logo=logoPersonalizacionVisualAtenea('estudiantes',obtenerConfiguracionPortalEstudiante('portal_logo'));
    $avatar=rutaFotoPerfil($perfil);
    $hora=(int)date('G');
    $saludo=$hora<12?'Buenos días':($hora<18?'Buenas tardes':'Buenas noches');
    $GLOBALS['portal_estudiante_flash']=is_array($_SESSION['portal_flash']??null)?$_SESSION['portal_flash']:null;
    unset($_SESSION['portal_flash']);
    $GLOBALS['portal_estudiante_activo']=$activo;
    require ATENEA_ROOT.'/src/estudiantes/includes/header.php';
    require ATENEA_ROOT.'/src/estudiantes/includes/navbar.php';
    echo '<div class="container-fluid page-body-wrapper">';
    require ATENEA_ROOT.'/src/estudiantes/includes/sidebar.php';
    echo '<div class="main-panel"><div class="content-wrapper">';
    echo '<div class="student-page-heading d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4"><div><nav aria-label="Ruta de navegación"><ol class="breadcrumb mb-2"><li class="breadcrumb-item"><a href="'.atenea_url('src/estudiantes/index.php').'">Aula virtual</a></li><li class="breadcrumb-item active" aria-current="page">'.atenea_e($titulo).'</li></ol></nav><h1 class="h3 mb-1">'.atenea_e($titulo).'</h1><p class="text-muted mb-0">'.atenea_e($descripcion?:obtenerConfiguracionPortalEstudiante('panel_texto_bienvenida')).'</p></div></div>';
    return ['perfil'=>$perfil,'datos'=>$datos];
}

function portalEstudiantePie(): void
{
    echo '</div>';
    require ATENEA_ROOT.'/src/estudiantes/includes/footer.php';
    echo '</div></div></div>';
    renderizarModalPerfil('estudiantes');
    renderizarControlSesionAtenea();
    require ATENEA_ROOT.'/src/estudiantes/includes/scripts.php';
}
