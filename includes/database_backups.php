<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config/services.php';
require_once __DIR__ . '/audit.php';
require_once __DIR__ . '/admin_notification_service.php';

const ATENEA_BACKUP_FORMAT = 'atenea-db-backup-v1';
const ATENEA_BACKUP_TRACKING_TABLE = 'respaldos_base_datos';

function respaldoEsSuperadmin(?int $usuarioId = null, ?PDO $pdo = null): bool
{
    $usuarioId ??= (int)($_SESSION['usuario_id'] ?? 0);
    if ($usuarioId < 1) return false;
    $pdo ??= obtenerConexion();
    $q = $pdo->prepare("SELECT 1 FROM usuarios WHERE id=:id AND rol='admin' AND es_superadmin=1 AND estado='activo' AND deleted_at IS NULL");
    $q->execute(['id'=>$usuarioId]);
    return (bool)$q->fetchColumn();
}

function respaldoDirectorioBase(): string
{
    $ruta = rtrim(AppConfig::value('BACKUP_STORAGE_PATH', dirname(ATENEA_ROOT).'/atenea-private/backups'), "/\\");
    if ($ruta === '') throw new RuntimeException('El almacenamiento privado de copias no está configurado.');
    if (!is_dir($ruta) && !mkdir($ruta, 0700, true) && !is_dir($ruta)) throw new RuntimeException('No fue posible preparar el almacenamiento privado.');
    @chmod($ruta, 0700);
    $real = realpath($ruta);
    $publico = realpath(ATENEA_ROOT);
    if ($real === false || ($publico !== false && str_starts_with(strtolower(str_replace('\\','/',$real)).'/', strtolower(str_replace('\\','/',$publico)).'/'))) {
        throw new RuntimeException('La ruta de copias debe estar fuera del directorio público.');
    }
    return $real;
}

function respaldoRetencionDias(): int
{
    return max(1, min(3650, (int)AppConfig::value('BACKUP_RETENTION_DAYS', '30')));
}

function respaldoMaximoArchivos(): int
{
    return max(2, min(500, (int)AppConfig::value('BACKUP_MAX_FILES', '20')));
}

function respaldoTablasSinDatos(): array
{
    $defecto = 'auth_remember_tokens,password_reset_tokens,verificaciones_cuenta,assisted_password_resets,website_preview_tokens,correo_imap_estado';
    $tablas = array_filter(array_map('trim', explode(',', AppConfig::value('BACKUP_EXCLUDED_DATA_TABLES', $defecto))));
    $tablas[] = ATENEA_BACKUP_TRACKING_TABLE;
    return array_values(array_unique(array_filter($tablas, static fn(string $t): bool => preg_match('/^[A-Za-z0-9_]+$/D', $t) === 1)));
}

function respaldoNombreSeguro(): string
{
    return 'atenea-db-'.date('Ymd-His').'-'.bin2hex(random_bytes(6)).'.atenea-db.gz';
}

function respaldoRutaAbsoluta(string $relativa, bool $debeExistir = true): string
{
    if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{10,179}\.atenea-db\.gz$/D', $relativa)) throw new DomainException('El archivo solicitado no es válido.');
    $base = respaldoDirectorioBase();
    $ruta = $base.DIRECTORY_SEPARATOR.$relativa;
    if ($debeExistir) {
        $real = realpath($ruta);
        if ($real === false || dirname($real) !== $base || !is_file($real)) throw new DomainException('La copia ya no está disponible.');
        return $real;
    }
    return $ruta;
}

function respaldoBloqueoOperacion()
{
    $archivo = respaldoDirectorioBase().DIRECTORY_SEPARATOR.'.operation.lock';
    $handle = fopen($archivo, 'c+b');
    if (!$handle || !flock($handle, LOCK_EX | LOCK_NB)) {
        if (is_resource($handle)) fclose($handle);
        throw new DomainException('Ya existe una operación de copia o restauración en curso.');
    }
    @chmod($archivo, 0600);
    return $handle;
}

