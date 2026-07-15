USE db_atenea;

DROP TABLE IF EXISTS account_cleanup_notifications;
DROP TABLE IF EXISTS user_deletions;
DROP TABLE IF EXISTS assisted_password_resets;
DROP TABLE IF EXISTS admin_notices;
DROP TABLE IF EXISTS audit_logs;

ALTER TABLE usuarios
  DROP FOREIGN KEY fk_usuario_deleted_by,
  DROP INDEX idx_usuarios_ciclo_vida,
  DROP INDEX idx_usuarios_inactividad,
  DROP COLUMN under_investigation,
  DROP COLUMN retention_hold,
  DROP COLUMN anonymized_at,
  DROP COLUMN deletion_scheduled_at,
  DROP COLUMN deletion_reason,
  DROP COLUMN deleted_by,
  DROP COLUMN deleted_at,
  DROP COLUMN last_activity_at;
