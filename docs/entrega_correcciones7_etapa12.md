# Entrega final — Correcciones 7 / Etapa 12

Fecha de validación: 20 de julio de 2026  
Proyecto: Atenea Escuela de Naturopatía Holística

## Resultado ejecutivo

- 321 de 321 comprobaciones de integración PHP aprobadas.
- 5 de 5 recorridos de navegador aprobados en Microsoft Edge headless.
- 12 capturas nuevas de escritorio y móvil.
- PHP lint aprobado en los archivos de la etapa 11 y en las pruebas nuevas de la etapa 12.
- IMAP disponible, configurado y con conexión de solo lectura correcta durante la regresión.
- `MAIL_TEST_MODE=true` durante toda la validación.
- `MAIL_TEST_RECIPIENT` permaneció vacío: no fue posible realizar una entrega SMTP real.
- No se ejecutaron bucles de correo ni envíos a usuarios de la base.
- Las cuentas, tokens, carritos, fotografías y registros de prueba temporales se eliminaron al finalizar.

## Cobertura solicitada

| Área | Validación | Resultado |
|---|---|---|
| Login tradicional | Registro real, logout, nuevo login y redirección por rol | Aprobado |
| Login con Google | Estado OAuth, callback, cuenta no vinculada, vinculación posterior y límites | Aprobado sin contactar Google |
| Registro tradicional | Formulario completo, ubicación, términos, contraseña y persistencia | Aprobado en navegador |
| Registro con Google | Cuenta pendiente y perfil obligatorio completo | Aprobado a nivel de servicio |
| Google no vinculado | No crea cuenta y ofrece registro o vinculación segura | Aprobado |
| Recuérdame | Token aleatorio, hash en BD, cookie HttpOnly y revocación | Aprobado |
| Logout | Revoca cookie/token y destruye sesión | Aprobado en navegador |
| Inactividad | Aviso a 5 minutos, continuidad y cierre backend a 10 minutos | Aprobado con reloj controlado |
| Recuperación | Solicitud indistinguible, token, expiración y cola segura | Aprobado |
| Perfil | Administrador, superadministrador, estudiante y docente | Aprobado |
| Fotografía | Selección, recorte, zoom, guardado, recarga y fallback | Aprobado en navegador |
| Eliminar cuenta | Código, contraseña, bloqueo, 60 días y anonimización | Aprobado con cuenta temporal |
| Carrito | Invitado, persistente, sincronización, 1→2→1 y stock | Aprobado |
| Pago | Precio/stock backend, webhook e inscripción pendiente | Aprobado sin cargo real |
| Asignación | Automática, idempotencia, aviso y correo único | Aprobado |
| Capacidad | Cupo 20, sin duplicados y reducción sin expulsión | Aprobado |
| Navegación estudiante | Rutas, sidebar, móvil, sección activa y datos reales | Aprobado |
| Navegación docente | Rutas y aislamiento entre dos docentes | Aprobado |
| Navbar dinámica | CRUD, roles, submenús, papelera, HTML saneado y preview | Aprobado |
| Editor visual | Tokens, preview inmediato, publicar, cancelar y restaurar | Aprobado |
| Contacto | Validación, CSRF, CAPTCHA, idempotencia y cola | Aprobado sin resolver CAPTCHA externo |
| Chat | Roles, AJAX, estados, respuesta, adjuntos e idempotencia | Aprobado |
| Notificaciones | No leídas, marcar una/todas, enlaces y duplicados | Aprobado |
| IMAP | Extensión, variables, conexión y diagnóstico | Aprobado |
| Backup | Archivo privado, hash, descarga, restauración desechable y retención | Aprobado |
| Errores | 403, 404, 419, 500, BD y 503 sin datos técnicos | Aprobado |
| Diseño móvil | Inicio, contacto, login, carrito, error y perfil docente | Aprobado |
| Correo | Modo prueba, duplicados, máximo 3/min y 5/usuario/h | Aprobado sin SMTP |

## Seguridad de correo verificada

La prueba de frecuencia creó registros temporales controlados en la cola, nunca destinatarios reales. Se confirmó:

