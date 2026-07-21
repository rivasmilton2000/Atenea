<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/contenido.php';

function areasPersonalizacionVisualAtenea(): array
{
    return [
        'website' => 'Website',
        'dashboard' => 'Dashboard de administrador',
        'estudiantes' => 'Dashboard de estudiante',
        'docente' => 'Dashboard de docente',
    ];
}

function elementosPersonalizacionVisualAtenea(): array
{
    return [
        'general' => 'Diseño general',
        'tipografia' => 'Tipografía y textos',
        'botones' => 'Botones',
        'tarjetas' => 'Tarjetas',
        'navbar' => 'Navbar',
        'sidebar' => 'Sidebar',
        'banner' => 'Banner',
        'footer' => 'Pie de página',
    ];
}

function fuentesPersonalizacionVisualAtenea(): array
{
    return ['Open Sans', 'Poppins', 'Raleway', 'Arial', 'Georgia', 'Trebuchet MS'];
}

function configuracionVisualOriginalAtenea(string $area): array
{
    $variantes = [
        'website' => ['#0b3d2e', '#d4a847', '#0b3d2e', '#ffffff', '#ffffff', '#0b3d2e', '#173f35', '#f5f7f6'],
        'dashboard' => ['#1f3b2d', '#d4a847', '#1f3b2d', '#f5f7ff', '#ffffff', '#ffffff', '#1f3b2d', '#f5f7ff'],
        'estudiantes' => ['#0b3d2e', '#d4a847', '#0b3d2e', '#f6f8fb', '#ffffff', '#ffffff', '#0b3d2e', '#f6f8fb'],
        'docente' => ['#1f3b2d', '#d4a847', '#1f3b2d', '#f5f7ff', '#ffffff', '#ffffff', '#1f3b2d', '#f5f7ff'],
    ];
    [$principal,$secundario,$boton,$fondo,$navbar,$sidebar,$banner,$footer] = $variantes[$area] ?? $variantes['website'];
    return [
        'color_principal'=>$principal,'color_secundario'=>$secundario,'color_botones'=>$boton,
        'color_fondo'=>$fondo,'color_navbar'=>$navbar,'color_sidebar'=>$sidebar,
        'color_banner'=>$banner,'color_footer'=>$footer,'tipografia'=>'Open Sans',
        'tamano_texto'=>16,'espaciado'=>1.0,'bordes'=>12,'tarjetas'=>'sombra',
        'logo'=>'','imagen_fondo'=>'','imagen_banner'=>'',
    ];
}

function tablaPersonalizacionVisualDisponible(PDO $pdo): bool
{
    try { return (bool) $pdo->query("SHOW TABLES LIKE 'personalizaciones_visuales'")->fetchColumn(); }
    catch (Throwable) { return false; }
}

function rutaVisualSeguraAtenea(string $ruta): string
{
    $ruta = str_replace('\\', '/', trim($ruta));
    return preg_match('~^uploads/personalizacion/[a-f0-9]{32}\.(?:jpg|png|webp)$~', $ruta) ? $ruta : '';
}

function normalizarConfiguracionVisualAtenea(string $area, array $entrada): array
{
    $resultado = configuracionVisualOriginalAtenea($area);
    foreach (['color_principal','color_secundario','color_botones','color_fondo','color_navbar','color_sidebar','color_banner','color_footer'] as $clave) {
        $valor = strtolower(trim((string)($entrada[$clave] ?? '')));
        if (preg_match('/^#[0-9a-f]{6}$/', $valor)) $resultado[$clave] = $valor;
    }
    $fuente = trim((string)($entrada['tipografia'] ?? ''));
    if (in_array($fuente, fuentesPersonalizacionVisualAtenea(), true)) $resultado['tipografia'] = $fuente;
    $resultado['tamano_texto'] = max(12, min(20, (int)($entrada['tamano_texto'] ?? 16)));
    $resultado['espaciado'] = max(0.75, min(1.5, round((float)($entrada['espaciado'] ?? 1), 2)));
    $resultado['bordes'] = max(0, min(24, (int)($entrada['bordes'] ?? 12)));
    $tarjetas = (string)($entrada['tarjetas'] ?? 'sombra');
    if (in_array($tarjetas, ['plana','borde','sombra'], true)) $resultado['tarjetas'] = $tarjetas;
    foreach (['logo','imagen_fondo','imagen_banner'] as $clave) {
        $resultado[$clave] = rutaVisualSeguraAtenea((string)($entrada[$clave] ?? ''));
    }
    return $resultado;
}

