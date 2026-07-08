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
        $this->SetY(-10);
        $this->SetFont('helvetica', '', 6.4);
        $this->SetTextColor(82, 96, 88);
        $this->Cell(58, 3.8, 'Documento generado por Atenea', 0, 0, 'L');

        $fiscalValidity = trim((string) ($this->footerContext['fiscal_validity'] ?? ''));
        if ($fiscalValidity !== '') {
            $this->SetTextColor(165, 28, 48);
            $this->SetFont('helvetica', 'B', 6.4);
            $this->Cell(84, 3.8, $fiscalValidity, 0, 0, 'C');
        } else {
            $this->Cell(84, 3.8, '', 0, 0, 'C');
        }

        $this->SetTextColor(96, 108, 101);
        $this->SetFont('helvetica', '', 6.4);
        $this->Cell(0, 3.8, 'Pagina ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'R');
    }
}

class DtePdfRenderer
{
    private const PAGE_TOP = 8.5;
    private const PAGE_LEFT = 8.5;
    private const PAGE_RIGHT = 8.5;
    private const PAGE_BOTTOM = 12.0;
    private const CONTENT_WIDTH = 193.0;
    private const SECTION_GAP = 2.5;

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
        $pdf->SetMargins(self::PAGE_LEFT, self::PAGE_TOP, self::PAGE_RIGHT);
        $pdf->SetAutoPageBreak(true, self::PAGE_BOTTOM);
        $pdf->setFooterContext([
            'fiscal_validity' => (string) ($context['fiscal_validity'] ?? ''),
        ]);
        $pdf->AddPage();

        $cursorY = self::PAGE_TOP;
        $cursorY = self::renderHeader($pdf, $dte, $response, $settings, $cursorY);
        $cursorY = self::renderQrs($pdf, $dte, $response, $settings, $cursorY + self::SECTION_GAP);
        $cursorY = self::renderReceiverBlock($pdf, $dte, $order, $cursorY + self::SECTION_GAP);
        $cursorY = self::renderItemsTable($pdf, $dte, $cursorY + self::SECTION_GAP);
        $cursorY = self::renderTotals($pdf, $dte, $context, $cursorY + self::SECTION_GAP);
        self::renderExtensionAndObservations($pdf, $dte, $context, $cursorY + self::SECTION_GAP);

        $pageCount = $pdf->getNumPages();
        $itemCount = count($dte['cuerpoDocumento'] ?? []);
        if ($itemCount > 0 && $itemCount <= 8 && $pageCount > 1) {
            error_log('[DTE PDF] El PDF compacto genero ' . $pageCount . ' paginas para ' . $itemCount . ' item(s).');
        }

        $pdf->Output($absolutePath, 'F');

