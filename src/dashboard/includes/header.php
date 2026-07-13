<?php
require_once __DIR__ . '/cms.php';
$dashboardTitle ??= 'Panel principal';
$configuracionAdmin ??= obtenerConfiguracionSitio();
$logoAdmin = $configuracionAdmin['logo'] ?? 'img/atenea-logo.png';
$faviconAdmin = $configuracionAdmin['favicon'] ?? 'img/atenea-logo.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?= atenea_e($dashboardTitle ?? 'Panel principal') ?> | Atenea</title>
  <link rel="stylesheet" href="<?= atenea_url('src/dashboard/assets/vendors/feather/feather.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/dashboard/assets/vendors/mdi/css/materialdesignicons.min.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/dashboard/assets/vendors/ti-icons/css/themify-icons.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/dashboard/assets/vendors/font-awesome/css/font-awesome.min.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/dashboard/assets/vendors/typicons/typicons.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/dashboard/assets/vendors/simple-line-icons/css/simple-line-icons.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/dashboard/assets/vendors/css/vendor.bundle.base.css') ?>">
  <link rel="stylesheet" href="<?= atenea_url('src/dashboard/assets/css/style.css') ?>">
  <link rel="icon" type="image/png" href="<?= rutaImagenContenido($faviconAdmin, 'img/atenea-logo.png') ?>">
</head>
<body class="with-welcome-text">
<div class="container-scroller">
