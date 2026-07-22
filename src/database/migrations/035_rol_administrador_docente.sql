USE db_atenea;

-- Se conserva el valor anterior durante la transición para no invalidar datos importados.
ALTER TABLE usuarios
  MODIFY COLUMN rol ENUM('admin','usuario','docente','administracion_docente','administrador_docente') NOT NULL DEFAULT 'usuario';

-- Normaliza las cuentas híbridas existentes al nombre solicitado e invalida sesiones antiguas.
UPDATE usuarios
SET rol='administrador_docente', session_version=session_version+1
WHERE rol='administracion_docente';

