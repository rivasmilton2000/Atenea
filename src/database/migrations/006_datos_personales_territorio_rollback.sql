-- Reversión segura de la migración 006.
-- Se detiene si algún usuario ya contiene datos personales o ubicación.
USE db_atenea;

DELIMITER //
CREATE PROCEDURE validar_reversion_datos_personales()
BEGIN
  IF EXISTS (
    SELECT 1 FROM usuarios
    WHERE fecha_nacimiento IS NOT NULL OR dui IS NOT NULL OR codigo_telefono IS NOT NULL
       OR telefono IS NOT NULL OR departamento_id IS NOT NULL OR municipio_id IS NOT NULL
       OR distrito_id IS NOT NULL OR direccion IS NOT NULL
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Reversión cancelada: existen datos personales guardados';
  END IF;
END//
DELIMITER ;

CALL validar_reversion_datos_personales();
DROP PROCEDURE validar_reversion_datos_personales;

ALTER TABLE usuarios
  DROP FOREIGN KEY fk_usuarios_distrito,
  DROP FOREIGN KEY fk_usuarios_municipio,
  DROP FOREIGN KEY fk_usuarios_departamento,
  DROP INDEX uq_usuarios_dui,
  DROP COLUMN direccion,
  DROP COLUMN distrito_id,
  DROP COLUMN municipio_id,
  DROP COLUMN departamento_id,
  DROP COLUMN telefono,
  DROP COLUMN codigo_telefono,
  DROP COLUMN dui,
  DROP COLUMN fecha_nacimiento;

DROP TABLE distritos;
DROP TABLE municipios;
DROP TABLE departamentos;
