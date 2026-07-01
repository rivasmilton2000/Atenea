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
    private const PAGE_LEFT = 12.0;
    private const PAGE_RIGHT = 12.0;
    private const PAGE_BOTTOM = 24.0;
    private const CONTENT_WIDTH = 186.0;

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
        $pdf->SetMargins(self::PAGE_LEFT, 12, self::PAGE_RIGHT);
        $pdf->SetAutoPageBreak(true, self::PAGE_BOTTOM);
        $pdf->setFooterContext([
            'fiscal_validity' => (string) ($context['fiscal_validity'] ?? ''),
        ]);
        $pdf->AddPage();

        $cursorY = 12.0;
        $cursorY = self::renderHeader($pdf, $dte, $response, $settings, $cursorY);
        $cursorY = self::renderQrs($pdf, $dte, $response, $settings, $cursorY + 4.0);
        $cursorY = self::renderReceiverBlock($pdf, $dte, $order, $cursorY + 4.0);
        $cursorY = self::renderItemsTable($pdf, $dte, $cursorY + 4.0);
        $cursorY = self::renderTotals($pdf, $dte, $context, $cursorY + 4.0);
        self::renderExtensionAndObservations($pdf, $dte, $context, $cursorY + 5.0);

        $pdf->Output($absolutePath, 'F');

        if (!is_file($absolutePath)) {
            throw new RuntimeException('No se pudo generar el PDF DTE.');
        }
    }

    private static function renderHeader(TCPDF $pdf, array $dte, array $response, array $settings, float $startY): float
    {
        $emisor = $dte['emisor'] ?? [];
        $identificacion = $dte['identificacion'] ?? [];
        $logoPath = self::logoPath();

        $leftWidth = 108.0;
        $gap = 4.0;
        $rightWidth = self::CONTENT_WIDTH - $leftWidth - $gap;
        $leftX = self::PAGE_LEFT;
        $rightX = $leftX + $leftWidth + $gap;
        $padding = 4.0;

        $rows = [
            ['Codigo generacion', (string) ($identificacion['codigoGeneracion'] ?? '')],
            ['Sello de recepcion', (string) ($response['selloRecibido'] ?? 'SIMULADO')],
            ['Numero de control', (string) ($identificacion['numeroControl'] ?? '')],
            ['Modelo facturacion', self::modelLabel((int) ($identificacion['tipoModelo'] ?? 1))],
            ['Version JSON', (string) ($identificacion['version'] ?? 1)],
            ['Tipo de transmision', self::operationLabel((int) ($identificacion['tipoOperacion'] ?? 1))],
            ['Hora emision', (string) ($identificacion['horEmi'] ?? '')],
            ['Fecha emision', (string) ($identificacion['fecEmi'] ?? '')],
        ];

        $labelWidth = 24.0;
        $valueWidth = $rightWidth - ($padding * 2) - $labelWidth;
        $rowsHeight = 0.0;
        foreach ($rows as $row) {
            $wrappedValue = self::formatHeaderValue($row[0], (string) $row[1]);
            $pdf->SetFont('helvetica', 'B', 7.1);
            $labelHeight = $pdf->getStringHeight($labelWidth, $row[0] . ':') + 0.8;
            $pdf->SetFont('helvetica', '', 6.8);
            $valueHeight = $pdf->getStringHeight($valueWidth, $wrappedValue) + 0.8;
            $rowHeight = max(
                5.2,
                $labelHeight,
                $valueHeight
            );
            $rowsHeight += $rowHeight;
        }

        $titleHeight = 13.0;
        $rightHeight = ($padding * 2) + $titleHeight + 2.5 + $rowsHeight;

        $commercialName = trim((string) ($emisor['nombreComercial'] ?? 'ATENEA'));
        $legalName = trim((string) ($emisor['nombre'] ?? 'ATENEA'));
        $emitterLines = [];
        $emitterLines[] = 'NIT: ' . self::displayValue($emisor['nit'] ?? '', 'No disponible');
        if (trim((string) ($emisor['nrc'] ?? '')) !== '') {
            $emitterLines[] = 'NRC: ' . trim((string) $emisor['nrc']);
        }
        $emitterLines[] = 'Actividad economica: ' . self::displayValue($emisor['descActividad'] ?? '', 'No disponible');
        $emitterLines[] = 'Direccion: ' . self::displayValue($emisor['direccion']['complemento'] ?? '', 'No disponible');
        $emitterLines[] = 'Correo: ' . self::displayValue($emisor['correo'] ?? '', 'No disponible');
        $emitterLines[] = 'Telefono: ' . self::displayValue($emisor['telefono'] ?? '', 'No disponible');

        $logoWidth = $logoPath !== null ? 18.0 : 0.0;
        $headerTextWidth = $leftWidth - ($padding * 2) - $logoWidth - ($logoWidth > 0 ? 2.0 : 0.0);
        $detailWidth = $leftWidth - ($padding * 2);
        $pdf->SetFont('helvetica', 'B', 12.5);
        $headerTextHeight = max(
            14.0,
            $pdf->getStringHeight($headerTextWidth, $commercialName . "\n" . $legalName) + 1.0
        );
        $pdf->SetFont('helvetica', '', 8.3);
        $detailsHeight = $pdf->getStringHeight($detailWidth, implode("\n", $emitterLines)) + 1.0;
        $leftHeight = ($padding * 2) + $headerTextHeight + $detailsHeight + 2.0;

        $boxHeight = max(58.0, $leftHeight, $rightHeight);

        $pdf->SetDrawColor(198, 206, 214);
        $pdf->SetFillColor(248, 249, 251);
        $pdf->RoundedRect($leftX, $startY, $leftWidth, $boxHeight, 2, '1111', 'DF');
        $pdf->RoundedRect($rightX, $startY, $rightWidth, $boxHeight, 2, '1111', 'DF');

        if ($logoPath !== null) {
            $pdf->Image($logoPath, $leftX + $padding, $startY + $padding, 14, 14, '', '', '', false, 300, '', false, false, 0, false, false, false);
        }

        $headerTextX = $leftX + $padding + ($logoWidth > 0 ? $logoWidth + 2.0 : 0.0);
        $pdf->SetTextColor(25, 35, 50);
        $pdf->SetFont('helvetica', 'B', 12.5);
        $pdf->SetXY($headerTextX, $startY + $padding);
        $pdf->MultiCell($headerTextWidth, 5.5, $commercialName, 0, 'L');

        $pdf->SetFont('helvetica', '', 9.0);
        $pdf->SetX($headerTextX);
        $pdf->MultiCell($headerTextWidth, 4.8, $legalName, 0, 'L');

        $detailY = $startY + $padding + $headerTextHeight + 1.0;
        $pdf->SetFont('helvetica', '', 8.3);
        $pdf->SetXY($leftX + $padding, $detailY);
        $pdf->MultiCell($detailWidth, 4.4, implode("\n", $emitterLines), 0, 'L');

        $pdf->SetTextColor(25, 35, 50);
        $pdf->SetFont('helvetica', 'B', 10.8);
        $pdf->SetXY($rightX + $padding, $startY + $padding);
        $pdf->MultiCell($rightWidth - ($padding * 2), 5.4, "DOCUMENTO TRIBUTARIO ELECTRONICO\nFACTURA", 0, 'C');

        $rowsY = $startY + $padding + $titleHeight + 2.0;
        foreach ($rows as $row) {
            $wrappedValue = self::formatHeaderValue($row[0], (string) $row[1]);
            $pdf->SetFont('helvetica', 'B', 7.1);
            $labelHeight = $pdf->getStringHeight($labelWidth, $row[0] . ':') + 0.8;
            $pdf->SetFont('helvetica', '', 6.8);
            $valueHeight = $pdf->getStringHeight($valueWidth, $wrappedValue) + 0.8;
            $rowHeight = max(
                5.2,
                $labelHeight,
                $valueHeight
            );

            $pdf->SetFont('helvetica', 'B', 7.1);
            $pdf->SetXY($rightX + $padding, $rowsY);
            $pdf->MultiCell($labelWidth, $rowHeight, $row[0] . ':', 0, 'L');

            $pdf->SetFont('helvetica', '', 6.8);
            $pdf->SetXY($rightX + $padding + $labelWidth, $rowsY);
            $pdf->MultiCell($valueWidth, $rowHeight, $wrappedValue, 0, 'L');
            $rowsY += $rowHeight;
        }

        return $startY + $boxHeight;
    }

    private static function renderQrs(TCPDF $pdf, array $dte, array $response, array $settings, float $startY): float
    {
        self::ensureSpace($pdf, 40.0);

        $consultaPayload = DteQrService::buildConsultaPayload($dte, $response, $settings);
        $codigoPayload = DteQrService::buildCodigoGeneracionPayload((string) ($dte['identificacion']['codigoGeneracion'] ?? ''));
        $selloPayload = DteQrService::buildSelloPayload((string) ($response['selloRecibido'] ?? ''));

        $cellWidth = 58.0;
        $gap = 6.0;
        $cellHeight = 36.0;
        $qrSize = 25.5;
        $cells = [
            ['label' => 'Consulta MH', 'payload' => $consultaPayload],
            ['label' => 'Codigo generacion', 'payload' => $codigoPayload],
            ['label' => 'Sello recepcion', 'payload' => $selloPayload],
        ];

        $x = self::PAGE_LEFT;
        foreach ($cells as $cell) {
            $pdf->SetDrawColor(214, 220, 226);
            $pdf->SetFillColor(252, 252, 252);
            $pdf->RoundedRect($x, $startY, $cellWidth, $cellHeight, 2, '1111', 'DF');

            $qrX = $x + (($cellWidth - $qrSize) / 2);
            DteQrService::renderToPdf($pdf, (string) $cell['payload'], $qrX, $startY + 2.5, $qrSize, (string) $cell['label']);
            $x += $cellWidth + $gap;
        }

        return $startY + $cellHeight;
    }

    private static function renderReceiverBlock(TCPDF $pdf, array $dte, array $order, float $startY): float
    {
        self::ensureSpace($pdf, 40.0);

        $receptor = $dte['receptor'] ?? [];
        $resumen = $dte['resumen'] ?? [];
        $receiverAddress = trim((string) ($order['billing_address'] ?? ($receptor['direccion']['complemento'] ?? '')));
        $receiverMunicipality = trim((string) ($order['billing_municipio'] ?? ($receptor['direccion']['municipio'] ?? '')));
        $receiverDepartment = trim((string) ($order['billing_departamento'] ?? ($receptor['direccion']['departamento'] ?? '')));
        $receiverDistrict = trim((string) ($order['billing_distrito'] ?? ''));
        $receiverPhone = trim((string) ($order['billing_telefono'] ?? ($receptor['telefono'] ?? '')));
        $receiverDocument = trim((string) ($order['billing_numero_documento'] ?? ($receptor['numDocumento'] ?? '')));
        $receiverNrc = trim((string) ($order['billing_nrc'] ?? ($receptor['nrc'] ?? '')));

        $leftRows = [
            ['Cliente', self::displayValue($receptor['nombre'] ?? '', 'No disponible')],
            ['Actividad economica', self::displayValue($receptor['descActividad'] ?? '', 'No aplica')],
            ['Correo', self::displayValue($receptor['correo'] ?? '', 'No disponible')],
            ['Departamento', self::displayValue($receiverDepartment, 'No disponible')],
            ['Municipio', self::displayValue($receiverMunicipality, 'No disponible')],
            ['Distrito/Ciudad', self::displayValue($receiverDistrict, 'No aplica')],
            ['Direccion', self::displayValue($receiverAddress, 'No disponible')],
        ];

        $rightRows = [
            [self::receiverDocumentLabel((string) ($receptor['tipoDocumento'] ?? '')), self::displayValue($receiverDocument, 'No disponible')],
            ['NRC', self::displayValue($receiverNrc, 'No aplica')],
            ['Telefono', self::displayValue($receiverPhone, 'No disponible')],
            ['Forma pago', 'Tarjeta (Stripe)'],
            ['Moneda', self::displayValue($dte['identificacion']['tipoMoneda'] ?? 'USD', 'USD')],
            ['Pago electronico', self::displayValue($resumen['numPagoElectronico'] ?? '', 'No disponible')],
        ];

        $padding = 3.0;
        $headerHeight = 7.0;
        $columnGap = 4.0;
        $columnWidth = (self::CONTENT_WIDTH - $columnGap - ($padding * 2)) / 2;
        $labelWidth = 24.0;
        $contentY = $startY + $padding + $headerHeight;
        $leftX = self::PAGE_LEFT + $padding;
        $rightX = $leftX + $columnWidth + $columnGap;

        $leftHeight = self::measureInfoTableHeight($pdf, $leftRows, $columnWidth, $labelWidth);
        $rightHeight = self::measureInfoTableHeight($pdf, $rightRows, $columnWidth, $labelWidth);
        $blockHeight = ($padding * 2) + $headerHeight + max($leftHeight, $rightHeight);

        $pdf->SetDrawColor(198, 206, 214);
        $pdf->SetFillColor(248, 249, 251);
        $pdf->RoundedRect(self::PAGE_LEFT, $startY, self::CONTENT_WIDTH, $blockHeight, 2, '1111', 'DF');

        $pdf->SetTextColor(25, 35, 50);
        $pdf->SetFont('helvetica', 'B', 9.3);
        $pdf->SetXY(self::PAGE_LEFT + $padding, $startY + $padding);
        $pdf->Cell(self::CONTENT_WIDTH - ($padding * 2), 5, 'Datos del cliente / receptor', 0, 1, 'L');

        self::renderInfoTable($pdf, $leftX, $contentY, $columnWidth, $leftRows, $labelWidth);
        self::renderInfoTable($pdf, $rightX, $contentY, $columnWidth, $rightRows, $labelWidth);

        return $startY + $blockHeight;
    }

    private static function renderItemsTable(TCPDF $pdf, array $dte, float $startY): float
    {
        $headers = ['No.', 'Tipo item', 'Cant.', 'U. med.', 'Codigo', 'Descripcion', 'P. unit.', 'Desc.', 'No suj.', 'Exenta', 'Gravada'];
        $widths = [7, 13, 10, 12, 15, 50, 16, 14, 14, 14, 21];
        $rows = $dte['cuerpoDocumento'] ?? [];

        self::ensureSpace($pdf, 10.0);
        $y = self::renderItemsTableHeader($pdf, $headers, $widths, $startY);

        foreach ($rows as $row) {
            $description = (string) ($row['descripcion'] ?? '');
            $pdf->SetFont('helvetica', '', 6.5);
            $rowHeight = max(7.5, $pdf->getStringHeight($widths[5], $description) + 1.4);

            if ($y + $rowHeight > self::pageBottomLimit($pdf)) {
                $pdf->AddPage();
                $y = self::renderItemsTableHeader($pdf, $headers, $widths, 14.0);
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

            $x = self::PAGE_LEFT;
            $pdf->SetFont('helvetica', '', 6.5);
            $pdf->SetTextColor(35, 35, 35);

            foreach ($rowValues as $index => $value) {
                $pdf->SetXY($x, $y);
                $pdf->MultiCell($widths[$index], $rowHeight, $value, 1, $alignments[$index], false, 0);
                $x += $widths[$index];
            }

            $y += $rowHeight;
        }

        return $y;
    }

    private static function renderTotals(TCPDF $pdf, array $dte, array $context, float $startY): float
    {
        self::ensureSpace($pdf, 42.0);

        $resumen = $dte['resumen'] ?? [];
        $lettersWidth = 110.0;
        $gap = 4.0;
        $totalsWidth = self::CONTENT_WIDTH - $lettersWidth - $gap;

        $lettersHeight = 22.0;
        $totalsRows = [
            ['Sumas', (float) ($resumen['subTotalVentas'] ?? 0)],
            ['Total descuentos', (float) ($resumen['totalDescu'] ?? 0)],
            ['Sub-total', (float) ($resumen['subTotal'] ?? 0)],
            ['IVA percibido', (float) ($resumen['totalIva'] ?? 0)],
            ['IVA retenido', (float) ($resumen['ivaRete1'] ?? 0)],
            ['Monto total operacion', (float) ($resumen['montoTotalOperacion'] ?? 0)],
            ['Total a pagar', (float) ($resumen['totalPagar'] ?? 0)],
        ];
        $totalsHeight = count($totalsRows) * 6.5;
        $blockHeight = max($lettersHeight, $totalsHeight);

        $pdf->SetTextColor(25, 35, 50);
        $pdf->SetFont('helvetica', 'B', 8.8);
        $pdf->SetXY(self::PAGE_LEFT, $startY);
        $pdf->Cell($lettersWidth, 5, 'Valor en letras', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 8.0);
        $pdf->SetXY(self::PAGE_LEFT, $startY + 6.0);
        $pdf->MultiCell(
            $lettersWidth,
            $blockHeight - 6.0,
            self::displayValue($resumen['totalLetras'] ?? '', 'NO DISPONIBLE'),
            1,
            'L',
            false
        );

        $totalsX = self::PAGE_LEFT + $lettersWidth + $gap;
        $totalsY = $startY;
        foreach ($totalsRows as $index => $row) {
            $isGrandTotal = $index === count($totalsRows) - 1;
            if ($isGrandTotal) {
                $pdf->SetFillColor(29, 78, 137);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont('helvetica', 'B', 8.0);
            } else {
                $pdf->SetFillColor(248, 249, 251);
                $pdf->SetTextColor(25, 35, 50);
                $pdf->SetFont('helvetica', '', 8.0);
            }

            $pdf->SetXY($totalsX, $totalsY);
            $pdf->Cell($totalsWidth - 24.0, 6.5, $row[0], 1, 0, 'L', true);
            $pdf->Cell(24.0, 6.5, '$' . number_format((float) $row[1], 2), 1, 1, 'R', true);
            $totalsY += 6.5;
        }

        return $startY + $blockHeight;
    }

    private static function renderExtensionAndObservations(TCPDF $pdf, array $dte, array $context, float $startY): void
    {
        self::ensureSpace($pdf, 24.0);

        $extension = $dte['extension'] ?? [];
        $boxWidth = 91.0;
        $gap = 4.0;
        $boxHeight = 22.0;

        $pdf->SetFillColor(248, 249, 251);
        $pdf->SetDrawColor(198, 206, 214);
        $pdf->RoundedRect(self::PAGE_LEFT, $startY, $boxWidth, $boxHeight, 2, '1111', 'DF');
        $pdf->RoundedRect(self::PAGE_LEFT + $boxWidth + $gap, $startY, $boxWidth, $boxHeight, 2, '1111', 'DF');

        $pdf->SetTextColor(25, 35, 50);
        $pdf->SetFont('helvetica', 'B', 8.5);
        $pdf->SetXY(self::PAGE_LEFT + 3.0, $startY + 2.0);
        $pdf->Cell($boxWidth - 6.0, 5, 'Extension', 0, 1, 'L');
        $pdf->SetXY(self::PAGE_LEFT + $boxWidth + $gap + 3.0, $startY + 2.0);
        $pdf->Cell($boxWidth - 6.0, 5, 'Observaciones', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 7.7);
        $pdf->SetXY(self::PAGE_LEFT + 3.0, $startY + 8.0);
        $pdf->MultiCell(
            $boxWidth - 6.0,
            4.2,
            'Entrega: ' . self::displayValue($extension['nombEntrega'] ?? '', 'No disponible') . "\n"
            . 'Documento: ' . self::displayValue($extension['docuEntrega'] ?? '', 'No disponible') . "\n"
            . 'Recibe: ' . self::displayValue($extension['nombRecibe'] ?? '', 'Atenea'),
            0,
            'L'
        );

        $pdf->SetXY(self::PAGE_LEFT + $boxWidth + $gap + 3.0, $startY + 8.0);
        $pdf->MultiCell(
            $boxWidth - 6.0,
            4.2,
            'Observaciones: ' . self::displayValue($extension['observaciones'] ?? '', 'Sin observaciones') . "\n"
            . 'Documento generado automaticamente por Atenea.',
            0,
            'L'
        );
    }

    private static function renderItemsTableHeader(TCPDF $pdf, array $headers, array $widths, float $y): float
    {
        $pdf->SetXY(self::PAGE_LEFT, $y);
        $pdf->SetFillColor(226, 231, 236);
        $pdf->SetTextColor(35, 45, 60);
        $pdf->SetFont('helvetica', 'B', 6.7);

        foreach ($headers as $index => $header) {
            $pdf->MultiCell($widths[$index], 8, $header, 1, 'C', true, 0);
        }
        $pdf->Ln();

        return $pdf->GetY();
    }

    private static function renderInfoTable(TCPDF $pdf, float $x, float $y, float $width, array $rows, float $labelWidth): void
    {
        $valueWidth = $width - $labelWidth;

        foreach ($rows as $row) {
            $label = (string) ($row[0] ?? '');
            $value = (string) ($row[1] ?? '');
            $pdf->SetFont('helvetica', 'B', 7.5);
            $labelHeight = $pdf->getStringHeight($labelWidth, $label . ':') + 0.6;
            $pdf->SetFont('helvetica', '', 7.5);
            $valueHeight = $pdf->getStringHeight($valueWidth, $value) + 0.6;
            $rowHeight = max(
                4.8,
                $labelHeight,
                $valueHeight
            );

            $pdf->SetTextColor(25, 35, 50);
            $pdf->SetFont('helvetica', 'B', 7.5);
            $pdf->SetXY($x, $y);
            $pdf->MultiCell($labelWidth, $rowHeight, $label . ':', 0, 'L');

            $pdf->SetFont('helvetica', '', 7.5);
            $pdf->SetXY($x + $labelWidth, $y);
            $pdf->MultiCell($valueWidth, $rowHeight, $value, 0, 'L');

            $y += $rowHeight;
        }
    }

    private static function measureInfoTableHeight(TCPDF $pdf, array $rows, float $width, float $labelWidth): float
    {
        $height = 0.0;
        $valueWidth = $width - $labelWidth;

        foreach ($rows as $row) {
            $label = (string) ($row[0] ?? '');
            $value = (string) ($row[1] ?? '');
            $pdf->SetFont('helvetica', 'B', 7.5);
            $labelHeight = $pdf->getStringHeight($labelWidth, $label . ':') + 0.6;
            $pdf->SetFont('helvetica', '', 7.5);
            $valueHeight = $pdf->getStringHeight($valueWidth, $value) + 0.6;
            $height += max(
                4.8,
                $labelHeight,
                $valueHeight
            );
        }

        return $height;
    }

    private static function ensureSpace(TCPDF $pdf, float $requiredHeight): void
    {
        if ($pdf->GetY() + $requiredHeight > self::pageBottomLimit($pdf)) {
            $pdf->AddPage();
        }
    }

    private static function pageBottomLimit(TCPDF $pdf): float
    {
        return $pdf->getPageHeight() - self::PAGE_BOTTOM;
    }

    private static function formatHeaderValue(string $label, string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return 'No disponible';
        }

        $normalizedLabel = strtolower(trim($label));
        if (in_array($normalizedLabel, ['codigo generacion', 'sello de recepcion'], true)) {
            return self::wrapIdentifier($value, 18);
        }

        return $value;
    }

    private static function wrapIdentifier(string $value, int $chunkLength): string
    {
        $value = trim($value);
        if ($value === '' || strlen($value) <= $chunkLength) {
            return $value;
        }

        if (strpos($value, '-') !== false) {
            return trim(wordwrap(str_replace('-', '- ', $value), $chunkLength, "\n", true));
        }

        return trim(wordwrap($value, $chunkLength, "\n", true));
    }

    private static function receiverDocumentLabel(string $tipoDocumento): string
    {
        $tipoDocumento = trim($tipoDocumento);

        if ($tipoDocumento === '13') {
            return 'DUI';
        }

        if ($tipoDocumento === '36') {
            return 'NIT';
        }

        return 'DUI/NIT';
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

    private static function displayValue($value, string $fallback): string
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