function respaldoEscribir($gz, array $registro): void
{
    $json = json_encode($registro, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    if (gzwrite($gz, $json."\n") === false) throw new RuntimeException('No fue posible escribir la copia de seguridad.');
}

function respaldoQuitarDefinidor(string $sql): string
{
    return preg_replace('/\s+DEFINER\s*=\s*(?:`[^`]*`|[^\s]+)@(?:`[^`]*`|[^\s]+)\s*/i', ' ', $sql) ?: $sql;
}

function respaldoObjetos(PDO $pdo): array
{
    $tablas=[];$vistas=[];
    foreach ($pdo->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM) as $fila) {
        $nombre=(string)$fila[0];
        if (!preg_match('/^[A-Za-z0-9_]+$/D',$nombre) || $nombre===ATENEA_BACKUP_TRACKING_TABLE) continue;
        if (strtoupper((string)$fila[1])==='VIEW') $vistas[]=$nombre; else $tablas[]=$nombre;
    }
    return ['tables'=>$tablas,'views'=>$vistas];
}

function crearRespaldoBaseDatos(int $actorId, string $tipo = 'manual', bool $usarBloqueo = true): array
{
    if (!in_array($tipo,['manual','previo_restauracion'],true)) throw new InvalidArgumentException('Tipo de copia inválido.');
    $pdo=obtenerConexion();$bloqueo=$usarBloqueo?respaldoBloqueoOperacion():null;
    $nombre=respaldoNombreSeguro();$ruta=respaldoRutaAbsoluta($nombre,false);$id=0;$gz=null;
    try {
        $q=$pdo->prepare("INSERT INTO respaldos_base_datos(creado_por,tipo,nombre_archivo,ruta_relativa,estado) VALUES(:u,:tipo,:nombre,:ruta,'creando')");
        $q->execute(['u'=>$actorId,'tipo'=>$tipo,'nombre'=>$nombre,'ruta'=>$nombre]);$id=(int)$pdo->lastInsertId();
        $gz=gzopen($ruta,'wb9');if(!$gz)throw new RuntimeException('No fue posible crear el archivo privado.');@chmod($ruta,0600);
        $objetos=respaldoObjetos($pdo);$sinDatos=respaldoTablasSinDatos();
        respaldoEscribir($gz,['kind'=>'header','format'=>ATENEA_BACKUP_FORMAT,'created_at'=>date(DATE_ATOM),'charset'=>'utf8mb4']);
        $columnas=[];
        foreach($objetos['tables'] as $tabla){
            $create=$pdo->query('SHOW CREATE TABLE `'.$tabla.'`')->fetch(PDO::FETCH_NUM);
            $cols=[];foreach($pdo->query('SHOW COLUMNS FROM `'.$tabla.'`')->fetchAll() as $col){if(!str_contains(strtolower((string)($col['Extra']??'')),'generated'))$cols[]=(string)$col['Field'];}
            $columnas[$tabla]=$cols;
            respaldoEscribir($gz,['kind'=>'table','name'=>$tabla,'create'=>(string)($create[1]??''),'columns'=>$cols,'data_included'=>!in_array($tabla,$sinDatos,true)]);
        }
        foreach($objetos['views'] as $vista){$create=$pdo->query('SHOW CREATE VIEW `'.$vista.'`')->fetch();$sql=(string)($create['Create View']??array_values($create)[1]??'');respaldoEscribir($gz,['kind'=>'view','name'=>$vista,'create'=>respaldoQuitarDefinidor($sql)]);}
        foreach($pdo->query('SHOW TRIGGERS')->fetchAll() as $trigger){$nombre=(string)$trigger['Trigger'];if(!preg_match('/^[A-Za-z0-9_]+$/D',$nombre))continue;$create=$pdo->query('SHOW CREATE TRIGGER `'.$nombre.'`')->fetch();$sql=(string)($create['SQL Original Statement']??$create['Create Trigger']??'');respaldoEscribir($gz,['kind'=>'trigger','name'=>$nombre,'create'=>respaldoQuitarDefinidor($sql)]);}
        $filas=0;
        foreach($objetos['tables'] as $tabla){
            if(in_array($tabla,$sinDatos,true)||!$columnas[$tabla])continue;
            $lista=implode(',',array_map(static fn(string $c):string=>'`'.$c.'`',$columnas[$tabla]));
            $consulta=$pdo->query('SELECT '.$lista.' FROM `'.$tabla.'`');
            while($row=$consulta->fetch(PDO::FETCH_NUM)){$valores=array_map(static fn($v)=>$v===null?null:base64_encode((string)$v),$row);respaldoEscribir($gz,['kind'=>'row','table'=>$tabla,'values'=>$valores]);$filas++;}
            $consulta->closeCursor();
        }
        respaldoEscribir($gz,['kind'=>'end','tables'=>count($objetos['tables']),'rows'=>$filas]);
        gzclose($gz);$gz=null;$tamano=filesize($ruta);$hash=hash_file('sha256',$ruta);
        if($tamano===false||$tamano<32||!is_string($hash))throw new RuntimeException('La copia generada no superó la validación de integridad.');
        $q=$pdo->prepare("UPDATE respaldos_base_datos SET tamano_bytes=:tamano,sha256=:hash,estado='disponible',tablas_incluidas=:tablas,filas_incluidas=:filas,error_sanitizado=NULL WHERE id=:id");
        $q->execute(['tamano'=>$tamano,'hash'=>$hash,'tablas'=>count($objetos['tables']),'filas'=>$filas,'id'=>$id]);
        registrarAuditoria(['actor_user_id'=>$actorId,'event_type'=>'database.backup.created','module'=>'backups','entity_type'=>'database_backup','entity_id'=>$id,'action'=>'create','result'=>'success','description'=>'Se creó una copia privada de la base de datos.','metadata'=>['type'=>$tipo,'size_bytes'=>$tamano,'tables'=>count($objetos['tables']),'rows'=>$filas]],$pdo);
        aplicarRetencionRespaldos($pdo,[$id]);
        return respaldoPorId($id,$pdo)??[];
    } catch(Throwable $e){
        if(is_resource($gz))gzclose($gz);if(is_file($ruta))@unlink($ruta);
        if($id>0){$q=$pdo->prepare("UPDATE respaldos_base_datos SET estado='fallido',error_sanitizado='No fue posible completar la copia de seguridad.' WHERE id=:id");$q->execute(['id'=>$id]);registrarAuditoria(['actor_user_id'=>$actorId,'event_type'=>'database.backup.failed','module'=>'backups','entity_type'=>'database_backup','entity_id'=>$id,'action'=>'create','result'=>'failure','description'=>'Falló la creación de una copia privada de la base de datos.'],$pdo);}
        error_log('Atenea backup: '.$e->getMessage());throw $e;
    } finally {if(is_resource($bloqueo)){flock($bloqueo,LOCK_UN);fclose($bloqueo);}}
}

