<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/auth.php';

$pdo = obtenerConexion();
$accion = $argv[1] ?? '';
$correos = ['perfil.google@example.invalid', 'dui.existente@example.invalid'];

$limpiar = static function () use ($pdo, $correos): void {
    $marcas = implode(',', array_fill(0, count($correos), '?'));
    $consulta = $pdo->prepare("SELECT id FROM usuarios WHERE correo IN ({$marcas})");
    $consulta->execute($correos);
    $ids = array_map('intval', array_column($consulta->fetchAll(), 'id'));
    if (!$ids) return;
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    try {
        foreach ($ids as $id) {
            foreach ($pdo->query("SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME='usuarios' AND TABLE_NAME<>'usuarios'")->fetchAll() as $referencia) {
                $tabla = str_replace('`', '``', (string) $referencia['TABLE_NAME']);
                $columna = str_replace('`', '``', (string) $referencia['COLUMN_NAME']);
                $pdo->prepare("DELETE FROM `{$tabla}` WHERE `{$columna}` = :id")->execute(['id' => $id]);
            }
            $pdo->prepare('DELETE FROM usuarios WHERE id = :id')->execute(['id' => $id]);
        }
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

$clave = password_hash('PerfilGoogle!2026', PASSWORD_DEFAULT);
$pendiente = $pdo->prepare("INSERT INTO usuarios(nombre,apellido,nombre_usuario,correo,password,google_id,proveedor,email_verificado,rol,es_superadmin,estado,perfil_estado) VALUES(:nombre,:apellido,:usuario,:correo,:password,:google,'google',1,'usuario',0,'activo','pendiente')");
$pendiente->execute([
    'nombre' => 'María José', 'apellido' => 'O’Connor-Peña', 'usuario' => 'perfil.google',
    'correo' => $correos[0], 'password' => $clave, 'google' => 'fixture-google-perfil-2026',
]);

$duplicado = $pdo->prepare("INSERT INTO usuarios(nombre,apellido,nombre_usuario,correo,password,proveedor,email_verificado,rol,es_superadmin,estado,perfil_estado,terminos_aceptados_at,fecha_nacimiento,dui,codigo_telefono,telefono,departamento_id,municipio_id,distrito_id,direccion) VALUES('DUI','Existente',:usuario,:correo,:password,'local',1,'usuario',0,'activo','completo',NOW(),'1990-01-01','12345678-9','+503','71234567',:departamento,:municipio,:distrito,'Dirección válida de prueba')");
$duplicado->execute([
    'usuario' => 'dui.existente', 'correo' => $correos[1], 'password' => $clave,
    'departamento' => (int) $ubicacion['departamento_id'], 'municipio' => (int) $ubicacion['municipio_id'],
    'distrito' => (int) $ubicacion['distrito_id'],
]);

echo json_encode([
    'correo' => $correos[0], 'password' => 'PerfilGoogle!2026', 'dui_duplicado' => '12345678-9',
    'departamento_id' => (int) $ubicacion['departamento_id'], 'municipio_id' => (int) $ubicacion['municipio_id'],
    'distrito_id' => (int) $ubicacion['distrito_id'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
