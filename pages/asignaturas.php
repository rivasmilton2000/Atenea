<?php
include '../includes/connection.php';
include '../includes/sidebar_admin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa=='Personal' || $Aa=='Estudiante' || $Aa=='Docente' || $Aa=='SuperAdmin'){
        if ($Aa=='Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa=='Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa=='Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa=='SuperAdmin') {
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

$sql = "SELECT DISTINCT A_NAME, ASIGNATURA_ID FROM asignaturas WHERE A_ESTADO = 1 ORDER BY A_NAME ASC"; // Modificación para seleccionar solo registros con A_ESTADO = 1
$result = mysqli_query($db, $sql) or die ("Bad SQL: $sql");

$aaa = "<select class='form-control' name='asignatura' required>
        <option disabled selected hidden>Seleccionar asignatura</option>";
while ($row = mysqli_fetch_assoc($result)) {
    $aaa .= "<option value='".$row['ASIGNATURA_ID']."'>".$row['A_NAME']."</option>";
}

$aaa .= "</select>";
?>
            
            <div class="card shadow mb-4">
            <div class="card-header py-3">
              <h4 class="m-2 font-weight-bold text-primary">Asignaturas&nbsp;
                <a  href="#" data-toggle="modal" data-target="#vAsiModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;"><i class="fas fa-fw fa-plus"></i></a></h4>
            </div>

            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"> 
               <thead>
                   <tr>
                     <th>ID</th>
                     <th>ASIGNATURA</th>
                     <th>ACCIONES</th>
                   </tr>
               </thead>
          <tbody>

          <?php
$query = 'SELECT ASIGNATURA_ID, A_NAME FROM asignaturas WHERE A_ESTADO = 1 GROUP BY ASIGNATURA_ID'; // Modificación para seleccionar solo registros con A_ESTADO = 1
$result = mysqli_query($db, $query) or die(mysqli_error($db));

while ($row = mysqli_fetch_assoc($result)) {
    echo '<tr>';
    echo '<td>' . $row['ASIGNATURA_ID'] . '</td>';
    echo '<td>' . $row['A_NAME'] . '</td>';
    echo '<td align="right">
            <div class="btn-group">
                <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="asignaturas_searchfrm.php?action=edit&id=' . $row['ASIGNATURA_ID'] . '">
                <i class="fas fa-fw fa-list-alt"></i> Detalles
                </a>
                </div>
                <div class="btn-group">
                    <a type="button" class="btn btn-danger bg-gradient-danger btn-sm" href="#" onclick="confirmDelete(' . $row['ASIGNATURA_ID'] . ')"><i class="fas fa-fw fa-trash"></i> Eliminar</a>
                </div>
            </div>
        </td>';
        echo '</tr>';
      }
      ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
function confirmDelete(asignaturaId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Deseas eliminar esta asignatura?",
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
            fetch('asignaturas_delete.php?id=' + asignaturaId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Eliminado',
                            text: 'La asignatura ha sido eliminado.',
                            icon: 'success',
                            customClass: { 
                                popup: 'custom-popup-class',
                                title: 'custom-title-class',
                                confirmButton: 'custom-confirm-button-class'
                            }
                        }).then(() => {
                            window.location.href = 'asignaturas.php';
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'Hubo un problema al eliminar el registro.',
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
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                  </div>

<?php
include '../includes/footer.php';
?>

  <!-- Product Modal-->
  <div class="modal fade" id="vAsiModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Ingresar asignatura</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <form role="form" method="post" action="asignaturas_transac.php?action=add">
           <div class="form-group">
             <input class="form-control" placeholder="Nombre de la asignatura" minlength="3" maxlength="30" name="asignatura" required>
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