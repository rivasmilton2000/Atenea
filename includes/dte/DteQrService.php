<?php

class DteQrService
{
    public static function buildConsultaPayload(array $dtePayload, array $responsePayload, array $settings): string
    {
        $codigoGeneracion = (string) ($dtePayload['identificacion']['codigoGeneracion'] ?? '');
        $numeroControl = (string) ($dtePayload['identificacion']['numeroControl'] ?? '');
        $fechaEmision = (string) ($dtePayload['identificacion']['fecEmi'] ?? '');
        $selloRecibido = (string) ($responsePayload['selloRecibido'] ?? '');
        $totalPagar = (string) ($dtePayload['resumen']['totalPagar'] ?? '');
        $modo = strtolower((string) ($settings['mode'] ?? 'simulation'));

        if ($modo === 'production' && defined('ATENEA_DTE_MH_CONSULTA_URL') && trim((string) ATENEA_DTE_MH_CONSULTA_URL) !== '') {
            return rtrim((string) ATENEA_DTE_MH_CONSULTA_URL, '?&') . '?codigoGeneracion=' . rawurlencode($codigoGeneracion) . '&fechaEmision=' . rawurlencode($fechaEmision);
        }

        return 'SIMULACION|ATENEA|' . $codigoGeneracion . '|' . $numeroControl . '|' . $fechaEmision . '|' . $totalPagar . '|' . $selloRecibido;
    }

    public static function buildCodigoGeneracionPayload(string $codigoGeneracion): string
    {
        return 'CODIGO-GENERACION|' . trim($codigoGeneracion);
    }

    public static function buildSelloPayload(string $selloRecibido): string
    {
        return 'SELLO-RECEPCION|' . trim($selloRecibido);
    }

    public static function renderToPdf(TCPDF $pdf, string $payload, float $x, float $y, float $size, string $label): void
    {
        $style = [
            'border' => 0,
            'padding' => 0,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false,
        ];

        try {
            $pdf->write2DBarcode($payload, 'QRCODE,M', $x, $y, $size, $size, $style, 'N');
        } catch (Throwable $exception) {
            $pdf->SetXY($x, $y);
            $pdf->SetFont('helvetica', '', 7);
            $pdf->MultiCell($size, 6, "QR no disponible\n" . $payload, 1, 'C');
        }

        $pdf->SetXY($x, $y + $size + 1.2);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->MultiCell($size, 5, $label, 0, 'C');
    }
}
