<?php
declare(strict_types=1);require_once dirname(__DIR__).'/includes/cms.php';require_once dirname(__DIR__,3).'/includes/json_response.php';$hash=(string)obtenerConexion()->query("SELECT SHA2(contenido_json,256) FROM website_publicaciones WHERE estado='borrador' ORDER BY id DESC LIMIT 1")->fetchColumn();responderJsonExitoAtenea(['hash'=>$hash]);
