-- Fix puntual para la sección pública "Lo que ofrecemos en Atenea Escuela"
-- Fuente real detectada: tabla `facilities`, columnas `titulo` y `descripcion`, IDs 1 a 6.
-- Respaldo generado antes de cualquier intento de actualización:
-- C:\xampp\htdocs\atenea_2.1\codex_backups\facilities_before_fix_20260627_112312.sql
--
-- Importante:
-- La instancia actual de MariaDB está corriendo con:
--   --innodb-force-recovery=6
--   --skip-grant-tables
-- e `innodb_read_only=ON`, por lo que estas sentencias NO se pudieron ejecutar todavía.
-- Ejecutar este archivo solo cuando MariaDB vuelva a modo escritura normal.

UPDATE facilities
SET titulo = 'Visión',
    descripcion = 'Ser una institución educativa referente en la formación de profesionales en Naturopatía Holística, promoviendo el conocimiento responsable, ético y consciente de las terapias naturales, con una visión integral del ser humano y respeto por la salud y la vida.'
WHERE id = 1;

UPDATE facilities
SET titulo = 'Misión',
    descripcion = 'Formar profesionales en Naturopatía Holística con una visión integral del ser humano, brindando educación ética, consciente y de calidad en terapias naturales. Nuestra misión es transmitir conocimiento sólido, responsable y aplicable, que contribuya al bienestar, la prevención y el cuidado de la salud desde un enfoque natural y humano.'
WHERE id = 2;

UPDATE facilities
SET titulo = 'Valores',
    descripcion = 'Nos guiamos por valores fundamentales que constituyen el núcleo de nuestra formación. Promovemos el respeto por la vida y la naturaleza, fomentamos una visión integral del ser humano y cultivamos la ética, la conciencia y la responsabilidad en el ejercicio de las terapias naturales. En nuestra comunidad impulsamos el conocimiento con sentido humano, el respeto mutuo y el compromiso con una salud natural, consciente y digna.'
WHERE id = 3;

UPDATE facilities
SET titulo = 'Servicios',
    descripcion = 'Ofrecemos formación integral en Naturopatía Holística mediante programas académicos, cursos y capacitaciones terapéuticas, orientados al desarrollo profesional y humano del estudiante. Brindamos educación teórica y práctica en terapias naturales, acompañada de formación ética, legal y deontológica, promoviendo un aprendizaje consciente en un entorno de respeto, responsabilidad y compromiso con la salud integral.'
WHERE id = 4;

UPDATE facilities
SET titulo = 'Historia',
    descripcion = 'ATENEA Escuela de Naturopatía Holística nace como resultado de un proceso de búsqueda, aprendizaje y evolución en el campo de la salud natural. Desde sus inicios, surge con el propósito de ofrecer una formación consciente y responsable en terapias naturales, integrando conocimiento, ética y una visión holística del ser humano. Cada paso de su creación ha sido parte de un crecimiento constante orientado al bienestar integral y a la profesionalización de la naturopatía.'
WHERE id = 5;

UPDATE facilities
SET titulo = 'Equipo Educativo',
    descripcion = 'Nuestro equipo educativo está conformado por profesionales capacitados en diversas áreas de la Naturopatía y las terapias holísticas, comprometidos con una enseñanza integral, ética y consciente. Trabajamos de manera cercana para acompañar a cada estudiante en su proceso de aprendizaje, promoviendo el conocimiento, la responsabilidad profesional y el respeto por la salud y la vida.'
WHERE id = 6;