- el cuarto intento dentro de un minuto devuelve `limitado`;
- el sexto intento de un usuario dentro de una hora devuelve `limitado`;
- ambos quedan con intento `0`, por lo que SMTP no se abre;
- un evento duplicado devuelve el mismo registro;
- en modo de pruebas los correos ordinarios quedan `cancelado`;
- la interfaz administrativa muestra el resumen antes de una prueba;
- exige dirección exacta autorizada, frase `ENVIAR PRUEBA` y casilla de confirmación;
- la acción no consulta ni recorre la tabla de usuarios;
- todos los intentos administrativos quedan en auditoría e historial.

## Migraciones SQL entregadas

Aplicar en orden después de las migraciones ya instaladas:

1. `024_contacto_chat_moderno.sql`
2. `025_autenticacion_sesiones_google.sql`
3. `026_perfil_avatar_eliminacion_cuenta.sql`
4. `027_copias_seguridad_base_datos.sql`
5. `028_personalizacion_visual_portales.sql`
6. `029_asignacion_clases_docentes.sql`

La etapa 12 no necesita una migración adicional.

## Variables documentadas en `.env.example`

- Aplicación y BD: `APP_ENV`, `APP_URL_LOCAL`, `APP_URL_PRODUCTION`, `ATENEA_DB_*`.
- Google: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`.
- SMTP: `SMTP_HOST`, `SMTP_PORT`, `SMTP_ENCRYPTION`, `SMTP_USERNAME`, `SMTP_PASSWORD`, `SMTP_FROM_EMAIL`, `SMTP_FROM_NAME`.
- Pruebas de correo: `MAIL_TEST_MODE`, `MAIL_TEST_RECIPIENT`, `MAIL_MAX_PER_MINUTE`, `MAIL_MAX_PER_USER_PER_HOUR`, `MAIL_MAX_RETRIES`.
- IMAP: `IMAP_HOST`, `IMAP_PORT`, `IMAP_ENCRYPTION`, `IMAP_USERNAME`, `IMAP_PASSWORD`, `IMAP_FOLDER`.
- Almacenamiento: `ACADEMIC_STORAGE_PATH`, `COMMUNICATION_STORAGE_PATH`, `BACKUP_STORAGE_PATH`.
- Backup: `BACKUP_RETENTION_DAYS`, `BACKUP_MAX_FILES`, `BACKUP_EXCLUDED_DATA_TABLES`.
- Comercio: `SHOP_SHIPPING_AMOUNT`, `SHOP_TAX_RATE`, `SHOP_TAX_INCLUDED`.
- DTE: `DTE_ENV`, rutas y credenciales DTE documentadas en el ejemplo.

## Archivos modificados

Configuración y servicios compartidos:

- `.env.example`
- `includes/alerts.php`, `auth.php`, `capacitaciones.php`, `comunicacion_centro.php`, `cuenta.php`
- `includes/docente_academico.php`, `footer.php`, `google_oauth.php`, `header.php`, `mailer.php`, `navbar.php`
- `includes/perfil_modal.php`, `perfil_usuario.php`, `permissions.php`, `portal_estudiante_layout.php`

Autenticación, cuenta y pagos:

- `src/auth/google-callback.php`, `google.php`, `procesar_login.php`, `procesar_registro.php`
- `src/auth/restablecer-asistido.php`, `restablecer-password.php`
- `src/cuenta/actualizar-perfil.php`, `confirmar-cambio.php`
- `src/login/assisted-reset.php`, `auth.css`, `logout.php`, `reset-password.php`, `sign-in.php`, `sign-up.php`
- `src/pagos/success-capacitacion.php`

Portales y comunicación:

- `src/comunicaciones/chat-api.php`, `chat.php`
- `src/dashboard/capacitaciones/editar.php`, `inscripciones.php`, `seccion-accion.php`, `secciones.php`
- `src/dashboard/includes/header.php`, `scripts.php`, `partials/_navbar.php`, `partials/_sidebar.php`, `usuarios/accion.php`
- `src/docente/_layout.php`, `comunicaciones.php`, `contenido_editar.php`, `contenidos.php`, `entregas.php`
- `src/docente/estudiantes.php`, `evaluacion_editar.php`, `evaluaciones.php`, `partials/_navbar.php`, `partials/_sidebar.php`, `progreso.php`
- `src/estudiantes/assets/css/atenea-branding.css`, `contenido.php`, `curso.php`, `cursos.php`, `dashboard/index.php`, `index.php`, `perfil.php`
- `src/website/assets/css/perfil-modal.css`, `assets/js/perfil-modal.js`, `contact.php`, `forms/contact.php`

## Archivos y módulos nuevos

Servicios:

- `includes/account_deletion.php`, `auth_remember.php`, `database_backups.php`
- `includes/personalizacion_visual.php`, `portal_estudiante_aula.php`

Autenticación y cuenta:

- `src/auth/completar-perfil-google.php`, `session-activity.php`
- `src/cuenta/confirmar-eliminacion.php`, `solicitar-eliminacion.php`
- `src/cron/anonymize-deleted-accounts.php`

Comunicación y administración:

- `src/comunicaciones/assets/`, `chat-adjunto.php`
- `src/dashboard/backups/`
- `src/dashboard/capacitaciones/auto-asignar.php`, `seccion-detalle.php`
- `src/dashboard/personalizacion/`

Portales:

- Docente: `calendario.php`, `calificaciones.php`, `perfil.php`, `tareas.php`.
- Estudiante: `calendario.php`, `calificaciones.php`, `clase.php`, `contenidos.php`, `evaluaciones.php`, `soporte.php`, `tareas.php`, `videos.php`.
- Estudiante JS: `src/estudiantes/assets/js/portal-navigation.js`.
- Website: `contact-modern.css`, `security-ui.css`, `contact-form.js`, `google-profile.js`, `security-ui.js`.

Pruebas y evidencia:

- `playwright.config.js`
- `tests/fixtures/etapa12_usuarios.php`
- `tests/integration/correcciones7_etapa4_contacto_chat.php` a `correcciones7_etapa12_entrega.php`
- `tests/visual/correcciones7_etapa12.spec.js`
- `artifacts/etapa12/`

## Explicación de módulos

- Autenticación: tokens recordados con hash, OAuth sin alta automática, perfil Google pendiente e inactividad backend/frontend.
- Perfil: modal compartido, actualización AJAX, avatar procesado y eliminación diferida con auditoría.
- Comercio: carrito de sesión/BD, sincronización, precio y stock recalculados, pago idempotente.
- Académico: asignación automática a secciones, cupos, movimientos, historial, aula y portal docente.
- Comunicación: contacto protegido, chat moderno, IMAP central y cola saliente segura.
- CMS: navbar dinámica saneada y personalización visual publicada desde base de datos.
- Operación: backups privados, retención, restauración protegida, errores profesionales y auditoría.

## Reproducir las pruebas

Integración PHP completa:

```powershell
$php='C:\xampp\php\php.exe'
Get-ChildItem tests\integration\correcciones7_*.php | Sort-Object Name | ForEach-Object { & $php $_.FullName }
```

Navegador y capturas, con Apache y MySQL locales activos:

```powershell
npm install --no-save --no-package-lock @playwright/test@1.61.1
C:\xampp\php\php.exe tests\fixtures\etapa12_usuarios.php setup
npx playwright test tests/visual/correcciones7_etapa12.spec.js --reporter=line
C:\xampp\php\php.exe tests\fixtures\etapa12_usuarios.php cleanup
```

La limpieza debe ejecutarse también si el navegador se interrumpe.

## Errores encontrados y corregidos

- El modal podía heredar reglas que ampliaban la fotografía; ahora tiene límites compartidos estrictos y scroll interno.
- Contenidos, Entregas y Progreso del docente obtenían el identificador antes de inicializar el contexto.
- Filtros docentes podían responder con textos técnicos; ahora usan la página 403 compartida.
- Se completaron rutas faltantes de Tareas, Calificaciones, Calendario y Perfil docente.
- Se corrigieron navegación móvil, estados activos y rutas canónicas del aula estudiantil.
- Se reforzó la prevención de doble envío en contacto, chat, carrito, pagos, asignaciones y correo.
- Durante las pruebas visuales se ajustaron selectores del propio test para distinguir “Crear cuenta”, “Guardar perfil”, cantidades visibles y productos disponibles. No fueron fallos de producción.

## Funciones no ejecutadas contra servicios reales

- Google OAuth real: no se inició sesión contra una identidad externa ni se modificó una cuenta real. Se probaron estado, callback y reglas con perfiles controlados.
- Stripe real: no se hizo ningún cargo. Se probaron checkout, webhook, importes, stock y asignación con eventos controlados.
- SMTP real: prohibido para esta entrega; `MAIL_TEST_RECIPIENT` estaba vacío.
- reCAPTCHA real: no se resolvió el reto de Google de forma automatizada; backend, expiración, errores e idempotencia sí se probaron.
- Restauración sobre producción: se restauró solamente una base desechable.
- “Personal” no es un rol independiente en el esquema actual; `usuarios.rol` admite `admin`, `usuario` y `docente`. Añadirlo exigiría una decisión funcional y una migración transversal, por lo que no se simuló.

## Revisión de permisos

- Administrador: requiere rol `admin` y permisos granulares por módulo.
- Superadministrador: es `admin` con `es_superadmin=1`; solo este puede restaurar backups y ejecutar acciones críticas reservadas.
- Docente: exige rol y verifica `docente_id`, curso, sección, contenido y estudiante relacionados.
- Estudiante: exige rol `usuario` y filtra inscripciones, contenidos, compras, certificados y progreso por `usuario_id`.
- Chat: aplica matriz de roles y relaciones académicas; rechaza conversaciones no autorizadas.
- Website público: no expone endpoints administrativos; acciones mutables exigen CSRF y validación backend.

## Activación en producción

1. Crear una copia desde el módulo administrativo y verificar su hash.
2. Aplicar las migraciones 024–029 en orden sobre una ventana de mantenimiento.
3. Configurar `APP_ENV=production` y `APP_URL_PRODUCTION` con HTTPS.
4. Configurar Google, Stripe, SMTP, IMAP y reCAPTCHA con credenciales de producción fuera del repositorio.
5. Crear y proteger las rutas privadas académicas, de comunicación, DTE y backups.
6. Mantener inicialmente `MAIL_TEST_MODE=true` y definir una única `MAIL_TEST_RECIPIENT` autorizada.
7. Ejecutar desde Administración → Comunicaciones → Correos una sola prueba confirmada.
8. Revisar cola, auditoría y recepción; después cambiar `MAIL_TEST_MODE=false`.
9. Programar `src/cron/procesar-cola-correos.php`, `sincronizar-imap.php`, `reintentar-dte.php`, `reintentar-correos-compra.php`, `cleanup-inactive-accounts.php` y `anonymize-deleted-accounts.php` con el PHP CLI de producción.
10. Repetir smoke tests de login, carrito, pago de prueba del proveedor, permisos y backup.

## Probar sin enviar correos reales

```dotenv
MAIL_TEST_MODE=true
MAIL_TEST_RECIPIENT=
MAIL_MAX_PER_MINUTE=3
MAIL_MAX_PER_USER_PER_HOUR=5
MAIL_MAX_RETRIES=3
```

Con el destinatario vacío, todos los correos ordinarios quedan registrados y cancelados. Para una prueba real única, colocar temporalmente una dirección controlada en `MAIL_TEST_RECIPIENT` y usar solamente el botón administrativo con la confirmación requerida.

## Evidencias

- `artifacts/etapa12/inicio-desktop.png`
- `artifacts/etapa12/login-mobile.png`
- `artifacts/etapa12/contacto-mobile.png`
- `artifacts/etapa12/carrito-mobile.png`
- `artifacts/etapa12/carrito-con-producto.png`
- `artifacts/etapa12/estudiante-dashboard-desktop.png`
- `artifacts/etapa12/perfil-estudiante-edicion.png`
- `artifacts/etapa12/dashboard-admin.png`
- `artifacts/etapa12/perfil-administrador.png`
- `artifacts/etapa12/editor-visual.png`
- `artifacts/etapa12/perfil-docente-mobile.png`
- `artifacts/etapa12/error-404-mobile.png`

## Confirmaciones

- No se sustituyó el proyecto ni se cambiaron sus rutas base.
- No se eliminaron módulos funcionales; Entregas, Progreso y Comunicaciones académicas se conservaron.
- No se enviaron correos masivos ni mensajes SMTP reales.
- No se usaron correos reales de usuarios en pruebas.
- Las pruebas temporales no dejaron cuentas de prueba activas.
