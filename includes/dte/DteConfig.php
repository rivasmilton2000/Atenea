<?php

class DteConfig
{
    private const REQUIRED_EMITTER_FIELDS = [
        'emisor_nit' => 'NIT del emisor',
        'emisor_nrc' => 'NRC del emisor',
        'emisor_nombre' => 'Nombre legal del emisor',
        'emisor_nombre_comercial' => 'Nombre comercial del emisor',
        'emisor_cod_actividad' => 'Codigo de actividad economica',
        'emisor_desc_actividad' => 'Descripcion de actividad economica',
        'emisor_tipo_establecimiento' => 'Tipo de establecimiento',
        'emisor_departamento' => 'Departamento',
        'emisor_municipio' => 'Municipio',
        'emisor_direccion' => 'Direccion complemento',
        'emisor_telefono' => 'Telefono',
        'emisor_correo' => 'Correo',
        'cod_estable_mh' => 'Codigo establecimiento MH',
        'cod_estable' => 'Codigo establecimiento interno',
        'cod_punto_venta_mh' => 'Codigo punto de venta MH',
        'cod_punto_venta' => 'Codigo punto de venta interno',
    ];

    public static function getActive(mysqli $db): array
    {
        DteSchema::ensure($db);
        DteStorage::ensureDirectories();
        self::ensureDefaultSettings($db);

        $sql = 'SELECT * FROM dte_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1';
        $result = $db->query($sql);
        if (!$result instanceof mysqli_result) {
            throw new RuntimeException('No se pudo cargar la configuracion DTE.');
        }

        $settings = $result->fetch_assoc() ?: [];
        mysqli_free_result($result);

        if ($settings === []) {
            throw new RuntimeException('No existe una configuracion DTE activa.');
        }

        $settings['mode_label'] = self::modeLabel((string) ($settings['mode'] ?? 'simulation'));
        $settings['ambiente'] = self::environmentCode((string) ($settings['mode'] ?? 'simulation'));

        return $settings;
    }

