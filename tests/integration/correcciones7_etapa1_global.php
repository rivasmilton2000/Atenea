<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/comunicacion_centro.php';
require_once dirname(__DIR__,2).'/includes/error_handler.php';
require_once dirname(__DIR__,2).'/includes/notificaciones.php';
$root=dirname(__DIR__,2);$ok=[];$assert=static function(bool $c,string $m)use(&$ok):void{if(!$c)throw new RuntimeException('FALLO: '.$m);$ok[]=$m;};
$assert(ImapConfig::extensionAvailable(),'La extensión IMAP está habilitada en PHP');
$assert(ImapConfig::missing()===[],'Todas las variables IMAP se leen desde la configuración central');
$config=configuracionImapAtenea();$assert($config['host']===entornoAtenea('IMAP_HOST')&&$config['user']===entornoAtenea('IMAP_USERNAME'),'Los módulos consumen los mismos valores centralizados');
$diagnostico=diagnosticoImapAtenea(obtenerConexion(),true);$assert($diagnostico['disponible']&&$diagnostico['conexion'],'La conexión IMAP de solo lectura es correcta');
$correo=file_get_contents($root.'/src/comunicaciones/correo.php');$assert(!str_contains($correo,'IMAP pendiente de configuración'),'La bandeja final no muestra advertencias técnicas de IMAP');
foreach(['403','404','419','500','503','database'] as$pagina)$assert(is_file($root.'/src/errors/'.$pagina.'.php'),'Existe la página de error '.$pagina);
$plantilla=file_get_contents($root.'/includes/templates/error_page.php');$assert(str_contains($plantilla,'Regresar')&&str_contains($plantilla,'Código de seguimiento'),'La vista de error ofrece retorno e identificador de incidente');
foreach(['index.php','api.php','accion.php'] as$archivo)$assert(is_file($root.'/src/notificaciones/'.$archivo),'Existe el flujo compartido de notificaciones: '.$archivo);
$js=file_get_contents($root.'/src/shared/assets/js/notificaciones-globales.js');$assert(str_contains($js,'setInterval')&&str_contains($js,'data-atenea-notification-count'),'El contador de notificaciones se actualiza periódicamente');
$tema=file_get_contents($root.'/src/shared/assets/css/atenea-theme.css');$assert(str_contains($tema,'.main-panel .btn-primary')&&str_contains($tema,'focus-visible'),'El área académica tiene botones verdes y foco accesible');
echo 'OK '.count($ok)." pruebas\n";foreach($ok as$m)echo '- '.$m."\n";