function obtenerPersonalizacionVisualAtenea(string $area, ?PDO $pdo = null): array
{
    if (!isset(areasPersonalizacionVisualAtenea()[$area])) $area = 'website';
    $original = configuracionVisualOriginalAtenea($area);
    try {
        $pdo ??= obtenerConexion();
        if (!tablaPersonalizacionVisualDisponible($pdo)) return $original;
        $q = $pdo->prepare('SELECT configuracion_json FROM personalizaciones_visuales WHERE area=:area LIMIT 1');
        $q->execute(['area'=>$area]);
        $json = $q->fetchColumn();
        if (!is_string($json) || $json === '') return $original;
        $datos = json_decode($json, true, 32, JSON_THROW_ON_ERROR);
        return normalizarConfiguracionVisualAtenea($area, is_array($datos) ? $datos : []);
    } catch (Throwable $e) {
        error_log('Personalización visual Atenea: '.$e->getMessage());
        return $original;
    }
}

function colorTextoContrasteAtenea(string $hex): string
{
    $r=hexdec(substr($hex,1,2));$g=hexdec(substr($hex,3,2));$b=hexdec(substr($hex,5,2));
    return (($r*299+$g*587+$b*114)/1000) >= 145 ? '#18201c' : '#ffffff';
}

function cssPersonalizacionVisualAtenea(string $area, ?array $configuracion = null): string
{
    $c = normalizarConfiguracionVisualAtenea($area, $configuracion ?? obtenerPersonalizacionVisualAtenea($area));
    $textoBoton=colorTextoContrasteAtenea($c['color_botones']);$textoNavbar=colorTextoContrasteAtenea($c['color_navbar']);$textoSidebar=colorTextoContrasteAtenea($c['color_sidebar']);
    $sombra = $c['tarjetas']==='sombra' ? '0 8px 24px rgba(20,45,34,.12)' : 'none';
    $borde = $c['tarjetas']==='borde' ? '1px solid rgba(25,55,42,.22)' : '1px solid transparent';
    $fondoImagen = $c['imagen_fondo'] !== '' ? "background-image:url('".atenea_url($c['imagen_fondo'])."');background-size:cover;background-attachment:fixed;" : '';
    $bannerImagen = $c['imagen_banner'] !== '' ? "background-image:linear-gradient(rgba(0,0,0,.24),rgba(0,0,0,.24)),url('".atenea_url($c['imagen_banner'])."');background-size:cover;background-position:center;" : '';
    return ":root{--atenea-visual-primary:{$c['color_principal']};--atenea-visual-secondary:{$c['color_secundario']};--atenea-visual-button:{$c['color_botones']};--atenea-visual-radius:{$c['bordes']}px;--atenea-visual-spacing:{$c['espaciado']};}body{font-family:'{$c['tipografia']}',sans-serif;font-size:{$c['tamano_texto']}px;background-color:{$c['color_fondo']};{$fondoImagen}}body .btn-primary{background:{$c['color_botones']}!important;border-color:{$c['color_botones']}!important;color:{$textoBoton}!important}body .btn-primary:hover,body .btn-primary:focus{filter:brightness(.88)}body .text-primary,body a:not(.btn):not(.nav-link){color:{$c['color_principal']}}body .bg-primary,body .progress-bar{background-color:{$c['color_principal']}!important}body .card{border-radius:{$c['bordes']}px!important;border:{$borde}!important;box-shadow:{$sombra}!important}body .content-wrapper,body main .container,body main .container-fluid{--atenea-content-spacing:calc(1rem * {$c['espaciado']})}body .navbar,body .header{background-color:{$c['color_navbar']}!important;color:{$textoNavbar}}body .navbar .nav-link,body .header .nav-link{color:{$textoNavbar}}body .sidebar{background-color:{$c['color_sidebar']}!important;color:{$textoSidebar}}body .sidebar .nav-link,body .sidebar .menu-title,body .sidebar .item-name{color:{$textoSidebar}!important}body .hero,body .page-title,body .atenea-banner{background-color:{$c['color_banner']}!important;{$bannerImagen}}body .footer,body footer{background-color:{$c['color_footer']}!important;border-radius:0}body input,body select,body textarea,body .modal-content{border-radius:{$c['bordes']}px}";
}