        if (!is_file($absolutePath)) {
            throw new RuntimeException('No se pudo generar el PDF DTE.');
        }
    }

    private static function renderHeader(TCPDF $pdf, array $dte, array $response, array $settings, float $startY): float
    {
        $startY = self::prepareSectionStart($pdf, $startY, 46.0);

        $emisor = $dte['emisor'] ?? [];
        $identificacion = $dte['identificacion'] ?? [];
        $logoPath = self::logoPath();

        $leftWidth = 108.0;
        $gap = 3.0;
        $rightWidth = self::CONTENT_WIDTH - $leftWidth - $gap;
        $leftX = self::PAGE_LEFT;
        $rightX = $leftX + $leftWidth + $gap;
        $padding = 3.0;

        $rows = [
            ['Codigo generacion', (string) ($identificacion['codigoGeneracion'] ?? '')],
            ['Sello recepcion', (string) ($response['selloRecibido'] ?? 'SIMULADO')],
            ['Numero control', (string) ($identificacion['numeroControl'] ?? '')],
            ['Modelo', self::modelLabel((int) ($identificacion['tipoModelo'] ?? 1))],
            ['Version JSON', (string) ($identificacion['version'] ?? 1)],
            ['Transmision', self::operationLabel((int) ($identificacion['tipoOperacion'] ?? 1))],
            ['Hora emision', (string) ($identificacion['horEmi'] ?? '')],
            ['Fecha emision', (string) ($identificacion['fecEmi'] ?? '')],
        ];

        $labelWidth = 21.0;
        $valueWidth = $rightWidth - ($padding * 2) - $labelWidth;
        $rowsHeight = 0.0;
        foreach ($rows as $row) {
            $wrappedValue = self::formatHeaderValue($row[0], (string) $row[1]);
            $pdf->SetFont('helvetica', 'B', 6.3);
            $labelHeight = $pdf->getStringHeight($labelWidth, $row[0] . ':') + 0.3;
            $pdf->SetFont('helvetica', '', 6.1);
            $valueHeight = $pdf->getStringHeight($valueWidth, $wrappedValue) + 0.3;
            $rowsHeight += max(3.8, $labelHeight, $valueHeight);
        }

        $titleHeight = 8.8;
        $rightHeight = ($padding * 2) + $titleHeight + 1.5 + $rowsHeight;

        $commercialName = trim((string) ($emisor['nombreComercial'] ?? 'ATENEA'));
        $legalName = trim((string) ($emisor['nombre'] ?? 'ATENEA'));
        $emitterLines = [];
        $emitterLines[] = 'NIT: ' . self::displayValue($emisor['nit'] ?? '', 'No disponible');
        if (trim((string) ($emisor['nrc'] ?? '')) !== '') {
            $emitterLines[] = 'NRC: ' . trim((string) $emisor['nrc']);
        }
        $emitterLines[] = 'Act.: ' . self::displayValue($emisor['descActividad'] ?? '', 'No disponible');
        $emitterLines[] = 'Dir.: ' . self::displayValue($emisor['direccion']['complemento'] ?? '', 'No disponible');
        $emitterLines[] = 'Correo: ' . self::displayValue($emisor['correo'] ?? '', 'No disponible');
        $emitterLines[] = 'Tel.: ' . self::displayValue($emisor['telefono'] ?? '', 'No disponible');

        $logoWidth = $logoPath !== null ? 14.0 : 0.0;
        $headerTextWidth = $leftWidth - ($padding * 2) - $logoWidth - ($logoWidth > 0 ? 2.0 : 0.0);
        $detailWidth = $leftWidth - ($padding * 2);
        $pdf->SetFont('helvetica', 'B', 10.5);
        $headerTextHeight = max(10.0, $pdf->getStringHeight($headerTextWidth, $commercialName . "\n" . $legalName));
        $pdf->SetFont('helvetica', '', 6.8);
        $detailsHeight = $pdf->getStringHeight($detailWidth, implode("\n", $emitterLines)) + 0.5;
        $leftHeight = ($padding * 2) + $headerTextHeight + 1.2 + $detailsHeight;

        $boxHeight = max(46.0, $leftHeight, $rightHeight);

        $pdf->SetDrawColor(182, 205, 191);
        $pdf->SetFillColor(245, 250, 246);
        $pdf->RoundedRect($leftX, $startY, $leftWidth, $boxHeight, 1.8, '1111', 'DF');
        $pdf->RoundedRect($rightX, $startY, $rightWidth, $boxHeight, 1.8, '1111', 'DF');

        if ($logoPath !== null) {
            $pdf->Image($logoPath, $leftX + $padding, $startY + $padding, 11.5, 11.5, '', '', '', false, 300, '', false, false, 0, false, false, false);
        }

        $headerTextX = $leftX + $padding + ($logoWidth > 0 ? $logoWidth + 2.0 : 0.0);
        $pdf->SetTextColor(23, 68, 45);
        $pdf->SetFont('helvetica', 'B', 10.5);
        $pdf->SetXY($headerTextX, $startY + $padding);
        $pdf->MultiCell($headerTextWidth, 4.2, $commercialName, 0, 'L');

        $pdf->SetFont('helvetica', '', 7.2);
        $pdf->SetX($headerTextX);
        $pdf->MultiCell($headerTextWidth, 3.8, $legalName, 0, 'L');

        $detailY = $startY + $padding + $headerTextHeight + 0.8;
        $pdf->SetFont('helvetica', '', 6.8);
        $pdf->SetXY($leftX + $padding, $detailY);
        $pdf->MultiCell($detailWidth, 3.5, implode("\n", $emitterLines), 0, 'L');

        $pdf->SetTextColor(23, 68, 45);
        $pdf->SetFont('helvetica', 'B', 9.2);
        $pdf->SetXY($rightX + $padding, $startY + $padding);
        $pdf->MultiCell($rightWidth - ($padding * 2), 4.2, "DOCUMENTO TRIBUTARIO ELECTRONICO\nFACTURA", 0, 'C');

        $rowsY = $startY + $padding + $titleHeight + 1.0;
        foreach ($rows as $row) {
            $wrappedValue = self::formatHeaderValue($row[0], (string) $row[1]);
            $pdf->SetFont('helvetica', 'B', 6.3);
            $labelHeight = $pdf->getStringHeight($labelWidth, $row[0] . ':') + 0.3;
            $pdf->SetFont('helvetica', '', 6.1);
            $valueHeight = $pdf->getStringHeight($valueWidth, $wrappedValue) + 0.3;
            $rowHeight = max(3.8, $labelHeight, $valueHeight);

            $pdf->SetFont('helvetica', 'B', 6.3);
            $pdf->SetXY($rightX + $padding, $rowsY);
            $pdf->MultiCell($labelWidth, $rowHeight, $row[0] . ':', 0, 'L');

            $pdf->SetFont('helvetica', '', 6.1);
            $pdf->SetXY($rightX + $padding + $labelWidth, $rowsY);
            $pdf->MultiCell($valueWidth, $rowHeight, $wrappedValue, 0, 'L');
            $rowsY += $rowHeight;
        }

        return $startY + $boxHeight;
    }

    private static function renderQrs(TCPDF $pdf, array $dte, array $response, array $settings, float $startY): float
    {
        $startY = self::prepareSectionStart($pdf, $startY, 25.0);

        $consultaPayload = DteQrService::buildConsultaPayload($dte, $response, $settings);
        $codigoPayload = DteQrService::buildCodigoGeneracionPayload((string) ($dte['identificacion']['codigoGeneracion'] ?? ''));
        $selloPayload = DteQrService::buildSelloPayload((string) ($response['selloRecibido'] ?? ''));

        $gap = 3.0;
        $cellWidth = (self::CONTENT_WIDTH - ($gap * 2)) / 3;
        $cellHeight = 24.0;
        $qrSize = 18.2;
        $cells = [
            ['label' => 'Consulta MH', 'payload' => $consultaPayload],
            ['label' => 'Codigo generacion', 'payload' => $codigoPayload],
            ['label' => 'Sello recepcion', 'payload' => $selloPayload],
        ];

        $x = self::PAGE_LEFT;
        foreach ($cells as $cell) {
            $pdf->SetDrawColor(196, 215, 203);
            $pdf->SetFillColor(251, 253, 251);
            $pdf->RoundedRect($x, $startY, $cellWidth, $cellHeight, 1.6, '1111', 'DF');

            $qrX = $x + (($cellWidth - $qrSize) / 2);
            DteQrService::renderToPdf($pdf, (string) $cell['payload'], $qrX, $startY + 1.7, $qrSize, (string) $cell['label']);
            $x += $cellWidth + $gap;
        }

        return $startY + $cellHeight;
    }

    private static function renderReceiverBlock(TCPDF $pdf, array $dte, array $order, float $startY): float
    {
        $startY = self::prepareSectionStart($pdf, $startY, 32.0);

        $receptor = $dte['receptor'] ?? [];
        $resumen = $dte['resumen'] ?? [];
        $receiverAddress = trim((string) ($order['billing_address'] ?? ($receptor['direccion']['complemento'] ?? '')));
        $receiverMunicipality = trim((string) ($order['billing_municipio'] ?? ($receptor['direccion']['municipio'] ?? '')));
        $receiverDepartment = trim((string) ($order['billing_departamento'] ?? ($receptor['direccion']['departamento'] ?? '')));
        $receiverDistrict = trim((string) ($order['billing_distrito'] ?? ''));
        $receiverPhone = trim((string) ($order['billing_telefono'] ?? ($receptor['telefono'] ?? '')));
        $receiverDocumentLabel = self::receiverDocumentLabel((string) ($receptor['tipoDocumento'] ?? ''));
        $receiverDocument = trim((string) ($order['billing_numero_documento'] ?? ($receptor['numDocumento'] ?? '')));
        $receiverNrc = trim((string) ($order['billing_nrc'] ?? ($receptor['nrc'] ?? '')));

        $columnOne = [
            ['Cliente', self::displayValue($receptor['nombre'] ?? '', 'No disponible')],
            ['Correo', self::displayValue($receptor['correo'] ?? '', 'No disponible')],
            ['Telefono', self::displayValue($receiverPhone, 'No disponible')],
            [$receiverDocumentLabel, self::displayValue($receiverDocument, 'No disponible')],
        ];

        $columnTwo = [
            ['Departamento', self::displayValue($receiverDepartment, 'No disponible')],
            ['Municipio', self::displayValue($receiverMunicipality, 'No disponible')],
            ['Distrito/Ciudad', self::displayValue($receiverDistrict, 'No aplica')],
            ['Direccion', self::displayValue($receiverAddress, 'No disponible')],
        ];

        $columnThree = [
            ['NRC', self::displayValue($receiverNrc, 'No aplica')],
            ['Forma pago', self::displayValue($order['payment_method_label'] ?? '', 'Tarjeta (Stripe)')],
            ['Moneda', self::displayValue($dte['identificacion']['tipoMoneda'] ?? 'USD', 'USD')],
            ['Pago electronico', self::displayValue($resumen['numPagoElectronico'] ?? '', 'No disponible')],
        ];

        $padding = 3.0;
        $headerHeight = 5.2;
        $columnGap = 3.0;
        $columnWidth = (self::CONTENT_WIDTH - ($padding * 2) - ($columnGap * 2)) / 3;
        $labelWidth = 16.8;
        $contentY = $startY + $padding + $headerHeight;
        $leftX = self::PAGE_LEFT + $padding;
        $middleX = $leftX + $columnWidth + $columnGap;
        $rightX = $middleX + $columnWidth + $columnGap;

        $leftHeight = self::measureInfoTableHeight($pdf, $columnOne, $columnWidth, $labelWidth);
        $middleHeight = self::measureInfoTableHeight($pdf, $columnTwo, $columnWidth, $labelWidth);
        $rightHeight = self::measureInfoTableHeight($pdf, $columnThree, $columnWidth, $labelWidth);
        $blockHeight = ($padding * 2) + $headerHeight + max($leftHeight, $middleHeight, $rightHeight);

        $pdf->SetDrawColor(182, 205, 191);
        $pdf->SetFillColor(245, 250, 246);
        $pdf->RoundedRect(self::PAGE_LEFT, $startY, self::CONTENT_WIDTH, $blockHeight, 1.8, '1111', 'DF');

        $pdf->SetTextColor(23, 68, 45);
        $pdf->SetFont('helvetica', 'B', 8.1);
        $pdf->SetXY(self::PAGE_LEFT + $padding, $startY + $padding);
        $pdf->Cell(self::CONTENT_WIDTH - ($padding * 2), 4.2, 'Datos del cliente / receptor', 0, 1, 'L');

        self::renderInfoTable($pdf, $leftX, $contentY, $columnWidth, $columnOne, $labelWidth);
        self::renderInfoTable($pdf, $middleX, $contentY, $columnWidth, $columnTwo, $labelWidth);
        self::renderInfoTable($pdf, $rightX, $contentY, $columnWidth, $columnThree, $labelWidth);

        return $startY + $blockHeight;
    }

    private static function renderItemsTable(TCPDF $pdf, array $dte, float $startY): float
    {
        $headers = ['#', 'Tipo', 'Cant.', 'U.M.', 'Codigo', 'Descripcion', 'P. unit.', 'Desc.', 'No suj.', 'Exenta', 'Gravada'];
        $widths = [6, 12, 9, 11, 14, 63, 14, 13, 14, 14, 23];
        $rows = $dte['cuerpoDocumento'] ?? [];

        $startY = self::prepareSectionStart($pdf, $startY, 8.0);
        $y = self::renderItemsTableHeader($pdf, $headers, $widths, $startY);

        foreach ($rows as $row) {
            $description = trim((string) ($row['descripcion'] ?? ''));
            $pdf->SetFont('helvetica', '', 5.8);
            $rowHeight = max(4.8, $pdf->getStringHeight($widths[5], $description) + 0.8);

            if ($y + $rowHeight > self::pageBottomLimit($pdf)) {
                $pdf->AddPage();
                $y = self::renderItemsTableHeader($pdf, $headers, $widths, self::PAGE_TOP);
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
            $pdf->SetFont('helvetica', '', 5.8);
            $pdf->SetTextColor(45, 61, 51);

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
        $resumen = $dte['resumen'] ?? [];
        $lettersWidth = 116.0;
        $gap = 4.0;
        $totalsWidth = self::CONTENT_WIDTH - $lettersWidth - $gap;
        $rowHeight = 5.0;
        $lettersHeadingHeight = 4.2;
        $totalsRows = [
            ['Sumas', (float) ($resumen['subTotalVentas'] ?? 0)],
            ['Total descuentos', (float) ($resumen['totalDescu'] ?? 0)],
            ['Sub-total', (float) ($resumen['subTotal'] ?? 0)],
            ['IVA percibido', (float) ($resumen['totalIva'] ?? 0)],
            ['IVA retenido', (float) ($resumen['ivaRete1'] ?? 0)],
            ['Monto total operacion', (float) ($resumen['montoTotalOperacion'] ?? 0)],
            ['Total a pagar', (float) ($resumen['totalPagar'] ?? 0)],
        ];

        $lettersText = self::displayValue($resumen['totalLetras'] ?? '', 'NO DISPONIBLE');
        $pdf->SetFont('helvetica', '', 6.9);
        $lettersBodyHeight = max(11.5, $pdf->getStringHeight($lettersWidth - 4.0, $lettersText) + 3.0);
        $totalsHeight = count($totalsRows) * $rowHeight;
        $blockHeight = max($lettersHeadingHeight + $lettersBodyHeight, $totalsHeight);

        $startY = self::prepareSectionStart($pdf, $startY, $blockHeight);

        $pdf->SetTextColor(23, 68, 45);
        $pdf->SetFont('helvetica', 'B', 7.6);
        $pdf->SetXY(self::PAGE_LEFT, $startY);
        $pdf->Cell($lettersWidth, 3.8, 'Valor en letras', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 6.9);
        $pdf->SetXY(self::PAGE_LEFT, $startY + $lettersHeadingHeight);
        $pdf->MultiCell(
            $lettersWidth,
            $blockHeight - $lettersHeadingHeight,
            $lettersText,
            1,
            'L',
            false
        );

        $totalsX = self::PAGE_LEFT + $lettersWidth + $gap;
        $totalsY = $startY;
        foreach ($totalsRows as $index => $row) {
            $isGrandTotal = $index === count($totalsRows) - 1;
            if ($isGrandTotal) {
                $pdf->SetFillColor(11, 122, 75);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont('helvetica', 'B', 7.1);
            } else {
                $pdf->SetFillColor(245, 250, 246);
                $pdf->SetTextColor(23, 68, 45);
                $pdf->SetFont('helvetica', '', 6.9);
            }

            $pdf->SetXY($totalsX, $totalsY);
            $pdf->Cell($totalsWidth - 24.0, $rowHeight, $row[0], 1, 0, 'L', true);
            $pdf->Cell(24.0, $rowHeight, '$' . number_format((float) $row[1], 2), 1, 1, 'R', true);
            $totalsY += $rowHeight;
        }

        return $startY + $blockHeight;
    }

    private static function renderExtensionAndObservations(TCPDF $pdf, array $dte, array $context, float $startY): float
    {
        $extension = $dte['extension'] ?? [];
        $lines = [];

        $deliveryParts = [];
        if (trim((string) ($extension['nombEntrega'] ?? '')) !== '') {
            $deliveryParts[] = 'Entrega: ' . trim((string) $extension['nombEntrega']);
        }
        if (trim((string) ($extension['docuEntrega'] ?? '')) !== '') {
            $deliveryParts[] = 'Documento: ' . trim((string) $extension['docuEntrega']);
        }
        if (trim((string) ($extension['nombRecibe'] ?? '')) !== '') {
            $deliveryParts[] = 'Recibe: ' . trim((string) $extension['nombRecibe']);
        }

        if ($deliveryParts !== []) {
            $lines[] = 'Extension: ' . implode(' | ', $deliveryParts);
        }

        $observations = trim((string) ($extension['observaciones'] ?? ''));
        if ($observations !== '') {
            $lines[] = 'Observaciones: ' . $observations;
        }

        if ($lines === []) {
            return $startY;
        }

        $text = implode("\n", $lines);
        $pdf->SetFont('helvetica', '', 6.4);
        $boxHeight = max(8.5, $pdf->getStringHeight(self::CONTENT_WIDTH - 6.0, $text) + 3.0);
        $startY = self::prepareSectionStart($pdf, $startY, $boxHeight);

        $pdf->SetFillColor(245, 250, 246);
        $pdf->SetDrawColor(182, 205, 191);
        $pdf->RoundedRect(self::PAGE_LEFT, $startY, self::CONTENT_WIDTH, $boxHeight, 1.8, '1111', 'DF');

        $pdf->SetTextColor(23, 68, 45);
        $pdf->SetFont('helvetica', '', 6.4);
        $pdf->SetXY(self::PAGE_LEFT + 3.0, $startY + 1.8);
        $pdf->MultiCell(self::CONTENT_WIDTH - 6.0, 3.4, $text, 0, 'L');

        return $startY + $boxHeight;
    }

    private static function renderItemsTableHeader(TCPDF $pdf, array $headers, array $widths, float $y): float
    {
        $pdf->SetXY(self::PAGE_LEFT, $y);
        $pdf->SetFillColor(220, 237, 226);
        $pdf->SetTextColor(31, 68, 50);
        $pdf->SetFont('helvetica', 'B', 5.9);

        foreach ($headers as $index => $header) {
            $pdf->MultiCell($widths[$index], 6.0, $header, 1, 'C', true, 0);
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
            $pdf->SetFont('helvetica', 'B', 6.5);
            $labelHeight = $pdf->getStringHeight($labelWidth, $label . ':') + 0.3;
            $pdf->SetFont('helvetica', '', 6.5);
            $valueHeight = $pdf->getStringHeight($valueWidth, $value) + 0.3;
            $rowHeight = max(3.8, $labelHeight, $valueHeight);

            $pdf->SetTextColor(23, 68, 45);
            $pdf->SetFont('helvetica', 'B', 6.5);
            $pdf->SetXY($x, $y);
            $pdf->MultiCell($labelWidth, $rowHeight, $label . ':', 0, 'L');

            $pdf->SetFont('helvetica', '', 6.5);
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
            $pdf->SetFont('helvetica', 'B', 6.5);
            $labelHeight = $pdf->getStringHeight($labelWidth, $label . ':') + 0.3;
            $pdf->SetFont('helvetica', '', 6.5);
            $valueHeight = $pdf->getStringHeight($valueWidth, $value) + 0.3;
            $height += max(3.8, $labelHeight, $valueHeight);
        }

        return $height;
    }

    private static function prepareSectionStart(TCPDF $pdf, float $startY, float $requiredHeight): float
    {
        $startY = max(self::PAGE_TOP, $startY);
        if ($startY + $requiredHeight > self::pageBottomLimit($pdf)) {
            $pdf->AddPage();

            return self::PAGE_TOP;
        }

        return $startY;
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
        if (in_array($normalizedLabel, ['codigo generacion', 'sello recepcion'], true)) {
            return self::wrapIdentifier($value, 24);
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
