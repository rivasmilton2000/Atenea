<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/auth.php';
require_once dirname(__DIR__,2).'/includes/permissions.php';
require_once dirname(__DIR__,2).'/includes/contenido.php';
require_once dirname(__DIR__,2).'/includes/perfil_modal.php';
require_once dirname(__DIR__,2).'/includes/alerts.php';
exigirRol(['docente','admin']);
