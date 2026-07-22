<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once dirname(__DIR__,2).'/includes/newsletter.php';$limite=isset($argv[1])?(int)$argv[1]:25;$resultado=procesarColaNewsletterAtenea($limite);echo json_encode($resultado,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