function renderizarPersonalizacionVisualAtenea(string $area): void
{
    if (($GLOBALS['atenea_personalizacion_renderizada'] ?? null) === $area) return;
    $GLOBALS['atenea_personalizacion_renderizada'] = $area;
    echo '<style id="atenea-personalizacion-visual">'.cssPersonalizacionVisualAtenea($area).'</style>';
}

function logoPersonalizacionVisualAtenea(string $area, string $fallback): string
{
    $logo = obtenerPersonalizacionVisualAtenea($area)['logo'] ?? '';
    return $logo !== '' ? rutaImagenContenido($logo, $fallback) : rutaImagenContenido($fallback, 'img/atenea-logo.png');
}

function subirImagenPersonalizacionVisualAtenea(string $campo): ?string
{
    if (!isset($_FILES[$campo]) || (int)($_FILES[$campo]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    $f=$_FILES[$campo];
    if ((int)$f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('No fue posible recibir la imagen seleccionada.');
    if ((int)$f['size'] > 5*1024*1024) throw new RuntimeException('Cada imagen puede pesar como máximo 5 MB.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file((string)$f['tmp_name']);$map=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if (!isset($map[$mime])) throw new RuntimeException('Solo se permiten imágenes JPG, PNG o WEBP.');
    $extension=strtolower(pathinfo((string)$f['name'],PATHINFO_EXTENSION));$validas=$mime==='image/jpeg'?['jpg','jpeg']:[$map[$mime]];
    if (!in_array($extension,$validas,true) || preg_match('/\.(?:php\d*|phtml|phar|html?|svg|js)(?:\.|$)/i',(string)$f['name'])) throw new RuntimeException('El archivo seleccionado no es una imagen segura.');
    $dim=getimagesize((string)$f['tmp_name']);if(!$dim||$dim[0]>8000||$dim[1]>8000)throw new RuntimeException('Las dimensiones de la imagen no son válidas.');
    $dir=ATENEA_ROOT.'/uploads/personalizacion';if(!is_dir($dir)&&!mkdir($dir,0755,true)&&!is_dir($dir))throw new RuntimeException('No fue posible preparar el almacenamiento de imágenes.');
    $nombre=bin2hex(random_bytes(16)).'.'.$map[$mime];
    if(!move_uploaded_file((string)$f['tmp_name'],$dir.'/'.$nombre))throw new RuntimeException('No fue posible guardar la imagen.');
    return 'uploads/personalizacion/'.$nombre;
}

function guardarPersonalizacionVisualAtenea(string $area, array $entrada, int $adminId, string $accion='publicar', ?PDO $pdo=null): array
{
    if(!isset(areasPersonalizacionVisualAtenea()[$area]))throw new DomainException('El área seleccionada no es válida.');
    if(!in_array($accion,['publicar','restaurar_original'],true))throw new DomainException('La acción solicitada no es válida.');
    $pdo??=obtenerConexion();if(!tablaPersonalizacionVisualDisponible($pdo))throw new RuntimeException('Aplica la migración 028 antes de publicar personalizaciones.');
    $config=$accion==='restaurar_original'?configuracionVisualOriginalAtenea($area):normalizarConfiguracionVisualAtenea($area,$entrada);
    $json=json_encode($config,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
    $pdo->beginTransaction();
    try{
        $q=$pdo->prepare('SELECT id,version FROM personalizaciones_visuales WHERE area=:area FOR UPDATE');$q->execute(['area'=>$area]);$actual=$q->fetch();$version=(int)($actual['version']??0)+1;
        if($actual){$q=$pdo->prepare('UPDATE personalizaciones_visuales SET configuracion_json=:json,version=:version,actualizado_por=:admin WHERE id=:id');$q->execute(['json'=>$json,'version'=>$version,'admin'=>$adminId,'id'=>$actual['id']]);$id=(int)$actual['id'];}
        else{$q=$pdo->prepare('INSERT INTO personalizaciones_visuales(area,configuracion_json,version,actualizado_por) VALUES(:area,:json,:version,:admin)');$q->execute(['area'=>$area,'json'=>$json,'version'=>$version,'admin'=>$adminId]);$id=(int)$pdo->lastInsertId();}
        $q=$pdo->prepare('INSERT INTO personalizaciones_visuales_historial(personalizacion_id,area,accion,configuracion_json,version,realizado_por) VALUES(:id,:area,:accion,:json,:version,:admin)');$q->execute(['id'=>$id,'area'=>$area,'accion'=>$accion,'json'=>$json,'version'=>$version,'admin'=>$adminId]);
        $pdo->commit();return $config;
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
