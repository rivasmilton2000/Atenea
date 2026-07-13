USE db_atenea;

CREATE TABLE IF NOT EXISTS verificaciones_cuenta (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  tipo ENUM('cambio_password','cambio_correo','vincular_google','desvincular_google') NOT NULL,
  codigo_hash CHAR(64) NOT NULL,
  datos_pendientes LONGTEXT NOT NULL,
  intentos TINYINT UNSIGNED NOT NULL DEFAULT 0,
  max_intentos TINYINT UNSIGNED NOT NULL DEFAULT 5,
  expira_at DATETIME NOT NULL,
  usado_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_verificacion_usuario_tipo (usuario_id,tipo,usado_at,expira_at),
  CONSTRAINT fk_verificacion_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS historial_cambios_cuenta (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  accion VARCHAR(60) NOT NULL,
  campos_modificados VARCHAR(500) NOT NULL,
  ip_hash CHAR(64) NOT NULL,
  sesion_hash CHAR(64) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_historial_usuario_fecha (usuario_id,created_at),
  CONSTRAINT fk_cuenta_historial_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
