<?php
declare(strict_types=1);

require_once __DIR__ . '/database_backups.php';

const ATENEA_SQL_BATCH_ROWS = 250;
const ATENEA_SQL_BATCH_BYTES = 1048576;

function identificadorSqlAtenea(string $identificador): string
{
    return '`' . str_replace('`', '``', $identificador) . '`';
}

function tablasSqlDisponiblesAtenea(?PDO $pdo = null): array
{
    $pdo ??= obtenerConexion();
    $tablas = [];
    foreach ($pdo->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM) as $fila) {
        $nombre = (string)($fila[0] ?? '');
        $tipo = strtoupper((string)($fila[1] ?? ''));
        if ($tipo === 'BASE TABLE' && preg_match('/^[A-Za-z0-9_]+$/D', $nombre)) $tablas[] = $nombre;
    }
    natcasesort($tablas);
    return array_values($tablas);
}

function vistasSqlDisponiblesAtenea(PDO $pdo): array
{
    $vistas = [];
    foreach ($pdo->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM) as $fila) {
        $nombre = (string)($fila[0] ?? '');
        if (strtoupper((string)($fila[1] ?? '')) === 'VIEW' && preg_match('/^[A-Za-z0-9_]+$/D', $nombre)) $vistas[] = $nombre;
    }
    natcasesort($vistas);
    return array_values($vistas);
}

function ordenarTablasSqlAtenea(PDO $pdo, array $tablas): array
{
    $tablas = array_values(array_unique($tablas));
    $permitidas = array_fill_keys($tablas, true);
    $dependencias = array_fill_keys($tablas, []);
    $consulta = $pdo->query("SELECT TABLE_NAME,REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL");
    foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $fila) {
        $hija = (string)$fila['TABLE_NAME'];
        $padre = (string)$fila['REFERENCED_TABLE_NAME'];
        if ($hija !== $padre && isset($permitidas[$hija], $permitidas[$padre])) $dependencias[$hija][$padre] = true;
    }
    $orden = [];
    while (count($orden) < count($tablas)) {
        $avance = false;
        foreach ($tablas as $tabla) {
            if (in_array($tabla, $orden, true)) continue;
            $pendientes = array_diff(array_keys($dependencias[$tabla]), $orden);
            if (!$pendientes) { $orden[] = $tabla; $avance = true; }
        }
        if (!$avance) {
            foreach ($tablas as $tabla) if (!in_array($tabla, $orden, true)) $orden[] = $tabla;
        }
    }
    return $orden;
}

function normalizarExportacionSqlAtenea(PDO $pdo, string $alcance, string $contenido, ?string $tabla): array
{
    if (!in_array($alcance, ['base', 'tabla'], true)) throw new DomainException('Selecciona un alcance válido.');
    if (!in_array($contenido, ['completa', 'estructura', 'datos'], true)) throw new DomainException('Selecciona un tipo de copia válido.');
    $disponibles = tablasSqlDisponiblesAtenea($pdo);
    if (!$disponibles) throw new DomainException('La base de datos no contiene tablas exportables.');
    if ($alcance === 'tabla') {
        $tabla = trim((string)$tabla);
        if (!in_array($tabla, $disponibles, true)) throw new DomainException('La tabla seleccionada no pertenece a la base de datos actual.');
        $tablas = [$tabla];
    } else {
        $tabla = null;
        $tablas = ordenarTablasSqlAtenea($pdo, $disponibles);
    }
    return [
        'alcance' => $alcance,
        'contenido' => $contenido,
        'tabla' => $tabla,
        'tablas' => $tablas,
        'incluir_estructura' => $contenido !== 'datos',
        'incluir_datos' => $contenido !== 'estructura',
    ];
}

function nombreExportacionSqlAtenea(array $opciones, ?DateTimeInterface $fecha = null): string
{
    $fecha ??= new DateTimeImmutable('now', new DateTimeZone('America/El_Salvador'));
    $marca = $fecha->format('Y-m-d_H-i-s');
    if (($opciones['alcance'] ?? '') === 'tabla') {
        $tabla = preg_replace('/[^A-Za-z0-9_]/', '_', (string)($opciones['tabla'] ?? 'tabla')) ?: 'tabla';
        return 'atenea_' . $tabla . '_' . $marca . '.sql';
    }
    return 'atenea_backup_' . $marca . '.sql';
}

function etiquetaExportacionSqlAtenea(array $opciones): string
{
    $contenido = match ($opciones['contenido'] ?? '') {
        'estructura' => 'solo estructura',
        'datos' => 'solo información',
        default => 'estructura e información',
    };
    return (($opciones['alcance'] ?? '') === 'tabla' ? 'Tabla ' . (string)$opciones['tabla'] : 'Base de datos completa') . ': ' . $contenido;
}

