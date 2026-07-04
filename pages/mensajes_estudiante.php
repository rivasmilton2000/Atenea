<?php
require_once __DIR__ . '/session.php';
confirm_logged_in();

require_once __DIR__ . '/../includes/connection.php';
include '../includes/sidebar_estudiante.php';

if ((string) ($_SESSION['TYPE'] ?? '') !== 'Estudiante') {
    atenea_render_auth_alert('warning', 'Acceso restringido', 'Esta pagina solo esta disponible para estudiantes.', atenea_dashboard_route_for_session());
}

if (!function_exists('mensajes_est_h')) {
    function mensajes_est_h($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('mensajes_est_linkify')) {
    function mensajes_est_linkify(string $message): string
    {
        $escaped = mensajes_est_h($message);

        return preg_replace_callback(
            '~https?://[^\s<]+~i',
            static function (array $matches): string {
                $url = mensajes_est_h($matches[0]);

                return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">Abrir enlace</a>';
            },
            $escaped
        ) ?? $escaped;
    }
}

if (!function_exists('mensajes_est_youtube_id')) {
    function mensajes_est_youtube_id(string $message): string
    {
        if (!preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})~', $message, $match)) {
            return '';
        }

        return $match[1];
    }
}

$asignaturaId = filter_input(INPUT_GET, 'asignatura_id', FILTER_VALIDATE_INT);
if (!$asignaturaId) {
    atenea_render_auth_alert('warning', 'Asignatura no valida', 'No se recibio una asignatura valida.', 'mensajes_estudiante_lista.php');
}

$studentId = (int) ($_SESSION['ESTUDIANTE_ID'] ?? 0);
$stmtSubject = $db->prepare(
    "SELECT a.ASIGNATURA_ID, a.A_NAME, g.G_NAME, p.p_name
     FROM estudiantes_docentes ed
     JOIN docentes_asignaturas da ON da.da_id = ed.doc_asi_id
     JOIN asignaturas a ON a.ASIGNATURA_ID = da.materia_id
     JOIN grados g ON g.G_ID = da.grado_id
     JOIN periodo p ON p.p_id = da.periodo_id
     WHERE ed.estudiante_id = ? AND da.materia_id = ? AND ed.ed_estado = 1 AND da.da_estado = 1
     LIMIT 1"
);
if (!$stmtSubject) {
    atenea_render_auth_alert('error', 'No disponible', 'No se pudo validar la asignatura.', 'mensajes_estudiante_lista.php');
}
$stmtSubject->bind_param('ii', $studentId, $asignaturaId);
$stmtSubject->execute();
$subjectResult = $stmtSubject->get_result();
$subject = $subjectResult instanceof mysqli_result ? $subjectResult->fetch_assoc() : null;
if ($subjectResult instanceof mysqli_result) {
    mysqli_free_result($subjectResult);
}
$stmtSubject->close();

if (!$subject) {
    atenea_render_auth_alert('warning', 'Asignatura restringida', 'No tienes esta asignatura asignada.', 'mensajes_estudiante_lista.php');
}

$stmt = $db->prepare(
    "SELECT m.mensaje, m.fecha, m.archivo, u.USERNAME
     FROM mensajes m
     JOIN users u ON u.ID = m.docente_id
     WHERE m.asignatura_id = ? AND m.estado = 1
     ORDER BY m.fecha ASC, m.mensaje_id ASC"
);
if (!$stmt) {
    atenea_render_auth_alert('error', 'No disponible', 'No se pudieron cargar los mensajes.', 'mensajes_estudiante_lista.php');
}
$stmt->bind_param('i', $asignaturaId);
$stmt->execute();
$result = $stmt->get_result();
$messages = [];
while ($result instanceof mysqli_result && ($row = $result->fetch_assoc())) {
    $messages[] = $row;
}
if ($result instanceof mysqli_result) {
    mysqli_free_result($result);
}
$stmt->close();
?>

