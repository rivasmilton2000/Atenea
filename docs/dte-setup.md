# Configuración DTE de Atenea

## Fuentes y alcance

La estructura `resources/dte/schemas/fe-fc-v1.json` corresponde a **Factura Electrónica v1 (`tipoDte` 01)** y se mantiene completa, sin sustituirla por una estructura propia. La referencia normativa y de integración es la [Guía de Integración de Factura Electrónica del Ministerio de Hacienda](https://factura.gob.sv/wp-content/uploads/2021/11/FESVDGIIMH_GuiaIntegracionFacturaElectronicasSV.pdf). El esquema fue recuperado de una copia pública identificada como `fe-fc-v1.json`; antes de activar producción debe compararse con la versión entregada por Hacienda al emisor autorizado.

## Desarrollo seguro

1. Ejecute `composer install` en la raíz.
2. Aplique `src/database/migrations/014_carrito_pedidos_dte.sql` una sola vez.
3. Habilite `extension=gd` en el `php.ini` usado por Apache y CLI; Dompdf la necesita para renderizar el logo PNG.
4. Cree una carpeta privada fuera de `htdocs`, por ejemplo `C:/xampp/atenea-private/dte`, y dé permiso de escritura únicamente a PHP/Apache.
5. Configure `DTE_ENV=simulation` y `DTE_STORAGE_PATH` en `.env`.
6. Desde **Dashboard > Configuración > Configuración DTE**, registre los datos reales del emisor. No se proporcionan NIT, NRC ni datos fiscales ficticios predeterminados.

En simulación se genera JSON, PDF, QR, UUID, correlativo y sello marcado `SIMULADO`; no se hace ninguna solicitud a Hacienda.

## Test y producción

Los secretos se configuran únicamente en `.env` o en variables del sistema: usuario, contraseña, ruta absoluta del certificado, secreto del certificado y URL del firmador. El certificado debe permanecer fuera del directorio público y del repositorio.

El proveedor de Hacienda prepara autenticación, firmado, recepción, consulta e invalidación. Antes de cambiar `DTE_ENV` a `test` o `production`:

1. Confirme con Hacienda las URLs vigentes asignadas al emisor.
2. Instale y proteja el servicio de firmado compatible; configure `DTE_SIGN_URL`.
3. Verifique certificado, secreto, usuario y contraseña.
4. Ejecute casos de certificación en `test` y confirme que el sello guardado coincide con la respuesta real.
5. Active producción solamente después de la autorización correspondiente.

La aplicación bloquea test/producción si falta cualquiera de esas piezas y jamás crea un sello simulado fuera del ambiente `simulation`.

## Correo y reintentos

El webhook intenta generar el DTE y después envía el correo. Un error de DTE o correo no revierte el pago ni duplica stock. Programe cada cinco minutos `src/cron/reintentar-dte.php` y `src/cron/reintentar-correos-compra.php` con el PHP de XAMPP. Cuando un DTE pendiente logra emitirse, se envía un aviso idempotente con su enlace seguro.
