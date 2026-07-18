# Instalación y actualización

## Requisitos

- PHP 8.0 o posterior con PDO MySQL, mysqli, mbstring, fileinfo, OpenSSL y JSON.
- Apache con HTTPS en producción.
- MySQL/MariaDB con InnoDB y soporte JSON/CHECK.
- Composer para Dompdf, QR y demás dependencias declaradas.
- Extensión PHP IMAP para sincronizar el buzón institucional.

## Procedimiento

1. Haga respaldo de archivos, `.env`, base de datos y almacenamiento privado.
2. Ejecute `composer install --no-dev --optimize-autoloader`.
3. Aplique únicamente las migraciones pendientes, en orden numérico. No ejecute archivos `*_rollback.sql` durante una actualización normal.
4. Configure `.env` tomando `.env.example` como referencia.
5. Cree los directorios privados y conceda escritura solo al proceso PHP.
6. Reinicie Apache/PHP y pruebe en un entorno de staging.
7. Abra el editor administrativo y confirme que existe una publicación inicial antes de editar.

Orden completo actual, omitiendo rollbacks:

```text
003_contenido_dinamico.sql
004_google_auth.sql
005_configuracion_portal_estudiante.sql
006_datos_personales_territorio.sql
007_comercio_stripe.sql
008_recuperacion_password.sql
009_perfiles_y_verificaciones.sql
010_gestion_categorias_producto.sql
011_pago_comprobante_correo.sql
012_administracion_usuarios_auditoria.sql
013_etapa1_google_usuarios_bitacora.sql
014_carrito_pedidos_dte.sql
015_notificaciones_comunicaciones_metricas.sql
016_portal_docente_relaciones_academicas.sql
017_index_publico_noticias.sql
018_capacitaciones_pagos_inscripciones.sql
019_flujo_academico_certificados.sql
020_comunicacion_agenda_correo.sql
021_website_borradores_versiones_preview.sql
```

Ejemplo local:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root db_atenea --default-character-set=utf8mb4 --execute="source C:/xampp/htdocs/Atenea/src/database/migrations/021_website_borradores_versiones_preview.sql"
```

## Variables externas

- Aplicación/Google: `APP_ENV`, `APP_URL_LOCAL`, `APP_URL_PRODUCTION`, `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`.
- Base de datos: `ATENEA_DB_HOST`, `ATENEA_DB_NAME`, `ATENEA_DB_USER`, `ATENEA_DB_PASSWORD`.
- Stripe: claves pública, secreta, webhook secret y moneda definidas en `.env.example`/configuración existente.
- SMTP: host, puerto, cifrado, usuario, contraseña de aplicación, correo y nombre institucional.
- IMAP: host, puerto, cifrado, usuario, contraseña de aplicación y carpeta.
- Privados: `ACADEMIC_STORAGE_PATH`, `COMMUNICATION_STORAGE_PATH`, rutas DTE/certificados.

Nunca versionar `.env`, secretos, certificados, respaldos ni archivos privados.

## Tareas programadas

La sincronización IMAP no se ejecuta durante cada carga web. Puede programarse cada 5–10 minutos:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\Atenea\src\cron\sincronizar-imap.php
```

El webhook Stripe debe conservar su URL HTTPS pública y `STRIPE_WEBHOOK_SECRET`. No use la página de éxito como confirmación del pago.

## Publicación

1. Entre como administrador.
2. Abra **Editor y vista previa**.
3. Edite dentro del panel izquierdo.
4. Valide escritorio, tablet y móvil en el iframe.
5. Añada un comentario y pulse **Publicar cambios**.
6. Si el resultado no es correcto, restaure una versión; la restauración queda como borrador hasta una nueva publicación.

## Producción

- Use HTTPS, cookies seguras y credenciales de mínimo privilegio.
- Deshabilite `display_errors`; conserve logs fuera del webroot.
- Verifique límites de subida, backups, permisos, cron, SMTP, IMAP y webhook.
- Pruebe OAuth y Stripe en sus entornos oficiales antes de aceptar usuarios/pagos reales.
