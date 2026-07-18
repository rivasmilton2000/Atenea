# Correcciones 6 — Resumen final

## Estado general

Las seis etapas quedaron integradas en la aplicación monolítica PHP/mysqli-PDO existente, sin reemplazar el sistema de usuarios ni mover secretos al repositorio.

1. Google OAuth centralizado, callback único, `state`, nonce, PKCE, vinculación por correo y separación local/producción.
2. Index público responsive, contenido administrable, noticias y límite de cuatro áreas.
3. Capacitaciones, docentes autorizados, secciones, Stripe, webhook idempotente e inscripción automática.
4. Contenidos, archivos privados, entregas, notas, progreso y certificado oficial PDF verificable.
5. Agenda, chat, notificaciones, centro SMTP/IMAP y moderación.
6. Borradores, publicación, historial, restauración e iframe real dentro del dashboard.

## Publicación del website

Las tablas administrables representan el borrador vivo. El website público no las consulta directamente: lee la última instantánea `publicado` de `website_publicaciones`. El guardado desde los módulos CMS actualiza el snapshot `borrador` y crea una entrada en `website_versiones` durante el cierre de la petición.

Ruta administrativa: `src/dashboard/website/editor.php`.

- Panel izquierdo: módulos reales de secciones, elementos, configuración, navbar, noticias, capacitaciones y galería de productos.
- Panel derecho: `index.php` real con datos del borrador.
- Publicación explícita, descarte y restauración.
- Comparación resumida por tablas, administrador, fecha, sección, estado y comentario.
- Escritorio, tablet y móvil.
- Sondeo de un solo hash SQL cada dos segundos y recarga con debounce de 900 ms.
- Token aleatorio de 256 bits, almacenado como hash, duración de 30 minutos y vinculado al administrador y `session_id`.

## Tablas y migraciones de Correcciones 6

- `013_etapa1_google_usuarios_bitacora.sql`
- `017_index_publico_noticias.sql`
- `018_capacitaciones_pagos_inscripciones.sql`
- `019_flujo_academico_certificados.sql`
- `020_comunicacion_agenda_correo.sql`
- `021_website_borradores_versiones_preview.sql`

Todas usan InnoDB/`utf8mb4` en las estructuras nuevas. La migración 021 añade `website_publicaciones`, `website_versiones` y `website_preview_tokens`.

## Rutas principales

- Website: `/Atenea/index.php`
- Noticias: `/Atenea/src/website/noticias.php`
- Capacitaciones: `/Atenea/src/website/courses.php`
- Editor: `/Atenea/src/dashboard/website/editor.php`
- Portal docente: `/Atenea/src/docente/`
- Portal estudiante: `/Atenea/src/estudiantes/`
- Agenda/chat/correo: `/Atenea/src/comunicaciones/`
- Supervisión académica: `/Atenea/src/dashboard/academico/seguimiento.php`
- Estado SMTP/IMAP: `/Atenea/src/dashboard/comunicaciones/servicio.php`
- Verificación de certificado: `/Atenea/src/website/verificar-certificado.php`

## Permisos

- Administrador: publicación, restauración, CMS, supervisión académica, comunicaciones, moderación y usuarios.
- Docente: únicamente cursos/secciones propios, contenidos, entregas, notas, progreso y contactos permitidos.
- Estudiante: únicamente inscripciones, contenidos, entregas, certificados, chats y contactos relacionados.
- Las rutas sensibles vuelven a validar rol, propiedad y CSRF en backend.

## Archivos modificados o añadidos

### Configuración y núcleo

`.env.example`, `.gitignore`, `index.php`, `includes/config/services.php`, `includes/google_oauth.php`, `includes/contenido.php`, `includes/noticias.php`, `includes/capacitaciones.php`, `includes/comercio.php`, `includes/carrito.php`, `includes/academico_flujo.php`, `includes/comunicacion_centro.php`, `includes/comunicacion_layout.php`, `includes/website_versionado.php`, `includes/mailer.php`, `includes/portal_estudiante_layout.php`, `includes/admin_metricas.php`.

### Administración

`src/dashboard/includes/cms.php`, `src/dashboard/partials/_sidebar.php`, módulos `secciones/`, `elementos/`, `configuracion/`, `navbar/`, `noticias/`, `capacitaciones/`, `academico/`, `comunicaciones/` y `website/`.

### Docente y estudiante

Los archivos de contenidos, entregas, progreso, finalización, comunicaciones y sidebars en `src/docente/`; cursos, contenidos, acciones y certificados en `src/estudiantes/`.

### Website, pagos y tareas

`src/website/courses.php`, `capacitacion.php`, `noticias.php`, `noticia.php`, `product-details.php`, `verificar-certificado.php`; `src/pagos/crear-checkout-capacitacion.php`, `success-capacitacion.php`, `webhook.php`; `src/academico/archivo.php`; `src/comunicaciones/`; `src/cron/sincronizar-imap.php`.

### Base de datos, pruebas y documentación

Migraciones 013, 017–021; pruebas `tests/integration/correcciones6_etapa3.php` a `correcciones6_etapa6.php`; documentos `docs/correcciones_6_etapa_*.md`, `google_oauth_configuracion.md`, `configuracion_correo_smtp_imap.md`, `instalacion_actualizacion.md` y `pruebas_correcciones_6.md`.

## Limitaciones reales

- Google Cloud, Stripe, SMTP e IMAP requieren credenciales/servicios del propietario. No se realizó un login Google interactivo, un cobro real, un envío SMTP exitoso ni una descarga IMAP real durante el cierre.
- IMAP está pendiente en el entorno actual; se probó el fallo controlado, no una sincronización exitosa.
- Los snapshots son completos y seguros para el tamaño actual. Si las tablas de catálogo crecen de forma masiva conviene separar el versionado por entidad.
- El catálogo y la galería pública respetan la última publicación. El iframe principal muestra el index; para inspeccionar visualmente el detalle de una galería antes de publicar todavía se debe ampliar el selector de página de preview.
- La primera ejecución conjunta de la prueba concurrente produjo una aserción fallida; cuatro repeticiones aisladas posteriores, incluidas tres consecutivas, pasaron. Debe mantenerse monitoreo de deadlocks/latencia en producción.
