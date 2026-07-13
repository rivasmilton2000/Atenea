USE db_atenea;

ALTER TABLE categorias_producto
  ADD COLUMN IF NOT EXISTS imagen VARCHAR(500) NULL AFTER descripcion,
  ADD COLUMN IF NOT EXISTS eliminado_at DATETIME NULL AFTER activo,
  ADD COLUMN IF NOT EXISTS creado_por INT UNSIGNED NULL AFTER eliminado_at,
  ADD COLUMN IF NOT EXISTS actualizado_por INT UNSIGNED NULL AFTER creado_por;

CREATE INDEX IF NOT EXISTS idx_categoria_estado_nombre
  ON categorias_producto(eliminado_at, activo, nombre);
CREATE INDEX IF NOT EXISTS idx_categoria_creado_por
  ON categorias_producto(creado_por);
CREATE INDEX IF NOT EXISTS idx_categoria_actualizado_por
  ON categorias_producto(actualizado_por);

DELIMITER $$
DROP PROCEDURE IF EXISTS atenea_agregar_fk_categoria_usuario$$
CREATE PROCEDURE atenea_agregar_fk_categoria_usuario()
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.REFERENTIAL_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = 'fk_categoria_creado'
  ) THEN
    ALTER TABLE categorias_producto
      ADD CONSTRAINT fk_categoria_creado FOREIGN KEY(creado_por) REFERENCES usuarios(id) ON DELETE SET NULL;
  END IF;
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.REFERENTIAL_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = 'fk_categoria_actualizado'
  ) THEN
    ALTER TABLE categorias_producto
      ADD CONSTRAINT fk_categoria_actualizado FOREIGN KEY(actualizado_por) REFERENCES usuarios(id) ON DELETE SET NULL;
  END IF;
END$$
CALL atenea_agregar_fk_categoria_usuario()$$
DROP PROCEDURE IF EXISTS atenea_agregar_fk_categoria_usuario$$
DELIMITER ;
