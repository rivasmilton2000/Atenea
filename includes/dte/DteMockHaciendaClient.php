<?php

class DteMockHaciendaClient
{
    public static function submit(array $dtePayload, array $settings): array
    {
        $template = self::loadResponseTemplate();
        $codigoGeneracion = (string) ($dtePayload['identificacion']['codigoGeneracion'] ?? '');
        $selloRecibido = strtoupper(bin2hex(random_bytes(20)));
        $fhProcesamiento = (new DateTimeImmutable('now', new DateTimeZone('America/El_Salvador')))->format('Y-m-d\TH:i:s');

        $template['ambiente'] = DteConfig::environmentCode((string) ($settings['mode'] ?? 'simulation'));
        $template['codigoGeneracion'] = $codigoGeneracion;
        $template['selloRecibido'] = $selloRecibido;
        $template['fhProcesamiento'] = $fhProcesamiento;
        $template['descripcionMsg'] = 'RECIBIDO - SIMULADO';

        return $template;
    }

    public static function buildSimulatedSignature(array $dtePayload): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
            'simulado' => true,
        ];

        $payload = [
            'iss' => 'ATENEA-SIMULACION',
            'iat' => time(),
            'codigoGeneracion' => (string) ($dtePayload['identificacion']['codigoGeneracion'] ?? ''),
            'numeroControl' => (string) ($dtePayload['identificacion']['numeroControl'] ?? ''),
            'advertencia' => 'Firma electronica simulada. No valida fiscalmente.',
        ];

        $headerEncoded = self::base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}');
        $payloadEncoded = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}');
        $signature = self::base64UrlEncode('FIRMA-SIMULADA-' . bin2hex(random_bytes(12)));

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signature;
    }

    public static function mockCertificateMetadata(): array
    {
        $path = DteStorage::mockPath('mock_certificado_hacienda_SIMULADO.json');
        if (is_file($path)) {
            $contents = file_get_contents($path);
            $decoded = is_string($contents) ? json_decode($contents, true) : null;
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [
            'modo' => 'SIMULACION',
            'nit' => '00000000000000',
            'nombre' => 'ATENEA SIMULACION',
            'advertencia' => 'Certificado simulado. No valido ante Ministerio de Hacienda.',
        ];
    }

    public static function testResponsePreview(array $settings): array
    {
        return [
            'mode' => (string) ($settings['mode'] ?? 'simulation'),
            'ambiente' => DteConfig::environmentCode((string) ($settings['mode'] ?? 'simulation')),
            'estado' => 'PROCESADO',
            'codigoMsg' => '001',
            'descripcionMsg' => 'RECIBIDO - SIMULADO',
        ];
    }

    private static function loadResponseTemplate(): array
    {
        $path = DteStorage::mockPath('mock_respuesta_recepcion_SIMULADA.json');
        if (is_file($path)) {
            $contents = file_get_contents($path);
            $decoded = is_string($contents) ? json_decode($contents, true) : null;
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [
            'version' => 1,
            'ambiente' => '00',
            'versionApp' => 2,
            'estado' => 'PROCESADO',
            'codigoGeneracion' => '',
            'selloRecibido' => '',
            'fhProcesamiento' => '',
            'clasificaMsg' => '10',
            'codigoMsg' => '001',
            'descripcionMsg' => 'RECIBIDO - SIMULADO',
            'observaciones' => [],
        ];
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
