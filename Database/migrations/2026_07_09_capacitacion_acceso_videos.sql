-- Atenea
-- Fase: inscripcion, curso activo, videos y record escolar para capacitacion

CREATE TABLE IF NOT EXISTS course_enrollments (
    id INT(11) NOT NULL AUTO_INCREMENT,
    public_user_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    programa_id INT(11) NOT NULL,
    estado_curso VARCHAR(30) NOT NULL DEFAULT 'curso_activo',
    estado_aprobacion VARCHAR(30) NOT NULL DEFAULT 'en_proceso',
    progreso DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    fecha_inscripcion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_course_enrollment_public_program (public_user_id, programa_id),
    KEY idx_course_enrollment_user (user_id),
    KEY idx_course_enrollment_program (programa_id),
    KEY idx_course_enrollment_status (estado_curso, estado_aprobacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS course_videos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    programa_id INT(11) NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    source_type VARCHAR(20) NOT NULL DEFAULT 'url',
    video_url VARCHAR(255) NULL,
    video_file_path VARCHAR(255) NULL,
    youtube_id VARCHAR(60) NULL,
    mass_enabled TINYINT(1) NOT NULL DEFAULT 0,
    orden INT(11) NOT NULL DEFAULT 1,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_course_videos_program (programa_id),
    KEY idx_course_videos_status (estado, mass_enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS course_video_access (
    id INT(11) NOT NULL AUTO_INCREMENT,
    course_video_id INT(11) NOT NULL,
    enrollment_id INT(11) NOT NULL,
    enabled TINYINT(1) NOT NULL DEFAULT 1,
    updated_by_user_id INT(11) NULL,
    enabled_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_course_video_access (course_video_id, enrollment_id),
    KEY idx_course_video_access_enrollment (enrollment_id),
    KEY idx_course_video_access_enabled (enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
