<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/database_sql_export.php';

$pdo = obtenerConexion(); $ok = [];
$assert = static function(bool $condicion, string $mensaje) use (&$ok): void { if (!$condicion) throw new RuntimeException('FALLO: ' . $mensaje); $ok[] = $mensaje; };
$sufijo = bin2hex(random_bytes(4)); $tabla = 'sql_export_test_' . $sufijo; $basePrueba = 'atenea_sql_test_' . $sufijo; $servidor = null; $archivoRestaurar = null;
try {
    $pdo->exec("CREATE TABLE `{$tabla}` (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nullable_text VARCHAR(120) NULL, amount DECIMAL(12,2) NOT NULL, happened_at DATETIME NOT NULL, unicode_text VARCHAR(190) NOT NULL, binary_data VARBINARY(32) NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $q = $pdo->prepare("INSERT INTO `{$tabla}`(nullable_text,amount,happened_at,unicode_text,binary_data) VALUES(:nullable,:amount,:date,:text,:binary)");
    for ($i=0; $i<251; $i++) {
        $q->bindValue('nullable', $i === 0 ? null : 'fila ' . $i, $i === 0 ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $q->bindValue('amount', '1234.50'); $q->bindValue('date', '2026-07-20 22:15:00');
        $q->bindValue('text', $i === 0 ? "Comilla ' salto\nAtenea ñ 漢字" : 'Texto ' . $i);
        $q->bindValue('binary', $i === 0 ? "\x00\x01\xFF" : pack('N', $i), PDO::PARAM_LOB); $q->execute();
    }
    $disponibles = tablasSqlDisponiblesAtenea($pdo);
    $assert(in_array($tabla, $disponibles, true), 'El selector se alimenta con las tablas reales de SHOW TABLES');
    $rechazada = false; try { normalizarExportacionSqlAtenea($pdo, 'tabla', 'completa', $tabla . '; DROP TABLE usuarios'); } catch (DomainException) { $rechazada = true; }
    $assert($rechazada, 'Una tabla manipulada se rechaza contra la lista permitida');

    $casos = [['base','completa',null],['base','estructura',null],['base','datos',null],['tabla','completa',$tabla],['tabla','estructura',$tabla],['tabla','datos',$tabla]];
    $sqlPorCaso = [];
    foreach ($casos as [$alcance,$contenido,$seleccion]) {
        $opciones = normalizarExportacionSqlAtenea($pdo,$alcance,$contenido,$seleccion); $sql = '';
        $resultado = exportarBaseActualSqlAtenea($pdo,$opciones,static function(string $bloque) use (&$sql): void { $sql .= $bloque; });
        $clave = $alcance . '-' . $contenido; $sqlPorCaso[$clave] = $sql;
        $assert(str_contains($sql,'SET NAMES utf8mb4')&&str_contains($sql,'SET FOREIGN_KEY_CHECKS=0')&&str_contains($sql,'SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS'),'La modalidad '.$clave.' conserva UTF-8 y restaura claves foráneas');
        $assert((bool)preg_match('/^atenea_(?:backup|[A-Za-z0-9_]+)_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/',nombreExportacionSqlAtenea($opciones)),'La modalidad '.$clave.' genera el nombre SQL requerido');
    }
    $assert(str_contains($sqlPorCaso['base-completa'],'CREATE DATABASE IF NOT EXISTS')&&str_contains($sqlPorCaso['base-completa'],'CREATE TABLE')&&str_contains($sqlPorCaso['base-completa'],'INSERT INTO'),'La copia completa incluye base, estructura e información');
    $assert(str_contains($sqlPorCaso['base-estructura'],'CREATE TABLE')&&!str_contains($sqlPorCaso['base-estructura'],'INSERT INTO'),'La copia global de estructura no incluye registros');
    $assert(!str_contains($sqlPorCaso['base-datos'],'CREATE TABLE')&&str_contains($sqlPorCaso['base-datos'],'INSERT INTO'),'La copia global de datos no incluye estructura');
    $assert(str_contains($sqlPorCaso['tabla-completa'],'CREATE TABLE `'.$tabla.'`')&&str_contains($sqlPorCaso['tabla-completa'],'INSERT INTO `'.$tabla.'`'),'La tabla completa incluye únicamente su estructura e información');
    $assert(str_contains($sqlPorCaso['tabla-estructura'],'CREATE TABLE `'.$tabla.'`')&&!str_contains($sqlPorCaso['tabla-estructura'],'INSERT INTO'),'La estructura de una tabla no incluye registros');
    $assert(!str_contains($sqlPorCaso['tabla-datos'],'CREATE TABLE')&&substr_count($sqlPorCaso['tabla-datos'],'INSERT INTO `'.$tabla.'`')>=2,'Los datos de una tabla se escriben en lotes sin incluir estructura');
    $assert(str_contains($sqlPorCaso['tabla-completa'],'NULL')&&str_contains($sqlPorCaso['tabla-completa'],'1234.50')&&str_contains($sqlPorCaso['tabla-completa'],'0x0001ff'),'NULL, números y valores binarios conservan su tipo SQL');

    $sqlRestaurar = $sqlPorCaso['base-completa']; $baseActual = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();
    $sqlRestaurar = str_replace(
        'CREATE DATABASE IF NOT EXISTS '.identificadorSqlAtenea($baseActual).' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'."\n".'USE '.identificadorSqlAtenea($baseActual).';',
        'CREATE DATABASE '.identificadorSqlAtenea($basePrueba).' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'."\n".'USE '.identificadorSqlAtenea($basePrueba).';',
        $sqlRestaurar
    );
    $mysql = realpath(dirname(PHP_BINARY).'/../mysql/bin/mysql.exe');
    $assert(is_string($mysql)&&is_file($mysql),'MySQL de XAMPP está disponible para validar DELIMITER y restaurar la copia completa');
    $archivoRestaurar = tempnam(sys_get_temp_dir(),'atenea_restore_');
    if(!is_string($archivoRestaurar)||file_put_contents($archivoRestaurar,$sqlRestaurar)===false)throw new RuntimeException('No se pudo preparar el SQL de restauración.');
    $hostMysql=entornoAtenea('ATENEA_DB_HOST','localhost');if(strtolower($hostMysql)==='localhost')$hostMysql='127.0.0.1';
    $comando=[$mysql,'--host='.$hostMysql,'--port='.entornoAtenea('ATENEA_DB_PORT','3306'),'--user='.entornoAtenea('ATENEA_DB_USER','root'),'--default-character-set=utf8mb4'];
    $entorno=getenv();if(!is_array($entorno))$entorno=[];$entorno['MYSQL_PWD']=entornoAtenea('ATENEA_DB_PASSWORD');$pipes=[];$proceso=proc_open($comando,[0=>['file',$archivoRestaurar,'r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,null,$entorno);
    if(!is_resource($proceso))throw new RuntimeException('No se pudo iniciar MySQL para la restauración.');
    $salida=(string)stream_get_contents($pipes[1]);$error=(string)stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$codigo=proc_close($proceso);
    $assert($codigo===0,'La copia completa se restaura con el cliente MySQL'.($codigo===0?'':': '.trim($error?:$salida)));
    $servidor = new PDO('mysql:host='.entornoAtenea('ATENEA_DB_HOST','localhost').';port='.entornoAtenea('ATENEA_DB_PORT','3306').';charset=utf8mb4',entornoAtenea('ATENEA_DB_USER','root'),entornoAtenea('ATENEA_DB_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);
    $restaurada = $servidor->query('SELECT * FROM '.identificadorSqlAtenea($basePrueba).'.'.identificadorSqlAtenea($tabla).' WHERE id=1')->fetch(PDO::FETCH_ASSOC);
    $assert($restaurada&&$restaurada['nullable_text']===null&&$restaurada['amount']==='1234.50'&&$restaurada['unicode_text']==="Comilla ' salto\nAtenea ñ 漢字"&&$restaurada['binary_data']==="\x00\x01\xFF",'La copia completa restaura la base, NULL, decimales, fechas, Unicode, comillas y binarios en una base desechable');

    $endpoint = file_get_contents(dirname(__DIR__,2).'/src/dashboard/backups/exportar-sql.php'); $pagina = file_get_contents(dirname(__DIR__,2).'/src/dashboard/backups/index.php');
    $assert(str_contains($endpoint,"exigirPermiso('backups.manage')")&&str_contains($endpoint,'validarTokenCsrf'),'El endpoint exige permiso administrativo y CSRF');
    $assert(str_contains($endpoint,'database.sql_export.generated')&&!str_contains($endpoint,'contenido_html'),'La generación registra metadatos sin guardar el contenido SQL');
    foreach(['sql-contenido','sql-alcance','sql-tabla','resumenExportacionSql','progresoExportacionSql','Generar copia SQL'] as $control) $assert(str_contains($pagina,$control),'La interfaz incorpora '.$control);
    echo 'OK '.count($ok)." pruebas\n"; foreach($ok as $mensaje) echo '- '.$mensaje."\n";
} finally {
    try { $pdo->exec('DROP TABLE IF EXISTS '.identificadorSqlAtenea($tabla)); } catch (Throwable) {}
    if ($servidor instanceof PDO) try { $servidor->exec('DROP DATABASE IF EXISTS '.identificadorSqlAtenea($basePrueba)); } catch (Throwable) {}
    if(is_string($archivoRestaurar)&&is_file($archivoRestaurar))@unlink($archivoRestaurar);
}
