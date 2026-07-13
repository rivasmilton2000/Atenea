USE db_atenea;

ALTER TABLE usuarios
  ADD COLUMN IF NOT EXISTS session_version INT UNSIGNED NOT NULL DEFAULT 1 AFTER ultimo_acceso;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  email_hash CHAR(64) NOT NULL,
  token_hash CHAR(64) NULL UNIQUE,
  request_ip_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_reset_usuario_estado (user_id, used_at, expires_at),
  INDEX idx_reset_email_fecha (email_hash, created_at),
  INDEX idx_reset_ip_fecha (request_ip_hash, created_at),
  CONSTRAINT fk_password_reset_usuario FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
