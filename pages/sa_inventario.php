<?php
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Personal' || $Aa == 'Estudiante' || $Aa == 'Docente' || $Aa == 'Admin') {
        if ($Aa == 'Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa == 'Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa == 'Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa == 'Admin') {
            $redirectUrl = "index.php";
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

$sql = "SELECT i_id, articulo, cantidad, i_estado FROM inventario";
$result = mysqli_query($db, $sql) or die(mysqli_error($db));
?>

<div class="container-fluid">

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
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<tr>';
                            echo '<td>' . $row['articulo'] . '</td>';
                            echo '<td>' . $row['cantidad'] . '</td>';
                            echo '<td>';
                            if ($row['i_estado'] == 1) {
                                echo '<span class="badge badge-success">Activo</span>';
                            } elseif ($row['i_estado'] == 0) {
                                echo '<span class="badge badge-danger">Inactivo</span>';
                            }
                            echo '</td>';
                            echo '<td align="right">
                                    <div class="btn-group">
                                        <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="sa_inventario_searchfrm.php?action=edit&id=' . $row['i_id'] . '"><i class="fas fa-fw fa-list-alt"></i> Detalles</a>
                                    </div>
                                    <div class="btn-group">
                                        <a type="button" class="btn btn-warning bg-gradient-warning btn-sm" href="sa_inventario_edit.php?action=edit&id='.$row['i_id'].'"><i class="fas fa-fw fa-edit"></i> Editar</a>
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
</div>

<!-- <script>
function confirmDelete(inventarioId) {
    if (confirm("¿Estás seguro de que quieres eliminar este registro?")) {
        window.location.href = 'sa_inventario_delete.php?id=' + inventarioId;
    }
}
</script> -->

<!-- Sección de script para confirmación de eliminación -->

<link href='https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap' rel='stylesheet'>  <!-- Fuente opens sans -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- libreria online para las alertas -->
    <script>
    function confirmDelete(inventarioId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas eliminar el artículo del inventario?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'custom-popup-class',
                title: 'custom-title-class',
                confirmButton: 'custom-confirm-button-class',
                cancelButton: 'custom-cancel-button-class'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Si el usuario confirma, realizamos la eliminación
                fetch('sa_inventario_delete.php?id=' + inventarioId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: 'El artículo ha sido eliminado del inventario correctamente.',
                            icon: 'success',
                            customClass: {
                                popup: 'custom-popup-class',
                                title: 'custom-title-class',
                                confirmButton: 'custom-confirm-button-class'
                            }
                        }).then(() => {
                            // Recargamos la página o actualizamos la tabla
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'No se pudo eliminar el artículo del inventario',
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

<?php
include '../includes/footer_superadmin.php';
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
        <form role="form" method="post" action="sa_inventario_transac.php?action=add">
          <div class="form-group">
            <input class="form-control" placeholder="Nombre del artículo" minlength="5" maxlength="55" name="articulo" required>
          </div>
          <div class="form-group">
            <input class="form-control" type="number" placeholder="Cantidad" name="cantidad" required>
          </div>
          <div class="form-group">
            <select class="form-control" name="estado" required>
              <option disabled selected hidden>Selecciona el estado del artículo</option>
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
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
