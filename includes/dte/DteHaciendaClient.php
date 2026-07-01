<?php

class DteHaciendaClient
{
    public static function testConfiguration(array $settings): void
    {
        self::assertReady($settings);

        throw new RuntimeException('La conexion real con Hacienda aun requiere configurar endpoints oficiales y firma electronica certificada. Mientras tanto, utiliza modo simulacion.');
    }

    public static function submit(array $dtePayload, array $settings): array
    {
        self::assertReady($settings);

        throw new RuntimeException('El envio real a Hacienda aun no esta implementado en Atenea porque faltan endpoints oficiales y firma certificada valida. El sistema no inventara una aceptacion real.');
    }

    public static function assertReady(array $settings): void
    {
        $missing = DteConfig::validateEmitter($settings);
        if ($missing !== []) {
            throw new RuntimeException('Falta configurar datos DTE del emisor.');
        }

        $apiUser = trim((string) ($settings['api_user'] ?? ''));
        $apiPassword = DteConfig::decryptedApiPassword($settings);
        $certificatePath = trim((string) ($settings['certificate_path'] ?? ''));
        $certificateFile = DteStorage::resolveStoredFile($certificatePath, [DteStorage::directory('certificates')]);

        if ($apiUser === '' || $apiPassword === '') {
            throw new RuntimeException('El modo real requiere usuario API y credenciales validas de Hacienda.');
        }

        if (empty($certificateFile['absolute_path']) || !is_readable((string) $certificateFile['absolute_path'])) {
            throw new RuntimeException('El modo real requiere certificado valido de Hacienda en storage/dte/certificates/.');
        }
    }
}
