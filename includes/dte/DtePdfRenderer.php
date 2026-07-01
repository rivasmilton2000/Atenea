<?php

class AteneaDtePdfDocument extends TCPDF
{
    private array $footerContext = [];

    public function setFooterContext(array $context): void
    {
        $this->footerContext = $context;
    }

    public function Footer(): void
    {
        $this->SetY(-18);
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(90, 90, 90);
        $this->Cell(0, 5, 'Documento generado por Atenea', 0, 1, 'L');

        $fiscalValidity = trim((string) ($this->footerContext['fiscal_validity'] ?? ''));
        if ($fiscalValidity !== '') {
            $this->SetTextColor(165, 28, 48);
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(0, 5, $fiscalValidity, 0, 0, 'L');
        }

        $this->SetTextColor(120, 120, 120);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(0, 5, 'Pagina ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'R');
    }
}

class DtePdfRenderer
{
    public static function render(array $context, string $absolutePath): void
    {
        $dte = $context['dte'] ?? [];
        $response = $context['response'] ?? [];
        $settings = $context['settings'] ?? [];
        $order = $context['order'] ?? [];

        $pdf = new AteneaDtePdfDocument('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Atenea');
        $pdf->SetAuthor('Atenea');
        $pdf->SetTitle('DTE ' . (string) ($dte['identificacion']['numeroControl'] ?? ''));
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 24);
        $pdf->setFooterContext([
            'fiscal_validity' => (string) ($context['fiscal_validity'] ?? ''),
        ]);
        $pdf->AddPage();

        self::renderHeader($pdf, $dte, $response, $settings);
        self::renderQrs($pdf, $dte, $response, $settings, (string) ($context['status_label'] ?? 'PROCESADO SIMULADO'));
        self::renderReceiverBlock($pdf, $dte, $order);
        self::renderItemsTable($pdf, $dte);
        self::renderTotals($pdf, $dte, $context);
        self::renderExtensionAndObservations($pdf, $dte, $context);

        $pdf->Output($absolutePath, 'F');

        if (!is_file($absolutePath)) {
            throw new RuntimeException('No se pudo generar el PDF DTE.');
        }
    }

    private static function renderHeader(TCPDF $pdf, array $dte, array $response, array $settings): void
    {
        $emisor = $dte['emisor'] ?? [];
        $identificacion = $dte['identificacion'] ?? [];
        $logoPath = self::logoPath();

        $pdf->SetDrawColor(198, 206, 214);
        $pdf->SetFillColor(248, 249, 251);
        $pdf->RoundedRect(12, 12, 108, 45, 2, '1111', 'DF');
        $pdf->RoundedRect(123, 12, 75, 45, 2, '1111', 'DF');

        if ($logoPath !== null) {
            $pdf->Image($logoPath, 16, 16, 15, 15, '', '', '', false, 300, '', false, false, 0, false, false, false);
        }

        $pdf->SetTextColor(25, 35, 50);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetXY($logoPath !== null ? 34 : 16, 15);
        $pdf->Cell(78, 6, 'ATENEA', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 8.5);
        $pdf->SetX($logoPath !== null ? 34 : 16);
        $pdf->MultiCell(
            78,
            4.5,
            trim((string) ($emisor['nombre'] ?? 'ATENEA SIMULACION')) . "\n"
            . 'NIT: ' . trim((string) ($emisor['nit'] ?? '')) . '   NRC: ' . trim((string) ($emisor['nrc'] ?? '')) . "\n"
            . trim((string) ($emisor['descActividad'] ?? '')) . "\n"
            . trim((string) ($emisor['direccion']['complemento'] ?? '')) . "\n"
            . trim((string) ($emisor['correo'] ?? '')) . '   Tel: ' . trim((string) ($emisor['telefono'] ?? '')),
            0,
            'L'
        );

        $pdf->SetXY(126, 15);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->MultiCell(69, 5, "DOCUMENTO TRIBUTARIO ELECTRONICO\nFACTURA", 0, 'C');

        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetXY(126, 28);
        $rows = [
            ['Codigo generacion', (string) ($identificacion['codigoGeneracion'] ?? '')],
            ['Sello de recepcion', (string) ($response['selloRecibido'] ?? 'SIMULADO')],
            ['Numero de control', (string) ($identificacion['numeroControl'] ?? '')],
            ['Modelo facturacion', self::modelLabel((int) ($identificacion['tipoModelo'] ?? 1))],
            ['Version JSON', (string) ($identificacion['version'] ?? 1)],
            ['Tipo de transmision', self::operationLabel((int) ($identificacion['tipoOperacion'] ?? 1))],
            ['Hora de emision', (string) ($identificacion['horEmi'] ?? '')],
            ['Fecha de emision', (string) ($identificacion['fecEmi'] ?? '')],
            ['Tipo DTE', (string) ($identificacion['tipoDte'] ?? '01')],
            ['Moneda', (string) ($identificacion['tipoMoneda'] ?? 'USD')],
        ];

        $y = 28;
        foreach ($rows as $row) {
            $pdf->SetXY(126, $y);
            $pdf->SetFont('helvetica', 'B', 6.6);
            $pdf->Cell(22, 4.2, $row[0], 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 6.6);
            $pdf->MultiCell(47, 4.2, $row[1], 0, 'L');
            $y += 4.2;
            if ($y > 53) {
                break;
            }
        }
    }

