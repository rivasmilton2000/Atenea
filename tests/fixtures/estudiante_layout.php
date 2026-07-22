<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';

$pdo = obtenerConexion();
$accion = $argv[1] ?? '';
$correo = 'layout.estudiante@example.invalid';

$limpiar = static function () use ($pdo, $correo): void {
    $q = $pdo->prepare('SELECT id,foto FROM usuarios WHERE correo=:correo');
    $q->execute(['correo'=>$correo]);
    $usuario = $q->fetch();
    if (!$usuario) return;
    $id = (int)$usuario['id'];
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    try {
        foreach ($pdo->query("SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME='usuarios' AND TABLE_NAME<>'usuarios'")->fetchAll() as $referencia) {
            $tabla = str_replace('`', '``', (string)$referencia['TABLE_NAME']);
            $columna = str_replace('`', '``', (string)$referencia['COLUMN_NAME']);
            $pdo->prepare("DELETE FROM `{$tabla}` WHERE `{$columna}`=:id")->execute(['id'=>$id]);
        }
        $pdo->prepare('DELETE FROM usuarios WHERE id=:id')->execute(['id'=>$id]);
    } finally {
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    }
};

if ($accion === 'cleanup') { $limpiar(); echo "OK cleanup\n"; exit; }
if ($accion !== 'setup') { fwrite(STDERR, "Uso: setup|cleanup\n"); exit(2); }

$limpiar();
$ubicacion = $pdo->query('SELECT d.id distrito_id,m.id municipio_id,m.departamento_id FROM distritos d INNER JOIN municipios m ON m.id=d.municipio_id ORDER BY d.id LIMIT 1')->fetch();
if (!$ubicacion) throw new RuntimeException('No existen ubicaciones para la prueba.');
$q = $pdo->prepare("INSERT INTO usuarios(nombre,apellido,nombre_usuario,correo,password,proveedor,email_verificado,rol,es_superadmin,estado,perfil_estado,terminos_aceptados_at,fecha_nacimiento,dui,codigo_telefono,telefono,departamento_id,municipio_id,distrito_id,direccion) VALUES(:nombre,:apellido,:usuario,:correo,:password,'local',1,'usuario',0,'activo','completo',NOW(),'1995-05-15','87654321-0','+503','71234567',:departamento,:municipio,:distrito,:direccion)");
$q->execute([
    'nombre'=>'Estudiante',
    'apellido'=>'Atenea',
    'usuario'=>'layout.estudiante',
    'correo'=>$correo,
    'password'=>password_hash('PruebaLayout!2026', PASSWORD_DEFAULT),
    'departamento'=>(int)$ubicacion['departamento_id'],
    'municipio'=>(int)$ubicacion['municipio_id'],
    'distrito'=>(int)$ubicacion['distrito_id'],
    'direccion'=>'Dirección temporal para validar el dashboard de Atenea.',
]);
$usuarioId=(int)$pdo->lastInsertId();
$q=$pdo->prepare("INSERT INTO pedidos(numero,usuario_id,subtotal,descuento,envio,impuestos,total,moneda,checkout_key,estado,payment_status) VALUES(:numero,:usuario,25,0,0,0,25,'usd',:clave,'pagado','paid')");
$q->execute(['numero'=>'TEST-EST-'.strtoupper(bin2hex(random_bytes(4))),'usuario'=>$usuarioId,'clave'=>hash('sha256',$correo)]);
echo json_encode(['correo'=>$correo], JSON_UNESCAPED_SLASHES) . "\n";
