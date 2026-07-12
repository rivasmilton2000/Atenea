<?php
include '../includes/connection.php';
include '../includes/sidebar_estudiante.php';

// Verificación de permisos de usuario
$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa=='Admin' || $Aa=='Docente' || $Aa=='Personal' || $Aa=='SuperAdmin'){
        if ($Aa=='Admin') {
            $redirectUrl = "index.php";
        } elseif ($Aa=='Personal') {
            $redirectUrl = "empleados_vista.php";
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
  ?>

<!-- Sección de visualización del calendario de actividades -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Calendario de actividades</h4>
    </div>

    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>

<!-- Scripts y estilos -->
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
        events: 'estudiantes_vista_calendario_get.php',
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
                `,
                confirmButtonText: 'Cerrar'
            });
        }
    });
});
</script>