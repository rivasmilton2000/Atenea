<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/perfil_usuario.php';

$pruebas = 0;
$afirmar = static function (bool $condicion, string $mensaje) use (&$pruebas): void {
    $pruebas++;
    if (!$condicion) {
        throw new RuntimeException("FALLO {$pruebas}: {$mensaje}");
    }
};

$hoy = new DateTimeImmutable('today', new DateTimeZone('America/El_Salvador'));
$afirmar(normalizarFechaNacimiento('31/12/1990') === '1990-12-31', 'convierte dd/mm/aaaa al formato de base de datos');
$afirmar(normalizarFechaNacimiento('1990-12-31') === '1990-12-31', 'conserva el formato ISO de la base de datos');
$afirmar(normalizarFechaNacimiento('31/02/1990') === null, 'rechaza una fecha visual inexistente');
$afirmar(fechaNacimientoValida($hoy->modify('-18 years')->format('Y-m-d')), 'acepta exactamente 18 años');
$afirmar(!fechaNacimientoValida($hoy->modify('-18 years +1 day')->format('Y-m-d')), 'rechaza a quien cumple 18 mañana');
$afirmar(!fechaNacimientoValida($hoy->modify('+1 day')->format('Y-m-d')), 'rechaza fecha futura');
$afirmar(!fechaNacimientoValida('2025-02-30'), 'rechaza fecha inexistente');
$afirmar(!fechaNacimientoValida($hoy->modify('-121 years')->format('Y-m-d')), 'rechaza más de 120 años');

$afirmar(nombrePersonaValido("  María   José  "), 'acepta nombres Unicode y normaliza espacios');
$afirmar(nombrePersonaValido("O’Connor-Peña"), 'acepta apóstrofes y guiones');
$afirmar(!nombrePersonaValido('Ana2'), 'rechaza números en nombres');
$afirmar(!nombrePersonaValido('<script>Ana</script>'), 'rechaza HTML en nombres');
$afirmar(!nombrePersonaValido('--'), 'rechaza nombres formados solo por símbolos');

$afirmar(duiValidoExacto('00000000-0'), 'acepta DUI exacto');
$afirmar(!duiValidoExacto('000000000'), 'rechaza DUI sin guion');
$afirmar(!duiValidoExacto('00000000-00'), 'rechaza DUI largo');
$afirmar(normalizarTelefonoParaCodigo('+503', '+503 7123-4567') === '71234567', 'evita prefijo +503 duplicado');
$afirmar(telefonoValido('+503', '71234567'), 'acepta celular salvadoreño de ocho dígitos');
$afirmar(!telefonoValido('+503', '7123456'), 'rechaza teléfono salvadoreño incompleto');
$afirmar(!telefonoValido('+503', '31234567'), 'rechaza inicio inválido en El Salvador');
$afirmar(telefonoValido('+52', '5512345678'), 'valida diez dígitos para México');

$afirmar(direccionPerfilValida('Colonia Escalón, pasaje 2, casa #14.'), 'acepta una dirección real');
$afirmar(!direccionPerfilValida('<script>alert(1)</script>'), 'rechaza HTML ejecutable en dirección');
$afirmar(!direccionPerfilValida('Casa'), 'rechaza dirección demasiado corta');
$afirmar(!direccionPerfilValida(str_repeat('A', 251)), 'rechaza dirección mayor de 250 caracteres');

$pdo = obtenerConexion();
$ubicaciones = $pdo->query('SELECT d.id distrito_id,m.id municipio_id,m.departamento_id FROM distritos d INNER JOIN municipios m ON m.id=d.municipio_id ORDER BY d.id LIMIT 2')->fetchAll();
$afirmar(count($ubicaciones) >= 1, 'hay ubicaciones reales para probar');
$primera = $ubicaciones[0];
$afirmar(ubicacionValida($pdo, (int)$primera['departamento_id'], (int)$primera['municipio_id'], (int)$primera['distrito_id']), 'acepta relación territorial real');
$afirmar(!ubicacionValida($pdo, (int)$primera['departamento_id'] + 99999, (int)$primera['municipio_id'], (int)$primera['distrito_id']), 'rechaza ubicación incompatible');

echo "OK {$pruebas} pruebas\n";
