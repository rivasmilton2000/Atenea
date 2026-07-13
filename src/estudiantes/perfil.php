<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/auth.php';
exigirRol(['usuario']);
$completar = isset($_GET['completar']) && $_GET['completar'] === '1';
if ($completar && !isset($_SESSION['cuenta_modal'])) $_SESSION['cuenta_modal']=['abrir'=>true,'errores'=>[],'mensaje'=>'Completa los datos obligatorios para ingresar al portal.'];
require_once dirname(__DIR__,2).'/includes/portal_estudiante_layout.php';
$portal=portalEstudianteCabecera('Mi perfil','perfil','Administra tus datos personales, dirección y seguridad.',true);
$perfil=$portal['perfil'];
?>
<div class="row"><div class="col-lg-8 mx-auto"><div class="card"><div class="card-body p-4"><div class="d-flex flex-column flex-md-row align-items-center gap-4"><img src="<?=atenea_e(rutaFotoPerfil($perfil))?>" class="avatar avatar-100 avatar-rounded" alt="Foto de perfil"><div class="flex-grow-1 text-center text-md-start"><h1 class="h3 mb-1"><?=atenea_e(trim((string)$perfil['nombre'].' '.(string)$perfil['apellido']))?></h1><p class="text-muted mb-2"><?=atenea_e((string)$perfil['correo'])?> · <?=atenea_e(etiquetaRol((string)$perfil['rol']))?></p><p>Actualiza tus datos, fotografía, dirección, correo y contraseña desde un único perfil seguro.</p><button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalPerfil">Abrir mi perfil</button></div></div></div></div></div></div>
<?php portalEstudiantePie(); ?>