function respaldoPorId(int $id, ?PDO $pdo=null): ?array
{
    $pdo??=obtenerConexion();$q=$pdo->prepare('SELECT r.*,CONCAT(u.nombre," ",u.apellido) creador,CONCAT(ur.nombre," ",ur.apellido) restaurador FROM respaldos_base_datos r LEFT JOIN usuarios u ON u.id=r.creado_por LEFT JOIN usuarios ur ON ur.id=r.restaurado_por WHERE r.id=:id');$q->execute(['id'=>$id]);$r=$q->fetch();return is_array($r)?$r:null;
}

function verificarIntegridadRespaldo(array $respaldo): string
{
    if(($respaldo['estado']??'')!=='disponible'&&($respaldo['estado']??'')!=='restaurado')throw new DomainException('La copia seleccionada no está disponible.');
    $ruta=respaldoRutaAbsoluta((string)$respaldo['ruta_relativa']);$hash=hash_file('sha256',$ruta);
    if(!is_string($hash)||!hash_equals((string)$respaldo['sha256'],$hash))throw new DomainException('La copia no superó la validación de integridad.');
    return $ruta;
}

function leerRegistrosRespaldo(string $ruta): Generator
{
    $gz=gzopen($ruta,'rb');if(!$gz)throw new RuntimeException('No fue posible leer la copia.');
    try{while(!gzeof($gz)){$linea=gzgets($gz,67108864);if($linea===false)break;$linea=trim($linea);if($linea==='')continue;$r=json_decode($linea,true,64,JSON_THROW_ON_ERROR);if(!is_array($r))throw new RuntimeException('Formato de copia inválido.');yield $r;}}finally{gzclose($gz);}
}

