-- Rollback de la etapa 3.
-- Antes de ejecutarlo, reasigna las cuentas híbridas a un rol existente.

UPDATE usuarios SET rol='docente', session_version=session_version+1
WHERE rol='administracion_docente';

DROP TABLE IF EXISTS usuario_permisos_historial;
DROP TABLE IF EXISTS usuario_permisos;

ALTER TABLE usuarios
  MODIFY COLUMN rol ENUM('admin','usuario','docente') NOT NULL DEFAULT 'usuario';
