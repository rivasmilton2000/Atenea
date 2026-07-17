<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/cms.php';

$pdo=obtenerConexion();
$id=cmsId($_GET['id']??$_POST['id']??0);
$preSeccion=cmsId($_GET['seccion_id']??0);
$f=['id'=>0,'seccion_id'=>$preSeccion,'titulo'=>'','subtitulo'=>'','tipo'=>'','nivel'=>'','precio'=>null,'duracion'=>'','instructor'=>'','descripcion'=>'','imagen'=>'','icono'=>'','enlace'=>'','texto_boton'=>'','activo'=>1,'orden'=>0];
if($id){
    $q=$pdo->prepare('SELECT * FROM elementos_seccion WHERE id=:id');
    $q->execute(['id'=>$id]);
    $f=$q->fetch()?:$f;
    if(!$f['id']){cmsFlash('error','El elemento no existe.');header('Location: index.php');exit;}
}
$secciones=$pdo->query('SELECT id,clave,nombre FROM secciones ORDER BY orden,id')->fetchAll();
$errores=[];

if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    if(!validarTokenCsrf(isset($_POST['csrf_token'])?(string)$_POST['csrf_token']:null))$errores[]='El token de seguridad no es válido.';
    $seccionId=cmsId($_POST['seccion_id']??0);
    $titulo=trim((string)($_POST['titulo']??''));
    $subtitulo=trim((string)($_POST['subtitulo']??''));
    $tipo=trim((string)($_POST['tipo']??''));
    $nivel=trim((string)($_POST['nivel']??''));
    $precioTexto=trim((string)($_POST['precio']??''));
    $precio=$precioTexto===''?null:filter_var($precioTexto,FILTER_VALIDATE_FLOAT);
    $duracion=trim((string)($_POST['duracion']??''));
    $instructor=trim((string)($_POST['instructor']??''));
    $descripcion=trim((string)($_POST['descripcion']??''));
    $icono=trim((string)($_POST['icono']??''));
    $enlace=trim((string)($_POST['enlace']??''));
    $textoBoton=trim((string)($_POST['texto_boton']??''));
    $orden=(int)($_POST['orden']??0);
    $activo=isset($_POST['activo'])?1:0;
    if(!$seccionId)$errores[]='Selecciona una sección.';
    if($titulo===''||mb_strlen($titulo)>255)$errores[]='El título es obligatorio y admite hasta 255 caracteres.';
    if(mb_strlen($subtitulo)>255||mb_strlen($descripcion)>5000||mb_strlen($icono)>100||mb_strlen($textoBoton)>100||mb_strlen($tipo)>80||mb_strlen($nivel)>80||mb_strlen($duracion)>120||mb_strlen($instructor)>180)$errores[]='Uno de los textos supera la longitud permitida.';
    if($precioTexto!==''&&($precio===false||(float)$precio<0||(float)$precio>99999999.99))$errores[]='El precio debe ser un número válido mayor o igual a cero.';
    if($icono!==''&&!preg_match('/^bi-[a-z0-9-]+$/',$icono))$errores[]='El icono debe ser una clase Bootstrap Icons como bi-star.';
    if(!cmsUrlValida($enlace))$errores[]='El enlace no es válido.';
    $imagen=(string)$f['imagen'];
    $nueva=null;
    if(!$errores){
        try{$nueva=cmsSubirImagen('imagen');if($nueva)$imagen=$nueva;}catch(RuntimeException $e){$errores[]=$e->getMessage();}
    }
    if(!$errores){
        try{
            $pdo->beginTransaction();
            if($activo)cmsValidarLimiteAreas($pdo,$seccionId,$id);
            $datos=['seccion_id'=>$seccionId,'titulo'=>$titulo,'subtitulo'=>$subtitulo?:null,'tipo'=>$tipo?:null,'nivel'=>$nivel?:null,'precio'=>$precio===false?null:$precio,'duracion'=>$duracion?:null,'instructor'=>$instructor?:null,'descripcion'=>$descripcion?:null,'imagen'=>$imagen?:null,'icono'=>$icono?:null,'enlace'=>$enlace?:null,'texto_boton'=>$textoBoton?:null,'activo'=>$activo,'orden'=>$orden];
            if($id){$datos['id']=$id;$q=$pdo->prepare('UPDATE elementos_seccion SET seccion_id=:seccion_id,titulo=:titulo,subtitulo=:subtitulo,tipo=:tipo,nivel=:nivel,precio=:precio,duracion=:duracion,instructor=:instructor,descripcion=:descripcion,imagen=:imagen,icono=:icono,enlace=:enlace,texto_boton=:texto_boton,activo=:activo,orden=:orden WHERE id=:id');}
            else{$q=$pdo->prepare('INSERT INTO elementos_seccion(seccion_id,titulo,subtitulo,tipo,nivel,precio,duracion,instructor,descripcion,imagen,icono,enlace,texto_boton,activo,orden) VALUES(:seccion_id,:titulo,:subtitulo,:tipo,:nivel,:precio,:duracion,:instructor,:descripcion,:imagen,:icono,:enlace,:texto_boton,:activo,:orden)');}
            $q->execute($datos);
            $pdo->commit();
            if($id&&$imagen!==$f['imagen'])cmsEliminarImagenSiNoSeUsa($f['imagen']);
            cmsFlash('exito','Elemento guardado correctamente.');
            header('Location: index.php?seccion_id='.$seccionId);exit;
        }catch(Throwable $e){
            if($pdo->inTransaction())$pdo->rollBack();
            if($nueva)cmsEliminarImagenSiNoSeUsa($nueva);
            error_log('Elemento CMS: '.$e->getMessage());
            $errores[]=$e instanceof DomainException?$e->getMessage():'No fue posible guardar el elemento.';
        }
    }
    $f=array_merge($f,['seccion_id'=>$seccionId,'titulo'=>$titulo,'subtitulo'=>$subtitulo,'tipo'=>$tipo,'nivel'=>$nivel,'precio'=>$precioTexto,'duracion'=>$duracion,'instructor'=>$instructor,'descripcion'=>$descripcion,'imagen'=>$imagen,'icono'=>$icono,'enlace'=>$enlace,'texto_boton'=>$textoBoton,'activo'=>$activo,'orden'=>$orden]);
}
cmsCabecera($id?'Editar elemento':'Agregar elemento','elementos/index.php');
?>
<?php foreach($errores as $error):?><div class="alert alert-danger"><?=atenea_e($error)?></div><?php endforeach;?>
<form method="post" enctype="multipart/form-data" class="card card-rounded"><div class="card-header bg-white"><h2 class="card-title mb-1">Contenido del elemento</h2><p class="text-muted mb-0">Las áreas admiten un máximo de cuatro elementos activos.</p></div><div class="card-body row g-3"><input type="hidden" name="csrf_token" value="<?=atenea_e(obtenerTokenCsrf())?>"><input type="hidden" name="id" value="<?=$id?>">
<div class="col-md-6"><label class="form-label" for="seccion_id">Sección *</label><select class="form-select" id="seccion_id" name="seccion_id" required><option value="">Seleccionar</option><?php foreach($secciones as $s):?><option value="<?=$s['id']?>" data-clave="<?=atenea_e($s['clave'])?>" <?=$f['seccion_id']==$s['id']?'selected':''?>><?=atenea_e($s['nombre'])?></option><?php endforeach;?></select></div><div class="col-md-6"><label class="form-label" for="titulo">Título *</label><input class="form-control" id="titulo" name="titulo" maxlength="255" value="<?=atenea_e((string)$f['titulo'])?>" required></div>
<div class="col-md-6"><label class="form-label" for="subtitulo">Subtítulo o categoría</label><input class="form-control" id="subtitulo" name="subtitulo" maxlength="255" value="<?=atenea_e((string)$f['subtitulo'])?>"></div><div class="col-md-6"><label class="form-label" for="icono">Icono Bootstrap</label><input class="form-control" id="icono" name="icono" maxlength="100" value="<?=atenea_e((string)$f['icono'])?>" placeholder="bi-star"></div>
<div class="col-12 training-fields"><div class="row g-3"><div class="col-md-3"><label class="form-label" for="tipo">Tipo</label><input class="form-control" id="tipo" name="tipo" maxlength="80" value="<?=atenea_e((string)$f['tipo'])?>"></div><div class="col-md-3"><label class="form-label" for="nivel">Nivel</label><input class="form-control" id="nivel" name="nivel" maxlength="80" value="<?=atenea_e((string)$f['nivel'])?>"></div><div class="col-md-3"><label class="form-label" for="precio">Precio</label><input class="form-control" id="precio" type="number" min="0" step="0.01" name="precio" value="<?=atenea_e((string)$f['precio'])?>"></div><div class="col-md-3"><label class="form-label" for="duracion">Duración</label><input class="form-control" id="duracion" name="duracion" maxlength="120" value="<?=atenea_e((string)$f['duracion'])?>"></div><div class="col-12"><label class="form-label" for="instructor">Instructor</label><input class="form-control" id="instructor" name="instructor" maxlength="180" value="<?=atenea_e((string)$f['instructor'])?>"></div></div></div>
<div class="col-12"><label class="form-label" for="descripcion">Descripción</label><textarea class="form-control" id="descripcion" rows="5" maxlength="5000" name="descripcion"><?=atenea_e((string)$f['descripcion'])?></textarea></div>
<div class="col-md-6"><label class="form-label" for="enlace">Enlace</label><input class="form-control" id="enlace" name="enlace" maxlength="500" value="<?=atenea_e((string)$f['enlace'])?>"></div><div class="col-md-6"><label class="form-label" for="texto_boton">Texto del botón</label><input class="form-control" id="texto_boton" name="texto_boton" maxlength="100" value="<?=atenea_e((string)$f['texto_boton'])?>"></div>
<div class="col-md-6"><label class="form-label" for="imagen">Imagen JPG, PNG o WEBP (máximo 5 MB)</label><input class="form-control" id="imagen" type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp"></div><div class="col-md-3"><label class="form-label" for="orden">Orden</label><input class="form-control" id="orden" type="number" name="orden" value="<?=(int)$f['orden']?>"></div><div class="col-md-3 d-flex align-items-end"><div class="form-check form-switch mb-2"><input class="form-check-input" id="activo" type="checkbox" name="activo" <?=$f['activo']?'checked':''?>><label class="form-check-label" for="activo">Activo</label></div></div>
<?php if($f['imagen']):?><div class="col-12"><img class="preview-img" src="<?=rutaImagenContenido($f['imagen'])?>" alt="Imagen actual"></div><?php endif;?><div class="col-12"><a class="btn btn-light" href="index.php">Volver</a> <button class="btn btn-primary text-white"><i class="mdi mdi-content-save me-1"></i>Guardar cambios</button></div></div></form>
<script>const sectionSelect=document.getElementById('seccion_id');const syncFields=()=>{const key=sectionSelect.selectedOptions[0]?.dataset.clave||'';document.querySelector('.training-fields').hidden=key!=='capacitaciones';};sectionSelect.addEventListener('change',syncFields);syncFields();</script>
<?php cmsPie(); ?>
