USE db_atenea;

CREATE TABLE personalizaciones_visuales (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  area ENUM('website','dashboard','estudiantes','docente') NOT NULL,
  configuracion_json LONGTEXT NOT NULL,
  version INT UNSIGNED NOT NULL DEFAULT 1,
  actualizado_por INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_personalizacion_area (area),
  CONSTRAINT fk_personalizacion_actualizador FOREIGN KEY (actualizado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE personalizaciones_visuales_historial (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  personalizacion_id INT UNSIGNED NULL,
  area ENUM('website','dashboard','estudiantes','docente') NOT NULL,
  accion ENUM('publicar','restaurar_original') NOT NULL,
  configuracion_json LONGTEXT NOT NULL,
  version INT UNSIGNED NOT NULL,
  realizado_por INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_personalizacion_historial_area (area,created_at),
  INDEX idx_personalizacion_historial_admin (realizado_por,created_at),
  CONSTRAINT fk_personalizacion_historial_config FOREIGN KEY (personalizacion_id) REFERENCES personalizaciones_visuales(id) ON DELETE SET NULL,
  CONSTRAINT fk_personalizacion_historial_admin FOREIGN KEY (realizado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
