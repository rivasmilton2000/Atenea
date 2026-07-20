<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/avatar.php';
require_once dirname(__DIR__, 2) . '/includes/json_response.php';

$pruebas = [];
$assert = static function (bool $condicion, string $descripcion) use (&$pruebas): void {
    if (!$condicion) throw new RuntimeException('FALLO: ' . $descripcion);
    $pruebas[] = $descripcion;
};

$assert(str_starts_with(atenea_url('src/login/sign-in.php'), '/') && str_ends_with(atenea_url('src/login/sign-in.php'), '/src/login/sign-in.php'), 'El resolvedor central construye una ruta interna estable');
$assert(urlAvatarAtenea(null) === atenea_url(ATENEA_AVATAR_FALLBACK), 'El avatar vacío utiliza el fallback institucional');
$assert(urlAvatarAtenea('C:\\xampp\\htdocs\\Atenea\\foto.jpg') === atenea_url(ATENEA_AVATAR_FALLBACK), 'El avatar rechaza rutas físicas de Windows');
$assert(urlAvatarAtenea('../../config.php') === atenea_url(ATENEA_AVATAR_FALLBACK), 'El avatar rechaza traversal fuera del directorio permitido');
$assert(urlAvatarAtenea('uploads/perfiles/no-existe.jpg') === atenea_url(ATENEA_AVATAR_FALLBACK), 'El avatar local inexistente utiliza fallback');
$assert(urlAvatarAtenea('https://images.example.test/avatar.png') === 'https://images.example.test/avatar.png', 'El avatar admite una URL HTTPS válida');
$assert(solicitudEsJsonAtenea() === false, 'Una solicitud CLI sin cabeceras no se confunde con AJAX');
$assert(datosPaginaErrorAtenea(403)['title'] === 'Acceso denegado', 'La página 403 tiene contenido profesional compartido');
$assert(datosPaginaErrorAtenea(419)['title'] === 'La sesión de seguridad venció', 'La vista equivalente a 419 está disponible');
$assert(datosPaginaErrorAtenea(500)['title'] !== '', 'La página 500 no depende de un mensaje técnico');

foreach ([403, 404, 419, 500, 503] as $codigo) {
    $assert(is_file(dirname(__DIR__, 2) . '/src/errors/' . $codigo . '.php'), 'Existe la página de error ' . $codigo);
}

$assert(is_file(dirname(__DIR__, 2) . '/includes/perfil_modal.php'), 'El perfil compartido sigue siendo único para todos los roles');
$assert(is_file(dirname(__DIR__, 2) . '/includes/mailer.php'), 'El servicio central de correo existente se conserva');
$assert(is_file(dirname(__DIR__, 2) . '/includes/audit.php'), 'El servicio central de auditoría existente se conserva');

echo 'OK ' . count($pruebas) . " pruebas\n";
foreach ($pruebas as $prueba) echo '- ' . $prueba . "\n";
