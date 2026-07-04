<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
include '../includes/sidebar_docente.php';

if ((string) ($_SESSION['TYPE'] ?? '') !== 'Docente') {
    atenea_render_auth_alert('warning', 'Acceso restringido', 'Esta pagina solo esta disponible para docentes.', atenea_dashboard_route_for_session());
}

if (!function_exists('mensajes_lista_h')) {
    function mensajes_lista_h($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$employeeId = (int) ($_SESSION['EMPLOYEE_ID'] ?? 0);
if ($employeeId <= 0) {
    $memberId = (int) ($_SESSION['MEMBER_ID'] ?? 0);
    $stmtEmployee = $db->prepare('SELECT EMPLOYEE_ID FROM users WHERE ID = ? LIMIT 1');
    if (!$stmtEmployee) {
        atenea_render_auth_alert('error', 'No disponible', 'No se pudo validar tu docente asociado.', 'docentes_vista.php');
    }
    $stmtEmployee->bind_param('i', $memberId);
    $stmtEmployee->execute();
    $employeeResult = $stmtEmployee->get_result();
    $employeeRow = $employeeResult instanceof mysqli_result ? $employeeResult->fetch_assoc() : null;
    if ($employeeResult instanceof mysqli_result) {
        mysqli_free_result($employeeResult);
    }
    $stmtEmployee->close();
    $employeeId = (int) ($employeeRow['EMPLOYEE_ID'] ?? 0);
}

$stmt = $db->prepare(
    "SELECT da.da_id, g.G_NAME, a.A_NAME, a.ASIGNATURA_ID, p.p_name,
            COUNT(m.mensaje_id) AS total_mensajes,
            MAX(m.fecha) AS ultimo_mensaje
     FROM docentes_asignaturas da
     JOIN grados g ON da.grado_id = g.G_ID
     JOIN asignaturas a ON da.materia_id = a.ASIGNATURA_ID
     JOIN periodo p ON da.periodo_id = p.p_id
     LEFT JOIN mensajes m ON m.asignatura_id = a.ASIGNATURA_ID AND m.estado = 1
     WHERE da.profesor_id = ? AND da.da_estado = 1
     GROUP BY da.da_id, g.G_NAME, a.A_NAME, a.ASIGNATURA_ID, p.p_name
     ORDER BY g.G_ID, p.p_id, a.A_NAME"
);
if (!$stmt) {
    atenea_render_auth_alert('error', 'No disponible', 'No se pudieron cargar tus asignaturas.', 'docentes_vista.php');
}
$stmt->bind_param('i', $employeeId);
$stmt->execute();
$result = $stmt->get_result();
$subjects = [];
while ($result instanceof mysqli_result && ($row = $result->fetch_assoc())) {
    $subjects[] = $row;
}
if ($result instanceof mysqli_result) {
    mysqli_free_result($result);
}
$stmt->close();
?>

<div class="card shadow border-0 mb-4 mensajes-list-shell">
  <div class="card-header bg-white border-0 py-3">
    <p class="text-uppercase text-muted font-weight-bold mb-1">Mensajes</p>
    <h4 class="mb-0">Anuncios por materia</h4>
  </div>
  <div class="card-body">
    <?php if ($subjects === []): ?>
      <div class="mensajes-list-empty">
        <h5>No tienes asignaturas activas</h5>
        <p class="mb-0">Cuando se te asignen materias, apareceran aqui para publicar anuncios.</p>
      </div>
    <?php else: ?>
      <div class="row">
        <?php foreach ($subjects as $subject): ?>
          <div class="col-xl-4 col-md-6 mb-4">
            <div class="mensaje-subject-card">
              <div class="mensaje-subject-card__media">
                <img src="img/libros.jpg" alt="Materia">
              </div>
              <div class="mensaje-subject-card__body">
                <span class="mensaje-subject-card__eyebrow"><?php echo mensajes_lista_h($subject['G_NAME'] . ' - ' . $subject['p_name']); ?></span>
                <h5><?php echo mensajes_lista_h($subject['A_NAME']); ?></h5>
                <div class="mensaje-subject-card__meta">
                  <span><?php echo (int) $subject['total_mensajes']; ?> mensajes activos</span>
                  <span><?php echo mensajes_lista_h($subject['ultimo_mensaje'] ?: 'Sin mensajes'); ?></span>
                </div>
                <a href="mensajes_docente.php?asignatura_id=<?php echo (int) $subject['ASIGNATURA_ID']; ?>" class="btn btn-primary btn-block">Entrar a anuncios</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
.mensajes-list-shell .card-header h4 { color: #1f2937; }
.mensajes-list-empty { text-align: center; padding: 42px 16px; color: #6b7280; border: 1px dashed #cbd5e1; border-radius: 12px; background: #f8fafc; }
.mensaje-subject-card { height: 100%; overflow: hidden; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; box-shadow: 0 10px 24px rgba(15, 23, 42, .08); display: flex; flex-direction: column; }
.mensaje-subject-card__media { height: 150px; background: #e5e7eb; overflow: hidden; }
.mensaje-subject-card__media img { width: 100%; height: 100%; object-fit: cover; display: block; }
.mensaje-subject-card__body { padding: 16px; display: flex; flex: 1; flex-direction: column; }
.mensaje-subject-card__eyebrow { color: #64748b; font-size: .78rem; font-weight: 800; text-transform: uppercase; margin-bottom: 6px; }
.mensaje-subject-card h5 { color: #111827; margin-bottom: 12px; }
.mensaje-subject-card__meta { display: grid; gap: 6px; color: #64748b; font-size: .88rem; margin-bottom: 16px; }
.mensaje-subject-card .btn { margin-top: auto; }
</style>

<?php include '../includes/footer.php'; ?>
