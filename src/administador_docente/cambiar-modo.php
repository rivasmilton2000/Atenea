<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/auth.php';
require_once dirname(__DIR__,2).'/includes/hybrid_permissions.php';
exigirRol(['administracion_docente','administrador_docente']);
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf((string)($_POST['csrf_token']??''))){
    registrarFalloGlobalAtenea('Cambio de modo híbrido con solicitud inválida.',403);
    mostrarPaginaErrorAtenea(403);
}
try{$destino=cambiarModoHibridoAtenea((string)($_POST['modo']??''));header('Location:'.$destino);exit;}
catch(Throwable $e){ateneaFlash('error','Acceso denegado',$e instanceof DomainException?$e->getMessage():'No fue posible cambiar de modo.');header('Location:'.atenea_url('src/administador_docente/dashboard/index.php'));exit;}
