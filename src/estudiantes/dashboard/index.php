<?php
declare(strict_types=1);require_once dirname(__DIR__,3).'/includes/auth.php';exigirRol(['usuario']);header('Location: '.atenea_url('src/estudiantes/index.php'),true,301);exit;
