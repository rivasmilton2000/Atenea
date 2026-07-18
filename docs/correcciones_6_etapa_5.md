# Correcciones 6 — Etapa 5

## Resultado

Se añadió agenda multirrol, chat interno por AJAX periódico, centro de correo SMTP/IMAP y supervisión administrativa sin reemplazar usuarios, autenticación, PHPMailer, notificaciones ni los layouts existentes. No se inició la etapa 6.

## Respaldo y migración

- Respaldo: `artifacts/db_atenea_pre_correcciones6_etapa5_20260717.sql`.
- SHA-256: `972CCEE4B1D7AF2096A1EC6B3FAF4D15709A02658070FB6032BA6C8D58CE8E98`.
- Migración: `src/database/migrations/020_comunicacion_agenda_correo.sql`.
- InnoDB, `utf8mb4_unicode_ci`, claves foráneas, índices y restricciones únicas.
- Aplicada repetidamente sin errores.

Tablas nuevas: `chat_conversaciones`, `chat_participantes`, `chat_mensajes`, `chat_lecturas`, `chat_adjuntos`, `chat_reportes`, `chat_bloqueos`, `correo_centro_hilos`, `correo_centro_mensajes`, `correo_centro_adjuntos` y `correo_imap_estado`.

## Funcionalidad

- Agenda por nombre/correo, rol, capacitación y sección, con alcance validado en backend.
- Chat individual deduplicado, búsqueda, contador de no leídos, lecturas, notificación en campana, sanitización, reportes, moderación y bloqueo.
- Polling AJAX cada cinco segundos; no requiere WebSocket.
- Centro compartido con Entrada, Enviados, Redactar, Chat, Agenda y No leídos.
- SMTP real mediante PHPMailer: `From` institucional y `Reply-To` del usuario que redacta.
- Registro de hilos, enviados, fallos, texto y adjuntos privados.
- IMAP seguro e idempotente por UID/Message-ID. Si no está disponible, la interfaz declara la configuración pendiente y no simula mensajes.
- Panel administrativo con estado SMTP/IMAP, pendientes, fallos, sincronización, bitácoras y moderación.
- Sidebar compartido preservado para administrador, docente y estudiante.

## Archivos principales

- `.env.example`, `.gitignore`.
- `includes/comunicacion_centro.php`, `includes/comunicacion_layout.php`, `includes/mailer.php`, `includes/portal_estudiante_layout.php`.
- `src/database/migrations/020_comunicacion_agenda_correo.sql`.
- `src/comunicaciones/` (agenda, chat, API, correo, hilos, adjuntos y sincronización IMAP).
- `src/dashboard/comunicaciones/servicio.php`, `moderacion.php`, `moderacion-accion.php`.
- `src/dashboard/partials/_sidebar.php`, `src/docente/partials/_sidebar.php`.
- `tests/integration/correcciones6_etapa5.php`.
- `docs/configuracion_correo_smtp_imap.md`.

## Pruebas

```powershell
C:\xampp\php\php.exe tests\integration\correcciones6_etapa5.php
```

Resultado: `OK 15 pruebas`.

Se validaron estudiante→docente, docente→administración, administrador→estudiante, respuesta, no leído, búsqueda/filtro por rol, privacidad de agenda, deduplicación, usuario bloqueado, acceso ajeno, eliminación de scripts/HTML, fallo SMTP real registrado, IMAP ausente sin simulación y adjunto malicioso rechazado. La prueba elimina usuarios y comunicaciones temporales.

## Configuración externa pendiente

En este entorno la extensión/configuración IMAP no está completa. Configure las variables indicadas en `docs/configuracion_correo_smtp_imap.md`, habilite PHP IMAP y reinicie Apache. SMTP debe configurarse con una contraseña de aplicación institucional. `COMMUNICATION_STORAGE_PATH` debe apuntar fuera del document root.
