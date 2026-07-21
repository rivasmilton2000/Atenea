<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/personalizacion_visual.php';
require_once dirname(__DIR__,2).'/includes/permissions.php';

$ok=0;$errores=[];$assert=function(bool $condicion,string $mensaje)use(&$ok,&$errores):void{if($condicion){$ok++;echo "- $mensaje\n";}else{$errores[]=$mensaje;echo "FALLO: $mensaje\n";}};
$pdo=obtenerConexion();
if(!tablaPersonalizacionVisualDisponible($pdo)){$sql=file_get_contents(dirname(__DIR__,2).'/src/database/migrations/028_personalizacion_visual_portales.sql');$pdo->exec((string)$sql);}
$assert(tablaPersonalizacionVisualDisponible($pdo),'La migración crea el almacenamiento de personalizaciones');
$assert((bool)$pdo->query("SHOW TABLES LIKE 'personalizaciones_visuales_historial'")->fetchColumn(),'La migración crea un historial independiente');
$adminId=(int)$pdo->query("SELECT id FROM usuarios WHERE rol='admin' AND estado='activo' AND deleted_at IS NULL ORDER BY es_superadmin DESC,id LIMIT 1")->fetchColumn();
$assert($adminId>0,'Existe un administrador autorizado para la prueba');

$area='docente';$q=$pdo->prepare('SELECT * FROM personalizaciones_visuales WHERE area=:area');$q->execute(['area'=>$area]);$anterior=$q->fetch()?:null;$maxHistorial=(int)$pdo->query('SELECT COALESCE(MAX(id),0) FROM personalizaciones_visuales_historial')->fetchColumn();
try{
    $normal=normalizarConfiguracionVisualAtenea($area,['color_principal'=>'red;}</style><script>','tipografia'=>'url(evil)','tamano_texto'=>99,'espaciado'=>9,'bordes'=>100,'logo'=>'../../shell.php']);
    $assert($normal['color_principal']===configuracionVisualOriginalAtenea($area)['color_principal'],'Los colores no válidos se rechazan');
    $assert(in_array($normal['tipografia'],fuentesPersonalizacionVisualAtenea(),true),'Solo se aceptan tipografías de la lista permitida');
    $assert($normal['tamano_texto']===20&&$normal['espaciado']===1.5&&$normal['bordes']===24,'Texto, espaciado y bordes respetan límites seguros');
    $assert($normal['logo']==='','Las rutas manipuladas de imágenes se rechazan');
    $config=guardarPersonalizacionVisualAtenea($area,['color_principal'=>'#123456','color_botones'=>'#abcdef','tipografia'=>'Georgia','tamano_texto'=>18,'espaciado'=>1.2,'bordes'=>8,'tarjetas'=>'borde'],$adminId,'publicar',$pdo);
    $assert($config['color_principal']==='#123456'&&$config['tipografia']==='Georgia','La publicación conserva únicamente valores validados');
    $guardada=obtenerPersonalizacionVisualAtenea($area,$pdo);$assert($guardada['color_botones']==='#abcdef','Los usuarios reciben la configuración publicada desde la base');
    $css=cssPersonalizacionVisualAtenea($area,$guardada);$assert(str_contains($css,'--atenea-visual-primary:#123456')&&!str_contains($css,'<script'),'El CSS se genera desde valores tipados sin código ejecutable');
    $q=$pdo->prepare('SELECT COUNT(*) FROM personalizaciones_visuales_historial WHERE area=:area AND id>:id AND realizado_por=:admin');$q->execute(['area'=>$area,'id'=>$maxHistorial,'admin'=>$adminId]);$assert((int)$q->fetchColumn()===1,'La publicación registra versión, fecha y administrador');
    $restaurada=guardarPersonalizacionVisualAtenea($area,[],$adminId,'restaurar_original',$pdo);$assert($restaurada===configuracionVisualOriginalAtenea($area),'La restauración recupera el diseño original');
}finally{
    $pdo->prepare('DELETE FROM personalizaciones_visuales_historial WHERE id>:id')->execute(['id'=>$maxHistorial]);
    if($anterior){$pdo->prepare('UPDATE personalizaciones_visuales SET configuracion_json=:json,version=:version,actualizado_por=:admin,created_at=:created,updated_at=:updated WHERE area=:area')->execute(['json'=>$anterior['configuracion_json'],'version'=>$anterior['version'],'admin'=>$anterior['actualizado_por'],'created'=>$anterior['created_at'],'updated'=>$anterior['updated_at'],'area'=>$area]);}
    else{$pdo->prepare('DELETE FROM personalizaciones_visuales WHERE area=:area')->execute(['area'=>$area]);}
}

$editor=file_get_contents(dirname(__DIR__,2).'/src/dashboard/personalizacion/index.php');$js=file_get_contents(dirname(__DIR__,2).'/src/dashboard/personalizacion/editor.js');$servicio=file_get_contents(dirname(__DIR__,2).'/includes/personalizacion_visual.php');
$assert(str_contains($editor,"exigirPermiso('appearance.manage')")&&str_contains($editor,'validarTokenCsrf'),'El módulo exige permiso administrativo y CSRF');
$assert(str_contains($editor,'data-preview-target')&&str_contains($editor,'visualElement'),'El administrador puede seleccionar visualmente un elemento');
$assert(str_contains($js,"form.addEventListener('input'")&&str_contains($js,'render(read())'),'La vista previa cambia inmediatamente sin guardar');
$assert(str_contains($editor,'Cancelar cambios')&&str_contains($editor,'restaurar_original'),'El editor permite cancelar y restaurar el diseño original');
$assert(str_contains($servicio,'finfo(FILEINFO_MIME_TYPE)')&&str_contains($servicio,'move_uploaded_file'),'Las imágenes se validan en backend antes de publicarse');
$assert(!preg_match('/<textarea[^>]+name=["\'](?:css|php|javascript|sql|codigo)/i',$editor),'El módulo no ofrece edición de código o consultas');
foreach(['includes/header.php','src/dashboard/includes/header.php','includes/portal_estudiante_layout.php','src/docente/_layout.php']as$archivo){$contenido=file_get_contents(dirname(__DIR__,2).'/'.$archivo);$assert(str_contains($contenido,'ateneaAlertasHead')||str_contains($contenido,'personalizacion_visual'),'La personalización se integra en '.$archivo);}
$assert(str_contains(file_get_contents(dirname(__DIR__,2).'/includes/alerts.php'),'renderizarPersonalizacionVisualAtenea'),'Las cabeceras compartidas publican el diseño automáticamente');
$assert(str_contains(file_get_contents(dirname(__DIR__,2).'/src/dashboard/partials/_sidebar.php'),'personalizacion/index.php'),'El editor está disponible desde la navegación administrativa');

if($errores){echo "\n".count($errores)." pruebas fallaron.\n";exit(1);}echo "\nOK $ok pruebas\n";
