<?php declare(strict_types=1); ?>
<script src="<?=atenea_url('src/estudiantes/dashboard_estudiantes/dashboard/assets/vendors/js/vendor.bundle.base.js')?>"></script>
<script src="<?=atenea_url('src/estudiantes/dashboard_estudiantes/dashboard/assets/js/off-canvas.js')?>"></script>
<script src="<?=atenea_url('src/estudiantes/dashboard_estudiantes/dashboard/assets/js/template.js')?>"></script>
<script src="<?=atenea_url('src/estudiantes/dashboard_estudiantes/dashboard/assets/js/hoverable-collapse.js')?>"></script>
<script src="<?=atenea_url('src/website/assets/js/perfil-modal.js')?>"></script>
<script src="<?=atenea_url('src/website/assets/js/security-ui.js')?>"></script>
<script>window.ateneaNotifications={url:<?=json_encode(atenea_url('src/notificaciones/api.php'),JSON_THROW_ON_ERROR)?>};</script>
<script src="<?=atenea_url('src/estudiantes/dashboard_estudiantes/dashboard/assets/js/notificaciones.js')?>"></script>
<?php ateneaAlertasScripts($GLOBALS['portal_estudiante_flash']??null);?>
</body>
</html>
