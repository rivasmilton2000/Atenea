<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/newsletter.php';
require_once dirname(__DIR__,2).'/includes/session.php';
$token=strtolower(trim((string)($_GET['token']??$_POST['token']??'')));$suscriptor=suscriptorNewsletterPorTokenAtenea($token);$mensaje='';$tipo='info';
if(($_SERVER['REQUEST_METHOD']??'')==='POST'){
 if(!validarTokenCsrf((string)($_POST['csrf_token']??'')))$mensaje='La solicitud expiró. Recarga la página.';
 elseif(!$suscriptor)$mensaje='El enlace no es válido o ya fue renovado.';
 else{$activar=($_POST['accion']??'')==='reactivar';if(cambiarEstadoNewsletterPorTokenAtenea($token,$activar)){$mensaje=$activar?'Tu suscripción fue reactivada.':'Tu suscripción fue cancelada correctamente.';$tipo='success';$suscriptor=$activar?['estado'=>'activo']:array_merge($suscriptor,['estado'=>'inactivo']);}else$mensaje='No fue posible actualizar la suscripción.';}
}
if(!$suscriptor)http_response_code(404);$pageTitle='Preferencias del boletín | Atenea';$pageDescription='Administra de forma segura tu suscripción al boletín de Atenea.';require dirname(__DIR__,2).'/includes/header.php';
?>
<main class="main"><section class="section"><div class="container py-5"><div class="mx-auto border rounded-4 shadow-sm p-4 p-md-5 text-center" style="max-width:680px"><i class="bi bi-envelope-heart display-3 text-success"></i><h1 class="h2 mt-3">Boletín de Atenea</h1><?php if($mensaje):?><div class="alert alert-<?=$tipo?>"><?=atenea_e($mensaje)?></div><?php endif;?><?php if(!$suscriptor):?><p>El enlace de suscripción no es válido.</p><?php elseif($suscriptor['estado']==='activo'):?><p>Tu suscripción está activa. Puedes cancelarla sin eliminar permanentemente el registro.</p><form method="post"><input type="hidden" name="csrf_token" value="<?=atenea_e(obtenerTokenCsrf())?>"><input type="hidden" name="token" value="<?=atenea_e($token)?>"><input type="hidden" name="accion" value="baja"><button class="btn btn-outline-danger" type="submit">Cancelar suscripción</button></form><?php else:?><p>Esta suscripción se encuentra inactiva. Puedes volver a recibir novedades cuando lo desees.</p><form method="post"><input type="hidden" name="csrf_token" value="<?=atenea_e(obtenerTokenCsrf())?>"><input type="hidden" name="token" value="<?=atenea_e($token)?>"><input type="hidden" name="accion" value="reactivar"><button class="btn-atenea border-0" type="submit">Volver a suscribirme</button></form><?php endif;?><a class="d-inline-block mt-4" href="<?=atenea_url('index.php')?>">Volver al sitio</a></div></div></section></main>
<?php require dirname(__DIR__,2).'/includes/footer.php';
