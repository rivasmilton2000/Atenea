# Correcciones 6 — Etapa 2 de 6

Fecha de ejecución: 17 de julio de 2026.

## Respaldo y migración

- Respaldo previo: `artifacts/db_atenea_pre_correcciones6_etapa2_20260717.sql`.
- Tamaño: 123402 bytes.
- SHA-256: `825580F58035EF7CA126E488E2C45FFE9AFD27B62F22B86ED6BE7C27F4EE217B`.
- El respaldo está excluido de Git porque contiene información real.
- Migración aplicada: `src/database/migrations/017_index_publico_noticias.sql`.
- La migración usa `utf8mb4`, es reutilizable, conserva los registros fuente y no elimina información.

## Arquitectura y datos reutilizados

El index conserva la arquitectura monolítica PHP/mysqli y se sigue componiendo desde `index.php`, `includes/contenido.php`, `includes/navbar.php` y las plantillas compartidas. Se reutilizan `secciones`, `elementos_seccion` y `menu_sitio` para propuesta, áreas, capacitaciones, títulos, subtítulos, botones, orden y estado.

Se añadieron a `elementos_seccion` los campos específicos de capacitación `tipo`, `nivel`, `precio`, `duracion` e `instructor`. La información antes comprimida en subtítulo y descripción se migró a esos campos.

Se creó `noticias` porque una noticia necesita ciclo editorial, contenido completo, slug y eliminación lógica que no corresponden al elemento genérico. Incluye índices para publicación, destacado y slug único; además incluye una clave foránea hacia `usuarios` para registrar quién elimina.

## Cambios realizados

### Propuesta institucional

Antes existía una caja dorada independiente y tarjetas angostas. Ahora el encabezado está integrado, las seis tarjetas son automáticas y se distribuyen en 3 columnas en escritorio, 2 en tablet y 1 en móvil. Título, subtítulo, iconos, textos, estado y orden continúan administrándose mediante `secciones` y `elementos_seccion`.

### Capacitación destacada

Las tarjetas tienen imagen con proporción fija y `object-fit: cover`, etiquetas de tipo y nivel, precio, resumen, duración, instructor y acción. La descripción pública es breve y los metadatos ya tienen campos independientes. Se corrigieron las rutas de dos imágenes reales que existían en `img/`; el tercer curso usa el fallback del proyecto porque su archivo original no existe.

### Áreas

El index aplica un máximo defensivo de cuatro elementos activos y usa cuatro columnas en escritorio. Digitopuntura permanece en la base como registro histórico inactivo. El administrador valida el límite dentro de una transacción y bloquea la activación de un quinto elemento con un mensaje explícito, sin impedir editar, ordenar, desactivar o eliminar.

### Noticias

Se corrigió el menú y se creó una página pública de noticias, detalle por slug validado y una respuesta 404 integrada. El index obtiene únicamente las tres noticias publicadas más recientes. El administrador permite crear, editar, previsualizar, publicar, despublicar y eliminar lógicamente; todos los cambios usan CSRF y auditoría.

La carga de portadas valida tamaño máximo de 5 MB, MIME real, extensión concordante, dimensiones de imagen y genera un nombre aleatorio. El nombre original no se utiliza como nombre de almacenamiento.

## Archivos modificados

- `.gitignore`
- `index.php`
- `includes/contenido.php`
- `includes/navbar.php`
- `includes/noticias.php`
- `src/website/assets/css/main.css`
- `src/website/noticias.php`
- `src/website/noticia.php`
- `src/dashboard/includes/cms.php`
- `src/dashboard/partials/_sidebar.php`
- `src/dashboard/elementos/index.php`
- `src/dashboard/elementos/editar.php`
- `src/dashboard/elementos/accion.php`
- `src/dashboard/noticias/index.php`
- `src/dashboard/noticias/editar.php`
- `src/dashboard/noticias/accion.php`
- `src/dashboard/noticias/preview.php`
- `src/database/migrations/017_index_publico_noticias.sql`
- `tests/visual/correcciones6_etapa2.spec.js`

## Evidencia visual

- `artifacts/correcciones6-etapa2-propuesta-desktop.png`
- `artifacts/correcciones6-etapa2-propuesta-tablet.png`
- `artifacts/correcciones6-etapa2-propuesta-mobile.png`
- `artifacts/correcciones6-etapa2-capacitaciones-desktop.png`
- `artifacts/correcciones6-etapa2-noticias-1366x768.png`

## Pruebas realizadas

- Migración ejecutada nuevamente sin duplicar noticias ni alterar los registros históricos.
- PHP público: index, listado y detalle respondieron HTTP 200 sin warnings ni errores PHP.
- Seguridad del detalle: un slug inexistente y un intento con texto SQL devolvieron HTTP 404.
- Index: 6 tarjetas institucionales, 4 áreas, 3 capacitaciones y 3 noticias recientes.
- Navbar: `Noticias` apunta a `/Atenea/src/website/noticias.php` y abre correctamente.
- Administrador: listado, alta, edición, previsualización, publicación, despublicación y eliminación lógica probados con un registro temporal posteriormente eliminado.
- Seguridad administrativa: CSRF activo, una falsa imagen PHP renombrada como JPG fue rechazada y el intento de activar una quinta área fue bloqueado.
- Responsive automatizado con Playwright en 1366×768, 768×1024 y 375×812: 1 prueba aprobada.
- El registro temporal, sus auditorías y la sesión administrativa temporal fueron retirados después de la prueba; permanecen las 3 noticias reales migradas.

## Compatibilidad y riesgos

- No se cambiaron nombres de tablas, carpetas ni archivos existentes.
- Los elementos de noticias anteriores se conservan inactivos; la publicación se hace desde `noticias`.
- Los enlaces restantes de la navbar no fueron modificados.
- Los campos nuevos permiten `NULL`, por lo que contenido anterior sigue siendo compatible.
- El archivo original de imagen del tercer curso y la portada original de “Escuela Atenea” no existen en el proyecto; se utiliza el fallback visual existente hasta que el administrador cargue una portada real.

No hay configuración externa pendiente para esta etapa.
