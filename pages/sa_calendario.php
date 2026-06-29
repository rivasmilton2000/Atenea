<?php
// Sección de inclusión de archivos
include '../includes/connection.php';
include '../includes/sidebar_superadmin.php';

// Sección de verificación de permisos de usuario
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa=='Personal' || $Aa=='Estudiante' || $Aa=='Docente' || $Aa=='Admin'){
        if ($Aa=='Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa=='Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa=='Docente') {
            $redirectUrl = "docentes_vista.php";
        } elseif ($Aa=='Admin') {
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

?>

<!-- Sección de visualización del calendario de actividades -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Calendario de actividades&nbsp;
            <a href="#" data-toggle="modal" data-target="#addActividadModal" type="button" class="btn btn-primary bg-gradient-primary" style="border-radius: 0px;"><i class="fas fa-fw fa-plus"></i></a>
        </h4>
    </div>

    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

<!-- Modal para agregar actividad -->
<div class="modal fade" id="addActividadModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Agregar nueva actividad</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" method="post" action="sa_calendario_transac.php?action=add">
                    <div class="form-group">
                        <input class="form-control" minlength="5" maxlength="110" placeholder="Nombre de la actividad" name="act_nombre" required>
                    </div>
                    <div class="form-group">
                        <input type="datetime-local" class="form-control" name="act_fecha_inicio" required>
                    </div>
                    <div class="form-group">
                        <input type="datetime-local" class="form-control" name="act_fecha_fin" required>
                    </div>
                    <div class="form-group">
                        <input type="color" class="form-control" name="act_color" required>
                    </div>
                    <div class="form-group">
                        <select class="form-control" name="act_estado" required>
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

<?php
include '../includes/footer_superadmin.php';
?>

<!-- Asegúrate de que jQuery esté cargado antes de estos scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.33/moment-timezone-with-data.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/es.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css' rel='stylesheet' />

<style>
.fc-event {
    font-size: 14px !important;
    color: white !important;
    font-weight: bold;
    padding: 3px 5px !important;
}

.fc-event-title {
    white-space: normal !important;
    overflow: visible !important;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.7);
}

.fc-daygrid-event {
    white-space: normal !important;
}
</style>

<script>
$(document).ready(function() {
    moment.tz.setDefault("America/El_Salvador");
    
    $('#calendar').fullCalendar({
        locale: 'es',
        timeZone: 'America/El_Salvador',
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día'
        },
        events: 'sa_calendario_get.php',
        eventRender: function(event, element) {
            element.find('.fc-title').html(element.find('.fc-title').text());
        },
        displayEventTime: false,
        eventLimit: false,
        eventClick: function(event, jsEvent, view) {
            Swal.fire({
                title: event.title,
                html: `
                    <p>Inicio: ${moment(event.start).format('LLLL')}</p>
                    <p>Fin: ${moment(event.end).format('LLLL')}</p>
                    <p>Estado: ${event.estado ? 'Activo' : 'Inactivo'}</p>
                `,
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: 'Editar',
                denyButtonText: 'Eliminar',
                cancelButtonText: 'Cerrar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `sa_calendario_edit.php?id=${event.id}`;
                } else if (result.isDenied) {
                    confirmDelete(event.id);
                }
            });
        }
    });
});

function confirmDelete(actividadId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¿Deseas eliminar esta actividad?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('sa_calendario_delete.php?id=' + actividadId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Eliminada', 'La actividad ha sido eliminada correctamente.', 'success');
                    $('#calendar').fullCalendar('refetchEvents');
                } else {
                    Swal.fire('Error', 'No se pudo eliminar la actividad.', 'error');
                }
            });
        }
    });
}
$(document).ready(function() {
    $('form').on('submit', function(e) {
        e.preventDefault(); // Evita el envío del formulario

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success === false) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                } else if (response.success === null) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Advertencia',
                        text: response.message,
                        showCancelButton: true,
                        confirmButtonText: 'Sí, añadir',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Si el usuario confirma, reenviar la solicitud con ignoreWarning
                            $.ajax({
                                url: 'sa_calendario_transac.php?action=add',
                                method: 'POST',
                                data: $.extend({}, response.data, { ignoreWarning: true }),
                                dataType: 'json',
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Éxito',
                                            text: response.message
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: response.message
                                        });
                                    }
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'info',
                                title: 'Cancelado',
                                text: 'Cancelaste añadir esta actividad.'
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message
                    }).then(() => {
                        window.location.reload();
                    });
                }
            }
        });
    });
});

</script>