function restaurarArchivoRespaldo(PDO $pdo,string $ruta,array $tablasProtegidas=[]): int
{
    $protegidas=array_fill_keys($tablasProtegidas,true);$tablas=[];$vistas=[];$triggers=[];$cabecera=false;
    foreach(leerRegistrosRespaldo($ruta) as $r){$kind=$r['kind']??'';if($kind==='header'){$cabecera=($r['format']??'')===ATENEA_BACKUP_FORMAT;}elseif($kind==='table')$tablas[(string)$r['name']]=$r;elseif($kind==='view')$vistas[]=$r;elseif($kind==='trigger')$triggers[]=$r;}
    if(!$cabecera||!$tablas)throw new DomainException('El formato de la copia no es compatible.');
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    try{
        foreach($pdo->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM) as $actual){$nombre=(string)$actual[0];if(isset($protegidas[$nombre]))continue;if(strtoupper((string)$actual[1])==='VIEW')$pdo->exec('DROP VIEW IF EXISTS `'.$nombre.'`');}
        foreach($pdo->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM) as $actual){$nombre=(string)$actual[0];if(!isset($protegidas[$nombre])&&strtoupper((string)$actual[1])!=='VIEW')$pdo->exec('DROP TABLE IF EXISTS `'.$nombre.'`');}
        foreach($tablas as $tabla){$sql=(string)($tabla['create']??'');if($sql==='')throw new RuntimeException('La estructura del respaldo está incompleta.');$pdo->exec($sql);}
        $preparadas=[];$filas=0;
        foreach(leerRegistrosRespaldo($ruta) as $r){if(($r['kind']??'')!=='row')continue;$tabla=(string)($r['table']??'');if(!isset($tablas[$tabla]))throw new RuntimeException('La copia contiene una tabla desconocida.');$cols=(array)($tablas[$tabla]['columns']??[]);$values=(array)($r['values']??[]);if(count($cols)!==count($values)||!$cols)throw new RuntimeException('Una fila de la copia está incompleta.');if(!isset($preparadas[$tabla])){$nombres=implode(',',array_map(static fn($c)=>'`'.$c.'`',$cols));$preparadas[$tabla]=$pdo->prepare('INSERT INTO `'.$tabla.'` ('.$nombres.') VALUES ('.implode(',',array_fill(0,count($cols),'?')).')');}$decodificados=array_map(static fn($v)=>$v===null?null:base64_decode((string)$v,true),$values);if(in_array(false,$decodificados,true))throw new RuntimeException('Una fila no superó la validación.');$preparadas[$tabla]->execute($decodificados);$filas++;}
        foreach($vistas as $vista)$pdo->exec((string)$vista['create']);foreach($triggers as $trigger)$pdo->exec((string)$trigger['create']);
        return $filas;
    }finally{$pdo->exec('SET FOREIGN_KEY_CHECKS=1');}
}

