-- Etapa 4: contexto auditable para Administración_Docente.
-- Idempotente para MariaDB 10.4+.

ALTER TABLE audit_logs
  ADD COLUMN IF NOT EXISTS actor_role VARCHAR(40) NULL AFTER actor_user_id,
  ADD COLUMN IF NOT EXISTS active_mode VARCHAR(20) NULL AFTER actor_role,
  ADD COLUMN IF NOT EXISTS route VARCHAR(255) NULL AFTER user_agent;

ALTER TABLE errores_sistema
  ADD COLUMN IF NOT EXISTS modo_activo VARCHAR(20) NULL AFTER usuario_id,
  ADD COLUMN IF NOT EXISTS ruta VARCHAR(255) NULL AFTER modo_activo,
  ADD COLUMN IF NOT EXISTS accion_intentada VARCHAR(100) NULL AFTER ruta,
  ADD COLUMN IF NOT EXISTS correlacion_id CHAR(32) NULL AFTER accion_intentada;

