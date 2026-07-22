<?php
declare(strict_types=1);if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once dirname(__DIR__,2).'/includes/comunicacion_centro.php';require_once dirname(__DIR__,2).'/includes/errores_sistema.php';
try{$resultado=sincronizarImapAtenea(obtenerConexion());echo 'IMAP sincronizado. Nuevos: '.$resultado['nuevos'].'; último UID: '.$resultado['ultimo_uid'].PHP_EOL;exit(0);}catch(Throwable$e){try{registrarErrorSistemaAtenea('correo','imap',mb_substr($e->getMessage(),0,300),[],'error');}catch(Throwable){}fwrite(STDERR,'IMAP: no fue posible sincronizar el buzón.'.PHP_EOL);exit(1);}
