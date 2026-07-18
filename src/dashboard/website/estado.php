<?php
declare(strict_types=1);require_once dirname(__DIR__).'/includes/cms.php';$hash=(string)obtenerConexion()->query("SELECT SHA2(contenido_json,256) FROM website_publicaciones WHERE estado='borrador' ORDER BY id DESC LIMIT 1")->fetchColumn();header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');echo json_encode(['hash'=>$hash],JSON_THROW_ON_ERROR);