    public static function save(mysqli $db, array $input, array $files = []): array
    {
        $current = self::getActive($db);
        $mode = self::sanitizeMode((string) ($input['mode'] ?? ($current['mode'] ?? 'simulation')));
        $ambiente = self::environmentCode($mode);

        $certificatePath = trim((string) ($current['certificate_path'] ?? ''));
        $newCertificate = $files['certificate_file'] ?? null;
        if (is_array($newCertificate) && (int) ($newCertificate['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $storedCertificate = DteStorage::storeUploadedCertificate($newCertificate);
            if ($storedCertificate !== null) {
                $oldCertificate = DteStorage::resolveStoredFile($certificatePath, [DteStorage::directory('certificates')]);
                if (!empty($oldCertificate['absolute_path'])) {
                    DteStorage::deleteIfExists($oldCertificate['absolute_path']);
                }

                $certificatePath = $storedCertificate['relative_path'];
            }
        }

        $apiPasswordEncrypted = (string) ($current['api_password_encrypted'] ?? '');
        $apiPasswordPlain = trim((string) ($input['api_password'] ?? ''));
        if ($apiPasswordPlain !== '') {
            $apiPasswordEncrypted = atenea_encrypt_secret($apiPasswordPlain);
        }

        $certificatePasswordEncrypted = (string) ($current['certificate_password_encrypted'] ?? '');
        $certificatePasswordPlain = trim((string) ($input['certificate_password'] ?? ''));
        if ($certificatePasswordPlain !== '') {
            $certificatePasswordEncrypted = atenea_encrypt_secret($certificatePasswordPlain);
        }

        $payload = [
            'mode' => $mode,
            'ambiente' => $ambiente,
            'emisor_nit' => self::sanitizeText($input['emisor_nit'] ?? $current['emisor_nit'] ?? '', 20),
            'emisor_nrc' => self::sanitizeText($input['emisor_nrc'] ?? $current['emisor_nrc'] ?? '', 20),
            'emisor_nombre' => self::sanitizeText($input['emisor_nombre'] ?? $current['emisor_nombre'] ?? '', 255),
            'emisor_nombre_comercial' => self::sanitizeText($input['emisor_nombre_comercial'] ?? $current['emisor_nombre_comercial'] ?? '', 255),
            'emisor_cod_actividad' => self::sanitizeText($input['emisor_cod_actividad'] ?? $current['emisor_cod_actividad'] ?? '', 20),
            'emisor_desc_actividad' => self::sanitizeText($input['emisor_desc_actividad'] ?? $current['emisor_desc_actividad'] ?? '', 255),
            'emisor_tipo_establecimiento' => self::sanitizeText($input['emisor_tipo_establecimiento'] ?? $current['emisor_tipo_establecimiento'] ?? '', 50),
            'emisor_departamento' => self::sanitizeText($input['emisor_departamento'] ?? $current['emisor_departamento'] ?? '', 100),
            'emisor_municipio' => self::sanitizeText($input['emisor_municipio'] ?? $current['emisor_municipio'] ?? '', 100),
            'emisor_direccion' => self::sanitizeText($input['emisor_direccion'] ?? $current['emisor_direccion'] ?? '', 255),
            'emisor_telefono' => self::sanitizeText($input['emisor_telefono'] ?? $current['emisor_telefono'] ?? '', 30),
            'emisor_correo' => self::sanitizeText($input['emisor_correo'] ?? $current['emisor_correo'] ?? '', 150),
            'cod_estable_mh' => DteNumbering::normalizeEstablishmentCode((string) ($input['cod_estable_mh'] ?? $current['cod_estable_mh'] ?? 'S001'), 'S'),
            'cod_estable' => DteNumbering::normalizeEstablishmentCode((string) ($input['cod_estable'] ?? $current['cod_estable'] ?? 'S001'), 'S'),
            'cod_punto_venta_mh' => DteNumbering::normalizeEstablishmentCode((string) ($input['cod_punto_venta_mh'] ?? $current['cod_punto_venta_mh'] ?? 'P001'), 'P'),
            'cod_punto_venta' => DteNumbering::normalizeEstablishmentCode((string) ($input['cod_punto_venta'] ?? $current['cod_punto_venta'] ?? 'P001'), 'P'),
            'api_user' => self::sanitizeText($input['api_user'] ?? $current['api_user'] ?? '', 150),
            'api_password_encrypted' => $apiPasswordEncrypted,
            'certificate_path' => $certificatePath,
            'certificate_password_encrypted' => $certificatePasswordEncrypted,
            'is_active' => 1,
        ];

        $sql = 'UPDATE dte_settings SET
            mode = ?,
            ambiente = ?,
            emisor_nit = ?,
            emisor_nrc = ?,
            emisor_nombre = ?,
            emisor_nombre_comercial = ?,
            emisor_cod_actividad = ?,
            emisor_desc_actividad = ?,
            emisor_tipo_establecimiento = ?,
            emisor_departamento = ?,
            emisor_municipio = ?,
            emisor_direccion = ?,
            emisor_telefono = ?,
            emisor_correo = ?,
            cod_estable_mh = ?,
            cod_estable = ?,
            cod_punto_venta_mh = ?,
            cod_punto_venta = ?,
            api_user = ?,
            api_password_encrypted = ?,
            certificate_path = ?,
            certificate_password_encrypted = ?,
            is_active = ?
            WHERE id = ?';

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('No se pudo guardar la configuracion DTE.');
        }

        $id = (int) ($current['id'] ?? 0);
        $types = 'ssssssssssssssssssssssii';
        $stmt->bind_param(
            $types,
            $payload['mode'],
            $payload['ambiente'],
            $payload['emisor_nit'],
            $payload['emisor_nrc'],
            $payload['emisor_nombre'],
            $payload['emisor_nombre_comercial'],
            $payload['emisor_cod_actividad'],
            $payload['emisor_desc_actividad'],
            $payload['emisor_tipo_establecimiento'],
            $payload['emisor_departamento'],
            $payload['emisor_municipio'],
            $payload['emisor_direccion'],
            $payload['emisor_telefono'],
            $payload['emisor_correo'],
            $payload['cod_estable_mh'],
            $payload['cod_estable'],
            $payload['cod_punto_venta_mh'],
            $payload['cod_punto_venta'],
            $payload['api_user'],
            $payload['api_password_encrypted'],
            $payload['certificate_path'],
            $payload['certificate_password_encrypted'],
            $payload['is_active'],
            $id
        );

        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('No se pudo persistir la configuracion DTE.');
        }

        $stmt->close();

        return self::getActive($db);
    }

