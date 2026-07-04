<?php

class DteJsonBuilder
{
    public static function build(array $order, array $items, array $settings, array $identifiers, array $receiverProfile = []): array
    {
        $missing = DteConfig::validateEmitter($settings);
        if ($missing !== []) {
            throw new RuntimeException('Falta configurar datos DTE del emisor.');
        }

        $issuedAt = self::resolveIssuedAt($order);
        $tipoDte = (string) ($identifiers['tipo_dte'] ?? '01');
        $currency = 'USD';
        $shippingAmount = self::money((float) ($order['shipping_amount'] ?? 0));
        $totalExpected = self::money((float) ($order['total_amount'] ?? 0));
        $subtotalExpected = self::money((float) ($order['subtotal'] ?? 0));

        $cuerpoDocumento = [];
        $runningTotal = 0.0;
        $itemIndex = 1;

        foreach ($items as $item) {
            $quantity = max(1, (int) ($item['cantidad'] ?? 1));
            $unitPrice = self::money((float) ($item['precio_unitario'] ?? ($item['precio'] ?? 0)));
            $lineTotal = self::money((float) ($item['subtotal'] ?? ($unitPrice * $quantity)));
            $runningTotal += $lineTotal;

            $cuerpoDocumento[] = [
                'numItem' => $itemIndex,
                'tipoItem' => 1,
                'cantidad' => $quantity,
                'codigo' => 'PROD-' . (int) ($item['producto_id'] ?? $itemIndex),
                'uniMedida' => 99,
                'descripcion' => trim((string) ($item['producto_nombre'] ?? $item['nombre'] ?? 'Producto Atenea')),
                'precioUni' => $unitPrice,
                'montoDescu' => 0.00,
                'ventaNoSuj' => 0.00,
                'ventaExenta' => 0.00,
                'ventaGravada' => $lineTotal,
                'tributos' => [],
                'ivaItem' => 0.00,
            ];

            $itemIndex++;
        }

        if ($shippingAmount > 0) {
            $runningTotal += $shippingAmount;
            $cuerpoDocumento[] = [
                'numItem' => $itemIndex,
                'tipoItem' => 2,
                'cantidad' => 1,
                'codigo' => 'ENVIO',
                'uniMedida' => 99,
                'descripcion' => 'ENVIO',
                'precioUni' => $shippingAmount,
                'montoDescu' => 0.00,
                'ventaNoSuj' => 0.00,
                'ventaExenta' => 0.00,
                'ventaGravada' => $shippingAmount,
                'tributos' => [],
                'ivaItem' => 0.00,
            ];
        }

        $runningTotal = self::money($runningTotal);
        if (abs($runningTotal - $totalExpected) > 0.009) {
            throw new RuntimeException('El total del JSON DTE no coincide con el total cobrado por Stripe.');
        }

        $receiverAddress = trim((string) ($receiverProfile['direccion'] ?? $order['billing_address'] ?? ''));
        $receiverDepartment = trim((string) ($receiverProfile['departamento'] ?? ($order['billing_departamento'] ?? '')));
        $receiverMunicipality = trim((string) ($receiverProfile['municipio'] ?? ($order['billing_municipio'] ?? '')));
        $receiverDistrict = trim((string) ($receiverProfile['distrito'] ?? ($order['billing_distrito'] ?? '')));
        $receiverPhone = trim((string) ($receiverProfile['telefono'] ?? ($order['billing_telefono'] ?? '')));
        $receiverEmail = trim((string) ($receiverProfile['correo'] ?? $order['billing_email'] ?? ''));
        $receiverName = trim((string) ($receiverProfile['nombre'] ?? $order['billing_name'] ?? 'CLIENTE FINAL'));
        $receiverNrc = trim((string) ($receiverProfile['nrc'] ?? ($order['billing_nrc'] ?? '')));
        $receiverDocumentType = trim((string) ($receiverProfile['tipo_documento'] ?? ($order['billing_tipo_documento'] ?? '')));
        $receiverDocumentNumber = trim((string) ($receiverProfile['numero_documento'] ?? ($order['billing_numero_documento'] ?? '')));
        $receiverActivityCode = trim((string) ($receiverProfile['cod_actividad'] ?? ''));
        $receiverActivityDescription = trim((string) ($receiverProfile['desc_actividad'] ?? ''));
        $receiverDocumentTypeCode = self::documentTypeCode($receiverDocumentType);
        $receiverAddressComplement = self::buildAddressComplement($receiverDistrict, $receiverAddress);
        $paymentReference = trim((string) ($order['payment_reference'] ?? ''));
        if ($paymentReference === '') {
            $paymentReference = trim((string) ($order['stripe_payment_intent'] ?? $order['stripe_session_id'] ?? 'ORD-' . (int) ($order['id'] ?? 0)));
        }
        $paymentCode = trim((string) ($order['payment_code'] ?? '03'));
        if ($paymentCode === '') {
            $paymentCode = '03';
        }

        $payload = [
            'identificacion' => [
                'version' => 1,
                'ambiente' => DteConfig::environmentCode((string) ($settings['mode'] ?? 'simulation')),
                'tipoDte' => $tipoDte,
                'numeroControl' => (string) ($identifiers['numero_control'] ?? ''),
                'codigoGeneracion' => (string) ($identifiers['codigo_generacion'] ?? ''),
                'tipoModelo' => 1,
                'tipoOperacion' => 1,
                'tipoContingencia' => null,
                'motivoContin' => null,
                'fecEmi' => $issuedAt->format('Y-m-d'),
                'horEmi' => $issuedAt->format('H:i:s'),
                'tipoMoneda' => $currency,
            ],
            'documentoRelacionado' => null,
            'emisor' => [
                'nit' => trim((string) ($settings['emisor_nit'] ?? '')),
                'nrc' => trim((string) ($settings['emisor_nrc'] ?? '')),
                'nombre' => trim((string) ($settings['emisor_nombre'] ?? '')),
                'nombreComercial' => trim((string) ($settings['emisor_nombre_comercial'] ?? '')),
                'codActividad' => trim((string) ($settings['emisor_cod_actividad'] ?? '')),
                'descActividad' => trim((string) ($settings['emisor_desc_actividad'] ?? '')),
                'tipoEstablecimiento' => trim((string) ($settings['emisor_tipo_establecimiento'] ?? '')),
                'direccion' => [
                    'departamento' => trim((string) ($settings['emisor_departamento'] ?? '')),
                    'municipio' => trim((string) ($settings['emisor_municipio'] ?? '')),
                    'complemento' => trim((string) ($settings['emisor_direccion'] ?? '')),
                ],
                'telefono' => trim((string) ($settings['emisor_telefono'] ?? '')),
                'correo' => trim((string) ($settings['emisor_correo'] ?? '')),
                'codEstableMH' => trim((string) ($settings['cod_estable_mh'] ?? '')),
                'codEstable' => trim((string) ($settings['cod_estable'] ?? '')),
                'codPuntoVentaMH' => trim((string) ($settings['cod_punto_venta_mh'] ?? '')),
                'codPuntoVenta' => trim((string) ($settings['cod_punto_venta'] ?? '')),
            ],
            'receptor' => [
                'tipoDocumento' => $receiverDocumentTypeCode,
                'numDocumento' => self::nullableText($receiverDocumentNumber),
                'nrc' => $receiverNrc !== '' ? $receiverNrc : null,
                'nombre' => $receiverName !== '' ? $receiverName : 'CLIENTE FINAL',
                'codActividad' => $receiverActivityCode !== '' ? $receiverActivityCode : null,
                'descActividad' => $receiverActivityDescription !== '' ? $receiverActivityDescription : null,
                'direccion' => [
                    'departamento' => self::nullableText($receiverDepartment),
                    'municipio' => self::nullableText($receiverMunicipality),
                    'complemento' => self::nullableText($receiverAddressComplement),
                ],
                'telefono' => self::nullableText($receiverPhone),
                'correo' => self::nullableText($receiverEmail),
            ],
            'otrosDocumentos' => null,
            'ventaTercero' => null,
            'cuerpoDocumento' => $cuerpoDocumento,
            'resumen' => [
                'totalNoSuj' => 0.00,
                'totalExenta' => 0.00,
                'totalGravada' => $runningTotal,
                'subTotalVentas' => $runningTotal,
                'descuNoSuj' => 0.00,
                'descuExenta' => 0.00,
                'descuGravada' => 0.00,
                'porcentajeDescuento' => 0.00,
                'totalDescu' => 0.00,
                'subTotal' => $runningTotal,
                'ivaRete1' => 0.00,
                'reteRenta' => 0.00,
                'montoTotalOperacion' => $runningTotal,
                'totalNoGravado' => 0.00,
                'totalPagar' => $totalExpected,
                'totalLetras' => DteMoneyToWords::toUsd($totalExpected),
                'totalIva' => 0.00,
                'saldoFavor' => 0.00,
                'condicionOperacion' => 1,
                'pagos' => [
                    [
                        'codigo' => $paymentCode,
                        'montoPago' => $totalExpected,
                        'referencia' => $paymentReference,
                        'periodo' => null,
                        'plazo' => null,
                    ],
                ],
                'numPagoElectronico' => $paymentReference,
            ],
            'extension' => [
                'nombEntrega' => $receiverName !== '' ? $receiverName : 'CLIENTE FINAL',
                'docuEntrega' => $receiverDocumentNumber !== '' ? $receiverDocumentNumber : ($receiverEmail !== '' ? $receiverEmail : null),
                'nombRecibe' => 'ATENEA',
                'docuRecibe' => trim((string) ($settings['emisor_nit'] ?? '')),
                'observaciones' => DteConfig::isSimulation($settings)
                    ? 'Documento generado en modo simulacion. No valido fiscalmente.'
                    : 'Documento pendiente de validacion real con Hacienda.',
                'placaVehiculo' => null,
            ],
            'apendice' => [
                [
                    'campo' => 'ORDEN_ID',
                    'etiqueta' => 'Orden de compra',
                    'valor' => (string) (int) ($order['id'] ?? 0),
                ],
                [
                    'campo' => 'CHECKOUT_SESSION',
                    'etiqueta' => 'Sesion Stripe',
                    'valor' => trim((string) ($order['stripe_session_id'] ?? '')),
                ],
                [
                    'campo' => 'PAYMENT_INTENT',
                    'etiqueta' => 'Payment Intent',
                    'valor' => trim((string) ($order['stripe_payment_intent'] ?? '')),
                ],
                [
                    'campo' => 'SUBTOTAL',
                    'etiqueta' => 'Subtotal cobrado',
                    'valor' => number_format($subtotalExpected, 2, '.', ''),
                ],
            ],
        ];

        if ($receiverDistrict !== '') {
            $payload['apendice'][] = [
                'campo' => 'DISTRITO',
                'etiqueta' => 'Distrito / ciudad',
                'valor' => $receiverDistrict,
            ];
        }

        self::assertJsonSerializable($payload);

        return $payload;
    }

