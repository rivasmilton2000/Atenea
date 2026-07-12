<?php
include '../includes/connection.php';
include '../includes/sidebar_estudiante.php';
require_once '../includes/material_dashboard.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'];
$result = mysqli_query($db, $query) or die(mysqli_error($db));

while ($row = mysqli_fetch_assoc($result)) {
    $userType = $row['TYPE'];
    if ($userType == 'Admin' || $userType == 'Docente' || $userType == 'Personal' || $userType == 'SuperAdmin') {
        if ($userType == 'Admin') {
            $redirectUrl = 'index.php';
        } elseif ($userType == 'Docente') {
            $redirectUrl = 'docentes_vista.php';
        } elseif ($userType == 'Personal') {
            $redirectUrl = 'empleados_vista.php';
        } elseif ($userType == 'SuperAdmin') {
            $redirectUrl = 'sa_vista.php';
        }
        echo '<script type="text/javascript">
                alert("Página restringida! Será redirigido.");
                window.location = "' . $redirectUrl . '";
              </script>';
        exit();
    }
}

if (isset($_SESSION['nombres_estudiante'], $_SESSION['apellidos_estudiante'])) {
    $nombres = $_SESSION['nombres_estudiante'];
    $apellidos = $_SESSION['apellidos_estudiante'];
    $direccion = $_SESSION['direccion_estudiante'];
    $correo = $_SESSION['correo_estudiante'];
    $foto = $_SESSION['foto_estudiante'];
    $fecha_nac = $_SESSION['fecha_nac_estudiante'];
    $edad = $_SESSION['edad_estudiante'];
    $genero = $_SESSION['genero_estudiante'];
    $grado_id = $_SESSION['grado_id_estudiante'];
    $carnet = $_SESSION['carnet_estudiante'];
    $numero_lista = $_SESSION['numero_lista_estudiante'];
    $info_medica = $_SESSION['info_medica_estudiante'];

    $queryEstudiante = "SELECT ESTUDIANTE_ID FROM estudiantes WHERE nombres_estudiante = '{$_SESSION['nombres_estudiante']}' AND apellidos_estudiante = '{$_SESSION['apellidos_estudiante']}'";
    $resultEstudiante = mysqli_query($db, $queryEstudiante);

    if (mysqli_num_rows($resultEstudiante) > 0) {
        $rowEstudiante = mysqli_fetch_assoc($resultEstudiante);
        $estudianteId = $rowEstudiante['ESTUDIANTE_ID'];
    } else {
        echo "Error: No se encontró el estudiante en la base de datos.";
        exit();
    }

    $query = "SELECT USERNAME FROM users WHERE ESTUDIANTE_ID = '$estudianteId'";
    $result = mysqli_query($db, $query) or die(mysqli_error($db));
    $row = mysqli_fetch_assoc($result);
    $username = $row['USERNAME'];

    $query = "SELECT G_NAME FROM grados WHERE G_ID = '$grado_id'";
    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_assoc($result);
    $grado_nombre = $row['G_NAME'];
} else {
    echo "No se encontró información del estudiante en la sesión.";
    exit;
}

$fullName = trim((string) ($nombres . ' ' . $apellidos));
$initials = strtoupper(substr(($nombres ?? ''), 0, 1) . substr(($apellidos ?? ''), 0, 1));
$accountType = 'Estudiante';
$accountStatusLabel = 'Activo';
$roleLabel = 'Estudiante';
?>

