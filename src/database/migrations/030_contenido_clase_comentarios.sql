USE db_atenea;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE contenidos
  ADD COLUMN IF NOT EXISTS publicado_at DATETIME NULL COMMENT 'Fecha real en que el contenido pasó por primera vez a publicado' AFTER fecha_publicacion,
  ADD COLUMN IF NOT EXISTS eliminado_at DATETIME NULL COMMENT 'Marca de borrado lógico de la publicación' AFTER activo,
  ADD COLUMN IF NOT EXISTS eliminado_por INT UNSIGNED NULL COMMENT 'Usuario que eliminó lógicamente la publicación' AFTER eliminado_at,
  ADD KEY IF NOT EXISTS idx_contenido_feed (seccion_id,estado,activo,eliminado_at,fecha_publicacion,created_at),
  ADD KEY IF NOT EXISTS fk_contenido_eliminado_por (eliminado_por),
  ADD CONSTRAINT fk_contenido_eliminado_por FOREIGN KEY IF NOT EXISTS (eliminado_por) REFERENCES usuarios(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS contenido_comentarios (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Identificador del comentario o respuesta',
  contenido_id BIGINT UNSIGNED NOT NULL COMMENT 'Publicación de clase a la que pertenece',
  usuario_id INT UNSIGNED NOT NULL COMMENT 'Autor del mensaje',
  parent_id BIGINT UNSIGNED NULL COMMENT 'Comentario principal al que responde; solo se admite un nivel',
  cuerpo VARCHAR(2000) NOT NULL COMMENT 'Texto plano del comentario, sin HTML',
  estado ENUM('visible','oculto','eliminado') NOT NULL DEFAULT 'visible' COMMENT 'Estado de moderación o borrado lógico',
  editado_at DATETIME NULL COMMENT 'Última edición realizada por el autor',
  moderado_por INT UNSIGNED NULL COMMENT 'Docente o administrador que ocultó el mensaje',
  moderado_at DATETIME NULL COMMENT 'Fecha de moderación',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_comentario_contenido_fecha (contenido_id,estado,created_at,id),
  KEY idx_comentario_parent_fecha (parent_id,created_at,id),
  KEY idx_comentario_usuario_fecha (usuario_id,created_at),
  KEY fk_comentario_moderador (moderado_por),
  CONSTRAINT fk_comentario_contenido FOREIGN KEY (contenido_id) REFERENCES contenidos(id) ON DELETE CASCADE,
  CONSTRAINT fk_comentario_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  CONSTRAINT fk_comentario_parent FOREIGN KEY (parent_id) REFERENCES contenido_comentarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_comentario_moderador FOREIGN KEY (moderado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT chk_comentario_cuerpo CHECK (CHAR_LENGTH(TRIM(cuerpo)) BETWEEN 2 AND 2000)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