    private static function resolveIssuedAt(array $order): DateTimeImmutable
    {
        $timeZone = new DateTimeZone('America/El_Salvador');
        $candidates = [
            (string) ($order['paid_at'] ?? ''),
            (string) ($order['created_at'] ?? ''),
        ];

        foreach ($candidates as $candidate) {
            $candidate = trim($candidate);
            if ($candidate === '') {
                continue;
            }

            try {
                return new DateTimeImmutable($candidate, $timeZone);
            } catch (Throwable $exception) {
                continue;
            }
        }

        return new DateTimeImmutable('now', $timeZone);
    }

    private static function assertJsonSerializable(array $payload): void
    {
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new RuntimeException('El JSON DTE generado no es valido.');
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('El JSON DTE generado no se pudo validar.');
        }
    }

    private static function money(float $value): float
    {
        return round($value, 2);
    }

    private static function nullableText(string $value): ?string
    {
        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    private static function documentTypeCode(string $documentType): ?string
    {
        $documentType = strtoupper(trim($documentType));

        if ($documentType === 'DUI' || $documentType === '13') {
            return '13';
        }

        if ($documentType === 'NIT' || $documentType === '36') {
            return '36';
        }

        return null;
    }

    private static function buildAddressComplement(string $district, string $address): string
    {
        $parts = [];

        $district = trim($district);
        $address = trim($address);

        if ($district !== '') {
            $parts[] = 'Distrito/Ciudad: ' . $district;
        }

        if ($address !== '') {
            $parts[] = $address;
        }

        return implode(' | ', $parts);
    }
}
