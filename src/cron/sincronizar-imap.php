<?php
declare(strict_types=1);if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once dirname(__DIR__,2).'/includes/comunicacion_centro.php';
try{$resultado=sincronizarImapAtenea(obtenerConexion());echo 'IMAP sincronizado. Nuevos: '.$resultado['nuevos'].'; último UID: '.$resultado['ultimo_uid'].PHP_EOL;exit(0);}catch(Throwable$e){fwrite(STDERR,'IMAP: '.mb_substr($e->getMessage(),0,300).PHP_EOL);exit(1);}