    public static function validateEmitter(array $settings): array
    {
        $missing = [];

        foreach (self::REQUIRED_EMITTER_FIELDS as $field => $label) {
            if (trim((string) ($settings[$field] ?? '')) === '') {
                $missing[] = $label;
            }
        }

        if (($settings['emisor_correo'] ?? '') !== '' && !filter_var((string) $settings['emisor_correo'], FILTER_VALIDATE_EMAIL)) {
            $missing[] = 'Correo del emisor valido';
        }

        return array_values(array_unique($missing));
    }

    public static function testConfiguration(mysqli $db): array
    {
        $settings = self::getActive($db);
        $missing = self::validateEmitter($settings);

        if ($missing !== []) {
            return [
                'ok' => false,
                'title' => 'Configuracion incompleta',
                'message' => 'Falta configurar datos DTE del emisor: ' . implode(', ', $missing) . '.',
            ];
        }

        if (self::isSimulation($settings)) {
            $mockCertificate = DteMockHaciendaClient::mockCertificateMetadata();
            $mockResponse = DteMockHaciendaClient::testResponsePreview($settings);

            return [
                'ok' => true,
                'title' => 'Simulacion verificada',
                'message' => 'La configuracion DTE esta lista para trabajar en modo simulacion.',
                'mock_certificate' => $mockCertificate,
                'mock_response' => $mockResponse,
            ];
        }

        try {
            DteHaciendaClient::testConfiguration($settings);
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'title' => 'Modo real no disponible',
                'message' => $exception->getMessage(),
            ];
        }

