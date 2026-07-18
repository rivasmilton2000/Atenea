USE db_atenea;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE asignaturas
  ADD COLUMN IF NOT EXISTS elemento_seccion_id INT UNSIGNED NULL AFTER id,
  ADD COLUMN IF NOT EXISTS slug VARCHAR(190) NULL AFTER nombre,
  ADD COLUMN IF NOT EXISTS descripcion_corta VARCHAR(500) NULL AFTER slug,
  ADD COLUMN IF NOT EXISTS descripcion_completa MEDIUMTEXT NULL AFTER descripcion,
  ADD COLUMN IF NOT EXISTS imagen VARCHAR(255) NULL AFTER descripcion_completa,
  ADD COLUMN IF NOT EXISTS tipo ENUM('curso','capacitacion','certificacion') NOT NULL DEFAULT 'capacitacion' AFTER imagen,
  ADD COLUMN IF NOT EXISTS nivel VARCHAR(80) NULL AFTER tipo,
  ADD COLUMN IF NOT EXISTS precio DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER nivel,
  ADD COLUMN IF NOT EXISTS duracion VARCHAR(120) NULL AFTER precio,
  ADD COLUMN IF NOT EXISTS fecha_inicio DATE NULL AFTER duracion,
  ADD COLUMN IF NOT EXISTS fecha_finalizacion DATE NULL AFTER fecha_inicio,
  ADD COLUMN IF NOT EXISTS estado_capacitacion ENUM('borrador','publicada','cerrada','archivada') NOT NULL DEFAULT 'borrador' AFTER fecha_finalizacion,
  ADD COLUMN IF NOT EXISTS cupo_seccion TINYINT UNSIGNED NOT NULL DEFAULT 30 AFTER estado_capacitacion,
  ADD COLUMN IF NOT EXISTS requisitos TEXT NULL AFTER cupo_seccion,
  ADD COLUMN IF NOT EXISTS objetivos TEXT NULL AFTER requisitos,
  ADD COLUMN IF NOT EXISTS modalidad ENUM('presencial','virtual','hibrida') NOT NULL DEFAULT 'presencial' AFTER objetivos,
  ADD COLUMN IF NOT EXISTS certificado_disponible TINYINT(1) NOT NULL DEFAULT 0 AFTER modalidad,
  ADD COLUMN IF NOT EXISTS orden INT NOT NULL DEFAULT 0 AFTER certificado_disponible,
  ADD COLUMN IF NOT EXISTS activo TINYINT(1) NOT NULL DEFAULT 1 AFTER orden,
  ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL AFTER activo,
  ADD COLUMN IF NOT EXISTS eliminado_por INT UNSIGNED NULL AFTER deleted_at;

ALTER TABLE asignaturas
  MODIFY COLUMN elemento_seccion_id INT UNSIGNED NULL,
  ADD UNIQUE KEY IF NOT EXISTS uq_asignatura_slug (slug),
  ADD UNIQUE KEY IF NOT EXISTS uq_asignatura_elemento (elemento_seccion_id),
  ADD KEY IF NOT EXISTS idx_asignatura_publica (estado_capacitacion,activo,deleted_at,orden),
  ADD KEY IF NOT EXISTS fk_asignatura_elemento (elemento_seccion_id),
  ADD KEY IF NOT EXISTS fk_asignatura_eliminado_por (eliminado_por);

