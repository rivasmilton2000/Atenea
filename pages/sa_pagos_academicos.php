<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
require_once __DIR__ . '/../includes/academic_payments_admin_page.php';

$state = atenea_academic_admin_prepare($db, 'sa_pagos_academicos.php');

include '../includes/sidebar_superadmin.php';
atenea_academic_admin_render($state);
include '../includes/footer_superadmin.php';