<div class="card shadow border-0 mb-4 mensajes-shell">
  <div class="card-header bg-white border-0 py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
      <div>
        <p class="text-uppercase text-muted font-weight-bold mb-1"><?php echo mensajes_est_h($subject['G_NAME'] . ' - ' . $subject['p_name']); ?></p>
        <h4 class="mb-0">Anuncios de <?php echo mensajes_est_h($subject['A_NAME']); ?></h4>
      </div>
      <a href="mensajes_estudiante_lista.php" class="btn btn-outline-dark btn-sm mt-3 mt-md-0">Volver a materias</a>
    </div>
  </div>
  <div class="card-body">
    <div class="mensajes-feed" id="mensajesFeed">
      <?php if ($messages === []): ?>
        <div class="mensajes-empty">
          <h5>No hay anuncios todavia</h5>
          <p class="mb-0">Cuando tu docente publique mensajes, apareceran aqui.</p>
        </div>
      <?php endif; ?>

      <?php foreach ($messages as $message): ?>
        <?php
        $archivo = trim((string) ($message['archivo'] ?? ''));
        $safeFile = basename($archivo);
        $filePath = $safeFile !== '' ? 'archivos_mensajes/' . $safeFile : '';
        $ext = strtolower(pathinfo($safeFile, PATHINFO_EXTENSION));
        $youtubeId = mensajes_est_youtube_id((string) $message['mensaje']);
        ?>
        <article class="mensaje-card">
          <div class="mensaje-card__header">
            <div>
              <strong><?php echo mensajes_est_h($message['USERNAME']); ?></strong>
              <span><?php echo mensajes_est_h($message['fecha']); ?></span>
            </div>
          </div>
          <div class="mensaje-card__body">
            <p><?php echo nl2br(mensajes_est_linkify((string) $message['mensaje'])); ?></p>
            <?php if ($youtubeId !== ''): ?>
              <div class="mensaje-video">
                <iframe src="https://www.youtube.com/embed/<?php echo mensajes_est_h($youtubeId); ?>" title="Video de YouTube" allowfullscreen></iframe>
              </div>
            <?php endif; ?>
            <?php if ($filePath !== ''): ?>
              <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)): ?>
                <button type="button" class="mensaje-image-button" data-image-preview="<?php echo mensajes_est_h($filePath); ?>">
                  <img src="<?php echo mensajes_est_h($filePath); ?>" alt="Archivo adjunto">
                </button>
              <?php else: ?>
                <a href="<?php echo mensajes_est_h($filePath); ?>" download class="btn btn-sm btn-outline-primary">Descargar archivo</a>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div id="mensajeImageModal" class="mensaje-image-modal" aria-hidden="true">
  <button type="button" aria-label="Cerrar imagen">Cerrar</button>
  <img src="" alt="Vista ampliada">
</div>

<style>
.mensajes-shell .card-header h4 { color: #1f2937; }
.mensajes-feed { max-height: 620px; overflow-y: auto; padding: 18px; background: #f4f6fb; border: 1px solid #e5e7eb; border-radius: 12px; }
.mensajes-empty { text-align: center; padding: 34px 16px; color: #6b7280; }
.mensaje-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; margin-bottom: 14px; box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06); }
.mensaje-card__header { border-bottom: 1px solid #edf0f5; padding-bottom: 10px; margin-bottom: 12px; }
.mensaje-card__header strong { display: block; color: #111827; }
.mensaje-card__header span { display: block; color: #6b7280; font-size: .82rem; }
.mensaje-card__body p { color: #253041; margin-bottom: 12px; }
.mensaje-card__body a { font-weight: 700; }
.mensaje-video { position: relative; width: 100%; max-width: 520px; aspect-ratio: 16 / 9; margin: 12px 0; overflow: hidden; border-radius: 10px; background: #111827; }
.mensaje-video iframe { width: 100%; height: 100%; border: 0; }
.mensaje-image-button { border: 0; padding: 0; background: transparent; cursor: zoom-in; }
.mensaje-image-button img { display: block; max-width: 240px; max-height: 180px; object-fit: cover; border-radius: 10px; box-shadow: 0 6px 18px rgba(15, 23, 42, .16); }
.mensaje-image-modal { display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(17, 24, 39, .88); align-items: center; justify-content: center; padding: 24px; }
.mensaje-image-modal.is-open { display: flex; }
.mensaje-image-modal img { max-width: 92vw; max-height: 86vh; border-radius: 12px; }
.mensaje-image-modal button { position: fixed; top: 18px; right: 18px; border: 1px solid rgba(255,255,255,.5); background: rgba(255,255,255,.12); color: #fff; border-radius: 8px; padding: 8px 12px; }
@media (max-width: 768px) {
  .mensajes-feed { max-height: 460px; padding: 12px; }
  .mensaje-image-button img { max-width: 100%; }
}
</style>

<script>
(function () {
  var feed = document.getElementById('mensajesFeed');
  if (feed) {
    feed.scrollTop = feed.scrollHeight;
  }

  var modal = document.getElementById('mensajeImageModal');
  var modalImage = modal ? modal.querySelector('img') : null;
  var closeButton = modal ? modal.querySelector('button') : null;

  document.querySelectorAll('[data-image-preview]').forEach(function (button) {
    button.addEventListener('click', function () {
      if (!modal || !modalImage) {
        return;
      }
      modalImage.src = button.getAttribute('data-image-preview') || '';
      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
    });
  });

  function closeModal() {
    if (!modal || !modalImage) {
      return;
    }
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    modalImage.src = '';
  }

  if (modal) {
    modal.addEventListener('click', function (event) {
      if (event.target === modal) {
        closeModal();
      }
    });
  }
  if (closeButton) {
    closeButton.addEventListener('click', closeModal);
  }
})();
</script>

<?php include '../includes/footer.php'; ?>
