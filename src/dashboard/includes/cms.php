<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/includes/auth.php';
require_once dirname(__DIR__, 3) . '/includes/conexion.php';
require_once dirname(__DIR__, 3) . '/includes/contenido.php';
require_once dirname(__DIR__, 3) . '/includes/perfil_modal.php';
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
    if (preg_match('/\.(?:php\d*|phtml|phar|html?|svg)(?:\.|$)/i', (string) $archivo['name'])) throw new RuntimeException('El nombre del archivo no es seguro.');
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
    $consulta = $pdo->prepare('SELECT (SELECT COUNT(*) FROM secciones WHERE imagen=:ruta1)+(SELECT COUNT(*) FROM elementos_seccion WHERE imagen=:ruta2)+(SELECT COUNT(*) FROM configuracion_sitio WHERE valor=:ruta3)+(SELECT COUNT(*) FROM productos WHERE imagen_principal=:ruta4)+(SELECT COUNT(*) FROM producto_imagenes WHERE ruta=:ruta5)');
    $consulta->execute(['ruta1'=>$ruta,'ruta2'=>$ruta,'ruta3'=>$ruta,'ruta4'=>$ruta,'ruta5'=>$ruta]);
    if ((int) $consulta->fetchColumn() === 0) {
        $archivo = ATENEA_ROOT . '/' . $ruta;
        if (is_file($archivo)) unlink($archivo);
    }
}

function cmsSubirGaleria(string $campo): array
{
    if (!isset($_FILES[$campo]['name']) || !is_array($_FILES[$campo]['name'])) return [];
    $rutas=[];$original=$_FILES[$campo];
    foreach($original['name'] as $i=>$nombre){if(($original['error'][$i]??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)continue;$_FILES['_galeria_temporal']=['name'=>$nombre,'type'=>$original['type'][$i]??'','tmp_name'=>$original['tmp_name'][$i]??'','error'=>$original['error'][$i]??UPLOAD_ERR_NO_FILE,'size'=>$original['size'][$i]??0];$rutas[]=cmsSubirImagen('_galeria_temporal');}
    unset($_FILES['_galeria_temporal']);return array_values(array_filter($rutas));
}

function fechaAdminActual(): string
{
    $dias = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
    $meses = [1=>'enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    return ucfirst($dias[(int)date('w')]) . ', ' . date('j') . ' de ' . $meses[(int)date('n')] . ' de ' . date('Y');
}

function cmsCabecera(string $titulo, string $activo, string $descripcion = 'Administra el contenido publicado en Atenea.'): void
{
    $dashboardTitle = $titulo;
    $dashboardActive = $activo;
    $dashboardDescription = $descripcion;
    $dashboardFlash = cmsObtenerFlash();
    $usuarioAdmin = obtenerUsuarioActual();
    $configuracionAdmin = obtenerConfiguracionSitio();
    require dirname(__DIR__) . '/includes/header.php';
    require dirname(__DIR__) . '/partials/_navbar.php';
    echo '<div class="container-fluid page-body-wrapper">';
    require dirname(__DIR__) . '/partials/_sidebar.php';
    echo '<div class="main-panel"><div class="content-wrapper">';
    echo '<div class="d-sm-flex align-items-center justify-content-between border-bottom mb-4"><div><nav aria-label="breadcrumb"><ol class="breadcrumb mb-2"><li class="breadcrumb-item"><a href="'.atenea_url('src/dashboard/index.php').'">Panel principal</a></li><li class="breadcrumb-item active">'.atenea_e($titulo).'</li></ol></nav><h1 class="h3 mb-1">'.atenea_e($titulo).'</h1><p class="text-muted mb-3">'.atenea_e($descripcion).'</p></div></div>';
    if ($dashboardFlash) {
        $clase = $dashboardFlash['tipo'] === 'exito' ? 'success' : 'danger';
        echo '<div class="alert alert-'.$clase.' alert-dismissible fade show" role="alert">'.atenea_e((string)$dashboardFlash['mensaje']).'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}

function cmsPie(): void
{
    echo '</div>';
    require dirname(__DIR__) . '/partials/_footer.php';
    echo '</div></div></div>';
    renderizarModalPerfil('dashboard');
    require dirname(__DIR__) . '/includes/scripts.php';
}
