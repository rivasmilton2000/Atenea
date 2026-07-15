<?php
declare(strict_types=1);

require_once __DIR__ . '/notificaciones.php';

function sanitizarContextoErrorAtenea(array $contexto): array
{
    $ocultas=['password','secret','token','authorization','card','tarjeta','certificate','certificado','cookie'];
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
    $mensaje=mb_substr(preg_replace('/[\r\n\t]+/',' ',trim($mensaje))?:'Error operativo sin detalle',0,500);
    $seguro=sanitizarContextoErrorAtenea($contexto);$pedido=filter_var($contexto['pedido_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])?:null;
    $fingerprint=hash('sha256',$categoria.'|'.$modulo.'|'.preg_replace('/\d+/','#',mb_strtolower($mensaje)).'|'.($pedido??''));
    $q=$pdo->prepare("INSERT INTO errores_sistema(fingerprint,categoria,modulo,nivel,mensaje,contexto_sanitizado,pedido_id,usuario_id,primera_ocurrencia_at,ultima_ocurrencia_at) VALUES(:f,:c,:m,:n,:msg,:ctx,:p,:u,NOW(),NOW()) ON DUPLICATE KEY UPDATE ocurrencias=ocurrencias+1,ultima_ocurrencia_at=NOW(),contexto_sanitizado=VALUES(contexto_sanitizado),estado=IF(estado='resuelto','nuevo',estado),id=LAST_INSERT_ID(id)");
    $q->execute(['f'=>$fingerprint,'c'=>$categoria,'m'=>substr($modulo,0,80),'n'=>in_array($nivel,['advertencia','error','critico'],true)?$nivel:'error','msg'=>$mensaje,'ctx'=>json_encode($seguro,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),'p'=>$pedido,'u'=>$contexto['usuario_id']??null]);
    $id=(int)$pdo->lastInsertId();
    crearNotificacionAtenea(['rol'=>'admin','tipo'=>'error_'.$categoria,'categoria'=>$categoria,'nivel'=>'error','titulo'=>'Error operativo: '.$modulo,'descripcion'=>$mensaje,'url'=>atenea_url('src/dashboard/errores/detalle.php?id='.$id),'error_id'=>$id,'pedido_id'=>$pedido,'idempotency_key'=>'error:'.$fingerprint.':'.date('Y-m-d-H')],$pdo);
    return $id;
}
