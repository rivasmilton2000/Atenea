<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_notification_service.php';
require_once __DIR__ . '/audit.php';

function sanitizarContextoErrorAtenea(array $contexto): array
{
    $ocultas=['password','secret','token','authorization','card','tarjeta','certificate','certificado','cookie','smtp_pass','stripe_key','google_secret','client_secret','api_key'];
    $limpiar=function(mixed $valor,string $clave='') use (&$limpiar,$ocultas):mixed {
        foreach($ocultas as $oculta) if(str_contains(strtolower($clave),$oculta)) return '[oculto]';
        if(is_array($valor)){ $salida=[];foreach(array_slice($valor,0,30,true) as $k=>$v)$salida[(string)$k]=$limpiar($v,(string)$k);return $salida; }
        if(is_scalar($valor)||$valor===null)return mb_substr(preg_replace('/[\r\n\t]+/',' ',(string)$valor)??'',0,500);
        return '[valor no serializable]';
    };
    return $limpiar($contexto);
}

function registrarErrorSistemaAtenea(string $categoria,string $modulo,string $mensaje,array $contexto=[],string $nivel='error',?PDO $pdo=null): int
{
    $pdo??=obtenerConexion();$permitidas=['pago','webhook','dte','correo','stock','base_datos','sistema'];if(!in_array($categoria,$permitidas,true))$categoria='sistema';
    $mensaje=sanitizarTextoAuditoria($mensaje?:'Error operativo sin detalle',500);
    $nivel=in_array($nivel,['advertencia','error','critico'],true)?$nivel:'error';
    $modo=in_array((string)($contexto['modo_activo']??($_SESSION['hybrid_mode']??'')),['admin','docente'],true)?(string)($contexto['modo_activo']??$_SESSION['hybrid_mode']):null;
    $ruta=mb_substr((string)($contexto['ruta']??$contexto['uri']??($_SERVER['REQUEST_URI']??'CLI')),0,255);
    $accion=mb_substr((string)($contexto['accion']??$contexto['method']??($_SERVER['REQUEST_METHOD']??'unknown')),0,100);
    $correlacion=requestIdAtenea();
    $contexto['correlacion_id']=$correlacion;$contexto['modo_activo']=$modo;$contexto['ruta']=$ruta;$contexto['accion']=$accion;
    $seguro=sanitizarContextoErrorAtenea($contexto);$pedido=filter_var($contexto['pedido_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:null;
    $fingerprint=hash('sha256',$categoria.'|'.$modulo.'|'.preg_replace('/\d+/','#',mb_strtolower($mensaje)).'|'.($pedido??''));
    $q=$pdo->prepare("INSERT INTO errores_sistema(fingerprint,categoria,modulo,nivel,mensaje,contexto_sanitizado,pedido_id,usuario_id,modo_activo,ruta,accion_intentada,correlacion_id,primera_ocurrencia_at,ultima_ocurrencia_at) VALUES(:f,:c,:m,:n,:msg,:ctx,:p,:u,:modo,:ruta,:accion,:correlacion,NOW(),NOW()) ON DUPLICATE KEY UPDATE ocurrencias=ocurrencias+1,ultima_ocurrencia_at=NOW(),contexto_sanitizado=VALUES(contexto_sanitizado),nivel=VALUES(nivel),usuario_id=VALUES(usuario_id),modo_activo=VALUES(modo_activo),ruta=VALUES(ruta),accion_intentada=VALUES(accion_intentada),correlacion_id=VALUES(correlacion_id),estado=IF(estado='resuelto','nuevo',estado),id=LAST_INSERT_ID(id)");
    $q->execute(['f'=>$fingerprint,'c'=>$categoria,'m'=>substr($modulo,0,80),'n'=>$nivel,'msg'=>$mensaje,'ctx'=>json_encode($seguro,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),'p'=>$pedido,'u'=>$contexto['usuario_id']??($_SESSION['usuario_id']??null),'modo'=>$modo,'ruta'=>$ruta,'accion'=>$accion,'correlacion'=>$correlacion]);
    $id=(int)$pdo->lastInsertId();
    $q=$pdo->prepare('SELECT ocurrencias FROM errores_sistema WHERE id=:id');$q->execute(['id'=>$id]);$ocurrencias=(int)$q->fetchColumn();
    registrarAuditoria(['actor_user_id'=>$contexto['usuario_id']??($_SESSION['usuario_id']??null),'target_user_id'=>$contexto['usuario_id']??($_SESSION['usuario_id']??null),'event_type'=>'system.error.recorded','module'=>$modulo,'entity_type'=>'system_error','entity_id'=>$id,'action'=>$accion,'result'=>'failure','description'=>'Se registró un error operativo sanitizado.','correlation_id'=>$correlacion,'metadata'=>['level'=>$nivel,'occurrences'=>$ocurrencias]],$pdo);
    if($nivel!=='advertencia'||$ocurrencias>=3)notificarAdministracionAtenea('error_'.$categoria,'Error operativo: '.$modulo,$mensaje,$nivel==='critico'?'critico':($nivel==='advertencia'?'advertencia':'error'),isset($contexto['usuario_id'])?(int)$contexto['usuario_id']:null,atenea_url('src/dashboard/errores/detalle.php?id='.$id),'error:'.$fingerprint.':'.date('Y-m-d-H'),['category'=>$categoria,'error_id'=>$id,'pedido_id'=>$pedido],$pdo);
    return $id;
}
