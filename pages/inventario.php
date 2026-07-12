<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Personal' || $Aa == 'Estudiante' || $Aa == 'Docente' || $Aa == 'SuperAdmin') {
        if ($Aa == 'Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa == 'Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa == 'Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa == 'SuperAdmin') {
            $redirectUrl = "sa_vista.php";
        }
        ?>
        <script type="text/javascript">
            //then it will be redirected
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
        <?php
        exit(); // Terminar la ejecución del script después de la redirección
    }
}

$sql = "SELECT DISTINCT G_NAME, G_ID FROM grados order by G_NAME asc";
$result = mysqli_query($db, $sql) or die ("Bad SQL: $sql");

$aaa = "<select class='form-control' name='grado' required>
        <option disabled selected hidden>Seleccionar grado</option>";
while ($row = mysqli_fetch_assoc($result)) {
    $aaa .= "<option value='".$row['G_ID']."'>".$row['G_NAME']."</option>";
}

$aaa .= "</select>";
?>
            
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Inventario&nbsp;
            <a href="#" data-toggle="modal" data-target="#vGraModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;"><i class="fas fa-fw fa-plus"></i></a>
        </h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"> 
                <thead>
                    <tr>
                        <th>ARTICULO</th>
                        <th>CANTIDAD</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = 'SELECT i_id, articulo, cantidad FROM inventario WHERE i_estado = 1';
                    $result = mysqli_query($db, $query) or die(mysqli_error($db));

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>' . $row['articulo'] . '</td>';
                        echo '<td>' . $row['cantidad'] . '</td>';
                        echo '<td align="right">
                                <div class="btn-group">
                                    <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="inventario_searchfrm.php?action=edit&id=' . $row['i_id'] . '"><i class="fas fa-fw fa-list-alt"></i> Detalles</a>
                                </div>
                                <div class="btn-group">
                                    <a type="button" class="btn btn-warning bg-gradient-warning btn-sm" href="inventario_edit.php?action=edit&id='.$row['i_id'].'"><i class="fas fa-fw fa-edit"></i> Editar</a>
                                </div> 
                                <div class="btn-group">
                                    <a type="button" class="btn btn-danger bg-gradient-danger btn-sm" href="#" onclick="confirmDelete(' . $row['i_id'] . ')"><i class="fas fa-fw fa-trash"></i> Eliminar</a>    
                                </div>
                              </td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href='https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap' rel='stylesheet'>
<style>
    .custom-popup-class, .custom-title-class, .custom-confirm-button-class, .custom-cancel-button-class {
        font-family: 'Open Sans', sans-serif;
    }
    .custom-title-class {
        font-weight: 700;
    }
    .custom-confirm-button-class, .custom-cancel-button-class {
        font-weight: 600;
    }
</style>

<script>
function confirmDelete(inventarioId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Quieres desactivar este registro de inventario?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'custom-popup-class',
            title: 'custom-title-class',
            confirmButton: 'custom-confirm-button-class',
            cancelButton: 'custom-cancel-button-class'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('inventario_delete.php?id=' + inventarioId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Desactivado!',
                            text: data.message,
                            icon: 'success',
                            customClass: {
                                popup: 'custom-popup-class',
                                title: 'custom-title-class',
                                confirmButton: 'custom-confirm-button-class'
                            }
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message,
                            icon: 'error',
                            customClass: {
                                popup: 'custom-popup-class',
                                title: 'custom-title-class',
                                confirmButton: 'custom-confirm-button-class'
                            }
                        });
                    }
                });
        }
    });
}
</script>


<?php
include '../includes/footer.php';
?>

  <!-- Inventario Modal-->
<div class="modal fade" id="vGraModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Ingresar artículo de inventario</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form role="form" method="post" action="inventario_transac.php?action=add">
          <div class="form-group">
            <input class="form-control" placeholder="Nombre del artículo" minlength="5" maxlength="55" name="articulo" required>
          </div>
          <div class="form-group">
            <input class="form-control" type="number" placeholder="Cantidad" name="cantidad" required>
          </div>

          <hr>
          <button type="submit" class="btn btn-success"><i class="fa fa-check fa-fw"></i>Guardar</button>
          <button type="reset" class="btn btn-warning"><i class="fa fa-circle-notch fa-fw"></i>Reiniciar</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times fa-fw"></i>Cancelar</button>
        </form>
      </div>
    </div>
  </div>
</div>