<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/cuenta.php';
require_once dirname(__DIR__,2).'/includes/audit.php';
exigirAutenticacion();
$retorno=cuentaRetornoSeguro($_POST['retorno']??null);
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf((string)($_POST['csrf_token']??''))){cuentaFlash(['general'=>'La solicitud expiró. Intenta nuevamente.']);header('Location:'.$retorno);exit;}
$permitidas=['academico','comunicaciones','comercio','novedades'];$seleccionadas=is_array($_POST['categorias']??null)?$_POST['categorias']:[];
foreach($seleccionadas as$categoria)if(!is_string($categoria)||!in_array($categoria,$permitidas,true)){cuentaFlash(['general'=>'Las preferencias recibidas no son válidas.']);header('Location:'.$retorno);exit;}
try{$pdo=obtenerConexion();$pdo->beginTransaction();$q=$pdo->prepare('INSERT INTO notificacion_preferencias(usuario_id,categoria,correo_habilitado,agrupar_habilitado) VALUES(:u,:c,:h,1) ON DUPLICATE KEY UPDATE correo_habilitado=VALUES(correo_habilitado),agrupar_habilitado=1');foreach($permitidas as$categoria)$q->execute(['u'=>(int)$_SESSION['usuario_id'],'c'=>$categoria,'h'=>in_array($categoria,$seleccionadas,true)?1:0]);registrarAuditoria(['actor_user_id'=>(int)$_SESSION['usuario_id'],'target_user_id'=>(int)$_SESSION['usuario_id'],'event_type'=>'notifications.preferences_updated','module'=>'account','entity_type'=>'user','entity_id'=>(int)$_SESSION['usuario_id'],'action'=>'update','result'=>'success','description'=>'El usuario actualizó sus preferencias de correo.'],$pdo);$pdo->commit();cuentaFlash([],'Tus preferencias de correo se actualizaron.');}catch(Throwable){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();cuentaFlash(['general'=>'No fue posible guardar las preferencias.']);}
header('Location:'.$retorno);exit;
