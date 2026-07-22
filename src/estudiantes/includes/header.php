<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?=atenea_e($titulo)?> | Atenea Aula Virtual</title>
  <meta name="description" content="<?=atenea_e($descripcion?:'Aula virtual de Atenea')?>">
  <link rel="stylesheet" href="<?=atenea_url('src/estudiantes/dashboard_estudiantes/dashboard/assets/vendors/feather/feather.css')?>">
  <link rel="stylesheet" href="<?=atenea_url('src/estudiantes/dashboard_estudiantes/dashboard/assets/vendors/mdi/css/materialdesignicons.min.css')?>">
  <link rel="stylesheet" href="<?=atenea_url('src/estudiantes/dashboard_estudiantes/dashboard/assets/vendors/ti-icons/css/themify-icons.css')?>">
  <link rel="stylesheet" href="<?=atenea_url('src/estudiantes/dashboard_estudiantes/dashboard/assets/vendors/font-awesome/css/font-awesome.min.css')?>">
  <link rel="stylesheet" href="<?=atenea_url('src/estudiantes/dashboard_estudiantes/dashboard/assets/vendors/simple-line-icons/css/simple-line-icons.css')?>">
  <link rel="stylesheet" href="<?=atenea_url('src/estudiantes/dashboard_estudiantes/dashboard/assets/vendors/css/vendor.bundle.base.css')?>">
  <link rel="stylesheet" href="<?=atenea_url('src/estudiantes/dashboard_estudiantes/dashboard/assets/css/style.css')?>">
  <link rel="stylesheet" href="<?=atenea_url('src/estudiantes/dashboard_estudiantes/dashboard/assets/css/atenea-branding.css')?>">
  <link rel="stylesheet" href="<?=atenea_url('src/estudiantes/assets/css/student-dashboard.css')?>">
  <link rel="stylesheet" href="<?=atenea_url('src/website/assets/css/cart.css')?>">
  <link rel="stylesheet" href="<?=atenea_url('src/website/assets/css/perfil-modal.css')?>">
  <link rel="stylesheet" href="<?=atenea_url('src/website/assets/css/security-ui.css')?>">
  <?php foreach(array_unique($GLOBALS['atenea_layout_styles']??[]) as $estilo):?><link rel="stylesheet" href="<?=atenea_url((string)$estilo)?>"><?php endforeach;?>
  <?php ateneaAlertasHead('dashboard');?>
  <link rel="icon" type="image/png" href="<?=atenea_e($logo)?>">
</head>
<body class="with-welcome-text student-dashboard">
<div class="container-scroller">
