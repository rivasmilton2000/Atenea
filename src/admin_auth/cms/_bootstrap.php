<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/includes/auth.php';
require_once dirname(__DIR__, 3) . '/includes/conexion.php';
require_once dirname(__DIR__, 3) . '/includes/contenido.php';
exigirRol(['admin']);

function cmsFlash(string $tipo, string $mensaje): void
{
    $_SESSION['cms_flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function cmsObtenerFlash(): ?array
{
    $flash = $_SESSION['cms_flash'] ?? null;
    unset($_SESSION['cms_flash']);
    return is_array($flash) ? $flash : null;
}

function cmsId(mixed $valor): int
{
    return filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
}

function cmsUrlValida(string $url): bool
{
    $url = trim($url);
    if ($url === '') return true;
    if (preg_match('/^(?:javascript|data|vbscript):/i', $url)) return false;
    if (preg_match('#^https?://#i', $url)) return filter_var($url, FILTER_VALIDATE_URL) !== false;
    if ($url === '#') return true;
    if (str_starts_with($url, '#')) return preg_match('/^#[A-Za-z][\w-]*$/', $url) === 1;
    return preg_match('~^[A-Za-z0-9_./?=&%#-]+$~', $url) === 1 && !str_contains($url, '..');
}

function cmsSubirImagen(string $campo): ?string
{
    if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) return null;
    $archivo = $_FILES[$campo];
    if ($archivo['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('No fue posible recibir la imagen.');
    if ((int) $archivo['size'] > 5 * 1024 * 1024) throw new RuntimeException('La imagen no puede superar 5 MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    $extensiones = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensiones[$mime])) throw new RuntimeException('Solo se permiten imágenes JPG, PNG o WEBP.');
    $original = (string) $archivo['name'];
    if (preg_match('/\.(?:php\d*|phtml|phar|html?|svg)(?:\.|$)/i', $original)) throw new RuntimeException('El nombre del archivo no es seguro.');
    $directorio = ATENEA_ROOT . '/uploads/contenido';
    if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) throw new RuntimeException('No se pudo preparar la carpeta de imágenes.');
    $nombre = bin2hex(random_bytes(16)) . '.' . $extensiones[$mime];
    if (!move_uploaded_file($archivo['tmp_name'], $directorio . '/' . $nombre)) throw new RuntimeException('No se pudo guardar la imagen.');
    return 'uploads/contenido/' . $nombre;
}

function cmsEliminarImagenSiNoSeUsa(?string $ruta): void
{
    $ruta = (string) $ruta;
    if (!str_starts_with($ruta, 'uploads/contenido/') || str_contains($ruta, '..')) return;
    $pdo = obtenerConexion();
    $consulta = $pdo->prepare('SELECT (SELECT COUNT(*) FROM secciones WHERE imagen=:ruta1)+(SELECT COUNT(*) FROM elementos_seccion WHERE imagen=:ruta2)+(SELECT COUNT(*) FROM configuracion_sitio WHERE valor=:ruta3) usos');
    $consulta->execute(['ruta1'=>$ruta,'ruta2'=>$ruta,'ruta3'=>$ruta]);
    if ((int) $consulta->fetchColumn() === 0) {
        $archivo = ATENEA_ROOT . '/' . $ruta;
        if (is_file($archivo)) unlink($archivo);
    }
}

function cmsCabecera(string $titulo, string $activo): void
{
    $flash = cmsObtenerFlash();
    $base = atenea_url('src/admin_auth/cms');
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.atenea_e($titulo).' | Atenea</title>';
    echo '<link rel="icon" href="'.atenea_url('img/atenea-logo.png').'"><link rel="stylesheet" href="'.atenea_url('src/dashboard/assets/vendors/mdi/css/materialdesignicons.min.css').'"><link rel="stylesheet" href="'.atenea_url('src/dashboard/assets/vendors/css/vendor.bundle.base.css').'"><link rel="stylesheet" href="'.atenea_url('src/dashboard/assets/css/style.css').'">';
    echo '<style>.cms-shell{min-height:100vh;background:#f4f5f7}.cms-nav{background:#171717}.cms-nav .nav-link,.cms-nav .navbar-brand{color:#fff}.cms-nav .nav-link.active,.text-atenea{color:#c49a3a!important}.btn-atenea{background:#c49a3a;border-color:#c49a3a;color:#fff}.btn-atenea:hover{background:#8f6b20;color:#fff}.preview-img{max-width:220px;max-height:140px;object-fit:contain;border:1px solid #ddd;border-radius:8px}.table td{vertical-align:middle}</style></head><body class="cms-shell">';
    echo '<nav class="navbar navbar-expand-lg cms-nav"><div class="container-fluid"><a class="navbar-brand" href="'.atenea_url('src/dashboard/index.php').'">Atenea Admin</a><button class="navbar-toggler bg-light" data-bs-toggle="collapse" data-bs-target="#cmsMenu"><span class="navbar-toggler-icon"></span></button><div id="cmsMenu" class="collapse navbar-collapse"><ul class="navbar-nav me-auto">';
    $links=['secciones/index.php'=>'Secciones','elementos/index.php'=>'Elementos','menu/index.php'=>'Navbar y menú','configuracion.php'=>'Configuración general'];
    foreach($links as $ruta=>$texto) echo '<li class="nav-item"><a class="nav-link '.($activo===$ruta?'active':'').'" href="'.$base.'/'.$ruta.'">'.atenea_e($texto).'</a></li>';
    echo '</ul><a class="btn btn-sm btn-outline-light me-2" href="'.atenea_url('index.php').'" target="_blank">Ver sitio</a><a class="btn btn-sm btn-outline-light" href="'.atenea_url('src/login/logout.php').'">Cerrar sesión</a></div></div></nav>';
    echo '<main class="container-fluid px-3 px-md-4 py-4"><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="'.atenea_url('src/dashboard/index.php').'">Dashboard</a></li><li class="breadcrumb-item active">Gestión del sitio web</li></ol></nav><div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3">'.atenea_e($titulo).'</h1></div>';
    if($flash) echo '<div class="alert alert-'.($flash['tipo']==='exito'?'success':'danger').'">'.atenea_e((string)$flash['mensaje']).'</div>';
}

function cmsPie(): void
{
    echo '</main><script src="'.atenea_url('src/dashboard/assets/vendors/js/vendor.bundle.base.js').'"></script></body></html>';
}
