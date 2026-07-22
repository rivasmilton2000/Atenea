<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/auth.php';
require_once dirname(__DIR__,2).'/includes/alerts.php';
require_once dirname(__DIR__,2).'/includes/permissions.php';
exigirAutenticacion();
if(($_SESSION['usuario_rol']??'')==='administracion_docente'){if(modoHibridoActualAtenea()==='admin')exigirPermiso('notifications.view');else{exigirModoHibridoAtenea('docente');exigirPermiso('academic.notifications.view');}}
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf((string)($_POST['csrf_token']??''))){mostrarPaginaErrorAtenea(419,registrarFalloGlobalAtenea('Token CSRF inválido en notificaciones.',419));}
$pdo=obtenerConexion();$uid=(int)$_SESSION['usuario_id'];$accion=(string)($_POST['accion']??'');
if($accion==='todas'){$q=$pdo->prepare("UPDATE admin_notices SET status='visto',read_at=COALESCE(read_at,NOW()) WHERE user_id=:u AND status='pendiente'");$q->execute(['u'=>$uid]);}
elseif($accion==='leer'){$id=filter_var($_POST['id']??0,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:0;$q=$pdo->prepare("UPDATE admin_notices SET status='visto',read_at=COALESCE(read_at,NOW()) WHERE id=:id AND user_id=:u AND status='pendiente'");$q->execute(['id'=>$id,'u'=>$uid]);}
else{mostrarPaginaErrorAtenea(403,registrarFalloGlobalAtenea('Acción de notificación no autorizada.',403));}
ateneaFlash('success','','Notificaciones actualizadas.');header('Location:'.atenea_url('src/notificaciones/index.php'));exit;
