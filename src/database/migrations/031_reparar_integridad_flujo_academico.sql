USE db_atenea;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Repara restricciones que no se añadieron cuando estas tablas ya existían
-- antes de ejecutar la migración 019.
ALTER TABLE entregas_contenido
  ADD CONSTRAINT fk_entrega_contenido FOREIGN KEY IF NOT EXISTS (contenido_id) REFERENCES contenidos(id),
  ADD CONSTRAINT fk_entrega_contenido_estudiante FOREIGN KEY IF NOT EXISTS (estudiante_id) REFERENCES usuarios(id),
  ADD CONSTRAINT fk_entrega_contenido_asignatura FOREIGN KEY IF NOT EXISTS (asignatura_id) REFERENCES asignaturas(id),
  ADD CONSTRAINT fk_entrega_contenido_seccion FOREIGN KEY IF NOT EXISTS (seccion_id) REFERENCES capacitacion_secciones(id),
  ADD CONSTRAINT fk_entrega_contenido_revisor FOREIGN KEY IF NOT EXISTS (revisado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
  ADD CONSTRAINT IF NOT EXISTS chk_entrega_intento CHECK (intento >= 1),
  ADD CONSTRAINT IF NOT EXISTS chk_entrega_nota CHECK (nota IS NULL OR nota BETWEEN 0.00 AND 10.00);

ALTER TABLE entrega_evidencias
  ADD CONSTRAINT fk_evidencia_entrega FOREIGN KEY IF NOT EXISTS (entrega_id) REFERENCES entregas_contenido(id) ON DELETE CASCADE;

ALTER TABLE entrega_revisiones
  ADD CONSTRAINT fk_revision_entrega FOREIGN KEY IF NOT EXISTS (entrega_id) REFERENCES entregas_contenido(id),
  ADD CONSTRAINT fk_revision_docente FOREIGN KEY IF NOT EXISTS (docente_id) REFERENCES usuarios(id),
  ADD CONSTRAINT IF NOT EXISTS chk_revision_nota_anterior CHECK (nota_anterior IS NULL OR nota_anterior BETWEEN 0.00 AND 10.00),
  ADD CONSTRAINT IF NOT EXISTS chk_revision_nota_nueva CHECK (nota_nueva IS NULL OR nota_nueva BETWEEN 0.00 AND 10.00);

ALTER TABLE progreso_contenido
  ADD CONSTRAINT fk_progreso_inscripcion FOREIGN KEY IF NOT EXISTS (inscripcion_id) REFERENCES inscripciones_capacitacion(id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_progreso_contenido FOREIGN KEY IF NOT EXISTS (contenido_id) REFERENCES contenidos(id) ON DELETE CASCADE;
