USE db_atenea;

-- Ampliacion compatible: conserva roles, cuentas y relaciones existentes.
ALTER TABLE usuarios
  ADD COLUMN IF NOT EXISTS nombre_usuario VARCHAR(80) NULL AFTER apellido,
  ADD COLUMN IF NOT EXISTS es_superadmin TINYINT(1) NOT NULL DEFAULT 0 AFTER rol;

UPDATE usuarios
SET nombre_usuario = CONCAT('usuario', id)
WHERE nombre_usuario IS NULL OR TRIM(nombre_usuario) = '';

CREATE UNIQUE INDEX IF NOT EXISTS uq_usuarios_nombre_usuario ON usuarios (nombre_usuario);

-- Si la instalacion aun no distingue SuperAdmin, protege al admin activo mas antiguo.
UPDATE usuarios
SET es_superadmin = 1
WHERE id = (
  SELECT id FROM (
    SELECT MIN(id) AS id
    FROM usuarios
    WHERE rol = 'admin' AND estado = 'activo' AND deleted_at IS NULL
  ) AS administrador_principal
)
AND NOT EXISTS (
  SELECT 1 FROM (
    SELECT id FROM usuarios WHERE rol = 'admin' AND es_superadmin = 1 LIMIT 1
  ) AS superadmin_existente
);

CREATE INDEX IF NOT EXISTS idx_usuarios_busqueda_nombre ON usuarios (nombre, apellido);
