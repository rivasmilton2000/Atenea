<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

if (!function_exists('atenea_invoice_bootstrap')) {
    function atenea_invoice_bootstrap(): void
    {
        static $booted = false;
        if ($booted) {
            return;
        }

        $autoload = __DIR__ . '/../vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }

        // Fallback para entornos donde PHPMailer fue copiado manualmente
        // en vendor/phpmailer y no registrado por Composer.
        if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            $manualPhpMailerBase = __DIR__ . '/../vendor/phpmailer/src/';
            if (file_exists($manualPhpMailerBase . 'Exception.php')) {
                require_once $manualPhpMailerBase . 'Exception.php';
            }
            if (file_exists($manualPhpMailerBase . 'PHPMailer.php')) {
                require_once $manualPhpMailerBase . 'PHPMailer.php';
            }
            if (file_exists($manualPhpMailerBase . 'SMTP.php')) {
                require_once $manualPhpMailerBase . 'SMTP.php';
            }
        }

        if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            throw new RuntimeException('PHPMailer no esta instalado correctamente.');
        }

        $booted = true;
    }
}

if (!function_exists('atenea_ensure_invoice_dir')) {
    function atenea_ensure_invoice_dir(): string
    {
        $dir = __DIR__ . '/../uploads/facturas';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        return $dir;
    }
}

if (!function_exists('atenea_build_legacy_invoice_pdf')) {
    function atenea_build_legacy_invoice_pdf(array $order, array $items): array
    {
        atenea_invoice_bootstrap();

        $dir = atenea_ensure_invoice_dir();
        $filename = 'factura_orden_' . (int)$order['id'] . '_' . date('Ymd_His') . '.pdf';
        $absolute_path = $dir . DIRECTORY_SEPARATOR . $filename;
        $relative_path = 'uploads/facturas/' . $filename;

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Atenea');
        $pdf->SetAuthor('Atenea');
        $pdf->SetTitle('Factura Orden #' . (int)$order['id']);
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 14);
        $pdf->AddPage();

        // Header visual
        $pdf->SetFillColor(14, 115, 74);
        $pdf->Rect(12, 12, 186, 24, 'F');

        $logoCandidates = [
            __DIR__ . '/../img/Atenea Logo.png',
            __DIR__ . '/../img/cecsb_logo.png',
            __DIR__ . '/../img/cecsb_logo.ico',
        ];
        $logoPath = null;
        foreach ($logoCandidates as $candidate) {
            if (file_exists($candidate)) {
                $logoPath = $candidate;
                break;
            }
        }

        if ($logoPath !== null) {
            $pdf->Image($logoPath, 16, 15, 16, 16, '', '', '', false, 300, '', false, false, 0, false, false, false);
        }

        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 18);
        $titleX = $logoPath !== null ? 35 : 16;
        $pdf->SetXY($titleX, 18);
        $pdf->Cell(120, 8, 'ATENEA - FACTURA', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(58, 8, '#' . (int)$order['id'], 0, 1, 'R');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetX($titleX);
        $pdf->Cell(120, 6, 'Comprobante de compra', 0, 0, 'L');
        $pdf->Cell(58, 6, date('Y-m-d H:i:s'), 0, 1, 'R');

        // Datos de cliente
        $pdf->SetTextColor(40, 40, 40);
        $pdf->SetFillColor(245, 248, 247);
        $pdf->Rect(12, 40, 186, 32, 'F');
        $pdf->SetXY(16, 44);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'DATOS DE FACTURACION', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetX(16);
        $pdf->Cell(0, 5, 'Cliente: ' . $order['billing_name'], 0, 1, 'L');
        $pdf->SetX(16);
        $pdf->Cell(0, 5, 'Correo: ' . $order['billing_email'], 0, 1, 'L');
        $pdf->SetX(16);
        $pdf->MultiCell(178, 5, 'Direccion: ' . $order['billing_address'], 0, 'L');

        // Tabla encabezado
        $pdf->Ln(4);
        $pdf->SetFillColor(224, 241, 235);
        $pdf->SetDrawColor(192, 212, 204);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(86, 8, 'Producto', 1, 0, 'L', true);
        $pdf->Cell(22, 8, 'Cant.', 1, 0, 'C', true);
        $pdf->Cell(38, 8, 'Precio Unit.', 1, 0, 'R', true);
        $pdf->Cell(40, 8, 'Subtotal', 1, 1, 'R', true);

        // Tabla contenido
        $pdf->SetFont('helvetica', '', 10);
        $fill = false;
        foreach ($items as $item) {
            $pdf->SetFillColor($fill ? 250 : 255, $fill ? 252 : 255, $fill ? 251 : 255);
            $pdf->Cell(86, 8, (string)$item['producto_nombre'], 1, 0, 'L', true);
            $pdf->Cell(22, 8, (string)$item['cantidad'], 1, 0, 'C', true);
            $pdf->Cell(38, 8, '$' . number_format((float)$item['precio_unitario'], 2), 1, 0, 'R', true);
            $pdf->Cell(40, 8, '$' . number_format((float)$item['subtotal'], 2), 1, 1, 'R', true);
            $fill = !$fill;
        }

        // Bloque de totales
        $pdf->Ln(4);
        $totalsX = 112;
        $pdf->SetX($totalsX);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(46, 7, 'Subtotal', 1, 0, 'R');
        $pdf->Cell(40, 7, '$' . number_format((float)$order['subtotal'], 2), 1, 1, 'R');
        $pdf->SetX($totalsX);
        $pdf->Cell(46, 7, 'Envio', 1, 0, 'R');
        $pdf->Cell(40, 7, '$' . number_format((float)$order['shipping_amount'], 2), 1, 1, 'R');
        $pdf->SetX($totalsX);
        $pdf->SetFillColor(14, 115, 74);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(46, 9, 'TOTAL', 1, 0, 'R', true);
        $pdf->Cell(40, 9, '$' . number_format((float)$order['total_amount'], 2), 1, 1, 'R', true);

        // Pie
        $pdf->SetTextColor(95, 95, 95);
        $pdf->SetFont('helvetica', 'I', 9);
        $pdf->Ln(8);
        $pdf->MultiCell(0, 5, 'Gracias por tu compra. Este documento es tu comprobante de pago.', 0, 'C');

        $pdf->Output($absolute_path, 'F');

        return [
            'absolute_path' => $absolute_path,
            'relative_path' => $relative_path,
        ];
    }
}