function restaurarRespaldoBaseDatos(int $respaldoId, int $actorId): array
{
    $pdo=obtenerConexion();if(!respaldoEsSuperadmin($actorId,$pdo))throw new DomainException('Solo un Superadministrador puede restaurar copias.');
    $bloqueo=respaldoBloqueoOperacion();
    try{
        $respaldo=respaldoPorId($respaldoId,$pdo);if(!$respaldo)throw new DomainException('La copia seleccionada no existe.');
        $ruta=verificarIntegridadRespaldo($respaldo);$previo=crearRespaldoBaseDatos($actorId,'previo_restauracion',false);if(empty($previo['id']))throw new RuntimeException('No se pudo crear la copia previa obligatoria.');
        $pdo->prepare("UPDATE respaldos_base_datos SET estado='restaurando',respaldo_previo_id=:previo WHERE id=:id")->execute(['previo'=>$previo['id'],'id'=>$respaldoId]);
        $filas=restaurarArchivoRespaldo($pdo,$ruta,[ATENEA_BACKUP_TRACKING_TABLE]);
        $q=$pdo->prepare("UPDATE respaldos_base_datos SET estado='restaurado',restaurado_por=:u,restaurado_at=NOW(),respaldo_previo_id=:previo,error_sanitizado=NULL WHERE id=:id");$q->execute(['u'=>$actorId,'previo'=>$previo['id'],'id'=>$respaldoId]);
        registrarAuditoria(['actor_user_id'=>$actorId,'event_type'=>'database.backup.restored','module'=>'backups','entity_type'=>'database_backup','entity_id'=>$respaldoId,'action'=>'restore','result'=>'success','description'=>'Un Superadministrador restauró la base de datos después de crear una copia previa automática.','metadata'=>['pre_restore_backup_id'=>(int)$previo['id'],'rows'=>$filas]],$pdo);
        notificarAdministracionAtenea('accion_administrativa_critica','Base de datos restaurada','Un Superadministrador restauró la copia #'.$respaldoId.'.','critico',$actorId,atenea_url('src/dashboard/backups/index.php'),'backup:restaurado:'.$respaldoId.':'.date('YmdHis'),['category'=>'administracion','created_by'=>$actorId],$pdo);
        return ['respaldo'=>$respaldoId,'previo'=>(int)$previo['id'],'filas'=>$filas];
    }catch(Throwable $e){try{$pdo->exec('SET FOREIGN_KEY_CHECKS=1');$pdo->prepare("UPDATE respaldos_base_datos SET estado='fallido',error_sanitizado='La restauración no pudo completarse. La copia previa permanece disponible.' WHERE id=:id")->execute(['id'=>$respaldoId]);registrarAuditoria(['actor_user_id'=>$actorId,'event_type'=>'database.backup.restore_failed','module'=>'backups','entity_type'=>'database_backup','entity_id'=>$respaldoId,'action'=>'restore','result'=>'failure','description'=>'Falló una restauración. La copia previa automática permanece disponible.'],$pdo);}catch(Throwable){}error_log('Atenea restore: '.$e->getMessage());throw $e;
    }finally{flock($bloqueo,LOCK_UN);fclose($bloqueo);}
}

function eliminarRespaldoBaseDatos(int $id,int $actorId,bool $retencion=false,?PDO $pdo=null): void
{
    $pdo??=obtenerConexion();$r=respaldoPorId($id,$pdo);if(!$r)throw new DomainException('La copia no existe.');if(in_array($r['estado'],['creando','restaurando'],true))throw new DomainException('No se puede eliminar una copia en proceso.');
    try{$ruta=respaldoRutaAbsoluta((string)$r['ruta_relativa'],false);if(is_file($ruta)&&!unlink($ruta))throw new RuntimeException('No fue posible eliminar el archivo privado.');$pdo->prepare("UPDATE respaldos_base_datos SET estado='eliminado',eliminado_at=NOW(),sha256=NULL,error_sanitizado=NULL WHERE id=:id")->execute(['id'=>$id]);registrarAuditoria(['actor_user_id'=>$actorId?:null,'event_type'=>$retencion?'database.backup.retention_deleted':'database.backup.deleted','module'=>'backups','entity_type'=>'database_backup','entity_id'=>$id,'action'=>'delete','result'=>'success','description'=>$retencion?'Una copia vencida fue retirada por la política de retención.':'Un administrador eliminó una copia de seguridad.'],$pdo);}catch(Throwable $e){error_log('Atenea delete backup: '.$e->getMessage());throw $e;}
}

function aplicarRetencionRespaldos(?PDO $pdo=null,array $protegidos=[]): int
{
    $pdo??=obtenerConexion();$dias=respaldoRetencionDias();$max=respaldoMaximoArchivos();$q=$pdo->query("SELECT id,created_at FROM respaldos_base_datos WHERE estado IN('disponible','restaurado','fallido') ORDER BY created_at DESC,id DESC");$filas=$q->fetchAll();$eliminados=0;
    foreach($filas as $i=>$r){$id=(int)$r['id'];if(in_array($id,$protegidos,true))continue;$vencido=strtotime((string)$r['created_at'])<time()-$dias*86400;$excede=$i>=$max;if(!$vencido&&!$excede)continue;try{eliminarRespaldoBaseDatos($id,0,true,$pdo);$eliminados++;}catch(Throwable){}}
    return $eliminados;
}

function formatoTamanoRespaldo(?int $bytes): string
{
    if(!$bytes)return '—';$u=['B','KB','MB','GB','TB'];$v=(float)$bytes;$i=0;while($v>=1024&&$i<count($u)-1){$v/=1024;$i++;}return number_format($v,$i===0?0:1,',','.').' '.$u[$i];
}
