-- Revision segura de textos publicos cargados desde base de datos.
-- No ejecuta cambios por si sola. Revisar primero en respaldo o entorno de prueba.
--
-- Vistas publicas que consumen estas tablas:
-- - pages/homepage.php
-- - pages/about.php
-- - pages/educacion.php
-- - pages/galeria.php
-- - pages/noticias.php
-- - pages/noticia_detalle.php
-- - pages/productos.php
-- - pages/producto_detalle.php
--
-- Tablas/campos a revisar si aun aparece texto con acentos rotos en produccion:
-- - about: titulo, descripcion_corta, descripcion, caracteristica1, caracteristica2, caracteristica3
-- - facilities: titulo, descripcion
-- - programas_educativos: titulo, descripcion_corta, descripcion_completa, nivel, instructor
-- - noticias: titulo, descripcion_corta, descripcion_completa
-- - galeria: titulo, categoria
-- - productos: nombre, descripcion_corta, descripcion
-- - categorias_productos: nombre

-- 1. Revisar contenido actual antes de cualquier ajuste.
SELECT id, titulo, descripcion_corta, descripcion, caracteristica1, caracteristica2, caracteristica3
FROM about
ORDER BY id;

SELECT id, titulo, descripcion
FROM facilities
ORDER BY orden, id;

SELECT id, titulo, descripcion_corta, descripcion_completa, nivel, instructor
FROM programas_educativos
ORDER BY orden, id;

SELECT id, titulo, descripcion_corta, descripcion_completa
FROM noticias
ORDER BY fecha_publicacion DESC, id DESC;

SELECT id, titulo, categoria
FROM galeria
ORDER BY orden, id;

SELECT id, nombre, descripcion_corta, descripcion
FROM productos
ORDER BY id;

SELECT id, nombre
FROM categorias_productos
ORDER BY nombre, id;

-- 2. Ejemplos de correccion manual, dejar comentados hasta validar respaldo.
-- UPDATE about
-- SET descripcion = REPLACE(descripcion, 'Vision', 'Visión')
-- WHERE id = <ID_AJUSTAR>;

-- UPDATE about
-- SET descripcion = REPLACE(descripcion, 'Mision', 'Misión')
-- WHERE id = <ID_AJUSTAR>;

-- UPDATE programas_educativos
-- SET descripcion_completa = REPLACE(descripcion_completa, 'Naturopatia', 'Naturopatía')
-- WHERE id = <ID_AJUSTAR>;

-- UPDATE noticias
-- SET descripcion_corta = REPLACE(descripcion_corta, 'Informacion', 'Información')
-- WHERE id = <ID_AJUSTAR>;
