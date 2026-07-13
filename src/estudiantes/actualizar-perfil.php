<?php
declare(strict_types=1);
// Compatibilidad con formularios anteriores: la lógica se mantiene centralizada.
require_once dirname(__DIR__,2).'/includes/config.php';
$_POST['retorno'] = $_POST['retorno'] ?? atenea_url('src/estudiantes/perfil.php');
require dirname(__DIR__,2).'/src/cuenta/actualizar-perfil.php';
