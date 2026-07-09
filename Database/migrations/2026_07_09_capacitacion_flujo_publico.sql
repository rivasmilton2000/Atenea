-- Atenea
-- Fase: flujo publico de capacitacion con cotizacion, detalle dinamico y gestion administrativa

ALTER TABLE programas_educativos
    ADD COLUMN IF NOT EXISTS tipo_programa VARCHAR(20) NOT NULL DEFAULT 'curso' AFTER titulo,
    ADD COLUMN IF NOT EXISTS precio DECIMAL(10,2) NOT NULL DEFAULT 100.00 AFTER descripcion_completa,
    ADD COLUMN IF NOT EXISTS duracion VARCHAR(120) DEFAULT NULL AFTER precio,
    ADD COLUMN IF NOT EXISTS modalidad VARCHAR(80) DEFAULT NULL AFTER duracion,
    ADD COLUMN IF NOT EXISTS detalles_programa TEXT DEFAULT NULL AFTER modalidad,
    ADD COLUMN IF NOT EXISTS beneficios TEXT DEFAULT NULL AFTER detalles_programa,
    ADD COLUMN IF NOT EXISTS requisitos TEXT DEFAULT NULL AFTER beneficios;

UPDATE programas_educativos
SET tipo_programa = 'certificacion'
WHERE LOWER(titulo) LIKE '%especializ%'
   OR LOWER(titulo) LIKE '%certific%';