ALTER TABLE asignaturas
  ADD CONSTRAINT fk_asignatura_elemento FOREIGN KEY IF NOT EXISTS (elemento_seccion_id) REFERENCES elementos_seccion(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_asignatura_eliminado_por FOREIGN KEY IF NOT EXISTS (eliminado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
  ADD CONSTRAINT IF NOT EXISTS chk_asignatura_cupo CHECK (cupo_seccion BETWEEN 1 AND 30),
  ADD CONSTRAINT IF NOT EXISTS chk_asignatura_precio CHECK (precio >= 0),
  ADD CONSTRAINT IF NOT EXISTS chk_asignatura_fechas CHECK (fecha_finalizacion IS NULL OR fecha_inicio IS NULL OR fecha_finalizacion >= fecha_inicio);

CREATE TABLE IF NOT EXISTS capacitacion_pagos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  asignatura_id INT UNSIGNED NOT NULL,
  checkout_key CHAR(64) NOT NULL,
  stripe_checkout_session_id VARCHAR(255) NULL,
  stripe_payment_intent_id VARCHAR(255) NULL,
  importe DECIMAL(10,2) NOT NULL,
  moneda CHAR(3) NOT NULL DEFAULT 'usd',
  estado ENUM('pendiente','pagado','fallido','expirado','reembolsado') NOT NULL DEFAULT 'pendiente',
  last_stripe_event_id VARCHAR(255) NULL,
  paid_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cap_pago_checkout_key (checkout_key),
  UNIQUE KEY uq_cap_pago_session (stripe_checkout_session_id),
  UNIQUE KEY uq_cap_pago_intent (stripe_payment_intent_id),
  KEY idx_cap_pago_usuario (usuario_id,created_at),
  KEY idx_cap_pago_asignatura (asignatura_id,estado),
  KEY idx_cap_pago_evento (last_stripe_event_id),
  CONSTRAINT fk_cap_pago_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  CONSTRAINT fk_cap_pago_asignatura FOREIGN KEY (asignatura_id) REFERENCES asignaturas(id),
  CONSTRAINT chk_cap_pago_importe CHECK (importe >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS capacitacion_secciones (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  asignatura_id INT UNSIGNED NOT NULL,
  docente_id INT UNSIGNED NOT NULL,
  codigo VARCHAR(80) NOT NULL,
  nombre VARCHAR(180) NOT NULL,
  fecha_inicio DATE NULL,
  fecha_finalizacion DATE NULL,
  capacidad_maxima TINYINT UNSIGNED NOT NULL DEFAULT 30,
  cantidad_actual TINYINT UNSIGNED NOT NULL DEFAULT 0,
  estado ENUM('abierta','cerrada','finalizada','inactiva') NOT NULL DEFAULT 'abierta',
  horario VARCHAR(255) NULL,
  creada_por INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cap_seccion_codigo (codigo),
  KEY idx_cap_seccion_disponible (asignatura_id,estado,cantidad_actual,capacidad_maxima),
  KEY idx_cap_seccion_docente (docente_id,estado),
  KEY fk_cap_seccion_creador (creada_por),
  CONSTRAINT fk_cap_seccion_asignatura FOREIGN KEY (asignatura_id) REFERENCES asignaturas(id),
  CONSTRAINT fk_cap_seccion_docente FOREIGN KEY (docente_id) REFERENCES usuarios(id),
  CONSTRAINT fk_cap_seccion_creador FOREIGN KEY (creada_por) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT chk_cap_seccion_cupo CHECK (capacidad_maxima BETWEEN 1 AND 30 AND cantidad_actual <= capacidad_maxima),
  CONSTRAINT chk_cap_seccion_fechas CHECK (fecha_finalizacion IS NULL OR fecha_inicio IS NULL OR fecha_finalizacion >= fecha_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inscripciones_capacitacion (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  asignatura_id INT UNSIGNED NOT NULL,
  pago_id BIGINT UNSIGNED NOT NULL,
  seccion_id BIGINT UNSIGNED NULL,
  docente_id INT UNSIGNED NULL,
  estado ENUM('pendiente_asignacion','inscrito','retirado','finalizado') NOT NULL DEFAULT 'pendiente_asignacion',
  asignado_por INT UNSIGNED NULL,
  assigned_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_inscripcion_pago (pago_id),
  UNIQUE KEY uq_inscripcion_usuario_capacitacion (usuario_id,asignatura_id),
  KEY idx_inscripcion_seccion (seccion_id,estado),
  KEY idx_inscripcion_docente (docente_id,estado),
  KEY idx_inscripcion_busqueda (asignatura_id,estado,usuario_id),
  KEY fk_inscripcion_asignador (asignado_por),
  CONSTRAINT fk_inscripcion_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  CONSTRAINT fk_inscripcion_asignatura FOREIGN KEY (asignatura_id) REFERENCES asignaturas(id),
  CONSTRAINT fk_inscripcion_pago FOREIGN KEY (pago_id) REFERENCES capacitacion_pagos(id),
  CONSTRAINT fk_inscripcion_seccion FOREIGN KEY (seccion_id) REFERENCES capacitacion_secciones(id),
  CONSTRAINT fk_inscripcion_docente FOREIGN KEY (docente_id) REFERENCES usuarios(id),
  CONSTRAINT fk_inscripcion_asignador FOREIGN KEY (asignado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inscripcion_movimientos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  inscripcion_id BIGINT UNSIGNED NOT NULL,
  seccion_origen_id BIGINT UNSIGNED NULL,
  seccion_destino_id BIGINT UNSIGNED NULL,
  docente_origen_id INT UNSIGNED NULL,
  docente_destino_id INT UNSIGNED NULL,
  motivo VARCHAR(500) NOT NULL,
  realizado_por INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_movimiento_inscripcion (inscripcion_id,created_at),
  KEY fk_movimiento_origen (seccion_origen_id),
  KEY fk_movimiento_destino (seccion_destino_id),
  KEY fk_movimiento_docente_origen (docente_origen_id),
  KEY fk_movimiento_docente_destino (docente_destino_id),
  KEY fk_movimiento_admin (realizado_por),
  CONSTRAINT fk_movimiento_inscripcion FOREIGN KEY (inscripcion_id) REFERENCES inscripciones_capacitacion(id),
  CONSTRAINT fk_movimiento_origen FOREIGN KEY (seccion_origen_id) REFERENCES capacitacion_secciones(id) ON DELETE SET NULL,
  CONSTRAINT fk_movimiento_destino FOREIGN KEY (seccion_destino_id) REFERENCES capacitacion_secciones(id) ON DELETE SET NULL,
  CONSTRAINT fk_movimiento_docente_origen FOREIGN KEY (docente_origen_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT fk_movimiento_docente_destino FOREIGN KEY (docente_destino_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT fk_movimiento_admin FOREIGN KEY (realizado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

START TRANSACTION;

INSERT IGNORE INTO asignaturas
  (elemento_seccion_id,codigo,nombre,slug,descripcion,descripcion_corta,descripcion_completa,imagen,tipo,nivel,precio,duracion,estado,estado_capacitacion,cupo_seccion,modalidad,certificado_disponible,orden,activo)
SELECT e.id,CONCAT('CAP-',e.id),e.titulo,
       CASE e.id WHEN 43 THEN 'introduccion-naturopatia' WHEN 44 THEN 'terapias-naturales-avanzadas' WHEN 45 THEN 'especializacion-naturopatia-holistica' ELSE CONCAT('capacitacion-',e.id) END,
       e.descripcion,e.descripcion,e.descripcion,e.imagen,
       CASE UPPER(COALESCE(e.tipo,'')) WHEN 'CURSO' THEN 'curso' WHEN 'CERTIFICACIÓN' THEN 'certificacion' WHEN 'CERTIFICACION' THEN 'certificacion' ELSE 'capacitacion' END,
       e.nivel,COALESCE(e.precio,0),e.duracion,'activo','publicada',30,'presencial',CASE WHEN UPPER(COALESCE(e.tipo,'')) LIKE 'CERTIF%' THEN 1 ELSE 0 END,e.orden,e.activo
FROM elementos_seccion e
INNER JOIN secciones s ON s.id=e.seccion_id AND s.clave='capacitaciones';

UPDATE elementos_seccion e
INNER JOIN asignaturas a ON a.elemento_seccion_id=e.id
SET e.enlace=CONCAT('src/website/capacitacion.php?slug=',a.slug),e.texto_boton='Ver detalles y pagar';

COMMIT;

DROP TRIGGER IF EXISTS trg_docente_capacitacion_max_insert;
DROP TRIGGER IF EXISTS trg_docente_capacitacion_max_update;
DROP TRIGGER IF EXISTS trg_capacitacion_publicacion_carga;

DELIMITER $$
CREATE TRIGGER trg_docente_capacitacion_max_insert
BEFORE INSERT ON docentes_asignaturas
FOR EACH ROW
BEGIN
  DECLARE carga INT DEFAULT 0;
  IF NEW.estado='activo' THEN
    SELECT COUNT(DISTINCT da.asignatura_id) INTO carga
    FROM docentes_asignaturas da
    INNER JOIN asignaturas a ON a.id=da.asignatura_id
    WHERE da.docente_id=NEW.docente_id AND da.estado='activo' AND a.activo=1
      AND a.estado_capacitacion IN('publicada','cerrada') AND a.deleted_at IS NULL;
    IF carga>=2 THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El docente ya tiene dos capacitaciones activas.';
    END IF;
  END IF;
END$$

CREATE TRIGGER trg_docente_capacitacion_max_update
BEFORE UPDATE ON docentes_asignaturas
FOR EACH ROW
BEGIN
  DECLARE carga INT DEFAULT 0;
  IF NEW.estado='activo' THEN
    SELECT COUNT(DISTINCT da.asignatura_id) INTO carga
    FROM docentes_asignaturas da
    INNER JOIN asignaturas a ON a.id=da.asignatura_id
    WHERE da.docente_id=NEW.docente_id AND da.estado='activo' AND a.activo=1
      AND a.estado_capacitacion IN('publicada','cerrada') AND a.deleted_at IS NULL
      AND da.id<>OLD.id;
    IF carga>=2 THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El docente ya tiene dos capacitaciones activas.';
    END IF;
  END IF;
END$$

CREATE TRIGGER trg_capacitacion_publicacion_carga
BEFORE UPDATE ON asignaturas
FOR EACH ROW
BEGIN
  DECLARE docentes_sobrecargados INT DEFAULT 0;
  IF NEW.activo=1 AND NEW.estado_capacitacion IN('publicada','cerrada') AND NEW.deleted_at IS NULL THEN
    SELECT COUNT(*) INTO docentes_sobrecargados
    FROM docentes_asignaturas objetivo
    WHERE objetivo.asignatura_id=NEW.id AND objetivo.estado='activo'
      AND (SELECT COUNT(DISTINCT da.asignatura_id)
           FROM docentes_asignaturas da
           INNER JOIN asignaturas a ON a.id=da.asignatura_id
           WHERE da.docente_id=objetivo.docente_id AND da.asignatura_id<>NEW.id
             AND da.estado='activo' AND a.activo=1
             AND a.estado_capacitacion IN('publicada','cerrada') AND a.deleted_at IS NULL)>=2;
    IF docentes_sobrecargados>0 THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La publicación superaría dos capacitaciones activas para un docente.';
    END IF;
  END IF;
END$$
DELIMITER ;
