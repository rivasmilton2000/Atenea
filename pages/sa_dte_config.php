<?php

require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/dte/admin_pages.php';

$state = atenea_dte_prepare_config_page($db, 'superadmin');

include '../includes/sidebar_superadmin.php';
atenea_dte_render_config_content($state);
include '../includes/footer_superadmin.php';
