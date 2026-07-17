# Correcciones 6 — etapa 1: auditoría y base técnica

Fecha de auditoría: 2026-07-16. Alcance: proyecto monolítico PHP Atenea y esquema vivo `db_atenea`. Esta etapa no inicia trabajos de la etapa 2.

## Respaldo previo

Antes de modificar código se creó `artifacts/db_atenea_pre_correcciones6_etapa1_20260716.sql` mediante `mysqldump --single-transaction --default-character-set=utf8mb4`. El archivo tiene 120623 bytes y SHA-256 `4EEBE75471C94F8EA8D106357302F098EE3E01962179D1A7B159B786C1BF78F2`.

El respaldo contiene datos reales y quedó excluido explícitamente mediante `.gitignore`; debe almacenarse con los controles de acceso y retención correspondientes, no publicarse en Git.

No se alteró la estructura ni la información de la base. El esquema ya contiene las columnas, claves únicas, índices y relaciones que necesita Google OAuth; por ello no se creó ni ejecutó una migración nueva.

## Arquitectura actual

- Aplicación monolítica PHP 8.0.30 sobre XAMPP/Apache y MariaDB 10.4.32. Composer administra librerías auxiliares; no hay framework MVC.
- Acceso a MySQL centralizado en `includes/conexion.php`. Aunque la consigna menciona mysqli, la arquitectura real encontrada usa PDO con consultas preparadas, excepciones y `charset=utf8mb4`; cambiar de controlador en esta etapa implicaría un riesgo innecesario.
- Variables y secretos: `.env`, cargado por `includes/env.php`; `.env` está ignorado por Git. `includes/config.php` resuelve rutas del proyecto e `includes/config/services.php` centraliza Google, correo, reCAPTCHA y Stripe.
- Sesión y CSRF: `includes/session.php`. Usa cookie HTTP-only, SameSite=Lax, modo estricto y token CSRF para formularios tradicionales.
- Autenticación y autorización: `includes/auth.php`, `includes/permissions.php` y guardas por portal. Los roles reales son `admin`, `docente` y `usuario`; `usuario` representa al estudiante y es el valor predeterminado de `usuarios.rol`.
- Login/registro tradicional: vistas `src/login/sign-in.php` y `src/login/sign-up.php`; procesos `src/auth/procesar_login.php` y `src/auth/procesar_registro.php`. Las contraseñas usan `password_hash`/`password_verify` y este flujo se conserva.
- Google OAuth: inicio único `src/auth/google.php`, callback real `src/auth/google-callback.php`, lógica compartida `includes/google_oauth.php` y configuración `includes/config/services.php`. Login y registro llaman el mismo sistema con una intención distinta.
- Dashboard administrador: `src/dashboard/index.php`, protegido por `src/dashboard/includes/cms.php` para rol `admin`.
- Portal docente: `src/docente/index.php` y módulos hermanos, protegidos por `src/docente/_guard.php` para `docente` o supervisión de `admin`.
- Portal estudiante: `src/estudiantes/index.php` y módulos hermanos, protegido para rol `usuario`. Un alta Google sin datos obligatorios se dirige a `src/estudiantes/perfil.php?completar=1`.
- Portada pública: `index.php` carga `includes/config.php`, `includes/conexion.php`, `includes/contenido.php`, `includes/header.php`, `includes/navbar.php` e `includes/footer.php`. El contenido dinámico proviene de `secciones`, `elementos_seccion`, `configuracion_sitio` y `menu_sitio`.

## Inventario funcional de tablas reutilizadas

La base contiene 49 tablas InnoDB. La base y todas las tablas funcionales auditadas usan `utf8mb4`; `configuracion_portal_estudiante` conserva `utf8mb4_general_ci` y las demás usan mayoritariamente `utf8mb4_unicode_ci`.

- Usuarios y seguridad: `usuarios`, `password_reset_tokens`, `assisted_password_resets`, `verificaciones_cuenta`, `historial_cambios_cuenta`, `user_deletions`, `account_cleanup_notifications`, `audit_logs`, `admin_notices` y `errores_sistema`.
- Cursos, contenidos e inscripciones: `asignaturas`, `docentes_asignaturas`, `estudiantes_docentes`, `contenidos`, `evaluaciones`, `ev_entregadas`, `notas` y `notas_historial`.
- Comercio y pagos: `categorias_producto`, `productos`, `producto_imagenes`, `promociones`, `carritos`, `carrito_items`, `direcciones_usuario`, `pedidos`, `pedido_detalles`, `pedido_historial`, `pagos`, `stripe_eventos` e `inventario_movimientos`.
- Mensajes y correo: `comunicacion_hilos`, `comunicacion_mensajes` y `correo_envios`.
- CMS y configuración: `secciones`, `elementos_seccion`, `configuracion_sitio`, `menu_sitio`, `configuracion_portal_estudiante` y `dte_configuracion`.
- DTE y territorio: `dte_correlativos`, `dte_documentos`, `dte_eventos`, `departamentos`, `municipios` y `distritos`.
- Respaldos de contenido ya existentes en la base: `respaldo_index_configuracion_20260717`, `respaldo_index_elementos_20260717` y `respaldo_index_secciones_20260717`. No se modificaron.

Para OAuth se reutiliza `usuarios`: correo único, `google_id` único, `password` nullable, `proveedor` (`local`, `google`, `mixto`), `email_verificado` y rol predeterminado `usuario`. Un correo Google existente se actualiza dentro de una transacción y queda como proveedor `mixto` si conserva contraseña; no se duplica el usuario.

## Módulos que editan contenido

