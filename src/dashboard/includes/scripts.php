<?php require_once __DIR__ . '/cms.php'; ?>
<script src="<?= atenea_url('src/dashboard/assets/vendors/js/vendor.bundle.base.js') ?>"></script>
<script src="<?= atenea_url('src/dashboard/assets/js/off-canvas.js') ?>"></script>
<script src="<?= atenea_url('src/dashboard/assets/js/template.js') ?>"></script>
<script src="<?= atenea_url('src/dashboard/assets/js/settings.js') ?>"></script>
<script src="<?= atenea_url('src/dashboard/assets/js/hoverable-collapse.js') ?>"></script>
<script src="<?= atenea_url('src/website/assets/js/perfil-modal.js') ?>"></script>
<?php ateneaAlertasScripts($GLOBALS['atenea_dashboard_flash'] ?? null); ?>
</body>
</html>
