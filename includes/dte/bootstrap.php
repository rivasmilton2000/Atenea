<?php

require_once __DIR__ . '/../app_security.php';

if (!function_exists('atenea_dte_vendor_bootstrap')) {
    function atenea_dte_vendor_bootstrap(): void
    {
        static $booted = false;

        if ($booted) {
            return;
        }

        $autoload = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
        if (is_file($autoload)) {
            require_once $autoload;
        }

        if (!class_exists('TCPDF')) {
            $tcpdfPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'tecnickcom' . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php';
            if (is_file($tcpdfPath)) {
                require_once $tcpdfPath;
            }
        }

        if (!class_exists('TCPDF')) {
            throw new RuntimeException('TCPDF no esta disponible para la generacion del PDF DTE.');
        }

        $booted = true;
    }
}

atenea_dte_vendor_bootstrap();

require_once __DIR__ . '/DteSchema.php';
require_once __DIR__ . '/DteStorage.php';
require_once __DIR__ . '/DteConfig.php';
require_once __DIR__ . '/DteNumbering.php';
require_once __DIR__ . '/DteMoneyToWords.php';
require_once __DIR__ . '/DteQrService.php';
require_once __DIR__ . '/DteJsonBuilder.php';
require_once __DIR__ . '/DteMockHaciendaClient.php';
require_once __DIR__ . '/DteHaciendaClient.php';
require_once __DIR__ . '/DtePdfRenderer.php';
require_once __DIR__ . '/DteService.php';
require_once __DIR__ . '/DteAcademicService.php';
