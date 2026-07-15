<?php
declare(strict_types=1);

require_once __DIR__ . '/notificaciones.php';

function textoComunicacionAtenea(string $texto,int $max=10000): string
{
    $texto=trim(strip_tags($texto));$texto=preg_replace('/\r\n?|\x00/','\n',$texto)??'';
    return mb_substr($texto,0,$max);
}

function crearHiloComunicacionAtenea(array $datos,?PDO $pdo=null): int
{
    $pdo??=obtenerConexion();$contenido=textoComunicacionAtenea((string)($datos['contenido']??''));if($contenido==='')throw new InvalidArgumentException('El mensaje esta vacio.');
    $pdo->beginTransaction();try{
        $q=$pdo->prepare("INSERT INTO comunicacion_hilos(canal,asunto,usuario_id,nombre_contacto,correo_contacto,pedido_id,estado,ultimo_mensaje_at) VALUES(:canal,:asunto,:usuario,:nombre,:correo,:pedido,'recibido',NOW())");
        $q->execute(['canal'=>in_array($datos['canal']??'',['contacto','soporte','pedido','plataforma'],true)?$datos['canal']:'plataforma','asunto'=>mb_substr(trim((string)($datos['asunto']??'Mensaje')),0,190),'usuario'=>$datos['usuario_id']??null,'nombre'=>mb_substr(trim((string)($datos['nombre']??'')),0,180)?:null,'correo'=>filter_var($datos['correo']??'',FILTER_VALIDATE_EMAIL)?strtolower((string)$datos['correo']):null,'pedido'=>$datos['pedido_id']??null]);
        $id=(int)$pdo->lastInsertId();$q=$pdo->prepare("INSERT INTO comunicacion_mensajes(hilo_id,direccion,autor_usuario_id,autor_nombre,autor_correo,contenido) VALUES(:h,'entrada',:u,:n,:c,:m)");$q->execute(['h'=>$id,'u'=>$datos['usuario_id']??null,'n'=>mb_substr(trim((string)($datos['nombre']??'')),0,180)?:null,'c'=>filter_var($datos['correo']??'',FILTER_VALIDATE_EMAIL)?strtolower((string)$datos['correo']):null,'m'=>$contenido]);
        crearNotificacionAtenea(['rol'=>'admin','tipo'=>'mensaje_nuevo','categoria'=>'comunicaciones','nivel'=>'informacion','titulo'=>'Nuevo mensaje: '.mb_substr((string)($datos['asunto']??'Consulta'),0,120),'descripcion'=>mb_substr($contenido,0,300),'url'=>atenea_url('src/dashboard/comunicaciones/hilo.php?id='.$id),'hilo_id'=>$id,'pedido_id'=>$datos['pedido_id']??null,'idempotency_key'=>'hilo:nuevo:'.$id],$pdo);
        $pdo->commit();return $id;
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
