<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/includes/permissions.php';
require_once dirname(__DIR__,2).'/includes/perfil_usuario.php';

$pdo=obtenerConexion();$ok=[];$assert=function(bool$condicion,string$mensaje)use(&$ok):void{if(!$condicion)throw new RuntimeException($mensaje);$ok[]=$mensaje;};$sesion=$_SESSION??[];
try{
    $tipo=(string)$pdo->query("SHOW COLUMNS FROM usuarios LIKE 'rol'")->fetch()['Type'];$assert(str_contains($tipo,"'administrador_docente'"),'El ENUM reconoce administrador_docente');
    $assert(in_array('administrador_docente',rolesAdministrablesAtenea(),true)&&!in_array('administracion_docente',rolesAdministrablesAtenea(),true),'El formulario asigna solo el nombre canónico');
    $pdo->beginTransaction();$tag='etapa2_'.bin2hex(random_bytes(5));$q=$pdo->prepare("INSERT INTO usuarios(nombre,apellido,correo,password,rol,estado,email_verificado,perfil_estado,session_version) VALUES('Admin','Docente',:correo,:password,'administrador_docente','activo',1,'completo',1)");$q->execute(['correo'=>$tag.'@example.invalid','password'=>password_hash('Temporal!2026',PASSWORD_DEFAULT)]);$id=(int)$pdo->lastInsertId();$usuario=['id'=>$id,'rol'=>'administrador_docente'];
    $assert(usuarioTienePermiso('users.view',$usuario)&&usuarioTienePermiso('orders.view',$usuario)&&usuarioTienePermiso('orders.manage',$usuario),'El rol ve usuarios y gestiona seguimiento de pedidos');
    $assert(!usuarioTienePermiso('users.edit',$usuario)&&!usuarioTienePermiso('users.change_role',$usuario)&&!usuarioTienePermiso('dte.configure',$usuario),'El rol no cambia usuarios, roles ni configuración DTE');
    $assert(!usuarioTienePermiso('backups.manage',$usuario)&&!usuarioTienePermiso('mail.manage',$usuario),'El rol no accede a copias, correo o credenciales críticas');
    $assert(usuarioTienePermiso('academic.courses.view',$usuario)&&usuarioTienePermiso('academic.students.view',$usuario),'El rol conserva funciones docentes y consulta académica');
    $_SESSION['usuario_id']=$id;$_SESSION['usuario_rol']='administrador_docente';$_SESSION['hybrid_mode']='admin';$assert(modoHibridoActualAtenea()==='admin'&&str_contains(rutaPanelPorRol('administrador_docente'),'administador_docente/dashboard'),'Login y redirección reconocen el rol');
    $assert(permisoRutaAdministrativaHibridaAtenea('/Atenea/src/dashboard/pedidos/estado.php','POST')==='orders.manage','El middleware autoriza solo la gestión logística');
    $assert(permisoRutaAdministrativaHibridaAtenea('/Atenea/src/dashboard/configuracion/index.php','GET')===null,'El middleware bloquea configuración crítica');
    $estadoCodigo=(string)file_get_contents(dirname(__DIR__,2).'/src/dashboard/pedidos/estado.php');$assert(str_contains($estadoCodigo,"\$permitidos=['en_proceso_envio','saliendo_almacen','entregado']")&&!str_contains($estadoCodigo,"payment_status='paid'"),'El endpoint administrativo no permite completar pagos manualmente');
    $usuariosCodigo=(string)file_get_contents(dirname(__DIR__,2).'/src/dashboard/usuarios/index.php');$assert(str_contains($usuariosCodigo,'telefono LIKE')&&str_contains($usuariosCodigo,'dui LIKE')&&str_contains($usuariosCodigo,'FROM usuarios'),'El listado consulta todos los usuarios y busca teléfono/DUI con paginación');
    $accionCodigo=(string)file_get_contents(dirname(__DIR__,2).'/src/dashboard/usuarios/accion.php');$assert(str_contains($accionCodigo,"es_superadmin=IF(:rol_super='admin',es_superadmin,0)")&&str_contains($accionCodigo,"VALUES(:nombre,:apellido,:correo,:password,:rol,0"),'Creación y cambios de rol no conceden SuperAdmin desde el frontend');
    $assert(usuarioTienePermiso('users.change_role',['id'=>0,'rol'=>'admin'])&&usuarioTienePermiso('dte.configure',['id'=>0,'rol'=>'admin']),'Administrador conserva gestion completa');
    $assert(!usuarioTienePermiso('orders.manage',['id'=>0,'rol'=>'docente'])&&!usuarioTienePermiso('users.view',['id'=>0,'rol'=>'docente']),'Docente no recibe permisos administrativos');
    $assert(!usuarioTienePermiso('orders.manage',['id'=>0,'rol'=>'usuario'])&&!usuarioTienePermiso('users.view',['id'=>0,'rol'=>'usuario']),'Usuario no recibe permisos administrativos');
    $pdo->rollBack();
}finally{if($pdo->inTransaction())$pdo->rollBack();$_SESSION=$sesion;}
echo 'OK '.count($ok)." pruebas\n- ".implode("\n- ",$ok)."\n";