if (!function_exists('atenea_build_invoice_pdf')) {
    function atenea_build_invoice_pdf(array $order, array $items): array
    {
        return atenea_build_legacy_invoice_pdf($order, $items);
    }
}

if (!function_exists('atenea_send_invoice_email')) {
    function atenea_send_invoice_email(array $order, string $pdf_absolute_path, array $extraAttachments = [], array $context = []): void
    {
        atenea_invoice_bootstrap();
        include __DIR__ . '/email_account.php';

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $myemail;
        $mail->Password = $mypassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($myemail, 'Atenea');
        $mail->addAddress($order['billing_email'], $order['billing_name']);
        $isSimulation = !empty($context['is_simulation']);
        $subjectPrefix = $isSimulation ? 'DTE simulada' : 'Factura';
        $mail->Subject = $subjectPrefix . ' de tu compra #' . (int)$order['id'];
        $mail->isHTML(true);
        $mail->Body = '<p>Hola ' . htmlspecialchars($order['billing_name'], ENT_QUOTES, 'UTF-8') . ',</p>'
            . '<p>Gracias por tu compra. Adjuntamos el documento PDF de la orden <strong>#' . (int)$order['id'] . '</strong>.</p>'
            . '<p>Total pagado: <strong>$' . number_format((float)$order['total_amount'], 2) . '</strong></p>';

        if ($isSimulation) {
            $mail->Body .= '<p><strong>MODO SIMULACION - NO VALIDO FISCALMENTE.</strong> Tambien adjuntamos el JSON DTE generado internamente.</p>';
        }

        $mail->addAttachment($pdf_absolute_path, 'factura_orden_' . (int)$order['id'] . '.pdf');

        foreach ($extraAttachments as $attachment) {
            $attachmentPath = trim((string) ($attachment['path'] ?? ''));
            if ($attachmentPath === '' || !is_file($attachmentPath)) {
                continue;
            }

            $attachmentName = trim((string) ($attachment['name'] ?? basename($attachmentPath)));
            $mail->addAttachment($attachmentPath, $attachmentName !== '' ? $attachmentName : basename($attachmentPath));
        }

        $mail->send();
    }
}
