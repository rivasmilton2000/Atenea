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
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
        <?php
        exit();
    }
}
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Productos&nbsp;
            <a href="productos_add.php" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;">
                <i class="fas fa-fw fa-plus"></i> Agregar Producto
            </a>
            <a href="categorias_productos.php" type="button" class="btn btn-secondary bg-gradient-secondary" style="border-radius: 0px;">
                <i class="fas fa-fw fa-list"></i> Categorias
            </a>
        </h4>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"> 
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>IMAGEN</th>
                        <th>NOMBRE</th>
                        <th>CATEGORIA</th>
                        <th>PRECIO</th>
                        <th>STOCK</th>
                        <th>DESTACADO</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = 'SELECT p.*, c.nombre as categoria_nombre 
                              FROM productos p 
                              LEFT JOIN categorias_productos c ON p.categoria_id = c.id 
                              ORDER BY p.id DESC';
                    $result = mysqli_query($db, $query) or die(mysqli_error($db));

                    while ($row = mysqli_fetch_assoc($result)) {
                        $estadoClass = $row['estado'] == 1 ? 'badge-success' : 'badge-danger';
                        $estadoText = $row['estado'] == 1 ? 'Activo' : 'Inactivo';
                        $destacadoClass = $row['destacado'] == 1 ? 'badge-warning' : 'badge-secondary';
                        $destacadoText = $row['destacado'] == 1 ? 'Si' : 'No';
                        
                        $precio_mostrar = $row['precio_descuento'] ? $row['precio_descuento'] : $row['precio'];
                        
                        echo '<tr>';
                        echo '<td>' . $row['id'] . '</td>';
                        echo '<td><img src="../img/' . $row['imagen'] . '" alt="' . $row['nombre'] . '" style="width: 60px; height: 60px; object-fit: cover;" class="rounded"></td>';
                        echo '<td>' . $row['nombre'] . '</td>';
                        echo '<td><span class="badge badge-info">' . $row['categoria_nombre'] . '</span></td>';
                        echo '<td>';
                        if ($row['precio_descuento']) {
                            echo '<strong>$' . number_format($precio_mostrar, 2) . '</strong><br>';
                            echo '<small class="text-muted"><del>$' . number_format($row['precio'], 2) . '</del></small>';
                        } else {
                            echo '$' . number_format($precio_mostrar, 2);
                        }
                        echo '</td>';
                        echo '<td>' . $row['stock'] . '</td>';
                        echo '<td><span class="badge ' . $destacadoClass . '">' . $destacadoText . '</span></td>';
                        echo '<td><span class="badge ' . $estadoClass . '">' . $estadoText . '</span></td>';
                        echo '<td align="right">
                                <div class="btn-group">
                                    <a type="button" class="btn btn-primary bg-gradient-primary btn-sm" href="productos_edit.php?id=' . $row['id'] . '">
                                        <i class="fas fa-fw fa-edit"></i> Editar
                                    </a>
                                </div>
                                <div class="btn-group">
                                    <a type="button" class="btn btn-danger bg-gradient-danger btn-sm" href="#" onclick="confirmDelete(' . $row['id'] . ')">
                                        <i class="fas fa-fw fa-trash"></i> Eliminar
                                    </a>
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

<!-- SweetAlert2 -->
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
function confirmDelete(productoId) {
    Swal.fire({
        title: '¿Estas seguro?',
        text: "¿Deseas eliminar este producto?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'custom-popup-class',
            title: 'custom-title-class',
            confirmButton: 'custom-confirm-button-class',
            cancelButton: 'custom-cancel-button-class'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('productos_delete.php?id=' + productoId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Eliminado',
                            text: 'El producto ha sido eliminado.',
                            icon: 'success',
                            customClass: { 
                                popup: 'custom-popup-class',
                                title: 'custom-title-class',
                                confirmButton: 'custom-confirm-button-class'
                            }
                        }).then(() => {
                            window.location.href = 'productos_admin.php';
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'Hubo un problema al eliminar el producto.',
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

<?php include '../includes/footer.php'; ?>