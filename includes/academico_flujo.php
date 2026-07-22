<?php
declare(strict_types=1);

require_once __DIR__.'/auth.php';
require_once __DIR__.'/docente_academico.php';
require_once __DIR__.'/notificaciones.php';
require_once __DIR__.'/audit.php';

function almacenamientoAcademicoBase(): string
{
    return rtrim(entornoAtenea('ACADEMIC_STORAGE_PATH', dirname(dirname(ATENEA_ROOT)).'/atenea-private/academico'),'/\\');
}

function rutaPrivadaAcademica(string $relpath): ?string
{
    if($relpath===''||str_contains($relpath,'..')||preg_match('/[^A-Za-z0-9_\/.\-]/',$relpath))return null;
    $base=almacenamientoAcademicoBase();$root=realpath($base);$ruta=realpath($base.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$relpath));
    return $root&&$ruta&&str_starts_with(strtolower($ruta),strtolower($root.DIRECTORY_SEPARATOR))&&is_file($ruta)?$ruta:null;
}

function bytesConfiguracionPhpAtenea(string $valor): int
{
    $valor=trim($valor);if($valor===''||$valor==='-1')return PHP_INT_MAX;
    if(!preg_match('/^(\d+)([KMG])?$/i',$valor,$partes))return 0;
    $bytes=(int)$partes[1];$unidad=strtoupper((string)($partes[2]??''));
    return match($unidad){'G'=>$bytes*1024*1024*1024,'M'=>$bytes*1024*1024,'K'=>$bytes*1024,default=>$bytes};
}

function limiteArchivoAcademicoMb(string $categoria): int
{
    $configurado=match ($categoria) {
        'video' => max(1, (int) entornoAtenea('ACADEMIC_VIDEO_MAX_MB', '250')),
        'contenido' => max(1, (int) entornoAtenea('ACADEMIC_DOCUMENT_MAX_MB', '20')),
        default => max(1, (int) entornoAtenea('ACADEMIC_EVIDENCE_MAX_MB', '10')),
    };
    $upload=(int)floor(bytesConfiguracionPhpAtenea((string)ini_get('upload_max_filesize'))/1024/1024);
    $post=(int)floor(bytesConfiguracionPhpAtenea((string)ini_get('post_max_size'))/1024/1024)-1;
    $limites=array_filter([$configurado,$upload,$post],static fn(int $valor):bool=>$valor>0);
    return max(1,min($limites?:[$configurado]));
}

