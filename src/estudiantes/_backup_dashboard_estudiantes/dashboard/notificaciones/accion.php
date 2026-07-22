<?php
declare(strict_types=1);require_once dirname(__DIR__).'/includes/cms.php';exigirPermiso('notifications.view');
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf((string)($_POST['csrf_token']??''))){http_response_code(400);exit;}
$pdo=obtenerConexion();$accion=(string)($_POST['accion']??'');$uid=(int)$_SESSION['usuario_id'];
if($accion==='todas'){$q=$pdo->prepare("UPDATE admin_notices SET status='visto',read_at=COALESCE(read_at,NOW()) WHERE user_id=:u AND status='pendiente'");$q->execute(['u'=>$uid]);}
elseif($accion==='leer'){$id=cmsId($_POST['id']??0);$q=$pdo->prepare("UPDATE admin_notices SET status='visto',read_at=COALESCE(read_at,NOW()) WHERE id=:id AND user_id=:u AND status='pendiente'");$q->execute(['id'=>$id,'u'=>$uid]);}
else{http_response_code(400);exit;}
cmsFlash('exito','Notificaciones actualizadas.');header('Location:index.php');
