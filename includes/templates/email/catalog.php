<?php
declare(strict_types=1);

require_once __DIR__ . '/layout.php';

function plantillaCorreoAtenea(string $tipo, array $datos): array
{
    $e = static fn(mixed $valor): string => atenea_e((string) $valor);
    $parrafo = static fn(string $texto): string => '<p style="margin:0 0 16px;line-height:1.65;">' . $texto . '</p>';
    $nombre = trim((string) ($datos['nombre'] ?? '')) ?: 'estudiante';
    $asunto = 'Notificación de Atenea';
    $preencabezado = 'Tienes una nueva notificación de Atenea.';
    $html = '';
    $texto = '';

    switch ($tipo) {
        case 'compra_confirmada':
            $asunto = 'Pago confirmado · Pedido ' . (string) ($datos['numero'] ?? '');
            $preencabezado = 'Tu pago fue confirmado y tu comprobante ya está disponible.';
            $metodo = trim((string) ($datos['metodo'] ?? '')) ?: 'Procesado por Stripe';
            $html = $parrafo('Hola, ' . $e($nombre) . '.')
                . $parrafo('Stripe confirmó correctamente tu pago. Atenea no almacena el número completo ni el CVC de tu tarjeta.')
                . '<table role="presentation" width="100%" cellspacing="0" cellpadding="8" style="margin:18px 0;background:#f7f4ec;border-radius:8px;">'
                . '<tr><td><strong>Pedido</strong></td><td align="right">' . $e($datos['numero'] ?? '') . '</td></tr>'
                . '<tr><td><strong>Fecha</strong></td><td align="right">' . $e($datos['fecha'] ?? '') . '</td></tr>'
                . '<tr><td><strong>Estado</strong></td><td align="right">Pagado</td></tr></table>';
            if (!empty($datos['productos']) && is_array($datos['productos'])) {
                $html .= '<table role="presentation" width="100%" cellspacing="0" cellpadding="8" style="margin:18px 0;border-collapse:collapse;border:1px solid #e2dccd;">'
                    . '<tr style="background:#173f35;color:#ffffff;"><th align="left">Producto</th><th align="center">Cantidad</th><th align="right">Importe</th></tr>';
                foreach ($datos['productos'] as $producto) {
                    $html .= '<tr><td style="border-bottom:1px solid #e2dccd;">' . $e($producto['nombre'] ?? '') . '</td><td align="center" style="border-bottom:1px solid #e2dccd;">' . (int) ($producto['cantidad'] ?? 0) . '</td><td align="right" style="border-bottom:1px solid #e2dccd;">' . $e($producto['subtotal'] ?? '') . '</td></tr>';
                }
                $html .= '</table>';
            }
            $html .= '<table role="presentation" width="100%" cellspacing="0" cellpadding="6" style="margin:18px 0;">'
                . '<tr><td>Subtotal</td><td align="right">' . $e($datos['subtotal_formateado'] ?? '') . '</td></tr>'
                . '<tr><td>Descuento</td><td align="right">' . $e($datos['descuento_formateado'] ?? '') . '</td></tr>'
                . '<tr><td><strong>Total</strong></td><td align="right"><strong>' . $e($datos['total_formateado'] ?? '') . '</strong></td></tr>'
                . '<tr><td>Método</td><td align="right">' . $e($metodo) . '</td></tr></table>'
                . botonCorreoAtenea('Ver comprobante de compra', (string) ($datos['comprobante_url'] ?? atenea_url_absoluta('src/estudiantes/pedidos.php')))
                . $parrafo('<strong>Dirección:</strong> '.$e($datos['direccion']??'').'<br><strong>Código de generación:</strong> '.$e($datos['codigo_generacion']??''));
            if(!empty($datos['pdf_url'])) $html .= botonCorreoAtenea('Descargar factura PDF',(string)$datos['pdf_url']);
            if(!empty($datos['json_url'])) $html .= $parrafo('<a href="'.$e($datos['json_url']).'">Descargar JSON validado</a>');
            $html .= $parrafo('<small>Los documentos requieren iniciar sesión y solo están disponibles para el propietario.</small>');
            $lineasProducto = [];
            foreach (is_array($datos['productos'] ?? null) ? $datos['productos'] : [] as $producto) $lineasProducto[] = ($producto['nombre'] ?? '') . ' · ' . (int) ($producto['cantidad'] ?? 0) . ' · ' . ($producto['subtotal'] ?? '');
            $texto = "Hola, {$nombre}.\n\nTu pago fue confirmado.\nPedido: " . ($datos['numero'] ?? '') . "\nFecha: " . ($datos['fecha'] ?? '') . "\nProductos:\n" . implode("\n", $lineasProducto) . "\nSubtotal: " . ($datos['subtotal_formateado'] ?? '') . "\nDescuento: " . ($datos['descuento_formateado'] ?? '') . "\nTotal: " . ($datos['total_formateado'] ?? '') . "\nMétodo: {$metodo}\nComprobante: " . ($datos['comprobante_url'] ?? '');
            break;

        case 'comprobante_disponible':
            $asunto = 'Comprobante disponible · Pedido ' . (string) ($datos['numero'] ?? '');
            $preencabezado = 'Tu comprobante interno de compra está disponible.';
            $html = $parrafo('Hola, ' . $e($nombre) . '. Tu comprobante interno no fiscal ya está disponible.')
                . botonCorreoAtenea('Consultar comprobante', (string) ($datos['comprobante_url'] ?? ''));
            $texto = "Hola, {$nombre}. Tu comprobante interno no fiscal está disponible: " . ($datos['comprobante_url'] ?? '');
            break;

        case 'contacto_recibido':
            $asunto = '[Contacto Atenea] ' . (string) ($datos['asunto'] ?? 'Nuevo mensaje');
            $preencabezado = 'Se recibió un nuevo mensaje desde el formulario de contacto.';
            $html = '<table role="presentation" width="100%" cellspacing="0" cellpadding="8" style="background:#f7f4ec;border-radius:8px;">'
                . '<tr><td><strong>Nombre</strong></td><td>' . $e($datos['nombre'] ?? '') . '</td></tr>'
                . '<tr><td><strong>Correo</strong></td><td>' . $e($datos['correo'] ?? '') . '</td></tr>'
                . '<tr><td><strong>Asunto</strong></td><td>' . $e($datos['asunto'] ?? '') . '</td></tr>'
                . '<tr><td valign="top"><strong>Mensaje</strong></td><td>' . nl2br($e($datos['mensaje'] ?? '')) . '</td></tr>'
                . '<tr><td><strong>Fecha</strong></td><td>' . $e($datos['fecha'] ?? date('d/m/Y H:i')) . '</td></tr>'
                . '<tr><td><strong>Dirección IP</strong></td><td>' . $e($datos['ip'] ?? 'No disponible') . '</td></tr>'
                . '<tr><td><strong>Referencia</strong></td><td>' . $e($datos['referencia'] ?? 'No disponible') . '</td></tr></table>'
                . (!empty($datos['enlace']) ? botonCorreoAtenea('Abrir mensaje en el dashboard', (string)$datos['enlace']) : '')
                . botonCorreoAtenea('Responder por correo', 'mailto:' . (string) ($datos['correo'] ?? ''));
            $texto = "Nuevo mensaje de contacto\nNombre: " . ($datos['nombre'] ?? '') . "\nCorreo: " . ($datos['correo'] ?? '') . "\nAsunto: " . ($datos['asunto'] ?? '') . "\nMensaje:\n" . ($datos['mensaje'] ?? '') . "\nFecha: " . ($datos['fecha'] ?? '') . "\nIP: " . ($datos['ip'] ?? 'No disponible') . "\nDashboard: " . ($datos['enlace'] ?? '');
            break;

        case 'recuperacion_password':
            $asunto = 'Restablece tu contraseña de Atenea';
            $preencabezado = 'Utiliza este enlace dentro de los próximos 30 minutos.';
            $html = $parrafo('Hola, ' . $e($nombre) . '.') . $parrafo('Recibimos una solicitud para restablecer tu contraseña.')
                . botonCorreoAtenea('Restablecer mi contraseña', (string) ($datos['enlace'] ?? ''))
                . $parrafo('El enlace vence en 30 minutos y solo puede utilizarse una vez. Si no solicitaste el cambio, ignora este mensaje.');
            $texto = "Hola, {$nombre}.\nRecibimos una solicitud para restablecer tu contraseña.\n" . ($datos['enlace'] ?? '') . "\nEl enlace vence en 30 minutos y solo puede usarse una vez.";
            break;

        case 'cambio_password':
            $asunto = 'Tu contraseña de Atenea fue actualizada';
            $preencabezado = 'Confirmación de cambio de contraseña.';
            $html = $parrafo('Hola, ' . $e($nombre) . '.') . $parrafo('Tu contraseña fue actualizada correctamente. Si no realizaste este cambio, comunícate con Atenea de inmediato.');
            $texto = "Hola, {$nombre}. Tu contraseña fue actualizada correctamente. Si no realizaste el cambio, comunícate con Atenea.";
            break;

        case 'cambio_rol':
            $asunto = 'Actualización de acceso en Atenea';
            $preencabezado = 'Se actualizó el tipo de acceso asociado a tu cuenta.';
            $html = $parrafo('Hola, ' . $e($nombre) . '. El tipo de acceso de tu cuenta ahora es: <strong>' . $e($datos['rol'] ?? '') . '</strong>.');
            $texto = "Hola, {$nombre}. El tipo de acceso de tu cuenta ahora es: " . ($datos['rol'] ?? '') . '.';
            break;

        case 'recuperacion_asistida_codigo':
            $asunto = 'Codigo de recuperacion asistida de Atenea';
            $preencabezado = 'Comparte este codigo unicamente con el personal de Atenea que atiende tu solicitud.';
            $html = $parrafo('Hola, ' . $e($nombre) . '.') . $parrafo('Un administrador autorizado inicio una recuperacion asistida para tu cuenta.')
                . '<p style="margin:22px 0;padding:16px;background:#f7f4ec;border:1px solid #e7c66a;border-radius:8px;text-align:center;font-size:28px;letter-spacing:6px;font-weight:700;color:#173f35;">' . $e($datos['codigo'] ?? '') . '</p>'
                . $parrafo('El codigo vence en 10 minutos, tiene intentos limitados y solo debe comunicarse al personal que ya atiende tu caso. Atenea nunca te pedira tu contrasena.');
            $texto = "Hola, {$nombre}. Codigo de recuperacion asistida: " . ($datos['codigo'] ?? '') . ". Vence en 10 minutos. Atenea nunca te pedira tu contrasena.";
            break;

        case 'recuperacion_asistida_enlace':
            $asunto = 'Crea una nueva contrasena para tu cuenta Atenea';
            $preencabezado = 'La verificacion asistida finalizo; solo tu puedes establecer la nueva contrasena.';
            $html = $parrafo('Hola, ' . $e($nombre) . '.') . $parrafo('El codigo de recuperacion fue validado. Utiliza el siguiente enlace de un solo uso para crear tu nueva contrasena:')
                . botonCorreoAtenea('Crear nueva contrasena', (string)($datos['enlace'] ?? ''))
                . $parrafo('El enlace vence en 30 minutos. El administrador no puede ver ni elegir tu contrasena.');
            $texto = "Hola, {$nombre}. Crea tu nueva contrasena con este enlace de un solo uso: " . ($datos['enlace'] ?? '') . ". Vence en 30 minutos.";
            break;

        case 'cuenta_eliminacion_solicitada':
            $asunto = 'Solicitud administrativa sobre tu cuenta Atenea';
            $preencabezado = 'Tu cuenta fue desactivada y entro en un periodo de gracia.';
            $html = $parrafo('Hola, ' . $e($nombre) . '.') . $parrafo('Tu cuenta fue desactivada y entro en un periodo de gracia hasta <strong>' . $e($datos['fecha_limite'] ?? '') . '</strong>.')
                . $parrafo('Los pedidos, comprobantes y registros que deban conservarse no se eliminan. Contacta a Atenea si consideras que se trata de un error.');
            $texto = "Hola, {$nombre}. Tu cuenta fue desactivada y entro en periodo de gracia hasta " . ($datos['fecha_limite'] ?? '') . ". Contacta a Atenea si necesitas asistencia.";
            break;

        case 'cuenta_restaurada':
            $asunto = 'Tu cuenta Atenea fue restaurada';
            $preencabezado = 'La solicitud de eliminacion fue cancelada.';
            $html = $parrafo('Hola, ' . $e($nombre) . '. Tu cuenta fue restaurada y la solicitud de eliminacion quedo cancelada. Por seguridad, deberas iniciar sesion nuevamente.');
            $texto = "Hola, {$nombre}. Tu cuenta Atenea fue restaurada. Por seguridad, deberas iniciar sesion nuevamente.";
            break;

        case 'cuenta_desactivada':
            $asunto = 'Aviso sobre tu cuenta de Atenea';
            $preencabezado = 'Tu cuenta fue desactivada.';
            $html = $parrafo('Hola, ' . $e($nombre) . '. Tu cuenta fue desactivada.') . $parrafo($e($datos['motivo'] ?? 'Comunícate con Atenea si necesitas asistencia.'));
            $texto = "Hola, {$nombre}. Tu cuenta fue desactivada. " . ($datos['motivo'] ?? 'Comunícate con Atenea si necesitas asistencia.');
            break;

        case 'eliminacion_inactividad':
            $asunto = 'Aviso de inactividad de tu cuenta Atenea';
            $preencabezado = 'Tu cuenta requiere atención para conservarse activa.';
            $html = $parrafo('Hola, ' . $e($nombre) . '.') . $parrafo($e($datos['mensaje'] ?? 'Tu cuenta presenta un período prolongado de inactividad.'));
            $texto = "Hola, {$nombre}. " . ($datos['mensaje'] ?? 'Tu cuenta presenta un período prolongado de inactividad.');
            break;

        case 'verificacion_cuenta':
            $asunto = 'Código de verificación de Atenea';
            $preencabezado = 'Utiliza el código para confirmar el cambio solicitado.';
            $html = $parrafo('Hola, ' . $e($nombre) . '. Usa este código para confirmar el cambio solicitado:')
                . '<p style="margin:22px 0;padding:16px;background:#f7f4ec;border:1px solid #e7c66a;border-radius:8px;text-align:center;font-size:28px;letter-spacing:6px;font-weight:700;color:#173f35;">' . $e($datos['codigo'] ?? '') . '</p>'
                . $parrafo('El código vence pronto. No lo compartas con nadie.');
            $texto = "Hola, {$nombre}. Código de verificación: " . ($datos['codigo'] ?? '') . '. No lo compartas con nadie.';
            break;

        case 'aviso_administrativo':
        default:
            $asunto = (string) ($datos['asunto'] ?? 'Aviso de Atenea');
            $preencabezado = (string) ($datos['resumen'] ?? 'Tienes un nuevo aviso de Atenea.');
            $html = $parrafo('Hola, ' . $e($nombre) . '.') . $parrafo(nl2br($e($datos['mensaje'] ?? '')));
            if (!empty($datos['enlace']) && !empty($datos['texto_boton'])) $html .= botonCorreoAtenea((string) $datos['texto_boton'], (string) $datos['enlace']);
            $texto = "Hola, {$nombre}.\n\n" . ($datos['mensaje'] ?? '');
            break;
    }

    $layout = renderizarLayoutCorreoAtenea($tipo === 'contacto_recibido' ? 'Nuevo mensaje de contacto' : $asunto, $preencabezado, $html, $texto);
    return ['subject' => $asunto, 'html' => $layout['html'], 'text' => $layout['text']];
}
