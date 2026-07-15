USE db_atenea;

-- Etapa 4. Estructura academica canonica, aditiva y sin datos ficticios.
CREATE TABLE IF NOT EXISTS asignaturas (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 codigo VARCHAR(40) NOT NULL,
 nombre VARCHAR(180) NOT NULL,
 descripcion TEXT NULL,
 estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
 creado_por INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_asignatura_codigo(codigo),
 INDEX idx_asignatura_estado_nombre(estado,nombre),
 CONSTRAINT fk_asignatura_admin FOREIGN KEY(creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS docentes_asignaturas (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 docente_id INT UNSIGNED NOT NULL,
 asignatura_id INT UNSIGNED NOT NULL,
 estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
 asignado_por INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_docente_asignatura(docente_id,asignatura_id),
 INDEX idx_docente_asignacion(docente_id,estado,asignatura_id),
 INDEX idx_asignatura_docente(asignatura_id,estado,docente_id),
 CONSTRAINT fk_da_docente FOREIGN KEY(docente_id) REFERENCES usuarios(id),
 CONSTRAINT fk_da_asignatura FOREIGN KEY(asignatura_id) REFERENCES asignaturas(id),
 CONSTRAINT fk_da_admin FOREIGN KEY(asignado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS estudiantes_docentes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 estudiante_id INT UNSIGNED NOT NULL,
 docente_id INT UNSIGNED NOT NULL,
 asignatura_id INT UNSIGNED NOT NULL,
 estado ENUM('activo','retirado','finalizado') NOT NULL DEFAULT 'activo',
 matriculado_por INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_matricula_academica(estudiante_id,docente_id,asignatura_id),
 INDEX idx_matricula_docente_curso(docente_id,asignatura_id,estado),
 INDEX idx_matricula_estudiante(estudiante_id,estado,asignatura_id),
 CONSTRAINT fk_ed_estudiante FOREIGN KEY(estudiante_id) REFERENCES usuarios(id),
 CONSTRAINT fk_ed_docente FOREIGN KEY(docente_id) REFERENCES usuarios(id),
 CONSTRAINT fk_ed_asignatura FOREIGN KEY(asignatura_id) REFERENCES asignaturas(id),
 CONSTRAINT fk_ed_admin FOREIGN KEY(matriculado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contenidos (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 asignatura_id INT UNSIGNED NOT NULL,
 docente_id INT UNSIGNED NOT NULL,
 titulo VARCHAR(190) NOT NULL,
 descripcion TEXT NULL,
 archivo_relpath VARCHAR(255) NULL,
 archivo_nombre VARCHAR(190) NULL,
 archivo_mime VARCHAR(100) NULL,
 archivo_tamano INT UNSIGNED NULL,
 estado ENUM('borrador','activo','inactivo') NOT NULL DEFAULT 'borrador',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_contenido_docente_curso(docente_id,asignatura_id,estado,created_at),
 CONSTRAINT fk_contenido_asignatura FOREIGN KEY(asignatura_id) REFERENCES asignaturas(id),
 CONSTRAINT fk_contenido_docente FOREIGN KEY(docente_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS evaluaciones (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 asignatura_id INT UNSIGNED NOT NULL,
 docente_id INT UNSIGNED NOT NULL,
 titulo VARCHAR(190) NOT NULL,
 descripcion TEXT NULL,
 fecha_apertura DATETIME NULL,
 fecha_cierre DATETIME NULL,
 nota_maxima DECIMAL(6,2) NOT NULL DEFAULT 10.00,
 estado ENUM('borrador','publicada','cerrada','inactiva') NOT NULL DEFAULT 'borrador',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_evaluacion_docente_curso(docente_id,asignatura_id,estado,fecha_cierre),
 CONSTRAINT fk_evaluacion_asignatura FOREIGN KEY(asignatura_id) REFERENCES asignaturas(id),
 CONSTRAINT fk_evaluacion_docente FOREIGN KEY(docente_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ev_entregadas (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 evaluacion_id BIGINT UNSIGNED NOT NULL,
 estudiante_id INT UNSIGNED NOT NULL,
 archivo_relpath VARCHAR(255) NULL,
 archivo_nombre VARCHAR(190) NULL,
 archivo_mime VARCHAR(100) NULL,
 archivo_tamano INT UNSIGNED NULL,
 comentario TEXT NULL,
 estado ENUM('pendiente','revisada','tardia') NOT NULL DEFAULT 'pendiente',
 entregado_at DATETIME NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_entrega_evaluacion_estudiante(evaluacion_id,estudiante_id),
 INDEX idx_entrega_estado_fecha(estado,entregado_at),
 INDEX idx_entrega_estudiante(estudiante_id,evaluacion_id),
 CONSTRAINT fk_entrega_evaluacion FOREIGN KEY(evaluacion_id) REFERENCES evaluaciones(id),
 CONSTRAINT fk_entrega_estudiante FOREIGN KEY(estudiante_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notas (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 entrega_id BIGINT UNSIGNED NOT NULL,
 evaluacion_id BIGINT UNSIGNED NOT NULL,
 estudiante_id INT UNSIGNED NOT NULL,
 docente_id INT UNSIGNED NOT NULL,
 nota DECIMAL(6,2) NOT NULL,
 observacion VARCHAR(1000) NULL,
 calificado_por INT UNSIGNED NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_nota_entrega(entrega_id),
 INDEX idx_nota_evaluacion_estudiante(evaluacion_id,estudiante_id),
 INDEX idx_nota_docente_fecha(docente_id,updated_at),
 CONSTRAINT fk_nota_entrega FOREIGN KEY(entrega_id) REFERENCES ev_entregadas(id),
 CONSTRAINT fk_nota_evaluacion FOREIGN KEY(evaluacion_id) REFERENCES evaluaciones(id),
 CONSTRAINT fk_nota_estudiante FOREIGN KEY(estudiante_id) REFERENCES usuarios(id),
 CONSTRAINT fk_nota_docente FOREIGN KEY(docente_id) REFERENCES usuarios(id),
 CONSTRAINT fk_nota_calificador FOREIGN KEY(calificado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notas_historial (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nota_id BIGINT UNSIGNED NOT NULL,
 nota_anterior DECIMAL(6,2) NULL,
 nota_nueva DECIMAL(6,2) NOT NULL,
 observacion_anterior VARCHAR(1000) NULL,
 observacion_nueva VARCHAR(1000) NULL,
 cambiado_por INT UNSIGNED NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_historial_nota_fecha(nota_id,created_at),
 CONSTRAINT fk_nh_nota FOREIGN KEY(nota_id) REFERENCES notas(id),
 CONSTRAINT fk_nh_usuario FOREIGN KEY(cambiado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE comunicacion_hilos
 ADD COLUMN asignatura_id INT UNSIGNED NULL AFTER pedido_id,
 ADD COLUMN docente_id INT UNSIGNED NULL AFTER asignatura_id,
 ADD INDEX idx_hilo_academico(asignatura_id,docente_id,ultimo_mensaje_at),
 ADD CONSTRAINT fk_hilo_asignatura FOREIGN KEY(asignatura_id) REFERENCES asignaturas(id) ON DELETE SET NULL,
 ADD CONSTRAINT fk_hilo_docente FOREIGN KEY(docente_id) REFERENCES usuarios(id) ON DELETE SET NULL;
