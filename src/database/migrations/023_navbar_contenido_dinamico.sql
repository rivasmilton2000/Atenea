USE db_atenea;

ALTER TABLE menu_sitio
  ADD COLUMN padre_id INT UNSIGNED NULL AFTER id,
  ADD COLUMN slug VARCHAR(190) NULL AFTER texto,
  ADD COLUMN icono VARCHAR(100) NULL AFTER slug,
  ADD COLUMN visibilidad ENUM('publica','autenticada') NOT NULL DEFAULT 'publica' AFTER nueva_pestana,
  ADD COLUMN roles_json LONGTEXT NULL AFTER visibilidad,
  ADD COLUMN tipo_contenido ENUM('pagina_informativa','texto_enriquecido','imagenes','galeria','noticias','productos','capacitaciones','formulario','video','archivo_descargable','enlace_interno','enlace_externo','bloques_reutilizables') NOT NULL DEFAULT 'enlace_interno' AFTER roles_json,
  ADD COLUMN contenido_html MEDIUMTEXT NULL AFTER tipo_contenido,
  ADD COLUMN contenido_json LONGTEXT NULL AFTER contenido_html,
  ADD COLUMN eliminado_at DATETIME NULL AFTER activo,
  ADD COLUMN eliminado_por INT UNSIGNED NULL AFTER eliminado_at,
  ADD INDEX idx_menu_padre_orden (padre_id, orden),
  ADD INDEX idx_menu_papelera (eliminado_at),
  ADD UNIQUE KEY uq_menu_slug (slug),
  ADD CONSTRAINT fk_menu_padre FOREIGN KEY (padre_id) REFERENCES menu_sitio(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_menu_eliminado_por FOREIGN KEY (eliminado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
  ADD CONSTRAINT chk_menu_roles_json CHECK (roles_json IS NULL OR JSON_VALID(roles_json)),
  ADD CONSTRAINT chk_menu_contenido_json CHECK (contenido_json IS NULL OR JSON_VALID(contenido_json));

UPDATE menu_sitio
SET tipo_contenido = CASE WHEN url REGEXP '^https?://' THEN 'enlace_externo' ELSE 'enlace_interno' END,
    slug = CONCAT('menu-', id)
WHERE slug IS NULL;

CREATE TABLE menu_formulario_envios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  menu_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NULL,
  datos_json LONGTEXT NOT NULL,
  estado ENUM('nuevo','revisado','cerrado') NOT NULL DEFAULT 'nuevo',
  ip_hash CHAR(64) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_menu_formulario_estado (menu_id, estado, created_at),
  CONSTRAINT fk_menu_formulario_menu FOREIGN KEY (menu_id) REFERENCES menu_sitio(id) ON DELETE CASCADE,
  CONSTRAINT fk_menu_formulario_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT chk_menu_formulario_json CHECK (JSON_VALID(datos_json))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
