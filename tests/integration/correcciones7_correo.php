<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/mailer.php';
$pdo=obtenerConexion();$ok=[];$assert=static function(bool $c,string $m)use(&$ok):void{if(!$c)throw new RuntimeException('FALLO: '.$m);$ok[]=$m;};
$tag='c7mail:'.bin2hex(random_bytes(10));
try{
 $assert(correoModoPruebaAtenea(),'MAIL_TEST_MODE permanece activo durante las pruebas');
 $columnas=array_column($pdo->query('SHOW COLUMNS FROM correo_envios')->fetchAll(),'Field');foreach(['evento_id','destinatario_email','contenido_html','max_intentos','es_modo_prueba','cancelado_motivo'] as$columna)$assert(in_array($columna,$columnas,true),'La cola contiene la columna '.$columna);
 $assert((bool)$pdo->query("SHOW TABLES LIKE 'notificacion_preferencias'")->fetchColumn(),'Existe la tabla de preferencias por usuario');
 $indice=$pdo->query("SHOW INDEX FROM correo_envios WHERE Key_name='uq_correo_evento'")->fetch();$assert($indice&&(int)$indice['Non_unique']===0,'El identificador de evento tiene una restricción única');
 $pdo->exec("UPDATE correo_envios SET disponible_at=TIMESTAMPADD(MINUTE,LEAST(60,POW(2,intento)),NOW()) WHERE id=0");$assert(true,'La expresión de espera exponencial es compatible con MariaDB');
 $op=['tipo'=>'prueba_integracion','categoria'=>'novedades','evento_id'=>$tag,'idempotency_key'=>$tag];
 $id1=encolarCorreoAtenea('destino@example.invalid','Destino de prueba','Prueba sin entrega','<p>Contenido de prueba</p>','Contenido de prueba',$op);
 $id2=encolarCorreoAtenea('destino@example.invalid','Destino de prueba','Prueba duplicada','<p>Duplicado</p>','Duplicado',$op);
 $assert($id1!==null&&$id1===$id2,'La idempotencia impide más de un correo por evento');
 $q=$pdo->prepare('SELECT estado,intento,es_modo_prueba,cancelado_motivo FROM correo_envios WHERE id=:id');$q->execute(['id'=>$id1]);$fila=$q->fetch();
 $assert($fila['estado']==='cancelado'&&(int)$fila['intento']===0,'El modo de pruebas registra y cancela sin intentar SMTP');
 $assert((int)$fila['es_modo_prueba']===1&&$fila['cancelado_motivo']!=='','El historial identifica por qué no se entregó el mensaje');
 $assert(procesarCorreoEnColaAtenea((int)$id1)==='omitido','Un correo cancelado no puede salir después desde el procesador');
 echo 'OK '.count($ok)." pruebas\n";foreach($ok as$m)echo '- '.$m."\n";
}finally{$pdo->prepare('DELETE FROM correo_envios WHERE idempotency_key=:k')->execute(['k'=>$tag]);}
