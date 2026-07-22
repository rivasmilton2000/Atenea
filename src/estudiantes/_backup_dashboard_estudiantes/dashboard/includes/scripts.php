<?php require_once __DIR__ . '/cms.php'; ?>
<script src="<?= atenea_url('src/administador_docente/dashboard/assets/vendors/js/vendor.bundle.base.js') ?>"></script>
<script src="<?= atenea_url('src/administador_docente/dashboard/assets/js/off-canvas.js') ?>"></script>
<script src="<?= atenea_url('src/administador_docente/dashboard/assets/js/template.js') ?>"></script>
<script src="<?= atenea_url('src/administador_docente/dashboard/assets/js/admin-profile-menu.js') ?>"></script>
<script src="<?= atenea_url('src/administador_docente/dashboard/assets/js/settings.js') ?>"></script>
<script src="<?= atenea_url('src/administador_docente/dashboard/assets/js/hoverable-collapse.js') ?>"></script>
<script src="<?= atenea_url('src/website/assets/js/perfil-modal.js') ?>"></script>
<?php renderizarControlSesionAtenea(); ?>
<script src="<?= atenea_url('src/website/assets/js/security-ui.js') ?>"></script>
<?php if(usuarioTienePermiso('notifications.view')):?><script>window.ateneaNotifications={url:<?=json_encode(atenea_url('src/dashboard/notificaciones/api.php'),JSON_THROW_ON_ERROR)?>};</script><script src="<?= atenea_url('src/administador_docente/dashboard/assets/js/notificaciones.js') ?>"></script><?php endif;?>
<?php ateneaAlertasScripts($GLOBALS['atenea_dashboard_flash'] ?? null); ?>
</body>
</html>
