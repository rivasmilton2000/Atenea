-- Limpieza logica y auditoria segura de Atenea
-- Fecha: 2026-07-10
-- Esta migracion NO elimina tablas ni datos legacy.
-- Normaliza el origen de registro y el rol de estudiantes reales,
-- y deja auditadas las estructuras activas / obsoletas del proyecto.

START TRANSACTION;

-- Asegurar soporte para el origen de registro del usuario publico.
SET @has_public_users := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'public_users'
);

SET @has_registration_source := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'public_users'
      AND column_name = 'REGISTRATION_SOURCE'
);

SET @alter_public_users_sql := IF(
    @has_public_users = 1 AND @has_registration_source = 0,
    "ALTER TABLE public_users ADD COLUMN REGISTRATION_SOURCE VARCHAR(20) NOT NULL DEFAULT 'normal' AFTER GOOGLE_EMAIL",
    "SELECT 1"
);
PREPARE stmt_public_users FROM @alter_public_users_sql;
EXECUTE stmt_public_users;
DEALLOCATE PREPARE stmt_public_users;

-- Backfill del origen de registro usando la identidad Google si existe.
UPDATE public_users
SET REGISTRATION_SOURCE = CASE
    WHEN COALESCE(NULLIF(TRIM(REGISTRATION_SOURCE), ''), '') <> '' THEN REGISTRATION_SOURCE
    WHEN COALESCE(GOOGLE_ID, '') <> '' OR COALESCE(GOOGLE_EMAIL, '') <> '' THEN 'google'
    ELSE 'normal'
END
WHERE COALESCE(NULLIF(TRIM(REGISTRATION_SOURCE), ''), '') = '';

-- Forzar rol estudiante para usuarios publicos reales que quedaron sin TYPE_ID
-- al venir de flujos heredados o de un registro anterior.
UPDATE users u
INNER JOIN public_users pu ON pu.USER_ID = u.ID
SET u.TYPE_ID = 3
WHERE (u.TYPE_ID IS NULL OR u.TYPE_ID = 0)
  AND (u.EMPLOYEE_ID IS NULL OR u.EMPLOYEE_ID = 0)
  AND (u.ESTUDIANTE_ID IS NULL OR u.ESTUDIANTE_ID = 0);

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

-- Resumen rapido del saneamiento de estudiantes reales
SELECT
    u.ID,
    u.TYPE_ID,
    t.TYPE,
    pu.EMAIL,
    pu.REGISTRATION_SOURCE
FROM users u
INNER JOIN public_users pu ON pu.USER_ID = u.ID
LEFT JOIN type t ON t.TYPE_ID = u.TYPE_ID
ORDER BY u.ID DESC;

COMMIT;