function guardarArchivoAcademico(string $campo,string $carpeta,string $categoria): ?array
{
    if(!isset($_FILES[$campo])||($_FILES[$campo]['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)return null;
    $f=$_FILES[$campo];if(($f['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK)throw new DomainException('No fue posible recibir el archivo.');
    $mapas=[
      'video'=>['max'=>limiteArchivoAcademicoMb('video')*1024*1024,'mimes'=>['video/mp4'=>'mp4','video/webm'=>'webm','video/ogg'=>'ogv']],
      'contenido'=>['max'=>limiteArchivoAcademicoMb('contenido')*1024*1024,'mimes'=>['application/pdf'=>'pdf','application/msword'=>'doc','application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx','application/vnd.ms-powerpoint'=>'ppt','application/vnd.openxmlformats-officedocument.presentationml.presentation'=>'pptx','application/vnd.ms-excel'=>'xls','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'xlsx','image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','text/plain'=>'txt']],
      'evidencia'=>['max'=>limiteArchivoAcademicoMb('evidencia')*1024*1024,'mimes'=>['application/pdf'=>'pdf','application/msword'=>'doc','application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx','image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp']],
    ];$regla=$mapas[$categoria]??null;if(!$regla||(int)$f['size']<1||(int)$f['size']>$regla['max'])throw new DomainException('El archivo está vacío o supera el límite configurado de '.limiteArchivoAcademicoMb($categoria).' MB.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);if(!isset($regla['mimes'][$mime]))throw new DomainException('El tipo MIME real del archivo no está permitido.');$ext=strtolower(pathinfo((string)$f['name'],PATHINFO_EXTENSION));$esperada=$regla['mimes'][$mime];if(!in_array($ext,$esperada==='jpg'?['jpg','jpeg']:[$esperada],true)||preg_match('/\.(?:php\d*|phtml|phar|html?|svg|exe|sh)(?:\.|$)/i',(string)$f['name']))throw new DomainException('La extensión no coincide con el archivo o no es segura.');
    $base=almacenamientoAcademicoBase();$dir=$base.DIRECTORY_SEPARATOR.$carpeta;if(!is_dir($dir)&&!mkdir($dir,0700,true)&&!is_dir($dir))throw new RuntimeException('No se pudo preparar el almacenamiento privado.');$nombre=bin2hex(random_bytes(24)).'.'.$esperada;if(!move_uploaded_file($f['tmp_name'],$dir.DIRECTORY_SEPARATOR.$nombre))throw new RuntimeException('No se pudo guardar el archivo privado.');
    return ['relpath'=>$carpeta.'/'.$nombre,'nombre'=>mb_substr(basename((string)$f['name']),0,190),'mime'=>$mime,'tamano'=>(int)$f['size']];
}

function urlVideoAcademicoValida(?string $url): bool
{
    $url=trim((string)$url);if($url==='')return true;if(!filter_var($url,FILTER_VALIDATE_URL)||strtolower((string)parse_url($url,PHP_URL_SCHEME))!=='https')return false;$host=strtolower((string)parse_url($url,PHP_URL_HOST));return in_array($host,['youtube.com','www.youtube.com','youtu.be','vimeo.com','www.vimeo.com','player.vimeo.com'],true);
}

function urlContenidoAcademicoValida(?string $url,string $tipo): bool
{
    $url=trim((string)$url);if($url==='')return true;if($tipo==='video')return urlVideoAcademicoValida($url);
    return $tipo==='enlace'&&filter_var($url,FILTER_VALIDATE_URL)!==false&&strtolower((string)parse_url($url,PHP_URL_SCHEME))==='https'&&!in_array(strtolower((string)parse_url($url,PHP_URL_HOST)),['localhost','127.0.0.1','::1'],true);
}

function docentePoseeSeccion(PDO $pdo,int $docenteId,int $seccionId): bool
{
    $q=$pdo->prepare("SELECT 1 FROM capacitacion_secciones s JOIN docentes_asignaturas da ON da.asignatura_id=s.asignatura_id AND da.docente_id=s.docente_id AND da.estado='activo' WHERE s.id=:s AND s.docente_id=:d AND s.estado IN('abierta','cerrada')");$q->execute(['s'=>$seccionId,'d'=>$docenteId]);return(bool)$q->fetchColumn();
}

function inscripcionEstudianteSeccion(PDO $pdo,int $estudianteId,int $seccionId,bool $bloquear=false): ?array
{
    $sql="SELECT i.*,a.nombre capacitacion,a.duracion,a.certificado_disponible,s.codigo seccion FROM inscripciones_capacitacion i JOIN asignaturas a ON a.id=i.asignatura_id JOIN capacitacion_secciones s ON s.id=i.seccion_id WHERE i.usuario_id=:u AND i.seccion_id=:s AND i.estado IN('inscrito','finalizado')".($bloquear?' FOR UPDATE':'');$q=$pdo->prepare($sql);$q->execute(['u'=>$estudianteId,'s'=>$seccionId]);return$q->fetch()?:null;
}

function progresoInscripcion(PDO $pdo,int $inscripcionId): array
{
    $q=$pdo->prepare('SELECT * FROM inscripciones_capacitacion WHERE id=:id');$q->execute(['id'=>$inscripcionId]);$i=$q->fetch();if(!$i)throw new DomainException('La inscripción no existe.');
    $q=$pdo->prepare("SELECT c.id,c.tipo,c.obligatorio,c.peso_progreso,p.visto_at,p.completado_at,(SELECT ec.estado FROM entregas_contenido ec WHERE ec.contenido_id=c.id AND ec.estudiante_id=:u ORDER BY ec.intento DESC LIMIT 1) entrega_estado,(SELECT ec.nota FROM entregas_contenido ec WHERE ec.contenido_id=c.id AND ec.estudiante_id=:u2 AND ec.nota IS NOT NULL ORDER BY ec.intento DESC LIMIT 1) nota FROM contenidos c LEFT JOIN progreso_contenido p ON p.contenido_id=c.id AND p.inscripcion_id=:i WHERE c.seccion_id=:s AND c.activo=1 AND c.estado='activo' AND c.eliminado_at IS NULL AND (c.fecha_publicacion IS NULL OR c.fecha_publicacion<=NOW()) ORDER BY c.modulo,c.orden,c.id");$q->execute(['u'=>$i['usuario_id'],'u2'=>$i['usuario_id'],'i'=>$inscripcionId,'s'=>$i['seccion_id']]);$contenidos=$q->fetchAll();
    $total=0.0;$logrado=0.0;$vistos=0;$pendientes=0;$aprobadas=0;$rechazadas=0;$notas=[];$obligatorios=0;
    foreach($contenidos as$c){if($c['visto_at'])$vistos++;$esEntrega=in_array($c['tipo'],['actividad','evaluacion'],true);$completo=$esEntrega?$c['entrega_estado']==='aprobada':!empty($c['completado_at']);if($c['entrega_estado']==='aprobada')$aprobadas++;if(in_array($c['entrega_estado'],['rechazada','requiere_correccion'],true))$rechazadas++;if($c['nota']!==null)$notas[]=(float)$c['nota'];if((int)$c['obligatorio']===1){$obligatorios++;$peso=(float)$c['peso_progreso']>0?(float)$c['peso_progreso']:1.0;$total+=$peso;if($completo)$logrado+=$peso;else$pendientes++;}}
    $q=$pdo->prepare('SELECT MAX(ultima_actividad_at) FROM progreso_contenido WHERE inscripcion_id=:i');$q->execute(['i'=>$inscripcionId]);$ultima=$q->fetchColumn()?:$i['updated_at'];
    return ['porcentaje'=>$total>0?round(min(100,($logrado/$total)*100),2):0.0,'contenidos_vistos'=>$vistos,'entregas_pendientes'=>$pendientes,'entregas_aprobadas'=>$aprobadas,'entregas_rechazadas'=>$rechazadas,'promedio'=>$notas?round(array_sum($notas)/count($notas),2):null,'ultima_actividad'=>$ultima,'obligatorios'=>$obligatorios,'elegible'=>$obligatorios>0&&$total>0&&$logrado>=$total&&$pendientes===0&&$rechazadas===0];
}

function generarCertificadoInscripcion(PDO $pdo,int $inscripcionId,?int $emisorId=null): array
{
    $pdo->beginTransaction();try{$q=$pdo->prepare("SELECT i.*,a.nombre capacitacion,a.duracion,a.certificado_disponible,u.nombre,u.apellido FROM inscripciones_capacitacion i JOIN asignaturas a ON a.id=i.asignatura_id JOIN usuarios u ON u.id=i.usuario_id WHERE i.id=:id FOR UPDATE");$q->execute(['id'=>$inscripcionId]);$i=$q->fetch();if(!$i)throw new DomainException('La inscripción no existe.');$q=$pdo->prepare('SELECT * FROM certificados_capacitacion WHERE inscripcion_id=:i');$q->execute(['i'=>$inscripcionId]);if($existente=$q->fetch()){$pdo->commit();return$existente;}if(!(int)$i['certificado_disponible'])throw new DomainException('Esta capacitación no ofrece certificado.');$progreso=progresoInscripcion($pdo,$inscripcionId);if(!$progreso['elegible']||$progreso['porcentaje']<100)throw new DomainException('Existen contenidos obligatorios pendientes, rechazados o sin aprobar.');
      $numero='ATN-'.date('Y').'-'.str_pad((string)$inscripcionId,8,'0',STR_PAD_LEFT).'-'.strtoupper(bin2hex(random_bytes(3)));$token=bin2hex(random_bytes(32));$plantilla='img/certificado/certificado sin nombre.png';$base=almacenamientoAcademicoBase();$dir=$base.DIRECTORY_SEPARATOR.'certificados';if(!is_dir($dir)&&!mkdir($dir,0700,true)&&!is_dir($dir))throw new RuntimeException('No se preparó la carpeta de certificados.');$rel='certificados/'.bin2hex(random_bytes(24)).'.pdf';$destino=$base.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$rel);$fecha=$i['finalizacion_confirmada_at']?date('d/m/Y',strtotime($i['finalizacion_confirmada_at'])):date('d/m/Y');$pdf=renderizarCertificadoPdf(trim($i['nombre'].' '.$i['apellido']),(string)$i['capacitacion'],(string)($i['duracion']?:''),$fecha,$numero,$token);if(file_put_contents($destino,$pdf,LOCK_EX)===false)throw new RuntimeException('No se pudo guardar el certificado.');$q=$pdo->prepare("INSERT INTO certificados_capacitacion(inscripcion_id,estudiante_id,asignatura_id,numero,token_verificacion,plantilla_relpath,pdf_relpath,finalizado_at,emitido_por) VALUES(:i,:e,:a,:n,:t,:p,:pdf,CURDATE(),:emisor)");$q->execute(['i'=>$inscripcionId,'e'=>$i['usuario_id'],'a'=>$i['asignatura_id'],'n'=>$numero,'t'=>$token,'p'=>$plantilla,'pdf'=>$rel,'emisor'=>$emisorId]);$id=(int)$pdo->lastInsertId();$pdo->prepare("UPDATE inscripciones_capacitacion SET estado='finalizado' WHERE id=:id")->execute(['id'=>$inscripcionId]);crearNotificacionAtenea(['usuario_id'=>(int)$i['usuario_id'],'tipo'=>'certificado_emitido','categoria'=>'academico','nivel'=>'exito','titulo'=>'Certificado disponible','descripcion'=>'Tu certificado de '.$i['capacitacion'].' ya está disponible.','url'=>atenea_url('src/estudiantes/certificados.php'),'idempotency_key'=>'certificado:'.$id],$pdo);$pdo->commit();$q=$pdo->prepare('SELECT * FROM certificados_capacitacion WHERE id=:id');$q->execute(['id'=>$id]);return$q->fetch();
    }catch(Throwable$e){if($pdo->inTransaction())$pdo->rollBack();if(isset($destino)&&is_file($destino))unlink($destino);throw$e;}
}

function renderizarCertificadoPdf(string $estudiante,string $capacitacion,string $duracion,string $fecha,string $numero,string $token): string
{
    require_once ATENEA_ROOT.'/vendor/autoload.php';
    $plantilla=ATENEA_ROOT.'/img/certificado/certificado sin nombre.png';
    if(!is_file($plantilla))throw new RuntimeException('La plantilla oficial del certificado no existe.');
    $png=(string)file_get_contents($plantilla);if(substr($png,1,3)!=='PNG')throw new RuntimeException('La plantilla oficial no es un PNG válido.');
    $ancho=unpack('N',substr($png,16,4))[1];$alto=unpack('N',substr($png,20,4))[1];$bit=ord($png[24]);$color=ord($png[25]);
    if($bit!==8||$color!==2)throw new RuntimeException('La plantilla debe ser PNG RGB de 8 bits.');
    $idat='';$pos=8;$largo=strlen($png);while($pos+12<=$largo){$n=unpack('N',substr($png,$pos,4))[1];$tipo=substr($png,$pos+4,4);if($tipo==='IDAT')$idat.=substr($png,$pos+8,$n);$pos+=12+$n;if($tipo==='IEND')break;}
    if($idat==='')throw new RuntimeException('La plantilla no contiene datos gráficos.');
    $svg=(new Endroid\QrCode\Builder\Builder())->writer(new Endroid\QrCode\Writer\SvgWriter())->data(atenea_url_absoluta('src/website/verificar-certificado.php?token='.$token))->size(130)->margin(2)->build()->getString();
    preg_match('/viewBox="0 0 ([0-9.]+) ([0-9.]+)"/',$svg,$vista);preg_match('/<rect id="block" width="([0-9.]+)" height="([0-9.]+)"/',$svg,$bloque);preg_match_all('/<use x="([0-9.]+)" y="([0-9.]+)"/',$svg,$usos,PREG_SET_ORDER);$vistaQr=(float)($vista[1]??134);$bloqueQr=(float)($bloque[1]??5);
    $pdfTexto=static function(string $texto):string{$convertido=iconv('UTF-8','Windows-1252//TRANSLIT',$texto);return str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$convertido===false?'':$convertido);};
    $centrar=static function(string $texto,float $y,float $tamano,string $fuente='F1',float $r=0.14,float $g=0.08,float $b=0.17)use($pdfTexto):string{$anchoTexto=max(1,mb_strlen($texto,'UTF-8'))*$tamano*.52;$x=max(130,(869-$anchoTexto)/2);return sprintf("BT /%s %.2F Tf %.3F %.3F %.3F rg %.2F %.2F Td (%s) Tj ET\n",$fuente,$tamano,$r,$g,$b,$x,$y,$pdfTexto($texto));};
    $lineas=[];$actual='';foreach(preg_split('/\s+/u',trim($capacitacion)) as$palabra){$candidato=trim($actual.' '.$palabra);if(mb_strlen($candidato)>42&&$actual!==''){$lineas[]=$actual;$actual=$palabra;}else$actual=$candidato;}if($actual!=='')$lineas[]=$actual;$lineas=array_slice($lineas,0,2);
    $contenido="q 869 0 0 651 0 0 cm /Im1 Do Q\n1 1 1 rg 125 211 620 205 re f\n";
    $tamNombre=max(16,min(27,590/(max(1,mb_strlen($estudiante,'UTF-8'))*.52)));$contenido.=$centrar($estudiante,375,$tamNombre,'F2',.09,.31,.21).$centrar('Por haber cumplido satisfactoriamente los requisitos de',346,12);
    $yCurso=count($lineas)>1?320:311;foreach($lineas as$linea){$contenido.=$centrar($linea,$yCurso,22,'F2',.85,.24,.09);$yCurso-=27;}
    $contenido.=$centrar('Finalizado el '.$fecha.($duracion!==''?' · Duración: '.$duracion:''),count($lineas)>1?258:278,11);
    $contenido.="1 1 1 rg 679 386 78 78 re f\n0 0 0 rg\n";$qrX=682.0;$qrY=389.0;$escala=72/$vistaQr;foreach($usos as$uso){$x=$qrX+(float)$uso[1]*$escala;$y=$qrY+72-((float)$uso[2]+$bloqueQr)*$escala;$lado=$bloqueQr*$escala;$contenido.=sprintf('%.3F %.3F %.3F %.3F re f'."\n",$x,$y,$lado,$lado);}
    $contenido.=sprintf("BT /F1 7 Tf 0.14 0.08 0.17 rg 688 374 Td (%s) Tj ET\n",$pdfTexto('Verificación pública')).sprintf("BT /F1 8 Tf 0.14 0.08 0.17 rg 122 88 Td (%s) Tj ET\n",$pdfTexto('Certificado '.$numero));
    $objetos=[];$objetos[1]='<< /Type /Catalog /Pages 2 0 R >>';$objetos[2]='<< /Type /Pages /Kids [3 0 R] /Count 1 >>';$objetos[3]='<< /Type /Page /Parent 2 0 R /MediaBox [0 0 869 651] /Resources << /XObject << /Im1 5 0 R >> /Font << /F1 6 0 R /F2 7 0 R >> >> /Contents 4 0 R >>';$objetos[4]='<< /Length '.strlen($contenido).' >>'."\nstream\n".$contenido."endstream";$objetos[5]='<< /Type /XObject /Subtype /Image /Width '.$ancho.' /Height '.$alto.' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /FlateDecode /DecodeParms << /Predictor 15 /Colors 3 /BitsPerComponent 8 /Columns '.$ancho.' >> /Length '.strlen($idat).' >>'."\nstream\n".$idat."\nendstream";$objetos[6]='<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';$objetos[7]='<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
    $pdf="%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";$offset=[0];foreach($objetos as$n=>$obj){$offset[$n]=strlen($pdf);$pdf.=$n." 0 obj\n".$obj."\nendobj\n";}$xref=strlen($pdf);$pdf.="xref\n0 8\n0000000000 65535 f \n";for($n=1;$n<=7;$n++)$pdf.=sprintf("%010d 00000 n \n",$offset[$n]);$pdf.="trailer\n<< /Size 8 /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";return$pdf;
}
