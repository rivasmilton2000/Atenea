<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/auth.php';
$pdo=obtenerConexion();$accion=$argv[1]??'';$correos=['c7e12.admin@example.invalid','c7e12.docente@example.invalid','c7e12.estudiante@example.invalid'];
$limpiar=static function()use($pdo,$correos):void{
    $marcas=implode(',',array_fill(0,count($correos),'?'));$q=$pdo->prepare("SELECT id,foto FROM usuarios WHERE correo IN($marcas)");$q->execute($correos);$usuarios=$q->fetchAll();$ids=array_map('intval',array_column($usuarios,'id'));
    foreach($usuarios as$u){$foto=(string)($u['foto']??'');if($foto!==''&&!str_starts_with($foto,'http')){$ruta=realpath(dirname(__DIR__,2).DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$foto));$base=realpath(dirname(__DIR__,2).'/uploads/perfiles');if($ruta&&$base&&str_starts_with(strtolower($ruta),strtolower($base.DIRECTORY_SEPARATOR))&&is_file($ruta))unlink($ruta);}}
    if(!$ids)return;$in=implode(',',array_fill(0,count($ids),'?'));$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    try{
        foreach($pdo->query("SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME='usuarios' AND TABLE_NAME<>'usuarios'")->fetchAll()as$ref){$tabla=str_replace('`','``',(string)$ref['TABLE_NAME']);$columna=str_replace('`','``',(string)$ref['COLUMN_NAME']);$pdo->prepare("DELETE FROM `$tabla` WHERE `$columna` IN($in)")->execute($ids);}
        $pdo->prepare("DELETE FROM usuarios WHERE id IN($in)")->execute($ids);
    }finally{$pdo->exec('SET FOREIGN_KEY_CHECKS=1');}
};
if($accion==='cleanup'){$limpiar();echo "OK cleanup\n";exit;}
if($accion==='status'){$marcas=implode(',',array_fill(0,count($correos),'?'));$q=$pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE correo IN($marcas)");$q->execute($correos);echo 'TEMP_USERS='.(int)$q->fetchColumn()."\n";exit;}
if($accion!=='setup'){fwrite(STDERR,"Uso: setup|cleanup|status\n");exit(2);}
$limpiar();$password=password_hash('PruebaEtapa12!2026',PASSWORD_DEFAULT);
$q=$pdo->prepare("INSERT INTO usuarios(nombre,apellido,nombre_usuario,correo,password,proveedor,email_verificado,rol,es_superadmin,estado,perfil_estado,terminos_aceptados_at) VALUES(:nombre,'Etapa 12',:usuario,:correo,:password,'local',1,:rol,:super,'activo','completo',NOW())");
$q->execute(['nombre'=>'Administración','usuario'=>'c7e12.admin','correo'=>$correos[0],'password'=>$password,'rol'=>'admin','super'=>1]);
$q->execute(['nombre'=>'Docente','usuario'=>'c7e12.docente','correo'=>$correos[1],'password'=>$password,'rol'=>'docente','super'=>0]);
echo json_encode(['admin'=>$correos[0],'docente'=>$correos[1]],JSON_UNESCAPED_SLASHES)."\n";
