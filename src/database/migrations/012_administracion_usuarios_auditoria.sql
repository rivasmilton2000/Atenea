USE db_atenea;

ALTER TABLE usuarios
  ADD COLUMN last_activity_at DATETIME NULL AFTER ultimo_acceso,
  ADD COLUMN deleted_at DATETIME NULL AFTER last_activity_at,
  ADD COLUMN deleted_by INT UNSIGNED NULL AFTER deleted_at,
  ADD COLUMN deletion_reason VARCHAR(500) NULL AFTER deleted_by,
  ADD COLUMN deletion_scheduled_at DATETIME NULL AFTER deletion_reason,
  ADD COLUMN anonymized_at DATETIME NULL AFTER deletion_scheduled_at,
  ADD COLUMN retention_hold TINYINT(1) NOT NULL DEFAULT 0 AFTER anonymized_at,
  ADD COLUMN under_investigation TINYINT(1) NOT NULL DEFAULT 0 AFTER retention_hold,
  ADD INDEX idx_usuarios_ciclo_vida (estado,deleted_at,deletion_scheduled_at),
  ADD INDEX idx_usuarios_inactividad (rol,last_activity_at,ultimo_acceso),
  ADD CONSTRAINT fk_usuario_deleted_by FOREIGN KEY (deleted_by) REFERENCES usuarios(id) ON DELETE SET NULL;

UPDATE usuarios
SET last_activity_at = COALESCE(ultimo_acceso, updated_at, created_at)
WHERE last_activity_at IS NULL;

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_user_id INT UNSIGNED NULL,
  target_user_id INT UNSIGNED NULL,
  event_type VARCHAR(100) NOT NULL,
  module VARCHAR(80) NOT NULL,
  entity_type VARCHAR(80) NULL,
  entity_id VARCHAR(100) NULL,
  action VARCHAR(100) NOT NULL,
  result VARCHAR(30) NOT NULL,
  description VARCHAR(500) NOT NULL,
  metadata JSON NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  request_id CHAR(32) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_fecha (created_at),
  INDEX idx_audit_actor_fecha (actor_user_id,created_at),
  INDEX idx_audit_target_fecha (target_user_id,created_at),
  INDEX idx_audit_filtros (event_type,module,result,created_at),
  CONSTRAINT fk_audit_actor FOREIGN KEY (actor_user_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT fk_audit_target FOREIGN KEY (target_user_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_notices (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  created_by INT UNSIGNED NOT NULL,
  type VARCHAR(50) NOT NULL,
  title VARCHAR(180) NOT NULL,
  message VARCHAR(2000) NOT NULL,
  target_section VARCHAR(100) NULL,
  priority VARCHAR(20) NOT NULL DEFAULT 'normal',
  status VARCHAR(20) NOT NULL DEFAULT 'pendiente',
  due_at DATETIME NULL,
  read_at DATETIME NULL,
  resolved_at DATETIME NULL,
  cancelled_at DATETIME NULL,
  email_sent_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_notice_user_status (user_id,status,created_at),
  INDEX idx_notice_creator (created_by,created_at),
  CONSTRAINT fk_notice_user FOREIGN KEY (user_id) REFERENCES usuarios(id),
  CONSTRAINT fk_notice_creator FOREIGN KEY (created_by) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE assisted_password_resets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  initiated_by INT UNSIGNED NOT NULL,
  verified_by INT UNSIGNED NULL,
  email_hash CHAR(64) NOT NULL,
  code_hash CHAR(64) NOT NULL,
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  max_attempts TINYINT UNSIGNED NOT NULL DEFAULT 5,
  expires_at DATETIME NOT NULL,
  locked_until DATETIME NULL,
  verified_at DATETIME NULL,
  recovery_token_hash CHAR(64) NULL,
  token_expires_at DATETIME NULL,
  token_used_at DATETIME NULL,
  cancelled_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_assisted_token (recovery_token_hash),
  INDEX idx_assisted_user_state (user_id,cancelled_at,expires_at),
  INDEX idx_assisted_admin (initiated_by,created_at),
  CONSTRAINT fk_assisted_user FOREIGN KEY (user_id) REFERENCES usuarios(id),
  CONSTRAINT fk_assisted_initiator FOREIGN KEY (initiated_by) REFERENCES usuarios(id),
  CONSTRAINT fk_assisted_verifier FOREIGN KEY (verified_by) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_deletions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  requested_by INT UNSIGNED NULL,
  restored_by INT UNSIGNED NULL,
  reason VARCHAR(500) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'periodo_gracia',
  effective_at DATETIME NOT NULL,
  restored_at DATETIME NULL,
  anonymized_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_deletion_state (status,effective_at),
  INDEX idx_deletion_user (user_id,created_at),
  CONSTRAINT fk_deletion_user FOREIGN KEY (user_id) REFERENCES usuarios(id),
  CONSTRAINT fk_deletion_requester FOREIGN KEY (requested_by) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT fk_deletion_restorer FOREIGN KEY (restored_by) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE account_cleanup_notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  notice_days SMALLINT UNSIGNED NOT NULL,
  cycle_key DATE NOT NULL,
  inactivity_reference_at DATETIME NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pendiente',
  email_sent_at DATETIME NULL,
  error_sanitized VARCHAR(500) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cleanup_notice (user_id,notice_days,cycle_key),
  INDEX idx_cleanup_state (status,created_at),
  CONSTRAINT fk_cleanup_user FOREIGN KEY (user_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