function columnasExportablesSqlAtenea(PDO $pdo, string $tabla): array
{
    $columnas = [];
    foreach ($pdo->query('SHOW FULL COLUMNS FROM ' . identificadorSqlAtenea($tabla))->fetchAll(PDO::FETCH_ASSOC) as $columna) {
        if (str_contains(strtolower((string)($columna['Extra'] ?? '')), 'generated')) continue;
        $tipo = strtolower((string)($columna['Type'] ?? ''));
        preg_match('/^[a-z]+/', $tipo, $coincidencia);
        $columnas[] = ['nombre'=>(string)$columna['Field'], 'tipo'=>(string)($coincidencia[0] ?? '')];
    }
    return $columnas;
}

function valorSqlAtenea(PDO $pdo, mixed $valor, string $tipo = ''): string
{
    if ($valor === null) return 'NULL';
    $texto = (string)$valor;
    if (in_array($tipo, ['binary','varbinary','blob','tinyblob','mediumblob','longblob','bit'], true)) return '0x' . bin2hex($texto);
    if (in_array($tipo, ['tinyint','smallint','mediumint','int','integer','bigint','decimal','numeric','float','double','real'], true)
        && preg_match('/^-?(?:\d+|\d*\.\d+)(?:[eE][+-]?\d+)?$/D', $texto)) return $texto;
    $citado = $pdo->quote($texto, PDO::PARAM_STR);
    return is_string($citado) ? $citado : '0x' . bin2hex($texto);
}

function escribirCabeceraSqlAtenea(callable $escribir, string $base, string $tipo, bool $crearBase, string $fecha): void
{
    $baseSql = identificadorSqlAtenea($base);
    $escribir("-- Copia SQL de Atenea\n-- Fecha: {$fecha} (America/El_Salvador)\n-- Base de datos: {$base}\n-- Tipo de exportación: {$tipo}\n-- Generado desde PHP sin incluir archivos del servidor ni variables de entorno.\n\n");
    $escribir("SET NAMES utf8mb4;\nSET @OLD_SQL_MODE=@@SQL_MODE;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\nSET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS;\nSET FOREIGN_KEY_CHECKS=0;\n\n");
    if ($crearBase) $escribir("CREATE DATABASE IF NOT EXISTS {$baseSql} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n");
    $escribir("USE {$baseSql};\n\n");
}

function escribirEstructuraTablasSqlAtenea(PDO $pdo, array $tablas, callable $escribir): void
{
    foreach (array_reverse($tablas) as $tabla) $escribir('DROP TABLE IF EXISTS ' . identificadorSqlAtenea($tabla) . ";\n");
    $escribir("\n");
    foreach ($tablas as $tabla) {
        $fila = $pdo->query('SHOW CREATE TABLE ' . identificadorSqlAtenea($tabla))->fetch(PDO::FETCH_NUM);
        if (!is_array($fila) || empty($fila[1])) throw new RuntimeException('No fue posible leer la estructura de ' . $tabla . '.');
        $escribir("-- Estructura de {$tabla}\n" . (string)$fila[1] . ";\n\n");
    }
}

function escribirDatosTablaSqlAtenea(PDO $pdo, string $tabla, callable $escribir): int
{
    $columnas = columnasExportablesSqlAtenea($pdo, $tabla);
    if (!$columnas) return 0;
    $nombres = array_column($columnas, 'nombre');
    $lista = implode(',', array_map('identificadorSqlAtenea', $nombres));
    $prefijo = 'INSERT INTO ' . identificadorSqlAtenea($tabla) . ' (' . $lista . ") VALUES\n";
    $filas = 0; $lote = []; $bytes = 0;
    $anterior = null;
    try {
        if (defined('PDO::MYSQL_ATTR_USE_BUFFERED_QUERY')) {
            try { $anterior = $pdo->getAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY); } catch (Throwable) { $anterior = null; }
            $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        }
        $consulta = $pdo->query('SELECT ' . $lista . ' FROM ' . identificadorSqlAtenea($tabla));
        while ($fila = $consulta->fetch(PDO::FETCH_NUM)) {
            $valores = [];
            foreach ($fila as $indice => $valor) $valores[] = valorSqlAtenea($pdo, $valor, (string)$columnas[$indice]['tipo']);
            $tupla = '(' . implode(',', $valores) . ')';
            $lote[] = $tupla; $bytes += strlen($tupla); $filas++;
            if (count($lote) >= ATENEA_SQL_BATCH_ROWS || $bytes >= ATENEA_SQL_BATCH_BYTES) {
                $escribir($prefijo . implode(",\n", $lote) . ";\n"); $lote = []; $bytes = 0;
            }
        }
        $consulta->closeCursor();
        if ($lote) $escribir($prefijo . implode(",\n", $lote) . ";\n");
    } finally {
        if ($anterior !== null) try { $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, (bool)$anterior); } catch (Throwable) {}
    }
    if ($filas) $escribir("\n");
    return $filas;
}

