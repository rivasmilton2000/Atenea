<?php

require_once __DIR__ . '/material_module_shell.php';

$moduleShellContext = function_exists('module_shell_context') ? module_shell_context() : [];

if (!empty($moduleShellContext['active'])) {
    module_shell_render_footer([
        'modalBundle' => 'modal.php',
        'renderLogoutModal' => false,
    ]);

    return;
}

global $db;
$legacyModalBundle = __DIR__ . '/modal.php';
if (is_file($legacyModalBundle)) {
    include $legacyModalBundle;
}
?>
        </div>
      </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top" aria-label="Ir arriba">
      <i class="fas fa-angle-up"></i>
    </a>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="../js/demo/datatables-demo.js"></script>
    <script src="../js/city.js"></script>
  </body>
</html>
