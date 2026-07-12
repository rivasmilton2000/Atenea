<?php
require_once __DIR__ . '/../_auth_guard.php';
$dashboardActive ??= 'panel';
$inicioAbierto = in_array($dashboardActive,['secciones/index.php','elementos/index.php','hero'],true);
$heroId = 0;
try { $heroId = (int) obtenerConexion()->query("SELECT id FROM secciones WHERE clave='hero' LIMIT 1")->fetchColumn(); } catch(Throwable $e) { error_log($e->getMessage()); }
?>
<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item <?=$dashboardActive==='panel'?'active':''?>"><a class="nav-link" href="<?=atenea_url('src/dashboard/index.php')?>"><i class="mdi mdi-grid-large menu-icon"></i><span class="menu-title">Panel principal</span></a></li>
    <li class="nav-item nav-category">Gestión del sitio web</li>
    <li class="nav-item <?=$dashboardActive==='resumen'?'active':''?>"><a class="nav-link" href="<?=atenea_url('src/dashboard/index.php')?>"><i class="mdi mdi-monitor-dashboard menu-icon"></i><span class="menu-title">Resumen del sitio</span></a></li>
    <li class="nav-item <?=$dashboardActive==='configuracion/index.php'?'active':''?>"><a class="nav-link" href="<?=atenea_url('src/dashboard/configuracion/index.php')?>"><i class="mdi mdi-cog-outline menu-icon"></i><span class="menu-title">Configuración general</span></a></li>
    <li class="nav-item <?=$dashboardActive==='navbar/index.php'?'active':''?>"><a class="nav-link" href="<?=atenea_url('src/dashboard/navbar/index.php')?>"><i class="mdi mdi-menu menu-icon"></i><span class="menu-title">Navbar y menú</span></a></li>
    <li class="nav-item <?=$dashboardActive==='portal-estudiante'?'active':''?>"><a class="nav-link" href="<?=atenea_url('src/dashboard/portal-estudiante/index.php')?>"><i class="mdi mdi-school-outline menu-icon"></i><span class="menu-title">Portal del estudiante</span></a></li>
    <li class="nav-item <?=$inicioAbierto?'active':''?>">
      <a class="nav-link" data-bs-toggle="collapse" href="#paginaInicio" aria-expanded="<?=$inicioAbierto?'true':'false'?>" aria-controls="paginaInicio"><i class="mdi mdi-home-edit-outline menu-icon"></i><span class="menu-title">Página de inicio</span><i class="menu-arrow"></i></a>
      <div class="collapse <?=$inicioAbierto?'show':''?>" id="paginaInicio"><ul class="nav flex-column sub-menu">
        <li class="nav-item"><a class="nav-link <?=$dashboardActive==='secciones/index.php'?'active':''?>" href="<?=atenea_url('src/dashboard/secciones/index.php')?>">Secciones</a></li>
        <li class="nav-item"><a class="nav-link <?=$dashboardActive==='elementos/index.php'?'active':''?>" href="<?=atenea_url('src/dashboard/elementos/index.php')?>">Elementos</a></li>
        <?php if($heroId):?><li class="nav-item"><a class="nav-link <?=$dashboardActive==='hero'?'active':''?>" href="<?=atenea_url('src/dashboard/secciones/editar.php?id='.$heroId)?>">Hero principal</a></li><?php endif;?>
      </ul></div>
    </li>
    <li class="nav-item nav-category">Cuenta</li>
    <li class="nav-item"><a class="nav-link" href="<?=atenea_url('index.php')?>" target="_blank" rel="noopener"><i class="mdi mdi-web menu-icon"></i><span class="menu-title">Ver sitio</span></a></li>
    <li class="nav-item"><a class="nav-link" href="<?=atenea_url('src/login/logout.php')?>"><i class="mdi mdi-logout menu-icon"></i><span class="menu-title">Cerrar sesión</span></a></li>
  </ul>
</nav>
