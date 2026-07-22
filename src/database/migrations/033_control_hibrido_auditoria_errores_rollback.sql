-- Rollback no destructivo de la etapa 4.
-- Conserva las entradas de bitácora y errores; solamente retira los campos añadidos.

ALTER TABLE errores_sistema
  DROP COLUMN IF EXISTS correlacion_id,
  DROP COLUMN IF EXISTS accion_intentada,
  DROP COLUMN IF EXISTS ruta,
  DROP COLUMN IF EXISTS modo_activo;

ALTER TABLE audit_logs
  DROP COLUMN IF EXISTS route,
  DROP COLUMN IF EXISTS active_mode,
  DROP COLUMN IF EXISTS actor_role;

