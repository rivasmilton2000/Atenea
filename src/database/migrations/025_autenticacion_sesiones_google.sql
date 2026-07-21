USE db_atenea;

ALTER TABLE usuarios
  ADD COLUMN perfil_estado ENUM('completo','pendiente') NOT NULL DEFAULT 'completo' AFTER email_verificado,
  ADD COLUMN terminos_aceptados_at DATETIME NULL AFTER perfil_estado,
  ADD COLUMN google_registro_iniciado_at DATETIME NULL AFTER terminos_aceptados_at;

CREATE TABLE auth_remember_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  selector CHAR(24) NOT NULL,
  token_hash CHAR(64) NOT NULL,
  session_version INT UNSIGNED NOT NULL,
  user_agent_hash CHAR(64) NOT NULL,
  ip_hash CHAR(64) NULL,
  expires_at DATETIME NOT NULL,
  last_used_at DATETIME NULL,
  revoked_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_auth_remember_selector (selector),
  INDEX idx_auth_remember_usuario (usuario_id,revoked_at,expires_at),
  CONSTRAINT fk_auth_remember_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

