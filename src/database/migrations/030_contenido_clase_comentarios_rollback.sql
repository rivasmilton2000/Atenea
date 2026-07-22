USE db_atenea;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DROP TABLE IF EXISTS contenido_comentarios;

ALTER TABLE contenidos
  DROP FOREIGN KEY IF EXISTS fk_contenido_eliminado_por,
  DROP INDEX IF EXISTS fk_contenido_eliminado_por,
  DROP INDEX IF EXISTS idx_contenido_feed,
  DROP COLUMN IF EXISTS eliminado_por,
  DROP COLUMN IF EXISTS eliminado_at,
  DROP COLUMN IF EXISTS publicado_at;

