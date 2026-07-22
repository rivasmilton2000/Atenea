USE db_atenea;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE progreso_contenido
  DROP FOREIGN KEY IF EXISTS fk_progreso_contenido,
  DROP FOREIGN KEY IF EXISTS fk_progreso_inscripcion;

ALTER TABLE entrega_revisiones
  DROP CONSTRAINT IF EXISTS chk_revision_nota_nueva,
  DROP CONSTRAINT IF EXISTS chk_revision_nota_anterior,
  DROP FOREIGN KEY IF EXISTS fk_revision_docente,
  DROP FOREIGN KEY IF EXISTS fk_revision_entrega;

ALTER TABLE entrega_evidencias
  DROP FOREIGN KEY IF EXISTS fk_evidencia_entrega;

ALTER TABLE entregas_contenido
  DROP CONSTRAINT IF EXISTS chk_entrega_nota,
  DROP CONSTRAINT IF EXISTS chk_entrega_intento,
  DROP FOREIGN KEY IF EXISTS fk_entrega_contenido_revisor,
  DROP FOREIGN KEY IF EXISTS fk_entrega_contenido_seccion,
  DROP FOREIGN KEY IF EXISTS fk_entrega_contenido_asignatura,
  DROP FOREIGN KEY IF EXISTS fk_entrega_contenido_estudiante,
  DROP FOREIGN KEY IF EXISTS fk_entrega_contenido;
