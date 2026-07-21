USE db_atenea;

ALTER TABLE verificaciones_cuenta
  MODIFY COLUMN tipo ENUM('cambio_password','cambio_correo','vincular_google','desvincular_google','eliminar_cuenta') NOT NULL;

ALTER TABLE user_deletions
  ADD COLUMN email_hash CHAR(64) NULL AFTER reason,
  ADD COLUMN request_ip_hash CHAR(64) NULL AFTER email_hash,
  ADD INDEX idx_deletion_email_hash (email_hash,status,effective_at);