    private static function renderQrs(TCPDF $pdf, array $dte, array $response, array $settings, string $statusLabel): void
    {
        $consultaPayload = DteQrService::buildConsultaPayload($dte, $response, $settings);
        $codigoPayload = DteQrService::buildCodigoGeneracionPayload((string) ($dte['identificacion']['codigoGeneracion'] ?? ''));
        $selloPayload = DteQrService::buildSelloPayload((string) ($response['selloRecibido'] ?? ''));

        $qrY = 61;
        $size = 24;
        DteQrService::renderToPdf($pdf, $consultaPayload, 18, $qrY, $size, 'Consulta MH');
        DteQrService::renderToPdf($pdf, $codigoPayload, 81, $qrY, $size, 'Codigo generacion');
        DteQrService::renderToPdf($pdf, $selloPayload, 144, $qrY, $size, 'Sello recepcion');

        $pdf->SetXY(12, 91);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(56, 69, 85);
        $pdf->Cell(0, 5, 'Estado interno: ' . trim($statusLabel !== '' ? $statusLabel : 'PROCESADO SIMULADO'), 0, 1, 'L');
    }

    private static function renderReceiverBlock(TCPDF $pdf, array $dte, array $order): void
    {
        $receptor = $dte['receptor'] ?? [];
        $resumen = $dte['resumen'] ?? [];
        $pdf->SetFillColor(248, 249, 251);
        $pdf->RoundedRect(12, 97, 186, 31, 2, '1111', 'DF');
        $pdf->SetXY(15, 100);
        $pdf->SetTextColor(25, 35, 50);
        $pdf->SetFont('helvetica', 'B', 9.5);
        $pdf->Cell(0, 5, 'Datos del cliente / receptor', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 7.6);

        $leftColumn = [
            'Cliente: ' . self::safeValue($receptor['nombre'] ?? ''),
            'Actividad economica: ' . self::safeValue($receptor['descActividad'] ?? '', 'No especificada'),
            'Correo: ' . self::safeValue($receptor['correo'] ?? '', 'No especificado'),
            'Direccion: ' . self::safeValue($receptor['direccion']['complemento'] ?? '', 'No especificada'),
            'Municipio: ' . self::safeValue($receptor['direccion']['municipio'] ?? '', 'No especificado'),
            'Departamento: ' . self::safeValue($receptor['direccion']['departamento'] ?? '', 'No especificado'),
        ];

        $rightColumn = [
            'DUI/NIT: ' . self::safeValue($receptor['numDocumento'] ?? '', 'No especificado'),
            'NRC: ' . self::safeValue($receptor['nrc'] ?? '', 'No especificado'),
            'Telefono: ' . self::safeValue($receptor['telefono'] ?? '', 'No especificado'),
            'Forma pago: Tarjeta (Stripe)',
            'Moneda: ' . self::safeValue($dte['identificacion']['tipoMoneda'] ?? 'USD'),
            'Pago electronico: ' . self::safeValue($resumen['numPagoElectronico'] ?? '', 'No especificado'),
        ];

        $pdf->SetXY(15, 106);
        $pdf->MultiCell(96, 4.1, implode("\n", $leftColumn), 0, 'L');
        $pdf->SetXY(112, 106);
        $pdf->MultiCell(80, 4.1, implode("\n", $rightColumn), 0, 'L');
    }

