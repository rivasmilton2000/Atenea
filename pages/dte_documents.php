<?php

require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/dte/admin_pages.php';

$state = atenea_dte_prepare_documents_page($db, 'admin');

include '../includes/sidebar_admin.php';
atenea_dte_render_documents_content($state);
include '../includes/footer.php';
