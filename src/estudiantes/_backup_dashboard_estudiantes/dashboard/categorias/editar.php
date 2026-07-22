<?php
declare(strict_types=1);
require_once __DIR__ . '/_categorias.php';
$pdo = obtenerConexion();
$id = cmsId($_GET['id'] ?? $_POST['id'] ?? 0);
$categoria = ['nombre'=>'','slug'=>'','descripcion'=>'','imagen'=>'','activo'=>1];
if ($id) {
    $consulta=$pdo->prepare('SELECT * FROM categorias_producto WHERE id=:id AND eliminado_at IS NULL LIMIT 1');
    $consulta->execute(['id'=>$id]);
    $categoria=$consulta->fetch() ?: [];
    if(!$categoria){cmsFlash('error','Categoría no encontrada.');header('Location:index.php');exit;}
}
$errores=[];
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    $nombre=trim(strip_tags((string)($_POST['nombre']??'')));
    $descripcion=trim(strip_tags((string)($_POST['descripcion']??'')));
    $activo=isset($_POST['activo'])?1:0;
    $regenerar=!$id||isset($_POST['regenerar_slug']);
    if(!validarTokenCsrf(isset($_POST['csrf_token'])?(string)$_POST['csrf_token']:null))$errores['general']='La solicitud expiró. Intenta nuevamente.';
    if($nombre===''||mb_strlen($nombre)>120)$errores['nombre']='Ingresa un nombre de hasta 120 caracteres.';
    if(mb_strlen($descripcion)>500)$errores['descripcion']='La descripción no puede superar 500 caracteres.';
    if(!$errores&&categoriaNombreDuplicado($pdo,$nombre,$id))$errores['nombre']='Ya existe una categoría con ese nombre.';
    $imagen=(string)($categoria['imagen']??'');$nuevaImagen=null;
    if(!$errores){try{$nuevaImagen=cmsSubirImagen('imagen');if($nuevaImagen)$imagen=$nuevaImagen;}catch(RuntimeException$e){$errores['imagen']=$e->getMessage();}}
    if(!$errores){
        try{
            $pdo->beginTransaction();
            $slug=$regenerar?categoriaSlugUnico($pdo,$nombre,$id):(string)$categoria['slug'];
            if($id){$consulta=$pdo->prepare('UPDATE categorias_producto SET nombre=:nombre,slug=:slug,descripcion=:descripcion,imagen=:imagen,activo=:activo,actualizado_por=:usuario WHERE id=:id AND eliminado_at IS NULL');$consulta->execute(['nombre'=>$nombre,'slug'=>$slug,'descripcion'=>$descripcion?:null,'imagen'=>$imagen?:null,'activo'=>$activo,'usuario'=>$_SESSION['usuario_id'],'id'=>$id]);}
            else{$consulta=$pdo->prepare('INSERT INTO categorias_producto(nombre,slug,descripcion,imagen,activo,creado_por,actualizado_por) VALUES(:nombre,:slug,:descripcion,:imagen,:activo,:creado,:actualizado)');$consulta->execute(['nombre'=>$nombre,'slug'=>$slug,'descripcion'=>$descripcion?:null,'imagen'=>$imagen?:null,'activo'=>$activo,'creado'=>$_SESSION['usuario_id'],'actualizado'=>$_SESSION['usuario_id']]);$id=(int)$pdo->lastInsertId();}
            $pdo->commit();
            if($nuevaImagen&&!empty($categoria['imagen']))cmsEliminarImagenSiNoSeUsa((string)$categoria['imagen']);
            cmsFlash('exito','Categoría guardada correctamente.');header('Location:index.php');exit;
        }catch(Throwable$e){if($pdo->inTransaction())$pdo->rollBack();if($nuevaImagen)cmsEliminarImagenSiNoSeUsa($nuevaImagen);error_log('Guardar categoría Atenea: '.$e->getMessage());$errores['general']='No fue posible guardar la categoría. Verifica que el nombre y el slug sean únicos.';}
    }
    $categoria=array_merge($categoria,['nombre'=>$nombre,'descripcion'=>$descripcion,'imagen'=>$imagen,'activo'=>$activo]);
}
cmsCabecera($id?'Editar categoría':'Nueva categoría','categorias/editar.php','Define la información visible y el estado de la categoría de productos.');
?>
<?php if(isset($errores['general'])):?><div class="alert alert-danger"><?=atenea_e($errores['general'])?></div><?php endif;?>
<form method="post" enctype="multipart/form-data" class="card card-rounded"><div class="card-body"><input type="hidden" name="csrf_token" value="<?=atenea_e(obtenerTokenCsrf())?>"><input type="hidden" name="id" value="<?=$id?>"><div class="row g-3">
  <div class="col-md-8"><label class="form-label" for="nombreCategoria">Nombre *</label><input class="form-control" id="nombreCategoria" name="nombre" maxlength="120" value="<?=atenea_e((string)$categoria['nombre'])?>" required><?php if(isset($errores['nombre'])):?><div class="invalid-feedback d-block"><?=atenea_e($errores['nombre'])?></div><?php endif;?></div>
  <div class="col-md-4"><label class="form-label">Slug</label><input class="form-control" value="<?=atenea_e((string)($categoria['slug']?:'Se generará al guardar'))?>" readonly><?php if($id):?><div class="form-check mt-2"><input class="form-check-input" type="checkbox" id="regenerarSlug" name="regenerar_slug"><label class="form-check-label" for="regenerarSlug">Regenerar desde el nombre</label></div><small class="text-muted">Déjalo sin marcar para conservar las URL existentes.</small><?php endif;?></div>
  <div class="col-12"><label class="form-label" for="descripcionCategoria">Descripción</label><textarea class="form-control" id="descripcionCategoria" name="descripcion" rows="4" maxlength="500"><?=atenea_e((string)$categoria['descripcion'])?></textarea><?php if(isset($errores['descripcion'])):?><div class="invalid-feedback d-block"><?=atenea_e($errores['descripcion'])?></div><?php endif;?></div>
  <div class="col-md-8"><label class="form-label" for="imagenCategoria">Imagen opcional</label><input class="form-control" id="imagenCategoria" type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><div class="form-text">JPG, PNG o WEBP; máximo 5 MB.</div><?php if(isset($errores['imagen'])):?><div class="invalid-feedback d-block"><?=atenea_e($errores['imagen'])?></div><?php endif;?></div>
  <div class="col-md-4 d-flex align-items-end"><div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" id="categoriaActiva" name="activo" <?=$categoria['activo']?'checked':''?>><label class="form-check-label" for="categoriaActiva">Categoría activa</label></div></div>
  <?php if(!empty($categoria['imagen'])):?><div class="col-12"><img src="<?=rutaImagenContenido((string)$categoria['imagen'],'img/atenea-logo.png')?>" class="rounded" style="width:140px;height:100px;object-fit:cover" alt="Imagen actual de la categoría"></div><?php endif;?>
  <div class="col-12 d-flex gap-2"><a class="btn btn-light" href="index.php">Cancelar</a><button class="btn btn-primary text-white" type="submit">Guardar categoría</button></div>
</div></div></form>
<?php cmsPie(); ?>