        return [
            'ok' => false,
            'title' => 'Modo real pendiente',
            'message' => 'Se detecto configuracion real, pero el flujo certificado con Hacienda aun requiere endpoints oficiales y firma electronica valida.',
        ];
    }

    public static function isSimulation(array $settings): bool
    {
        return self::sanitizeMode((string) ($settings['mode'] ?? 'simulation')) === 'simulation';
    }

    public static function modeLabel(string $mode): string
    {
        switch (self::sanitizeMode($mode)) {
            case 'production':
                return 'Modo produccion Hacienda';
            case 'test':
                return 'Modo prueba Hacienda';
            default:
                return 'Modo simulacion';
        }
    }

    public static function environmentCode(string $mode): string
    {
        return self::sanitizeMode($mode) === 'production' ? '01' : '00';
    }

    public static function fiscalValidityLabel(array $settings, array $response = []): string
    {
        $mode = self::sanitizeMode((string) ($settings['mode'] ?? 'simulation'));
        $selloRecibido = trim((string) ($response['selloRecibido'] ?? ''));
        $estado = strtoupper(trim((string) ($response['estado'] ?? '')));

        if ($mode === 'production' && $estado === 'PROCESADO' && $selloRecibido !== '') {
            return 'VALIDACION REAL EN HACIENDA';
        }

        if ($mode === 'test') {
            return 'NO VALIDO FISCALMENTE - MODO PRUEBA HACIENDA';
        }

        return 'NO VALIDO FISCALMENTE - MODO SIMULACION';
    }

    public static function decryptedApiPassword(array $settings): string
    {
        return atenea_decrypt_secret((string) ($settings['api_password_encrypted'] ?? ''));
    }

    public static function decryptedCertificatePassword(array $settings): string
    {
        return atenea_decrypt_secret((string) ($settings['certificate_password_encrypted'] ?? ''));
    }

    private static function ensureDefaultSettings(mysqli $db): void
    {
        $result = $db->query('SELECT COUNT(*) AS total FROM dte_settings');
        if (!$result instanceof mysqli_result) {
            throw new RuntimeException('No se pudo validar la configuracion DTE inicial.');
        }

        $row = $result->fetch_assoc() ?: ['total' => 0];
        mysqli_free_result($result);

        if ((int) ($row['total'] ?? 0) > 0) {
            return;
        }

        $defaults = self::defaultSettings();
        $sql = 'INSERT INTO dte_settings (
            mode, ambiente, emisor_nit, emisor_nrc, emisor_nombre, emisor_nombre_comercial,
            emisor_cod_actividad, emisor_desc_actividad, emisor_tipo_establecimiento,
            emisor_departamento, emisor_municipio, emisor_direccion, emisor_telefono, emisor_correo,
            cod_estable_mh, cod_estable, cod_punto_venta_mh, cod_punto_venta,
            api_user, api_password_encrypted, certificate_path, certificate_password_encrypted, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, NULL, NULL, 1)';

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('No se pudo crear la configuracion DTE inicial.');
        }

        $stmt->bind_param(
            'ssssssssssssssssss',
            $defaults['mode'],
            $defaults['ambiente'],
            $defaults['emisor_nit'],
            $defaults['emisor_nrc'],
            $defaults['emisor_nombre'],
            $defaults['emisor_nombre_comercial'],
            $defaults['emisor_cod_actividad'],
            $defaults['emisor_desc_actividad'],
            $defaults['emisor_tipo_establecimiento'],
            $defaults['emisor_departamento'],
            $defaults['emisor_municipio'],
            $defaults['emisor_direccion'],
            $defaults['emisor_telefono'],
            $defaults['emisor_correo'],
            $defaults['cod_estable_mh'],
            $defaults['cod_estable'],
            $defaults['cod_punto_venta_mh'],
            $defaults['cod_punto_venta']
        );

        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('No se pudo insertar la configuracion DTE por defecto.');
        }

        $stmt->close();
    }

    private static function defaultSettings(): array
    {
        return [
            'mode' => 'simulation',
            'ambiente' => '00',
            'emisor_nit' => '00000000000000',
            'emisor_nrc' => '0000000',
            'emisor_nombre' => 'ATENEA SIMULACION',
            'emisor_nombre_comercial' => 'ATENEA',
            'emisor_cod_actividad' => '99999',
            'emisor_desc_actividad' => 'FORMACION DIGITAL SIMULADA',
            'emisor_tipo_establecimiento' => 'CASA MATRIZ',
            'emisor_departamento' => 'SAN SALVADOR',
            'emisor_municipio' => 'SAN SALVADOR CENTRO',
            'emisor_direccion' => 'CONFIGURACION INICIAL DE SIMULACION',
            'emisor_telefono' => '0000-0000',
            'emisor_correo' => 'simulacion@atenea.local',
            'cod_estable_mh' => 'S001',
            'cod_estable' => 'S001',
            'cod_punto_venta_mh' => 'P001',
            'cod_punto_venta' => 'P001',
        ];
    }

    private static function sanitizeMode(string $mode): string
    {
        $mode = strtolower(trim($mode));

        if (!in_array($mode, ['simulation', 'test', 'production'], true)) {
            return 'simulation';
        }

        return $mode;
    }

    private static function sanitizeText($value, int $maxLength): string
    {
        $value = trim((string) $value);

        return mb_substr($value, 0, $maxLength, 'UTF-8');
    }
}
