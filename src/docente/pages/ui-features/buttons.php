<?php require_once dirname(__DIR__,2).'/_layout.php'; ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Star Admin2 </title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="../../assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="../../assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../../assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../assets/vendors/typicons/typicons.css">
    <link rel="stylesheet" href="../../assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="../../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../../assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="<?=atenea_url('src/website/assets/css/perfil-modal.css')?>">
    <!-- endinject -->
    <link rel="shortcut icon" href="../../assets/images/favicon.png" />
  </head>
  <body>
    <div class="container-scroller">
      <?php require dirname(__DIR__,2).'/partials/_navbar.php'; ?>
      <div class="container-fluid page-body-wrapper">
        <?php require dirname(__DIR__,2).'/partials/_sidebar.php'; ?>
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="card-body">
                        <h4 class="card-title">Single color buttons</h4>
                        <p class="card-description">Add class <code>.btn-{color}</code> for buttons in theme colors</p>
                        <div class="template-demo">
                          <button type="button" class="btn btn-primary">Primary</button>
                          <button type="button" class="btn btn-secondary">Secondary</button>
                          <button type="button" class="btn btn-success">Success</button>
                          <button type="button" class="btn btn-danger">Danger</button>
                          <button type="button" class="btn btn-warning">Warning</button>
                          <button type="button" class="btn btn-info">Info</button>
                          <button type="button" class="btn btn-light">Light</button>
                          <button type="button" class="btn btn-dark">Dark</button>
                          <button type="button" class="btn btn-link">Link</button>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="card-body">
                        <h4 class="card-title">Rounded buttons</h4>
                        <p class="card-description">Add class <code>.btn-rounded</code></p>
                        <div class="template-demo">
                          <button type="button" class="btn btn-primary btn-rounded btn-fw">Primary</button>
                          <button type="button" class="btn btn-secondary btn-rounded btn-fw">Secondary</button>
                          <button type="button" class="btn btn-success btn-rounded btn-fw">Success</button>
                          <button type="button" class="btn btn-danger btn-rounded btn-fw">Danger</button>
                          <button type="button" class="btn btn-warning btn-rounded btn-fw">Warning</button>
                          <button type="button" class="btn btn-info btn-rounded btn-fw">Info</button>
                          <button type="button" class="btn btn-light btn-rounded btn-fw">Light</button>
                          <button type="button" class="btn btn-dark btn-rounded btn-fw">Dark</button>
                          <button type="button" class="btn btn-link btn-rounded btn-fw">Link</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="card-body">
                        <h4 class="card-title">Outlined buttons</h4>
                        <p class="card-description">Add class <code>.btn-outline-{color}</code> for outline buttons</p>
                        <div class="template-demo">
                          <button type="button" class="btn btn-outline-primary btn-fw">Primary</button>
                          <button type="button" class="btn btn-outline-secondary btn-fw">Secondary</button>
                          <button type="button" class="btn btn-outline-success btn-fw">Success</button>
                          <button type="button" class="btn btn-outline-danger btn-fw">Danger</button>
                          <button type="button" class="btn btn-outline-warning btn-fw">Warning</button>
                          <button type="button" class="btn btn-outline-info btn-fw">Info</button>
                          <button type="button" class="btn btn-outline-light btn-fw">Light</button>
                          <button type="button" class="btn btn-outline-dark btn-fw">Dark</button>
                          <button type="button" class="btn btn-link btn-fw">Link</button>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="card-body">
                        <h4 class="card-title">Inverse buttons</h4>
                        <p class="card-description">Add class <code>.btn-inverse-{color} for inverse buttons</code></p>
                        <div class="template-demo">
                          <button type="button" class="btn btn-inverse-primary btn-fw">Primary</button>
                          <button type="button" class="btn btn-inverse-secondary btn-fw">Secondary</button>
                          <button type="button" class="btn btn-inverse-success btn-fw">Success</button>
                          <button type="button" class="btn btn-inverse-danger btn-fw">Danger</button>
                          <button type="button" class="btn btn-inverse-warning btn-fw">Warning</button>
                          <button type="button" class="btn btn-inverse-info btn-fw">Info</button>
                          <button type="button" class="btn btn-inverse-light btn-fw">Light</button>
                          <button type="button" class="btn btn-inverse-dark btn-fw">Dark</button>
                          <button type="button" class="btn btn-link btn-fw">Link</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- content-wrapper ends -->
          <?php require dirname(__DIR__,2).'/partials/_footer.php'; ?>
        </div>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <?php renderizarModalPerfil(($_SESSION['usuario_rol']??'')==='admin'?'dashboard':'docente'); ?>    <!-- plugins:js -->
    <script src="../../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../../assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="../../assets/js/off-canvas.js"></script>
    <script src="../../assets/js/template.js"></script>
    <script src="../../assets/js/settings.js"></script>
    <script src="../../assets/js/hoverable-collapse.js"></script>
    <script src="../../assets/js/todolist.js"></script>
    <!-- endinject -->
    <script src="<?=atenea_url('src/website/assets/js/perfil-modal.js')?>"></script>  </body>
</html>