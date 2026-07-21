<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/auth.php';require_once dirname(__DIR__,2).'/includes/json_response.php';
if(!usuarioAutenticado())responderJsonErrorAtenea('SESSION_EXPIRED','Tu sesión venció por inactividad.',401);
if(($_SERVER['REQUEST_METHOD']??'')!=='POST')responderJsonErrorAtenea('METHOD_NOT_ALLOWED','Método no permitido.',405);
if(!validarTokenCsrf((string)($_POST['csrf_token']??'')))responderJsonErrorAtenea('CSRF_EXPIRED','La sesión del formulario venció.',419);
if(($_POST['accion']??'')!=='continuar')responderJsonErrorAtenea('ACTION_INVALID','La acción no es válida.',400);
registrarActividadSesionAtenea(true);responderJsonExitoAtenea(['expira_en'=>ATENEA_SESSION_TIMEOUT_SECONDS]);
