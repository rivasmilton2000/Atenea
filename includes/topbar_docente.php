
<!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

          <!-- Sidebar Toggle (Topbar) -->
          <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>

          <!-- Topbar Navbar -->
          <ul class="navbar-nav ml-auto">

          <li class="nav-item dropdown no-arrow d-none d-sm-block">
            <a class="nav-link">
                <span class="mr-2 text-gray-600 small admin-text">DOCENTE</span>
            </a>
          </li> 

            <!-- Agregar este nuevo elemento para dispositivos móviles -->
            <li class="nav-item dropdown no-arrow d-sm-none">
                <a class="nav-link">
                    <span class="mr-2 text-gray-600 small admin-text">DOCENTE</span>
                </a>
            </li>

    <div class="topbar-divider d-none d-sm-block"></div>

    <!-- Nav Item - User Information -->
    <li class="nav-item dropdown no-arrow">
        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['FIRST_NAME'] . ' ' . $_SESSION['LAST_NAME']; ?></span>
            <img class="img-profile rounded-circle" <?php if ($_SESSION['GENDER'] == 'Hombre') {
                                                        echo 'src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTS0rikanm-OEchWDtCAWQ_s1hQq1nOlQUeJr242AdtgqcdEgm0Dg"';
                                                    } else {
                                                        echo 'src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSNngF0RFPjyGl4ybo78-XYxxeap88Nvsyj1_txm6L4eheH8ZBu"';
                                                    } ?>>
        </a>
        <?php
        $query = 'SELECT ID, FIRST_NAME, LAST_NAME, USERNAME, PASSWORD FROM users u JOIN employee e ON e.EMPLOYEE_ID=u.EMPLOYEE_ID';
        $result = mysqli_query($db, $query) or die(mysqli_error($db));
        while ($row = mysqli_fetch_assoc($result)) {
            $a = $_SESSION['MEMBER_ID'];
        }
        ?>

        <!-- Dropdown - User Information -->
        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
          <a class="dropdown-item" href="docentes_vista_perfil.php?action=edit&id=<?php echo $a; ?>">
                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                Perfil
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                Cerrar sesión
            </a>
        </div>
    </li>
</ul>

        </nav>
        <!-- End of Topbar -->
        <!-- Begin Page Content -->
        <div class="container-fluid">

<style>
  .admin-text {
  font-weight: bold;
  }

  @media (max-width: 575.98px) {
    .admin-text {
    font-size: 0.7rem;
    }
  }
</style>