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
require_once ATENEA_ROOT.'/src/estudiantes/includes/student_context.php';

function estadoPedidoEstudiante(string $estado): string{return match($estado){'pagado'=>'Pagado','pendiente_pago'=>'Pendiente de pago','preparando'=>'Preparando','enviado'=>'Enviado','entregado'=>'Entregado','pago_fallido'=>'Pago fallido','cancelado'=>'Cancelado','reembolsado'=>'Reembolsado',default=>ucfirst(str_replace('_',' ',$estado))};}
function claseEstadoPedido(string $estado): string{return match($estado){'pagado','entregado'=>'bg-success','pago_fallido','cancelado'=>'bg-danger','reembolsado'=>'bg-info','enviado'=>'bg-primary',default=>'bg-warning'};}

function portalEstudianteCabecera(string $titulo,string $activo='inicio',string $descripcion='',bool $permitirIncompleto=false): array
{
    $contexto=cargarContextoEstudianteAtenea($permitirIncompleto);
    $perfil=$contexto['perfil'];
    $datos=$contexto['datos'];
    $cantidadCarrito=$contexto['cantidad_carrito'];
    $notificacionesPortal=$contexto['notificaciones'];
    $mensajesNoLeidos=$contexto['mensajes_no_leidos'];
    $logo=$contexto['logo'];
    $avatar=$contexto['avatar'];
    $saludo=$contexto['saludo'];
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
