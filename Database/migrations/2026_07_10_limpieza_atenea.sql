-- Limpieza logica y auditoria segura de Atenea - fase 2
-- Fecha: 2026-07-10
-- Esta migracion NO elimina tablas ni datos.
-- Documenta las estructuras activas de Atenea y las tablas heredadas
-- que quedan fuera de uso mientras se valida una segunda fase destructiva.

START TRANSACTION;

-- Roles activos y cuentas enlazadas
SELECT
    t.TYPE_ID,
    t.TYPE,
    COUNT(u.ID) AS total_usuarios
FROM type t
LEFT JOIN users u ON u.TYPE_ID = t.TYPE_ID
GROUP BY t.TYPE_ID, t.TYPE
ORDER BY t.TYPE_ID;

-- Tablas foco de Atenea que deben mantenerse
SELECT table_name
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_name IN (
    'users',
    'type',
    'public_users',
    'programas_educativos',
    'course_enrollments',
    'course_videos',
    'course_video_access',
    'course_video_progress',
    'productos',
    'categorias_productos',
    'ordenes',
    'orden_detalles',
    'orden_facturas',
    'dte_documents',
    'about',
    'facilities',
    'noticias',
    'galeria',
    'configmail'
  )
ORDER BY table_name;

-- Tablas legacy detectadas que quedan en observacion
SELECT table_name
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_name IN (
    'asignaturas',
    'docentes_asignaturas',
    'estudiantes_docentes',
    'grados',
    'jobs',
    'vehicles',
    'archivos',
    'contenidos',
    'evaluaciones',
    'ev_entregadas',
    'notas',
    'actividades',
    'inventario',
    'academic_charges',
    'academic_cycles'
  )
ORDER BY table_name;

-- Acciones futuras sugeridas, NO ejecutar aun sin validar dependencias:
-- DROP TABLE asignaturas;
-- DROP TABLE docentes_asignaturas;
-- DROP TABLE estudiantes_docentes;
-- DROP TABLE grados;
-- DROP TABLE jobs;
-- DROP TABLE vehicles;
-- DROP TABLE archivos;
-- DROP TABLE contenidos;
-- DROP TABLE evaluaciones;
-- DROP TABLE ev_entregadas;
-- DROP TABLE notas;
-- DROP TABLE actividades;
-- DROP TABLE inventario;
-- DROP TABLE academic_charges;
-- DROP TABLE academic_cycles;

COMMIT;
