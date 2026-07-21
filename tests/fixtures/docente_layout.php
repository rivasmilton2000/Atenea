<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';
$pdo = obtenerConexion();
$accion = $argv[1] ?? '';
$correo = 'layout.docente@example.invalid';

$limpiar = static function () use ($pdo, $correo): void {
    $consulta = $pdo->prepare('SELECT id FROM usuarios WHERE correo = :correo');
    $consulta->execute(['correo' => $correo]);
    $id = (int) $consulta->fetchColumn();
    if (!$id) return;
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    try {
        foreach ($pdo->query("SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME='usuarios' AND TABLE_NAME<>'usuarios'")->fetchAll() as $referencia) {
            $tabla = str_replace('`', '``', (string) $referencia['TABLE_NAME']);
            $columna = str_replace('`', '``', (string) $referencia['COLUMN_NAME']);
            $pdo->prepare("DELETE FROM `{$tabla}` WHERE `{$columna}` = :id")->execute(['id' => $id]);
        }
        $pdo->prepare('DELETE FROM usuarios WHERE id = :id')->execute(['id' => $id]);
    } finally {
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    }
};

if ($accion === 'cleanup') {
    $limpiar();
    echo "OK cleanup\n";
    exit;
}
if ($accion !== 'setup') {
    fwrite(STDERR, "Uso: setup|cleanup\n");
    exit(2);
}

$limpiar();
$ubicacion = $pdo->query('SELECT d.id distrito_id,m.id municipio_id,m.departamento_id FROM distritos d INNER JOIN municipios m ON m.id=d.municipio_id ORDER BY d.id LIMIT 1')->fetch();
if (!$ubicacion) throw new RuntimeException('No existen ubicaciones para la prueba.');
$consulta = $pdo->prepare("INSERT INTO usuarios(nombre,apellido,nombre_usuario,correo,password,proveedor,email_verificado,rol,es_superadmin,estado,perfil_estado,terminos_aceptados_at,fecha_nacimiento,dui,codigo_telefono,telefono,departamento_id,municipio_id,distrito_id,direccion) VALUES('Docente','Hope UI','layout.docente',:correo,:password,'local',1,'docente',0,'activo','completo',NOW(),'1985-04-12','34567890-1','+503','71234567',:departamento,:municipio,:distrito,'Dirección temporal para probar el portal docente')");
$consulta->execute([
    'correo' => $correo, 'password' => password_hash('DocenteLayout!2026', PASSWORD_DEFAULT),
    'departamento' => (int) $ubicacion['departamento_id'], 'municipio' => (int) $ubicacion['municipio_id'],
    'distrito' => (int) $ubicacion['distrito_id'],
]);
echo json_encode(['correo' => $correo, 'password' => 'DocenteLayout!2026'], JSON_UNESCAPED_SLASHES) . "\n";
