<?php
include '../includes/connection.php';
include '../includes/sidebar_personal.php';
require_once '../includes/material_dashboard.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = '.$_SESSION['MEMBER_ID'].'';
$result = mysqli_query($db, $query) or die (mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa=='Admin' || $Aa=='Estudiante' || $Aa=='Docente' || $Aa=='SuperAdmin'){
        if ($Aa=='Admin') {
            $redirectUrl = "index.php";
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

if (isset($_SESSION['MEMBER_ID']) && !empty($_SESSION['MEMBER_ID'])) {
    $id = (int) $_SESSION['MEMBER_ID'];

    $query = "SELECT u.ID, e.FIRST_NAME, e.LAST_NAME, e.GENDER, u.USERNAME, e.EMAIL, e.PHONE_NUMBER, j.JOB_TITLE, e.HIRED_DATE, t.TYPE, l.PROVINCE, l.CITY
              FROM users u
              JOIN employee e ON u.EMPLOYEE_ID = e.EMPLOYEE_ID
              JOIN job j ON e.JOB_ID = j.JOB_ID
              JOIN location l ON e.LOCATION_ID = l.LOCATION_ID
              JOIN type t ON u.TYPE_ID = t.TYPE_ID
              WHERE u.ID = '$id'";

    $result = mysqli_query($db, $query) or die(mysqli_error($db));

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $a = $row['FIRST_NAME'];
        $b = $row['LAST_NAME'];
        $c = $row['GENDER'];
        $d = $row['USERNAME'];
        $f = $row['EMAIL'];
        $g = $row['PHONE_NUMBER'];
        $h = $row['JOB_TITLE'];
        $i = $row['HIRED_DATE'];
        $j = $row['PROVINCE'];
        $k = $row['CITY'];
        $l = $row['TYPE'];
    } else {
        echo "No se encontró información del usuario.";
        exit;
    }
} else {
    echo "Identificador de usuario inválido.";
    exit;
}

$fullName = trim((string) ($a . ' ' . $b));
$initials = strtoupper(substr(($a ?? ''), 0, 1) . substr(($b ?? ''), 0, 1));
$accountType = 'Personal';
$accountStatusLabel = 'Activo';
$roleLabel = 'Personal';
?>

<div class="container-fluid py-4">
    <div class="atenea-profile-shell">
        <div class="d-flex justify-content-end mb-3">
            <a href="empleados_vista.php" class="btn btn-outline-dark btn-sm">
                <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
            </a>
        </div>

        <section class="atenea-profile-hero profile-hero" aria-label="Resumen del perfil personal">
            <div class="atenea-profile-hero__avatar" aria-hidden="true">
                <span><?php echo dashboard_h($initials ?: 'PE'); ?></span>
            </div>

            <div class="atenea-profile-hero__main profile-info">
                <div class="atenea-profile-hero__identity profile-identity">
                    <p class="atenea-profile-hero__kicker">Perfil de personal</p>
                    <h3 class="atenea-profile-hero__name"><?php echo dashboard_h($fullName !== '' ? $fullName : 'Pendiente de completar'); ?></h3>
                    <p class="atenea-profile-hero__username">@<?php echo dashboard_h($d ?: 'no-disponible'); ?></p>
                    <p class="atenea-profile-hero__email">
                        <span class="material-symbols-rounded" aria-hidden="true">mail</span>
                        <span><?php echo dashboard_h($f ?: 'No disponible'); ?></span>
                    </p>
                    <div class="atenea-profile-badges">
                        <span class="badge bg-gradient-success"><?php echo dashboard_h($roleLabel); ?></span>
                        <span class="badge bg-gradient-dark"><?php echo dashboard_h($accountStatusLabel); ?></span>
                    </div>
                </div>
            </div>

            <div class="atenea-profile-hero__aside profile-side-cards" aria-label="Detalles del personal">
                <div class="atenea-profile-hero__status profile-side-card">
                    <div class="atenea-profile-side-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">verified_user</span>
                    </div>
                    <div class="atenea-profile-side-card__content">
                        <span>Estado de la cuenta</span>
                        <strong><?php echo dashboard_h($accountStatusLabel); ?></strong>
                        <small>El acceso del personal está habilitado para utilizar Atenea.</small>
                    </div>
                </div>
                <div class="atenea-profile-hero__meta profile-side-card">
                    <div class="atenea-profile-side-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">work</span>
                    </div>
                    <div class="atenea-profile-side-card__content">
                        <span>Rol de trabajo</span>
                        <strong><?php echo dashboard_h($h ?: 'No especificado'); ?></strong>
                        <small>Función asignada en la institución.</small>
                    </div>
                </div>
                <div class="atenea-profile-hero__meta profile-side-card">
                    <div class="atenea-profile-side-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">calendar_month</span>
                    </div>
                    <div class="atenea-profile-side-card__content">
                        <span>Fecha de contratación</span>
                        <strong><?php echo dashboard_h($i ? date('d-m-Y', strtotime($i)) : 'No disponible'); ?></strong>
                        <small>Información registrada en el sistema.</small>
                    </div>
                </div>
            </div>
        </section>

        <section class="atenea-profile-section atenea-profile-section--readonly">
            <div class="atenea-profile-section__header">
                <h5>Información personal</h5>
                <p>Consulta tus datos principales y el detalle de contacto registrado en Atenea.</p>
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
                        <strong><?php echo dashboard_h($d ?: 'No disponible'); ?></strong>
                    </div>
                </article>
                <article class="profile-info-item profile-info-item--wide atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">mail</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                        <span>Correo electrónico</span>
                        <strong><?php echo dashboard_h($f ?: 'No disponible'); ?></strong>
                    </div>
                </article>
                <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">call</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                        <span>Número telefónico</span>
                        <strong><?php echo dashboard_h($g ?: 'No especificado'); ?></strong>
                    </div>
                </article>
                <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">location_on</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                        <span>Dirección</span>
                        <strong><?php echo dashboard_h(($k ?: 'No disponible') . ($k && $j ? ', ' : '') . ($j ?: '')); ?></strong>
                    </div>
                </article>
                <article class="profile-info-item atenea-profile-info-card">
                    <div class="atenea-profile-info-card__icon" aria-hidden="true">
                        <span class="material-symbols-rounded">person</span>
                    </div>
                    <div class="atenea-profile-info-card__body">
                        <span>Género</span>
                        <strong><?php echo dashboard_h($c ?: 'No especificado'); ?></strong>
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
                        <strong><?php echo dashboard_h($d ?: 'No disponible'); ?></strong>
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