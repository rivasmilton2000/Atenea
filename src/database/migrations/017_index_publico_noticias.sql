USE db_atenea;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Los metadatos de una capacitación dejan de estar comprimidos en subtítulo/descripción.
ALTER TABLE elementos_seccion
  ADD COLUMN IF NOT EXISTS tipo VARCHAR(80) NULL AFTER subtitulo,
  ADD COLUMN IF NOT EXISTS nivel VARCHAR(80) NULL AFTER tipo,
  ADD COLUMN IF NOT EXISTS precio DECIMAL(10,2) NULL AFTER nivel,
  ADD COLUMN IF NOT EXISTS duracion VARCHAR(120) NULL AFTER precio,
  ADD COLUMN IF NOT EXISTS instructor VARCHAR(180) NULL AFTER duracion;

-- Módulo editorial independiente. La eliminación es lógica para preservar historial.
CREATE TABLE IF NOT EXISTS noticias (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  titulo VARCHAR(190) NOT NULL,
  slug VARCHAR(190) NOT NULL,
  resumen VARCHAR(500) NOT NULL,
  contenido MEDIUMTEXT NOT NULL,
  imagen_portada VARCHAR(255) NULL,
  fecha_publicacion DATETIME NULL,
  autor VARCHAR(180) NULL,
  estado ENUM('borrador','publicado') NOT NULL DEFAULT 'borrador',
  destacado TINYINT(1) NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  deleted_at DATETIME NULL,
  eliminado_por INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_noticias_slug (slug),
  KEY idx_noticias_publicacion (estado, activo, deleted_at, fecha_publicacion),
  KEY idx_noticias_destacadas (destacado, estado, activo),
  KEY fk_noticias_eliminado_por (eliminado_por),
  CONSTRAINT fk_noticias_eliminado_por FOREIGN KEY (eliminado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

START TRANSACTION;

UPDATE elementos_seccion e
INNER JOIN secciones s ON s.id=e.seccion_id AND s.clave='capacitaciones'
SET e.tipo=COALESCE(NULLIF(e.tipo,''),TRIM(SUBSTRING_INDEX(e.subtitulo,(CONVERT(0xC2B7 USING utf8mb4) COLLATE utf8mb4_unicode_ci),1))),
    e.nivel=COALESCE(NULLIF(e.nivel,''),TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(e.subtitulo,(CONVERT(0xC2B7 USING utf8mb4) COLLATE utf8mb4_unicode_ci),2),(CONVERT(0xC2B7 USING utf8mb4) COLLATE utf8mb4_unicode_ci),-1))),
    e.precio=COALESCE(e.precio,CAST(REPLACE(REPLACE(TRIM(SUBSTRING_INDEX(e.subtitulo,(CONVERT(0xC2B7 USING utf8mb4) COLLATE utf8mb4_unicode_ci),-1)),'$',''),',','') AS DECIMAL(10,2))),
    e.instructor=COALESCE(NULLIF(e.instructor,''),TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(e.descripcion,'Instructor:',-1),'. Dur',1))),
    e.duracion=COALESCE(NULLIF(e.duracion,''),TRIM(TRAILING '.' FROM TRIM(SUBSTRING_INDEX(e.descripcion,':',-1)))),
    e.descripcion=TRIM(TRAILING ' ' FROM TRIM(TRAILING '.' FROM TRIM(SUBSTRING_INDEX(e.descripcion,'Instructor:',1))))
WHERE LOCATE((CONVERT(0xC2B7 USING utf8mb4) COLLATE utf8mb4_unicode_ci),e.subtitulo)>0;

-- Corrige referencias heredadas a archivos que realmente permanecen en img/.
UPDATE elementos_seccion
SET imagen='img/programa_6976e84aafd1d_1769400394.jpg'
WHERE imagen='uploads/contenido/migrado/programa_6976e84aafd1d_1769400394.jpg';

UPDATE elementos_seccion
SET imagen='img/programa_6976e886e2b54_1769400454.jpg'
WHERE imagen='uploads/contenido/migrado/programa_6976e886e2b54_1769400454.jpg';

-- Se conserva Digitopuntura como registro histórico, pero ya no se publica en el index.
UPDATE elementos_seccion e
INNER JOIN secciones s ON s.id=e.seccion_id AND s.clave='areas'
SET e.activo=0
WHERE LOWER(TRIM(e.titulo))='digitopuntura';

-- Migración sin pérdida desde los elementos genéricos actuales.
INSERT IGNORE INTO noticias
  (titulo,slug,resumen,contenido,imagen_portada,fecha_publicacion,autor,estado,destacado,activo,created_at,updated_at)
SELECT e.titulo,
       CASE e.id
         WHEN 51 THEN 'escuela-atenea'
         WHEN 52 THEN 'conoterapia'
         WHEN 53 THEN 'naturopatia'
         ELSE CONCAT('noticia-',e.id)
       END,
       LEFT(e.descripcion,500),
       e.descripcion,
       NULLIF(e.imagen,''),
       CASE e.id
         WHEN 51 THEN '2026-01-21 08:00:00'
         WHEN 52 THEN '2024-05-15 08:00:00'
         WHEN 53 THEN '2026-01-22 08:00:00'
         ELSE e.created_at
       END,
       NULL,
       'publicado',0,e.activo,e.created_at,e.updated_at
FROM elementos_seccion e
INNER JOIN secciones s ON s.id=e.seccion_id AND s.clave='noticias';

UPDATE noticias
SET imagen_portada='img/noticia_6972a39b01db9_1769120667.jpg'
WHERE imagen_portada='uploads/contenido/migrado/noticia_6972a39b01db9_1769120667.jpg';

UPDATE noticias
SET imagen_portada='img/noticia_6972a279886dc_1769120377.jpg'
WHERE imagen_portada='uploads/contenido/migrado/noticia_6972a279886dc_1769120377.jpg';

-- Los registros fuente permanecen en la base, pero la publicación usa noticias.
UPDATE elementos_seccion e
INNER JOIN secciones s ON s.id=e.seccion_id AND s.clave='noticias'
SET e.activo=0;

UPDATE menu_sitio
SET texto='Noticias',url='src/website/noticias.php'
WHERE LOWER(TRIM(texto)) IN ('noticias','noticas')
   OR LOWER(url) LIKE '%noticas%'
   OR url='index.php#noticias';

UPDATE secciones
SET boton_url='src/website/noticias.php'
WHERE clave='noticias';

COMMIT;
