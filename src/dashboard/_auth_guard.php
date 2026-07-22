<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/permissions.php';
exigirRol(['admin','administracion_docente','administrador_docente']);
if(esRolAdministradorDocenteAtenea($_SESSION['usuario_rol']??null)){
    exigirModoHibridoAtenea('admin');
    $permiso=permisoRutaAdministrativaHibridaAtenea((string)($_SERVER['SCRIPT_NAME']??''),(string)($_SERVER['REQUEST_METHOD']??'GET'));
    if($permiso===null||!usuarioTienePermiso($permiso)){
        registrarFalloGlobalAtenea('Ruta administrativa híbrida denegada.',403);
        mostrarPaginaErrorAtenea(403);
    }
}
