<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/includes/cms.php';require_once dirname(__DIR__,3).'/includes/notificaciones.php';require_once dirname(__DIR__,3).'/includes/json_response.php';exigirPermiso('notifications.view');
$r=notificacionesAdminResumen((int)$_SESSION['usuario_id']);
responderJsonExitoAtenea(['no_leidas'=>$r['no_leidas'],'notificaciones'=>array_map(static fn($n)=>['titulo'=>$n['title'],'descripcion'=>mb_substr($n['message'],0,90),'nivel'=>$n['level'],'url'=>$n['action_url']?:atenea_url('src/dashboard/notificaciones/index.php')],$r['notificaciones'])]);
