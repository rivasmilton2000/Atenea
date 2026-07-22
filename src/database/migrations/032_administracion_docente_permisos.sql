-- Etapa 3: rol híbrido Administración_Docente y permisos individuales.
-- Idempotente para MariaDB 10.4+ / MySQL 8.

ALTER TABLE usuarios
  MODIFY COLUMN rol ENUM('admin','usuario','docente','administracion_docente') NOT NULL DEFAULT 'usuario';

CREATE TABLE IF NOT EXISTS usuario_permisos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  permiso VARCHAR(100) NOT NULL,
  habilitado TINYINT(1) NOT NULL DEFAULT 0,
  actualizado_por INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_usuario_permiso (usuario_id, permiso),
  INDEX idx_usuario_permisos_habilitados (usuario_id, habilitado, permiso),
  INDEX idx_usuario_permisos_actor (actualizado_por, updated_at),
  CONSTRAINT fk_usuario_permisos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_usuario_permisos_actor FOREIGN KEY (actualizado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuario_permisos_historial (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  permiso VARCHAR(100) NOT NULL,
  valor_anterior TINYINT(1) NOT NULL,
  valor_nuevo TINYINT(1) NOT NULL,
  cambiado_por INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_permiso_historial_usuario (usuario_id, created_at),
  INDEX idx_permiso_historial_actor (cambiado_por, created_at),
  CONSTRAINT fk_permiso_historial_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_permiso_historial_actor FOREIGN KEY (cambiado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
