USE db_atenea;

CREATE TABLE respaldos_base_datos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  creado_por INT UNSIGNED NULL,
  restaurado_por INT UNSIGNED NULL,
  respaldo_previo_id BIGINT UNSIGNED NULL,
  tipo ENUM('manual','previo_restauracion') NOT NULL DEFAULT 'manual',
  nombre_archivo VARCHAR(180) NOT NULL,
  ruta_relativa VARCHAR(255) NOT NULL,
  tamano_bytes BIGINT UNSIGNED NULL,
  sha256 CHAR(64) NULL,
  estado ENUM('creando','disponible','restaurando','restaurado','fallido','eliminado') NOT NULL DEFAULT 'creando',
  tablas_incluidas SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  filas_incluidas BIGINT UNSIGNED NOT NULL DEFAULT 0,
  error_sanitizado VARCHAR(500) NULL,
  restaurado_at DATETIME NULL,
  eliminado_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_respaldo_ruta (ruta_relativa),
  INDEX idx_respaldo_estado_fecha (estado,created_at),
  INDEX idx_respaldo_creador (creado_por,created_at),
  CONSTRAINT fk_respaldo_creador FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT fk_respaldo_restaurador FOREIGN KEY (restaurado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT fk_respaldo_previo FOREIGN KEY (respaldo_previo_id) REFERENCES respaldos_base_datos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
