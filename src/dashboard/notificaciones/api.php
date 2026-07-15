<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/includes/cms.php';require_once dirname(__DIR__,3).'/includes/notificaciones.php';exigirPermiso('notifications.view');
header('Content-Type: application/json; charset=UTF-8');header('Cache-Control: private, no-store');
$r=notificacionesAdminResumen((int)$_SESSION['usuario_id']);
echo json_encode(['no_leidas'=>$r['no_leidas'],'notificaciones'=>array_map(static fn($n)=>['titulo'=>$n['title'],'descripcion'=>mb_substr($n['message'],0,90),'nivel'=>$n['level'],'url'=>$n['action_url']?:atenea_url('src/dashboard/notificaciones/index.php')],$r['notificaciones'])],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