function escribirVistasYTriggersSqlAtenea(PDO $pdo, callable $escribir): void
{
    foreach (vistasSqlDisponiblesAtenea($pdo) as $vista) {
        $fila = $pdo->query('SHOW CREATE VIEW ' . identificadorSqlAtenea($vista))->fetch(PDO::FETCH_ASSOC);
        $crear = (string)($fila['Create View'] ?? '');
        if ($crear === '') continue;
        $escribir('DROP VIEW IF EXISTS ' . identificadorSqlAtenea($vista) . ";\n" . respaldoQuitarDefinidor($crear) . ";\n\n");
    }
    foreach ($pdo->query('SHOW TRIGGERS')->fetchAll(PDO::FETCH_ASSOC) as $trigger) {
        $nombre = (string)($trigger['Trigger'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_]+$/D', $nombre)) continue;
        $fila = $pdo->query('SHOW CREATE TRIGGER ' . identificadorSqlAtenea($nombre))->fetch(PDO::FETCH_ASSOC);
        $crear = (string)($fila['SQL Original Statement'] ?? $fila['Create Trigger'] ?? '');
        if ($crear === '') continue;
        $escribir('DROP TRIGGER IF EXISTS ' . identificadorSqlAtenea($nombre) . ";\nDELIMITER $$\n" . respaldoQuitarDefinidor($crear) . "$$\nDELIMITER ;\n\n");
    }
}

function exportarBaseActualSqlAtenea(PDO $pdo, array $opciones, callable $escribir): array
{
    $base = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();
    if ($base === '' || !preg_match('/^[A-Za-z0-9_$-]+$/D', $base)) throw new RuntimeException('No fue posible identificar la base de datos actual.');
    $fecha = (new DateTimeImmutable('now', new DateTimeZone('America/El_Salvador')))->format('Y-m-d H:i:s');
    escribirCabeceraSqlAtenea($escribir, $base, etiquetaExportacionSqlAtenea($opciones), $opciones['alcance'] === 'base' && $opciones['incluir_estructura'], $fecha);
    if ($opciones['incluir_estructura']) {
        if ($opciones['alcance'] === 'base') foreach (vistasSqlDisponiblesAtenea($pdo) as $vista) $escribir('DROP VIEW IF EXISTS ' . identificadorSqlAtenea($vista) . ";\n");
        escribirEstructuraTablasSqlAtenea($pdo, $opciones['tablas'], $escribir);
    }
    $filas = 0;
    if ($opciones['incluir_datos']) foreach ($opciones['tablas'] as $tabla) {
        $escribir("-- Información de {$tabla}\n");
        $filas += escribirDatosTablaSqlAtenea($pdo, $tabla, $escribir);
    }
    if ($opciones['alcance'] === 'base' && $opciones['incluir_estructura']) escribirVistasYTriggersSqlAtenea($pdo, $escribir);
    $escribir("SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;\nSET SQL_MODE=@OLD_SQL_MODE;\n-- Fin de la copia SQL de Atenea.\n");
    return ['tablas'=>count($opciones['tablas']), 'filas'=>$filas, 'base'=>$base];
}

function crearArchivoExportacionSqlAtenea(PDO $pdo, array $opciones): array
{
    $ruta = tempnam(respaldoDirectorioBase(), 'sql-export-');
    if ($ruta === false) throw new RuntimeException('No fue posible preparar el archivo SQL temporal.');
    @chmod($ruta, 0600); $archivo = fopen($ruta, 'wb');
    if (!$archivo) { @unlink($ruta); throw new RuntimeException('No fue posible abrir el archivo SQL temporal.'); }
    $escribir = static function(string $sql) use ($archivo): void { if (fwrite($archivo, $sql) === false) throw new RuntimeException('No fue posible escribir la copia SQL.'); };
    try {
        $resultado = exportarBaseActualSqlAtenea($pdo, $opciones, $escribir);
        fflush($archivo); fclose($archivo); $archivo = null;
        $tamano = filesize($ruta);
        if ($tamano === false || $tamano < 100) throw new RuntimeException('La copia SQL generada está incompleta.');
        return $resultado + ['ruta'=>$ruta, 'nombre'=>nombreExportacionSqlAtenea($opciones), 'tamano'=>(int)$tamano];
    } catch (Throwable $e) {
        if (is_resource($archivo)) fclose($archivo);
        @unlink($ruta);
        throw $e;
    }
}

function crearArchivoSqlDesdeRespaldoAtenea(array $respaldo): array
{
    $rutaOrigen = verificarIntegridadRespaldo($respaldo);
    $pdo = obtenerConexion(); $base = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();
    $tablas = []; $vistas = []; $triggers = [];
    foreach (leerRegistrosRespaldo($rutaOrigen) as $registro) {
        if (($registro['kind'] ?? '') === 'table') $tablas[(string)$registro['name']] = $registro;
        elseif (($registro['kind'] ?? '') === 'view') $vistas[] = $registro;
        elseif (($registro['kind'] ?? '') === 'trigger') $triggers[] = $registro;
    }
    if (!$tablas) throw new RuntimeException('La copia histórica no contiene tablas exportables.');
    $ruta = tempnam(respaldoDirectorioBase(), 'sql-history-');
    if ($ruta === false) throw new RuntimeException('No fue posible preparar la exportación histórica.');
    @chmod($ruta, 0600); $archivo = fopen($ruta, 'wb');
    if (!$archivo) { @unlink($ruta); throw new RuntimeException('No fue posible abrir la exportación histórica.'); }
    $escribir = static function(string $sql) use ($archivo): void { if (fwrite($archivo, $sql) === false) throw new RuntimeException('No fue posible escribir la exportación histórica.'); };
    $filas = 0;
    try {
        escribirCabeceraSqlAtenea($escribir, $base, 'Copia histórica: estructura e información persistente', true, (string)$respaldo['created_at']);
        foreach ($vistas as $vista) $escribir('DROP VIEW IF EXISTS ' . identificadorSqlAtenea((string)$vista['name']) . ";\n");
        foreach (array_reverse(array_keys($tablas)) as $tabla) $escribir('DROP TABLE IF EXISTS ' . identificadorSqlAtenea($tabla) . ";\n");
        $escribir("\n");
        foreach ($tablas as $tabla => $definicion) $escribir("-- Estructura de {$tabla}\n" . (string)$definicion['create'] . ";\n\n");
        $loteTabla = null; $lote = []; $bytes = 0;
        $vaciar = static function() use (&$loteTabla, &$lote, &$bytes, $tablas, $escribir): void {
            if ($loteTabla === null || !$lote) return;
            $columnas = (array)($tablas[$loteTabla]['columns'] ?? []);
            $lista = implode(',', array_map('identificadorSqlAtenea', $columnas));
            $escribir('INSERT INTO ' . identificadorSqlAtenea($loteTabla) . ' (' . $lista . ") VALUES\n" . implode(",\n", $lote) . ";\n");
            $lote = []; $bytes = 0;
        };
        foreach (leerRegistrosRespaldo($rutaOrigen) as $registro) {
            if (($registro['kind'] ?? '') !== 'row') continue;
            $tabla = (string)$registro['table'];
            if (!isset($tablas[$tabla])) throw new RuntimeException('La copia histórica contiene una tabla desconocida.');
            if ($loteTabla !== null && $loteTabla !== $tabla) $vaciar();
            $loteTabla = $tabla; $valores = [];
            foreach ((array)$registro['values'] as $valor) {
                if ($valor === null) $valores[] = 'NULL';
                else { $decodificado = base64_decode((string)$valor, true); if ($decodificado === false) throw new RuntimeException('La copia histórica contiene un valor inválido.'); $valores[] = valorSqlAtenea($pdo, $decodificado); }
            }
            $tupla = '(' . implode(',', $valores) . ')'; $lote[] = $tupla; $bytes += strlen($tupla); $filas++;
            if (count($lote) >= ATENEA_SQL_BATCH_ROWS || $bytes >= ATENEA_SQL_BATCH_BYTES) $vaciar();
        }
        $vaciar(); $escribir("\n");
        foreach ($vistas as $vista) $escribir('DROP VIEW IF EXISTS ' . identificadorSqlAtenea((string)$vista['name']) . ";\n" . (string)$vista['create'] . ";\n\n");
        foreach ($triggers as $trigger) $escribir('DROP TRIGGER IF EXISTS ' . identificadorSqlAtenea((string)$trigger['name']) . ";\nDELIMITER $$\n" . (string)$trigger['create'] . "$$\nDELIMITER ;\n\n");
        $escribir("SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;\nSET SQL_MODE=@OLD_SQL_MODE;\n-- Fin de la copia SQL de Atenea.\n");
        fflush($archivo); fclose($archivo); $archivo = null; $tamano = filesize($ruta);
        if ($tamano === false || $tamano < 100) throw new RuntimeException('La exportación histórica está incompleta.');
        $fecha = new DateTimeImmutable((string)$respaldo['created_at'], new DateTimeZone('America/El_Salvador'));
        return ['ruta'=>$ruta, 'nombre'=>'atenea_backup_' . $fecha->format('Y-m-d_H-i-s') . '.sql', 'tamano'=>(int)$tamano, 'tablas'=>count($tablas), 'filas'=>$filas, 'base'=>$base];
    } catch (Throwable $e) {
        if (is_resource($archivo)) fclose($archivo);
        @unlink($ruta); throw $e;
    }
}
