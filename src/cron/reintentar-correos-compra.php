<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once dirname(__DIR__,2).'/includes/mailer.php';
$r=procesarColaCorreoAtenea(25);
echo 'Cola revisada: '.$r['revisados'].'; enviados: '.$r['enviados'].'; fallidos: '.$r['fallidos'].'; cancelados: '.$r['cancelados'].'; limitados: '.$r['limitados'].PHP_EOL;