- `src/dashboard/secciones/{index,editar,accion}.php`: altas, cambios, publicación y eliminación de `secciones`; al eliminar, la FK elimina sus `elementos_seccion` en cascada.
- `src/dashboard/elementos/{index,editar,accion}.php`: CRUD de `elementos_seccion`.
- `src/dashboard/configuracion/index.php`: UPSERT transaccional de `configuracion_sitio` y gestión de logo/favicon.
- `src/dashboard/navbar/{index,editar,accion}.php`: CRUD de `menu_sitio`.
- `src/dashboard/portal-estudiante/index.php`: actualiza `configuracion_portal_estudiante` y archivos visuales del portal.
- `src/dashboard/configuracion/dte.php`: versiona la fila activa de `dte_configuracion`; los secretos DTE permanecen fuera de la tabla.
- `src/dashboard/includes/cms.php`: guarda imágenes y evita borrar archivos todavía referenciados por tablas de contenido/productos.

## Tablas nuevas necesarias

Ninguna en esta etapa. Las migraciones históricas `004_google_auth.sql` y `013_etapa1_google_usuarios_bitacora.sql` ya están reflejadas en la base viva. Crear otra tabla de identidades sería duplicar la responsabilidad de `usuarios` y elevar el riesgo de compatibilidad.

## Problemas detectados y corrección aplicada

- La URI estaba repetida en `GOOGLE_REDIRECT_URI` aunque ya existía `APP_URL`. Esa duplicación permitía diferencias de host, protocolo, capitalización, subcarpeta o barra final. Ahora el callback se genera exclusivamente desde una base seleccionada por entorno y la ruta constante `src/auth/google-callback.php`.
- Local y producción no tenían claves base explícitamente separadas. Se añadieron `APP_URL_LOCAL` y `APP_URL_PRODUCTION`, manteniendo `APP_URL` como compatibilidad para instalaciones actuales.
- El botón de registro no declaraba su intención y era indistinguible del login. Ambos siguen usando el mismo endpoint, ahora con `accion=login` o `accion=registro`; el callback conserva la intención en la sesión.
- El flujo ya validaba `state`, pero ahora también enlaza la autorización mediante nonce OIDC y PKCE S256. Los valores son aleatorios, expiran en 10 minutos, se guardan solo en sesión y cada `state` se consume una vez.
- El dominio de producción no existe en archivos de configuración ni documentación del proyecto. OAuth queda bloqueado en `APP_ENV=production` si la base no es una URL HTTPS válida; no se inventó un dominio.
- La documentación previa `docs/google-oauth-setup.md` fue convertida en un enlace de compatibilidad. La guía normativa para esta etapa es `docs/google_oauth_configuracion.md`.

## Archivos modificados

- `.env` (ignorado por Git): se retiró la URI duplicada; conserva la base y los secretos locales.
- `.gitignore`: excluye el respaldo real de esta etapa.
- `.env.example`: configuración separada local/producción sin credenciales ni callback repetido.
- `config/google.example.php`: ejemplo heredado sin secretos y sin `redirect_uri` manual.
- `includes/config.php` e `includes/config/services.php`: selección única de URL base y generación de callback/origen.
- `includes/google_oauth.php`: validación central, PKCE y nonce.
- `src/auth/google.php`: inicio común seguro para login, registro y vinculación.
- `src/auth/google-callback.php`: consumo de estado, nonce y verificador PKCE; retorno según intención.
- `src/login/sign-in.php` y `src/login/sign-up.php`: ambos botones apuntan al mismo sistema seguro.
- `docs/correcciones_6_etapa_1.md` y `docs/google_oauth_configuracion.md`.
- `docs/google-oauth-setup.md`: enlace de compatibilidad hacia la guía vigente.
- `artifacts/db_atenea_pre_correcciones6_etapa1_20260716.sql`: respaldo previo solicitado.

## Riesgos de compatibilidad

- Google compara la URI carácter por carácter. El cambio de código no sustituye el alta manual de la URI exacta en Google Cloud.
- En producción debe existir `APP_URL_PRODUCTION` o el legado `APP_URL`, con HTTPS, dominio y subcarpeta reales. Una URL incorrecta deshabilita OAuth de forma segura.
- `APP_URL` sigue aceptado para no romper despliegues existentes, pero se recomienda migrar a las claves por entorno.
- Las cuentas inactivas o eliminadas lógicamente no se reactivan por Google; el enlace se revierte y el acceso falla.
- El perfil Google no entrega DUI, fecha de nacimiento, teléfono ni ubicación. La cuenta se crea sin datos falsos y el guard del portal obliga a completarlos.
- La prueba completa contra Google depende de la configuración externa del cliente OAuth y no puede automatizarse sin completar Google Cloud.

## Pruebas realizadas

- Respaldo restaurable a nivel lógico generado con `mysqldump`; tamaño y SHA-256 verificados.
- Sintaxis PHP (`php -l`) correcta en todos los archivos PHP modificados.
- Extensiones requeridas presentes: cURL, PDO MySQL, mysqli, OpenSSL y mbstring.
- Portada, login y registro tradicionales responden HTTP 200 en Apache local.
- POST tradicional sin token válido es rechazado con redirección HTTP 302 y no cambia el conteo ni las contraseñas de `usuarios`.
- Login y registro Google generan el mismo callback exacto; cada solicitud incluye `state` y nonce de 64 caracteres, PKCE S256 con reto de 43 caracteres y no incluye `client_secret`.
- Callback con estado inválido es rechazado y redirige al login.
- En `APP_ENV=production` con la base local HTTP, OAuth queda deshabilitado por exigir HTTPS.
- Una solicitud real al cliente actual de Google devuelve todavía `redirect_uri_mismatch`; debe registrarse en Google Cloud `http://localhost/Atenea/src/auth/google-callback.php` exactamente como se indica en la guía.
