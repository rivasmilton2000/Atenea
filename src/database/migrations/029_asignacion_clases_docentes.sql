USE db_atenea;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE asignaturas
  MODIFY COLUMN cupo_seccion TINYINT UNSIGNED NOT NULL DEFAULT 20,
  ADD COLUMN IF NOT EXISTS asignacion_automatica TINYINT(1) NOT NULL DEFAULT 1 AFTER cupo_seccion;

ALTER TABLE capacitacion_secciones
  DROP CONSTRAINT IF EXISTS chk_cap_seccion_cupo,
  MODIFY COLUMN capacidad_maxima TINYINT UNSIGNED NOT NULL DEFAULT 20,
  ADD CONSTRAINT chk_cap_seccion_cupo CHECK (capacidad_maxima BETWEEN 1 AND 30);

ALTER TABLE inscripciones_capacitacion
  ADD COLUMN IF NOT EXISTS asignacion_limite_at DATETIME NULL AFTER estado,
  ADD COLUMN IF NOT EXISTS ultimo_intento_asignacion_at DATETIME NULL AFTER asignacion_limite_at,
  ADD COLUMN IF NOT EXISTS metodo_asignacion ENUM('automatica','manual') NULL AFTER asignado_por,
  ADD KEY IF NOT EXISTS idx_inscripcion_asignacion_pendiente (estado,asignacion_limite_at,asignatura_id);

CREATE TABLE capacitacion_seccion_historial (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  seccion_id BIGINT UNSIGNED NOT NULL,
  accion ENUM('creada','editada','abierta','cerrada','docente_cambiado','asignacion_automatica','asignacion_manual','estudiante_movido') NOT NULL,
  datos_anteriores LONGTEXT NULL,
  datos_nuevos LONGTEXT NULL,
  motivo VARCHAR(500) NULL,
  realizado_por INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_seccion_historial_fecha (seccion_id,created_at),
  KEY idx_seccion_historial_admin (realizado_por,created_at),
  CONSTRAINT fk_seccion_historial_seccion FOREIGN KEY (seccion_id) REFERENCES capacitacion_secciones(id),
  CONSTRAINT fk_seccion_historial_admin FOREIGN KEY (realizado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
