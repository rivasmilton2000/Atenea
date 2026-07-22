<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/admin_notification_service.php';

$fallos = [];
$comprobar = static function (bool $condicion, string $mensaje) use (&$fallos): void {
    if (!$condicion) $fallos[] = $mensaje;
};

$paginas = ['about.php', 'courses.php', 'trainers.php', 'events.php', 'pricing.php', 'noticias.php', 'contact.php'];
foreach ($paginas as $pagina) {
    $contenido = file_get_contents(dirname(__DIR__, 2) . '/src/website/' . $pagina) ?: '';
    $comprobar(str_contains($contenido, 'class="breadcrumbs"'), $pagina . ' no usa el breadcrumb compartido.');
    $comprobar(str_contains($contenido, 'class="current"'), $pagina . ' no identifica la pagina actual.');
}

$css = file_get_contents(dirname(__DIR__, 2) . '/src/website/assets/css/main.css') ?: '';
$comprobar(str_contains($css, 'background-color: #fffaf0'), 'El breadcrumb no tiene fondo marfil.');
$comprobar(str_contains($css, 'border-radius: 999px'), 'La viñeta actual no es completamente redondeada.');
$comprobar(str_contains($css, 'color: #173f35 !important'), 'Inicio no usa verde oscuro.');

$contacto = file_get_contents(dirname(__DIR__, 2) . '/src/website/forms/contact.php') ?: '';
$comprobar(str_contains($contacto, "notificarAdministracionAtenea('mensaje_contacto'"), 'Contacto no usa el servicio administrativo.');
$comprobar(!str_contains($contacto, "enviarPlantillaCorreoAtenea('contacto_recibido'"), 'Contacto conserva un segundo envio directo.');
$comprobar(str_contains($contacto, "'ip'=>\$ip"), 'El correo de contacto no incorpora la IP disponible.');

$pdo = obtenerConexion();
$evento = 'prueba:etapa1:' . bin2hex(random_bytes(8));
$pdo->beginTransaction();
try {
    $resultado = notificarAdministracionAtenea(
        'prueba_etapa1', 'Prueba de alerta administrativa', 'Registro transaccional descartable.',
        'informacion', null, atenea_url('src/dashboard/index.php'), $evento, ['category' => 'sistema'], $pdo
    );
    $q = $pdo->prepare('SELECT COUNT(*) FROM correo_envios WHERE idempotency_key=:clave');
    $q->execute(['clave' => 'admin-alert:' . $evento]);
    $comprobar((int)$q->fetchColumn() === 1, 'El evento no genero exactamente un registro de correo.');
    notificarAdministracionAtenea(
        'prueba_etapa1', 'Prueba de alerta administrativa', 'Registro transaccional descartable.',
        'informacion', null, atenea_url('src/dashboard/index.php'), $evento, ['category' => 'sistema'], $pdo
    );
    $q->execute(['clave' => 'admin-alert:' . $evento]);
    $comprobar((int)$q->fetchColumn() === 1, 'La repeticion del evento duplico el correo.');
    $comprobar(is_bool($resultado), 'El servicio no devolvio un resultado valido.');
} finally {
    if ($pdo->inTransaction()) $pdo->rollBack();
}

if ($fallos) {
    fwrite(STDERR, implode(PHP_EOL, $fallos) . PHP_EOL);
    exit(1);
}

echo "Etapa 1: breadcrumb, contacto y alertas administrativas verificados.\n";
