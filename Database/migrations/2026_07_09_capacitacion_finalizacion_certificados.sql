-- Atenea
-- Fase: finalizacion del curso, aprobacion y certificado dinamico

ALTER TABLE course_enrollments
    ADD COLUMN IF NOT EXISTS fecha_finalizacion DATETIME NULL AFTER fecha_inscripcion,
    ADD COLUMN IF NOT EXISTS fecha_aprobacion DATETIME NULL AFTER fecha_finalizacion,
    ADD COLUMN IF NOT EXISTS certificado_disponible TINYINT(1) NOT NULL DEFAULT 0 AFTER fecha_aprobacion,
    ADD COLUMN IF NOT EXISTS certificado_generado_at DATETIME NULL AFTER certificado_disponible,
    ADD COLUMN IF NOT EXISTS certificate_regenerated_count INT(11) NOT NULL DEFAULT 0 AFTER certificado_generado_at,
    ADD COLUMN IF NOT EXISTS finalizado_por_user_id INT(11) NULL AFTER certificate_regenerated_count,
    ADD COLUMN IF NOT EXISTS aprobado_por_user_id INT(11) NULL AFTER finalizado_por_user_id;

CREATE TABLE IF NOT EXISTS course_video_progress (
    id INT(11) NOT NULL AUTO_INCREMENT,
    enrollment_id INT(11) NOT NULL,
    course_video_id INT(11) NOT NULL,
    completed TINYINT(1) NOT NULL DEFAULT 1,
    completed_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    updated_by_user_id INT(11) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_course_video_progress (enrollment_id, course_video_id),
    KEY idx_course_video_progress_completed (completed),
    KEY idx_course_video_progress_video (course_video_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
