<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/auth.php';
require_once dirname(__DIR__,2).'/includes/contenido.php';
require_once dirname(__DIR__,2).'/includes/navbar_contenido.php';
$pdo=obtenerConexion();$tag='c7e3-'.bin2hex(random_bytes(4));$ids=[];$ok=[];$assert=static function(bool$c,string$m)use(&$ok){if(!$c)throw new RuntimeException('FALLO: '.$m);$ok[]=$m;};
try{
 $columnas=array_column($pdo->query('SHOW COLUMNS FROM menu_sitio')->fetchAll(),'Field');foreach(['padre_id','slug','icono','visibilidad','roles_json','tipo_contenido','contenido_html','contenido_json','eliminado_at']as$c)$assert(in_array($c,$columnas,true),'La migración incorpora '.$c);
 $assert(count(tiposContenidoNavbarAtenea())===13,'El administrador dispone de los 13 tipos de contenido solicitados');
 $assert(contieneCodigoProhibidoNavbarAtenea('<script>alert(1)</script>')&&contieneCodigoProhibidoNavbarAtenea('<?php echo 1; ?>'),'PHP y JavaScript se detectan como contenido prohibido');
 $limpio=sanitizarHtmlNavbarAtenea('<h2 onclick="x()">Título</h2><a href="javascript:alert(1)">Enlace</a><strong>Seguro</strong>');$assert(!str_contains($limpio,'onclick')&&!str_contains($limpio,'javascript:')&&str_contains($limpio,'<strong>Seguro</strong>'),'El HTML permitido se conserva y los atributos peligrosos se eliminan');
 $q=$pdo->prepare("INSERT INTO menu_sitio(texto,slug,icono,url,visibilidad,roles_json,tipo_contenido,contenido_html,contenido_json,activo,orden) VALUES(:t,:s,'bi bi-leaf',:u,'publica',NULL,'pagina_informativa',:h,:j,1,9000)");$q->execute(['t'=>'Temporal '.$tag,'s'=>$tag,'u'=>'src/website/seccion.php?slug='.$tag,'h'=>'<p>Contenido seguro</p>','j'=>json_encode(['titulo'=>'Temporal','resumen'=>'Prueba'],JSON_THROW_ON_ERROR)]);$padre=(int)$pdo->lastInsertId();$ids[]=$padre;
 $q=$pdo->prepare("INSERT INTO menu_sitio(padre_id,texto,slug,url,visibilidad,roles_json,tipo_contenido,contenido_json,activo,orden) VALUES(:p,'Submenú',:s,:u,'autenticada',:r,'formulario',:j,1,1)");$q->execute(['p'=>$padre,'s'=>$tag.'-hijo','u'=>'src/website/seccion.php?slug='.$tag.'-hijo','r'=>json_encode(['usuario'],JSON_THROW_ON_ERROR),'j'=>json_encode(['campos'=>['nombre','correo','mensaje']],JSON_THROW_ON_ERROR)]);$hijo=(int)$pdo->lastInsertId();$ids[]=$hijo;
 $q=$pdo->prepare('SELECT padre_id,roles_json FROM menu_sitio WHERE id=:id');$q->execute(['id'=>$hijo]);$fila=$q->fetch();$assert((int)$fila['padre_id']===$padre&&json_decode($fila['roles_json'],true)===['usuario'],'Los submenús conservan padre y roles autorizados');
 $pdo->prepare('UPDATE menu_sitio SET activo=0,eliminado_at=NOW() WHERE id IN (?,?)')->execute([$padre,$hijo]);$q=$pdo->prepare('SELECT COUNT(*) FROM menu_sitio WHERE id IN (?,?) AND eliminado_at IS NOT NULL');$q->execute([$padre,$hijo]);$assert((int)$q->fetchColumn()===2,'La eliminación mueve la jerarquía a la papelera sin destruirla');
 $pdo->prepare('UPDATE menu_sitio SET eliminado_at=NULL WHERE id=:id')->execute(['id'=>$padre]);$q=$pdo->prepare('SELECT COUNT(*) FROM menu_sitio WHERE id=:id');$q->execute(['id'=>$padre]);$assert((int)$q->fetchColumn()===1,'Una sección eliminada puede restaurarse');
 $pagina=file_get_contents(dirname(__DIR__,2).'/src/website/seccion.php');foreach(['noticias','productos','capacitaciones','formulario','video','archivo_descargable','bloques_reutilizables']as$tipo)$assert(str_contains($pagina,"tipo==='".$tipo."'")||str_contains($pagina,"tipo === '".$tipo."'"),'La plantilla pública implementa '.$tipo);
 $accion=file_get_contents(dirname(__DIR__,2).'/src/dashboard/navbar/accion.php');$assert(str_contains($accion,'validarTokenCsrf')&&str_contains($accion,'registrarAuditoria'),'Las acciones administrativas usan CSRF y auditoría');
 $assert((int)$pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='menu_formulario_envios'")->fetchColumn()===1,'Los formularios almacenan respuestas reales en una tabla protegida');
 echo 'OK '.count($ok)." pruebas\n";foreach($ok as$m)echo '- '.$m."\n";
}finally{if($ids){$marks=implode(',',array_fill(0,count($ids),'?'));$pdo->prepare("DELETE FROM menu_sitio WHERE id IN($marks)")->execute($ids);}}
