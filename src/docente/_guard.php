<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/auth.php';
require_once dirname(__DIR__,2).'/includes/permissions.php';
require_once dirname(__DIR__,2).'/includes/contenido.php';
require_once dirname(__DIR__,2).'/includes/perfil_modal.php';
require_once dirname(__DIR__,2).'/includes/alerts.php';
exigirRol(['docente','admin','administracion_docente','administrador_docente']);
if(esRolAdministradorDocenteAtenea($_SESSION['usuario_rol']??null)){
    require_once dirname(__DIR__,2).'/includes/hybrid_permissions.php';
    exigirModoHibridoAtenea('docente');
    $permiso=permisoRutaDocenteHibridaAtenea((string)($_SERVER['SCRIPT_NAME']??''));
    if($permiso!==null&&!usuarioTienePermiso($permiso)){
        registrarFalloGlobalAtenea('Ruta docente híbrida denegada.',403);
        mostrarPaginaErrorAtenea(403);
    }
}