<div class="container-fluid py-4">
    <div class="atenea-profile-shell">
        <div class="d-flex justify-content-end mb-3">
            <a href="estudiante_vista.php" class="btn btn-outline-dark btn-sm">
                <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
            </a>
        </div>

        <section class="atenea-profile-hero profile-hero" aria-label="Resumen del perfil del estudiante">
            <div class="atenea-profile-hero__avatar" aria-hidden="true">
                <?php if (!empty($foto) && file_exists(__DIR__ . '/imagenes_estudiantes/' . $foto)): ?>
                    <img src="imagenes_estudiantes/<?php echo dashboard_h($foto); ?>" alt="Foto de perfil del estudiante">
                <?php else: ?>
                    <span><?php echo dashboard_h($initials ?: 'ES'); ?></span>
                <?php endif; ?>
            </div>

            <div class="atenea-profile-hero__main profile-info">
                <div class="atenea-profile-hero__identity profile-identity">
                    <p class="atenea-profile-hero__kicker">Perfil de estudiante</p>
                    <h3 class="atenea-profile-hero__name"><?php echo dashboard_h($fullName !== '' ? $fullName : 'Pendiente de completar'); ?></h3>
                    <p class="atenea-profile-hero__username">@<?php echo dashboard_h($username ?: 'no-disponible'); ?></p>
                    <p class="atenea-profile-hero__email">
                        <span class="material-symbols-rounded" aria-hidden="true">mail</span>
                        <span><?php echo dashboard_h($correo ?: 'No disponible'); ?></span>
                    </p>
                    <div class="atenea-profile-badges">
                        <span class="badge bg-gradient-success"><?php echo dashboard_h($roleLabel); ?></span>
                        <span class="badge bg-gradient-dark"><?php echo dashboard_h($accountStatusLabel); ?></span>
                    </div>
                </div>
            </div>

            <div class="atenea-profile-hero__aside profile-side-cards" aria-label="Detalles del estudiante">
                <div class="atenea-profile-hero__status profile-side-card">
                    <div class="atenea-profile-side-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">verified_user</span>
                    </div>
                    <div class="atenea-profile-side-card__content">
                        <span>Estado de la cuenta</span>
                        <strong><?php echo dashboard_h($accountStatusLabel); ?></strong>
                        <small>El acceso del estudiante está habilitado para utilizar Atenea.</small>
                    </div>
                </div>
                <div class="atenea-profile-hero__meta profile-side-card">
                    <div class="atenea-profile-side-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">school</span>
                    </div>
                    <div class="atenea-profile-side-card__content">
                        <span>Grado</span>
                        <strong><?php echo dashboard_h($grado_nombre ?: 'No especificado'); ?></strong>
                        <small>Información académica registrada.</small>
                    </div>
                </div>
                <div class="atenea-profile-hero__meta profile-side-card">
                    <div class="atenea-profile-side-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">pin</span>
                    </div>
                    <div class="atenea-profile-side-card__content">
                        <span>Carnet / lista</span>
                        <strong><?php echo dashboard_h(($carnet ?: 'No disponible') . ($carnet && $numero_lista ? ' · ' : '') . ($numero_lista ?: '')); ?></strong>
                        <small>Datos de identificación del estudiante.</small>
                    </div>
                </div>
            </div>
        </section>

        <section class="atenea-profile-section atenea-profile-section--readonly">
            <div class="atenea-profile-section__header">
                <h5>Información personal</h5>
                <p>Consulta los datos de contacto, académicos y de salud del estudiante registrados en Atenea.</p>
            </div>
            <div class="profile-info-grid">
                <article class="profile-info-item profile-info-item--wide atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">badge</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                        <span>Nombre completo</span>
                        <strong><?php echo dashboard_h($fullName !== '' ? $fullName : 'Pendiente de completar'); ?></strong>
                    </div>
                </article>
                <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">alternate_email</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                        <span>Nombre de usuario</span>
                        <strong><?php echo dashboard_h($username ?: 'No disponible'); ?></strong>
                    </div>
                </article>
                <article class="profile-info-item profile-info-item--wide atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">mail</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                        <span>Correo electrónico</span>
                        <strong><?php echo dashboard_h($correo ?: 'No disponible'); ?></strong>
                    </div>
                </article>
                <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">location_on</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                        <span>Dirección</span>
                        <strong><?php echo dashboard_h($direccion ?: 'No especificada'); ?></strong>
                    </div>
                </article>
                <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">cake</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                        <span>Fecha de nacimiento</span>
                        <strong><?php echo dashboard_h($fecha_nac ? date('d-m-Y', strtotime($fecha_nac)) : 'No disponible'); ?></strong>
                    </div>
                </article>
                <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">person</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                        <span>Género</span>
                        <strong><?php echo dashboard_h($genero ?: 'No especificado'); ?></strong>
                    </div>
                </article>
                <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">monitor_heart</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                        <span>Información médica</span>
                        <strong><?php echo dashboard_h($info_medica ?: 'No especificada'); ?></strong>
                    </div>
                </article>
            </div>
        </section>

        <section class="atenea-profile-section atenea-profile-section--readonly">
            <div class="atenea-profile-section__header">
                <h5>Información de la cuenta</h5>
                <p>Detalle del acceso del usuario dentro de la plataforma Atenea.</p>
            </div>
            <div class="profile-info-grid">
                <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">account_circle</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                        <span>Usuario</span>
                        <strong><?php echo dashboard_h($username ?: 'No disponible'); ?></strong>
                    </div>
                </article>
                <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">admin_panel_settings</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                        <span>Tipo de cuenta</span>
                        <strong><?php echo dashboard_h($accountType); ?></strong>
                    </div>
                </article>
            </div>
        </section>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