    private static function renderItemsTable(TCPDF $pdf, array $dte): void
    {
        $headers = ['No.', 'Tipo item', 'Cant.', 'U. med.', 'Codigo', 'Descripcion', 'P. unit.', 'Desc.', 'No suj.', 'Exenta', 'Gravada'];
        $widths = [7, 13, 10, 12, 15, 50, 16, 14, 14, 14, 21];
        $x = 12;
        $y = 132;

        $pdf->SetXY($x, $y);
        $pdf->SetFillColor(226, 231, 236);
        $pdf->SetTextColor(35, 45, 60);
        $pdf->SetFont('helvetica', 'B', 6.7);

        foreach ($headers as $index => $header) {
            $pdf->MultiCell($widths[$index], 8, $header, 1, 'C', true, 0);
        }
        $pdf->Ln();

        $pdf->SetFont('helvetica', '', 6.6);
        $pdf->SetTextColor(35, 35, 35);
        $rows = $dte['cuerpoDocumento'] ?? [];

        foreach ($rows as $row) {
            $description = (string) ($row['descripcion'] ?? '');
            $descriptionHeight = max(8, $pdf->getStringHeight($widths[5], $description) + 1.5);

            if ($pdf->GetY() + $descriptionHeight > 255) {
                $pdf->AddPage();
                $pdf->SetXY($x, 18);
                $pdf->SetFillColor(226, 231, 236);
                $pdf->SetTextColor(35, 45, 60);
                $pdf->SetFont('helvetica', 'B', 6.7);
                foreach ($headers as $index => $header) {
                    $pdf->MultiCell($widths[$index], 8, $header, 1, 'C', true, 0);
                }
                $pdf->Ln();
                $pdf->SetFont('helvetica', '', 6.6);
                $pdf->SetTextColor(35, 35, 35);
            }

            $rowValues = [
                (string) ($row['numItem'] ?? ''),
                self::itemTypeLabel((int) ($row['tipoItem'] ?? 1)),
                (string) ($row['cantidad'] ?? ''),
                (string) ($row['uniMedida'] ?? ''),
                (string) ($row['codigo'] ?? ''),
                $description,
                '$' . number_format((float) ($row['precioUni'] ?? 0), 2),
                '$' . number_format((float) ($row['montoDescu'] ?? 0), 2),
                '$' . number_format((float) ($row['ventaNoSuj'] ?? 0), 2),
                '$' . number_format((float) ($row['ventaExenta'] ?? 0), 2),
                '$' . number_format((float) ($row['ventaGravada'] ?? 0), 2),
            ];

            $alignments = ['C', 'C', 'C', 'C', 'C', 'L', 'R', 'R', 'R', 'R', 'R'];
            $startX = $x;
            $startY = $pdf->GetY();

            foreach ($rowValues as $index => $value) {
                $pdf->SetXY($startX, $startY);
                $pdf->MultiCell($widths[$index], $descriptionHeight, $value, 1, $alignments[$index], false, 0);
                $startX += $widths[$index];
            }

            $pdf->SetY($startY + $descriptionHeight);
        }
    }

