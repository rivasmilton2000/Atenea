-- Atenea: pago manual antes de crear/activar una inscripcion.
CREATE TABLE IF NOT EXISTS course_payment_requests (
    id INT(11) NOT NULL AUTO_INCREMENT,
    public_user_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    programa_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    order_id INT(11) NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    payment_method VARCHAR(30) NOT NULL DEFAULT 'stripe',
    reference VARCHAR(120) NULL,
    notes TEXT NULL,
    reviewed_by_user_id INT(11) NULL,
    reviewed_at DATETIME NULL,
    paid_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_course_payment_user_program (public_user_id, programa_id),
    KEY idx_course_payment_status (status),
    KEY idx_course_payment_program (programa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE course_payment_requests
    ADD COLUMN IF NOT EXISTS order_id INT(11) NULL AFTER programa_id,
    MODIFY status VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    MODIFY payment_method VARCHAR(30) NOT NULL DEFAULT 'stripe';

ALTER TABLE orden_detalles
    MODIFY producto_id INT(11) NULL,
    ADD COLUMN IF NOT EXISTS programa_id INT(11) NULL AFTER producto_id,
    ADD KEY IF NOT EXISTS idx_orden_detalle_programa (programa_id);


-- Los nuevos registros nunca deben nacer activos por defecto.
ALTER TABLE course_enrollments
    MODIFY estado_curso VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    MODIFY estado_aprobacion VARCHAR(30) NOT NULL DEFAULT 'pendiente';

-- Cierra el acceso heredado que habia quedado activo sin evidencia de pago.
UPDATE course_enrollments ce
LEFT JOIN course_payment_requests cpr
  ON cpr.public_user_id = ce.public_user_id
 AND cpr.programa_id = ce.programa_id
 AND cpr.status = 'pagado'
SET ce.estado_curso = 'pendiente',
    ce.estado_aprobacion = 'pendiente',
    ce.progreso = 0,
    ce.certificado_disponible = 0
WHERE cpr.id IS NULL;
