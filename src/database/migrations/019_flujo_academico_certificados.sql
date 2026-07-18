USE db_atenea;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE contenidos
  ADD COLUMN IF NOT EXISTS seccion_id BIGINT UNSIGNED NULL AFTER asignatura_id,
  ADD COLUMN IF NOT EXISTS modulo VARCHAR(120) NOT NULL DEFAULT 'Unidad 1' AFTER docente_id,
  ADD COLUMN IF NOT EXISTS tipo ENUM('video','texto','documento','enlace','actividad','evaluacion','recurso_descargable') NOT NULL DEFAULT 'texto' AFTER modulo,
  ADD COLUMN IF NOT EXISTS orden INT NOT NULL DEFAULT 0 AFTER descripcion,
  ADD COLUMN IF NOT EXISTS video_url VARCHAR(500) NULL AFTER orden,
  ADD COLUMN IF NOT EXISTS fecha_publicacion DATETIME NULL AFTER archivo_tamano,
  ADD COLUMN IF NOT EXISTS fecha_limite DATETIME NULL AFTER fecha_publicacion,
  ADD COLUMN IF NOT EXISTS activo TINYINT(1) NOT NULL DEFAULT 1 AFTER estado,
  ADD COLUMN IF NOT EXISTS obligatorio TINYINT(1) NOT NULL DEFAULT 1 AFTER activo,
  ADD COLUMN IF NOT EXISTS peso_progreso DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER obligatorio;

ALTER TABLE contenidos
  ADD KEY IF NOT EXISTS idx_contenido_seccion_publicacion (seccion_id,activo,fecha_publicacion,modulo,orden),
  ADD KEY IF NOT EXISTS fk_contenido_seccion (seccion_id),
  ADD CONSTRAINT fk_contenido_seccion FOREIGN KEY IF NOT EXISTS (seccion_id) REFERENCES capacitacion_secciones(id),
  ADD CONSTRAINT IF NOT EXISTS chk_contenido_peso CHECK (peso_progreso BETWEEN 0 AND 100),
  ADD CONSTRAINT IF NOT EXISTS chk_contenido_fechas CHECK (fecha_limite IS NULL OR fecha_publicacion IS NULL OR fecha_limite >= fecha_publicacion);

