<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/auth.php';require_once dirname(__DIR__,2).'/includes/carrito.php';require_once dirname(__DIR__,2).'/includes/alerts.php';exigirRol(['usuario']);
$volver=($_POST['checkout']??'')==='1'?atenea_url('src/estudiantes/checkout.php'):atenea_url('src/estudiantes/direcciones.php');
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'||!validarTokenCsrf((string)($_POST['csrf_token']??''))){ateneaFlash('error','Solicitud expirada','Recarga e inténtalo nuevamente.');header('Location:'.$volver);exit;}
$id=filter_var($_POST['id']??0,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:0;$etiqueta=(string)($_POST['etiqueta']??'');$personal=trim((string)($_POST['etiqueta_personalizada']??''));
$receptor=trim((string)($_POST['receptor']??''));$telefono=trim((string)($_POST['telefono']??''));$detalle=trim((string)($_POST['direccion_detallada']??''));$referencias=trim((string)($_POST['referencias']??''));
$dep=filter_var($_POST['departamento_id']??0,FILTER_VALIDATE_INT)?:0;$mun=filter_var($_POST['municipio_id']??0,FILTER_VALIDATE_INT)?:0;$dis=filter_var($_POST['distrito_id']??0,FILTER_VALIDATE_INT)?:null;$pred=!empty($_POST['predeterminada'])?1:0;
try{
 if(!in_array($etiqueta,['casa','oficina','otra'],true))throw new DomainException('Selecciona una etiqueta válida.');if($etiqueta==='otra'&&($personal===''||mb_strlen($personal)>60))throw new DomainException('La etiqueta personalizada es obligatoria y admite hasta 60 caracteres.');
 if($receptor===''||mb_strlen($receptor)>160||$telefono===''||mb_strlen($telefono)>30||$detalle===''||mb_strlen($detalle)>500||mb_strlen($referencias)>500)throw new DomainException('Revisa el receptor, teléfono y dirección.');
 $normal=mb_strtolower(preg_replace('/\s+/u',' ',trim($etiqueta==='otra'?$personal:$etiqueta)));
 $pdo=obtenerConexion();$pdo->beginTransaction();$q=$pdo->prepare('SELECT m.id FROM municipios m LEFT JOIN distritos d ON d.municipio_id=m.id AND d.id=:distrito WHERE m.id=:municipio AND m.departamento_id=:departamento AND (:sin_distrito=1 OR d.id IS NOT NULL)');$q->execute(['distrito'=>$dis,'municipio'=>$mun,'departamento'=>$dep,'sin_distrito'=>$dis?0:1]);if(!$q->fetchColumn())throw new DomainException('Departamento, municipio o distrito no coinciden.');
 if($pred)$pdo->prepare('UPDATE direcciones_usuario SET predeterminada=0 WHERE usuario_id=:u')->execute(['u'=>$_SESSION['usuario_id']]);
 $p=['u'=>$_SESSION['usuario_id'],'e'=>$etiqueta,'ep'=>$etiqueta==='otra'?$personal:null,'n'=>$normal,'r'=>$receptor,'t'=>$telefono,'dep'=>$dep,'mun'=>$mun,'dis'=>$dis,'dir'=>$detalle,'ref'=>$referencias?:null,'pred'=>$pred];
 if($id){$p['id']=$id;$q=$pdo->prepare('UPDATE direcciones_usuario SET etiqueta=:e,etiqueta_personalizada=:ep,etiqueta_normalizada=:n,receptor=:r,telefono=:t,departamento_id=:dep,municipio_id=:mun,distrito_id=:dis,direccion_detallada=:dir,referencias=:ref,predeterminada=:pred WHERE id=:id AND usuario_id=:u AND activa=1');$q->execute($p);if($q->rowCount()<1)throw new DomainException('Dirección no encontrada o sin cambios.');}
 else{$q=$pdo->prepare('INSERT INTO direcciones_usuario(usuario_id,etiqueta,etiqueta_personalizada,etiqueta_normalizada,receptor,telefono,departamento_id,municipio_id,distrito_id,direccion_detallada,referencias,predeterminada) VALUES(:u,:e,:ep,:n,:r,:t,:dep,:mun,:dis,:dir,:ref,:pred)');$q->execute($p);}
 $pdo->commit();ateneaFlash('success','Dirección guardada','Ya puedes seleccionarla en el checkout.');
}catch(Throwable $e){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();$msg=$e instanceof DomainException?$e->getMessage():($e instanceof PDOException&&$e->getCode()==='23000'?'Ya existe una dirección con esa etiqueta.':'No fue posible guardar la dirección.');ateneaFlash('warning','Revisa la dirección',$msg);}
header('Location:'.$volver);exit;