    private static function renderTotals(TCPDF $pdf, array $dte, array $context): void
    {
        $resumen = $dte['resumen'] ?? [];
        $y = $pdf->GetY() + 4;
        if ($y > 232) {
            $pdf->AddPage();
            $y = 20;
        }

        $pdf->SetXY(12, $y);
        $pdf->SetFont('helvetica', 'B', 8.5);
        $pdf->SetTextColor(25, 35, 50);
        $pdf->Cell(96, 5, 'Valor en letras', 0, 1, 'L');
        $pdf->SetXY(12, $y + 6);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->MultiCell(96, 18, self::safeValue($resumen['totalLetras'] ?? '', 'NO DISPONIBLE'), 1, 'L', false);

        $rows = [
            ['Sumas', (float) ($resumen['subTotalVentas'] ?? 0)],
            ['Total descuentos', (float) ($resumen['totalDescu'] ?? 0)],
            ['Sub-total', (float) ($resumen['subTotal'] ?? 0)],
            ['IVA percibido', (float) ($resumen['totalIva'] ?? 0)],
            ['IVA retenido', (float) ($resumen['ivaRete1'] ?? 0)],
            ['Monto total operacion', (float) ($resumen['montoTotalOperacion'] ?? 0)],
            ['Total a pagar', (float) ($resumen['totalPagar'] ?? 0)],
        ];

        $totalsX = 116;
        $totalsY = $y;
        foreach ($rows as $index => $row) {
            $fill = $index === count($rows) - 1;
            if ($fill) {
                $pdf->SetFillColor(29, 78, 137);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont('helvetica', 'B', 8.1);
            } else {
                $pdf->SetFillColor(248, 249, 251);
                $pdf->SetTextColor(25, 35, 50);
                $pdf->SetFont('helvetica', '', 8);
            }

            $pdf->SetXY($totalsX, $totalsY);
            $pdf->Cell(48, 6.6, $row[0], 1, 0, 'L', true);
            $pdf->Cell(34, 6.6, '$' . number_format((float) $row[1], 2), 1, 1, 'R', true);
            $totalsY += 6.6;
        }

        $pdf->SetTextColor(25, 35, 50);
        $pdf->SetFont('helvetica', '', 7.8);
        $pdf->SetXY(12, max($y + 26, $totalsY + 3));
        $pdf->MultiCell(
            186,
            5,
            'Validez fiscal: ' . self::safeValue($context['fiscal_validity'] ?? '', 'NO VALIDO FISCALMENTE'),
            0,
            'L'
        );
    }

    private static function renderExtensionAndObservations(TCPDF $pdf, array $dte, array $context): void
    {
        $extension = $dte['extension'] ?? [];
        $response = $context['response'] ?? [];
        $y = $pdf->GetY() + 5;
        if ($y > 235) {
            $pdf->AddPage();
            $y = 20;
        }

        $pdf->SetFillColor(248, 249, 251);
        $pdf->RoundedRect(12, $y, 91, 22, 2, '1111', 'DF');
        $pdf->RoundedRect(107, $y, 91, 22, 2, '1111', 'DF');

        $pdf->SetXY(15, $y + 2);
        $pdf->SetFont('helvetica', 'B', 8.5);
        $pdf->Cell(0, 5, 'Extension', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 7.8);
        $pdf->SetXY(15, $y + 8);
        $pdf->MultiCell(
            84,
            4.5,
            'Entrega: ' . self::safeValue($extension['nombEntrega'] ?? '', 'No especificado') . "\n"
            . 'Documento: ' . self::safeValue($extension['docuEntrega'] ?? '', 'No especificado') . "\n"
            . 'Recibe: ' . self::safeValue($extension['nombRecibe'] ?? '', 'Atenea'),
            0,
            'L'
        );

        $pdf->SetXY(110, $y + 2);
        $pdf->SetFont('helvetica', 'B', 8.5);
        $pdf->Cell(0, 5, 'Observaciones', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 7.8);
        $pdf->SetXY(110, $y + 8);
        $pdf->MultiCell(
            84,
            4.4,
            'Mensaje: ' . self::safeValue($extension['observaciones'] ?? '', 'Sin observaciones') . "\n"
            . 'Respuesta Hacienda: ' . self::safeValue($response['descripcionMsg'] ?? '', 'Sin respuesta') . "\n"
            . 'Sello: ' . self::safeValue($response['selloRecibido'] ?? '', 'No disponible'),
            0,
            'L'
        );
    }

    private static function logoPath(): ?string
    {
        $candidates = [
            dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'Atenea Logo.png',
            dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'cecsb_logo.png',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private static function safeValue($value, string $fallback = 'No especificado'): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : $fallback;
    }

    private static function modelLabel(int $tipoModelo): string
    {
        return $tipoModelo === 1 ? 'Previo' : (string) $tipoModelo;
    }

    private static function operationLabel(int $tipoOperacion): string
    {
        return $tipoOperacion === 1 ? 'Normal' : (string) $tipoOperacion;
    }

    private static function itemTypeLabel(int $tipoItem): string
    {
        switch ($tipoItem) {
            case 2:
                return 'Servicio';
            case 3:
                return 'Otro';
            default:
                return 'Bien';
        }
    }
}
