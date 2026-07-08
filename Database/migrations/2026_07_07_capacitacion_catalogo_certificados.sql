-- Atenea
-- Cambios para:
-- 1. Reforzar el lenguaje de "capacitacion" en contenido administrable
-- 2. Extender el catalogo para soportar cursos y certificaciones
-- 3. Permitir activar/desactivar video por elemento del catalogo

ALTER TABLE productos
    ADD COLUMN IF NOT EXISTS tipo_oferta VARCHAR(20) NOT NULL DEFAULT 'producto' AFTER categoria_id,
    ADD COLUMN IF NOT EXISTS duracion VARCHAR(120) DEFAULT NULL AFTER stock,
    ADD COLUMN IF NOT EXISTS video_url VARCHAR(255) DEFAULT NULL AFTER imagen3,
    ADD COLUMN IF NOT EXISTS video_activo TINYINT(1) NOT NULL DEFAULT 0 AFTER video_url;

UPDATE about
SET
    descripcion_corta = REPLACE(
        REPLACE(descripcion_corta, 'opcion educativa', 'opcion de capacitacion'),
        'educacion de calidad',
        'capacitacion de calidad'
    ),
    descripcion = REPLACE(
        REPLACE(
            REPLACE(descripcion, 'educacion de calidad', 'capacitacion de calidad'),
            'propuesta educativa',
            'propuesta de capacitacion'
        ),
        'enfoque educativo consciente',
        'enfoque de capacitacion consciente'
    )
WHERE estado = 1;

UPDATE facilities
SET descripcion = REPLACE(descripcion, 'institución educativa referente', 'institución referente en capacitación')
WHERE id = 1;

UPDATE facilities
SET descripcion = REPLACE(descripcion, 'brindando educación ética, consciente y de calidad', 'brindando capacitación ética, consciente y de calidad')
WHERE id = 2;

UPDATE facilities
SET descripcion = REPLACE(descripcion, 'Brindamos educación teórica y práctica', 'Brindamos capacitación teórica y práctica')
WHERE id = 4;

UPDATE noticias
SET descripcion_completa = REPLACE(
        REPLACE(descripcion_completa, 'brindar educación de calidad', 'brindar capacitación de calidad'),
        'propuesta educativa',
        'propuesta de capacitación'
    )
WHERE descripcion_completa LIKE '%educaci%';

UPDATE categorias_productos
SET descripcion = REPLACE(descripcion, 'Material educativo', 'Material de capacitación')
WHERE descripcion LIKE '%educativ%';