CREATE TABLE IF NOT EXISTS entregas_contenido (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  contenido_id BIGINT UNSIGNED NOT NULL,
  estudiante_id INT UNSIGNED NOT NULL,
  asignatura_id INT UNSIGNED NOT NULL,
  seccion_id BIGINT UNSIGNED NOT NULL,
  intento SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  comentario TEXT NULL,
  resultado TEXT NULL,
  estado ENUM('pendiente','enviada','en_revision','aprobada','rechazada','requiere_correccion') NOT NULL DEFAULT 'enviada',
  nota DECIMAL(4,2) NULL,
  retroalimentacion TEXT NULL,
  enviado_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  revisado_at DATETIME NULL,
  revisado_por INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_entrega_intento (contenido_id,estudiante_id,intento),
  KEY idx_entrega_revision (seccion_id,estado,enviado_at),
  KEY idx_entrega_estudiante (estudiante_id,asignatura_id,estado),
  KEY fk_entrega_contenido_asignatura (asignatura_id),
  KEY fk_entrega_contenido_revisor (revisado_por),
  CONSTRAINT fk_entrega_contenido FOREIGN KEY (contenido_id) REFERENCES contenidos(id),
  CONSTRAINT fk_entrega_contenido_estudiante FOREIGN KEY (estudiante_id) REFERENCES usuarios(id),
  CONSTRAINT fk_entrega_contenido_asignatura FOREIGN KEY (asignatura_id) REFERENCES asignaturas(id),
  CONSTRAINT fk_entrega_contenido_seccion FOREIGN KEY (seccion_id) REFERENCES capacitacion_secciones(id),
  CONSTRAINT fk_entrega_contenido_revisor FOREIGN KEY (revisado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT chk_entrega_intento CHECK (intento >= 1),
  CONSTRAINT chk_entrega_nota CHECK (nota IS NULL OR (nota BETWEEN 0.00 AND 10.00))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS entrega_evidencias (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  entrega_id BIGINT UNSIGNED NOT NULL,
  archivo_relpath VARCHAR(255) NOT NULL,
  archivo_nombre VARCHAR(190) NOT NULL,
  archivo_mime VARCHAR(100) NOT NULL,
  archivo_tamano INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_evidencia_entrega (entrega_id),
  CONSTRAINT fk_evidencia_entrega FOREIGN KEY (entrega_id) REFERENCES entregas_contenido(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS entrega_revisiones (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  entrega_id BIGINT UNSIGNED NOT NULL,
  docente_id INT UNSIGNED NOT NULL,
  estado_anterior VARCHAR(30) NOT NULL,
  estado_nuevo ENUM('en_revision','aprobada','rechazada','requiere_correccion') NOT NULL,
  nota_anterior DECIMAL(4,2) NULL,
  nota_nueva DECIMAL(4,2) NULL,
  retroalimentacion TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_revision_entrega_fecha (entrega_id,created_at),
  KEY idx_revision_docente (docente_id,created_at),
  CONSTRAINT fk_revision_entrega FOREIGN KEY (entrega_id) REFERENCES entregas_contenido(id),
  CONSTRAINT fk_revision_docente FOREIGN KEY (docente_id) REFERENCES usuarios(id),
  CONSTRAINT chk_revision_nota_anterior CHECK (nota_anterior IS NULL OR nota_anterior BETWEEN 0.00 AND 10.00),
  CONSTRAINT chk_revision_nota_nueva CHECK (nota_nueva IS NULL OR nota_nueva BETWEEN 0.00 AND 10.00)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS progreso_contenido (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  inscripcion_id BIGINT UNSIGNED NOT NULL,
  contenido_id BIGINT UNSIGNED NOT NULL,
  visto_at DATETIME NULL,
  completado_at DATETIME NULL,
  ultima_actividad_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_progreso_inscripcion_contenido (inscripcion_id,contenido_id),
  KEY idx_progreso_actividad (inscripcion_id,ultima_actividad_at),
  KEY fk_progreso_contenido (contenido_id),
  CONSTRAINT fk_progreso_inscripcion FOREIGN KEY (inscripcion_id) REFERENCES inscripciones_capacitacion(id) ON DELETE CASCADE,
  CONSTRAINT fk_progreso_contenido FOREIGN KEY (contenido_id) REFERENCES contenidos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE inscripciones_capacitacion
  ADD COLUMN IF NOT EXISTS finalizacion_confirmada_por INT UNSIGNED NULL AFTER assigned_at,
  ADD COLUMN IF NOT EXISTS finalizacion_confirmada_at DATETIME NULL AFTER finalizacion_confirmada_por,
  ADD KEY IF NOT EXISTS fk_inscripcion_confirmador (finalizacion_confirmada_por),
  ADD CONSTRAINT fk_inscripcion_confirmador FOREIGN KEY IF NOT EXISTS (finalizacion_confirmada_por) REFERENCES usuarios(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS certificados_capacitacion (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  inscripcion_id BIGINT UNSIGNED NOT NULL,
  estudiante_id INT UNSIGNED NOT NULL,
  asignatura_id INT UNSIGNED NOT NULL,
  numero VARCHAR(80) NOT NULL,
  token_verificacion CHAR(64) NOT NULL,
  plantilla_relpath VARCHAR(255) NOT NULL,
  pdf_relpath VARCHAR(255) NOT NULL,
  emitido_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  finalizado_at DATE NOT NULL,
  emitido_por INT UNSIGNED NULL,
  estado ENUM('emitido','revocado') NOT NULL DEFAULT 'emitido',
  motivo_revocacion VARCHAR(500) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_certificado_inscripcion (inscripcion_id),
  UNIQUE KEY uq_certificado_numero (numero),
  UNIQUE KEY uq_certificado_token (token_verificacion),
  KEY idx_certificado_estudiante (estudiante_id,emitido_at),
  KEY idx_certificado_asignatura (asignatura_id,estado),
  KEY fk_certificado_emisor (emitido_por),
  CONSTRAINT fk_certificado_inscripcion FOREIGN KEY (inscripcion_id) REFERENCES inscripciones_capacitacion(id),
  CONSTRAINT fk_certificado_estudiante FOREIGN KEY (estudiante_id) REFERENCES usuarios(id),
  CONSTRAINT fk_certificado_asignatura FOREIGN KEY (asignatura_id) REFERENCES asignaturas(id),
  CONSTRAINT fk_certificado_emisor FOREIGN KEY (emitido_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
