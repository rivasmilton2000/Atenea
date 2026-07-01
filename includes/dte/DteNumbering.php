<?php

class DteNumbering
{
    public static function reserve(mysqli $db, array $settings, string $tipoDte = '01', ?array $existingDocument = null): array
    {
        $existingNumeroControl = trim((string) ($existingDocument['numero_control'] ?? ''));
        $existingCodigoGeneracion = trim((string) ($existingDocument['codigo_generacion'] ?? ''));

        if ($existingNumeroControl !== '' && $existingCodigoGeneracion !== '') {
            return [
                'tipo_dte' => $tipoDte,
                'numero_control' => $existingNumeroControl,
                'codigo_generacion' => strtoupper($existingCodigoGeneracion),
            ];
        }

        $codEstable = self::normalizeEstablishmentCode((string) ($settings['cod_estable'] ?? ''), 'S');
        $codPuntoVenta = self::normalizeEstablishmentCode((string) ($settings['cod_punto_venta'] ?? ''), 'P');

        $selectSql = 'SELECT id, current_number FROM dte_sequences WHERE tipo_dte = ? AND cod_estable = ? AND cod_punto_venta = ? LIMIT 1 FOR UPDATE';
        $selectStmt = $db->prepare($selectSql);
        if (!$selectStmt) {
            throw new RuntimeException('No se pudo preparar la secuencia DTE.');
        }

        $selectStmt->bind_param('sss', $tipoDte, $codEstable, $codPuntoVenta);
        $selectStmt->execute();
        $result = $selectStmt->get_result();
        $sequenceRow = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
        }
        $selectStmt->close();

        if (!$sequenceRow) {
            $insertStmt = $db->prepare('INSERT INTO dte_sequences (tipo_dte, cod_estable, cod_punto_venta, current_number) VALUES (?, ?, ?, 0)');
            if (!$insertStmt) {
                throw new RuntimeException('No se pudo inicializar la secuencia DTE.');
            }

            $insertStmt->bind_param('sss', $tipoDte, $codEstable, $codPuntoVenta);
            $insertStmt->execute();
            $insertStmt->close();

            $selectStmt = $db->prepare($selectSql);
            if (!$selectStmt) {
                throw new RuntimeException('No se pudo recargar la secuencia DTE.');
            }

            $selectStmt->bind_param('sss', $tipoDte, $codEstable, $codPuntoVenta);
            $selectStmt->execute();
            $result = $selectStmt->get_result();
            $sequenceRow = $result instanceof mysqli_result ? $result->fetch_assoc() : null;
            if ($result instanceof mysqli_result) {
                mysqli_free_result($result);
            }
            $selectStmt->close();
        }

        if (!$sequenceRow) {
            throw new RuntimeException('No se pudo obtener una secuencia DTE valida.');
        }

        $currentNumber = (int) ($sequenceRow['current_number'] ?? 0) + 1;
        $sequenceId = (int) ($sequenceRow['id'] ?? 0);

        $updateStmt = $db->prepare('UPDATE dte_sequences SET current_number = ? WHERE id = ?');
        if (!$updateStmt) {
            throw new RuntimeException('No se pudo actualizar la secuencia DTE.');
        }

        $updateStmt->bind_param('ii', $currentNumber, $sequenceId);
        $updateStmt->execute();
        $updateStmt->close();

        return [
            'tipo_dte' => $tipoDte,
            'numero_control' => sprintf('DTE-%s-%s%s-%015d', $tipoDte, $codEstable, $codPuntoVenta, $currentNumber),
            'codigo_generacion' => self::uuidV4(),
        ];
    }

    public static function normalizeEstablishmentCode(string $value, string $prefix): string
    {
        $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', trim($value)) ?: '');
        if ($clean === '') {
            return $prefix . '001';
        }

        if (strpos($clean, $prefix) !== 0) {
            $clean = $prefix . preg_replace('/[^0-9]/', '', $clean);
        }

        $numeric = preg_replace('/[^0-9]/', '', substr($clean, 1));
        $numeric = str_pad(substr($numeric, 0, 3), 3, '0', STR_PAD_LEFT);

        return $prefix . $numeric;
    }

    public static function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return strtoupper(vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)));
    }
}
