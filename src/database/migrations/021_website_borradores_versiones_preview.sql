USE db_atenea;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS website_publicaciones (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 estado ENUM('borrador','publicado','archivado') NOT NULL,
 contenido_json LONGTEXT NOT NULL,
 comentario VARCHAR(500) NULL,
 creado_por INT UNSIGNED NOT NULL,
 publicado_por INT UNSIGNED NULL,
 publicado_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 KEY idx_website_publicacion_estado(estado,updated_at),
 CONSTRAINT fk_website_publicacion_creador FOREIGN KEY(creado_por) REFERENCES usuarios(id),
 CONSTRAINT fk_website_publicacion_publicador FOREIGN KEY(publicado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
 CONSTRAINT chk_website_publicacion_json CHECK(JSON_VALID(contenido_json))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS website_versiones (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 administrador_id INT UNSIGNED NOT NULL,
 seccion_modificada VARCHAR(120) NOT NULL,
 datos_anteriores LONGTEXT NOT NULL,
 datos_nuevos LONGTEXT NOT NULL,
 estado ENUM('borrador','publicado','archivado','restaurado','descartado') NOT NULL,
 comentario VARCHAR(500) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 KEY idx_website_version_fecha(created_at),
 KEY idx_website_version_seccion(seccion_modificada,created_at),
 KEY idx_website_version_admin(administrador_id,created_at),
 CONSTRAINT fk_website_version_admin FOREIGN KEY(administrador_id) REFERENCES usuarios(id),
 CONSTRAINT chk_website_version_anterior CHECK(JSON_VALID(datos_anteriores)),
 CONSTRAINT chk_website_version_nuevo CHECK(JSON_VALID(datos_nuevos))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS website_preview_tokens (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 token_hash CHAR(64) NOT NULL,
 administrador_id INT UNSIGNED NOT NULL,
 session_hash CHAR(64) NOT NULL,
 expira_at DATETIME NOT NULL,
 ultimo_uso_at DATETIME NULL,
 revocado_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_website_preview_token(token_hash),
 KEY idx_website_preview_expira(expira_at,revocado_at),
 CONSTRAINT fk_website_preview_admin FOREIGN KEY(administrador_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
