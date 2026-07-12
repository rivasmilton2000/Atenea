-- Migración 003: contenido dinámico del sitio Atenea
USE db_atenea;

CREATE TABLE IF NOT EXISTS configuracion_sitio (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  clave VARCHAR(100) NOT NULL UNIQUE,
  valor TEXT NULL,
  tipo ENUM('texto','email','telefono','url','imagen') NOT NULL DEFAULT 'texto',
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS secciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  clave VARCHAR(100) NOT NULL UNIQUE,
  nombre VARCHAR(150) NOT NULL,
  titulo VARCHAR(255) NULL,
  subtitulo VARCHAR(255) NULL,
  descripcion TEXT NULL,
  imagen VARCHAR(255) NULL,
  boton_texto VARCHAR(100) NULL,
  boton_url VARCHAR(500) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  orden INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_secciones_activo_orden (activo, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS elementos_seccion (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seccion_id INT UNSIGNED NOT NULL,
  titulo VARCHAR(255) NOT NULL,
  subtitulo VARCHAR(255) NULL,
  descripcion TEXT NULL,
  imagen VARCHAR(255) NULL,
  icono VARCHAR(100) NULL,
  enlace VARCHAR(500) NULL,
  texto_boton VARCHAR(100) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  orden INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_elementos_seccion_activo_orden (seccion_id, activo, orden),
  CONSTRAINT fk_elementos_seccion FOREIGN KEY (seccion_id) REFERENCES secciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS menu_sitio (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  texto VARCHAR(100) NOT NULL,
  url VARCHAR(500) NOT NULL,
  nueva_pestana TINYINT(1) NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  orden INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_menu_activo_orden (activo, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO configuracion_sitio (clave, valor, tipo) VALUES
('nombre_sitio','Atenea Escuela de Naturopatía Holística','texto'),
('logo','img/atenea-logo.png','imagen'),
('favicon','img/atenea-logo.png','imagen'),
('correo','info@atenea.edu.sv','email'),
('telefono','','telefono'),
('direccion','El Salvador','texto'),
('facebook','#','url'),
('instagram','#','url'),
('whatsapp','','url')
ON DUPLICATE KEY UPDATE clave = VALUES(clave);

INSERT INTO secciones (clave,nombre,titulo,subtitulo,descripcion,imagen,boton_texto,boton_url,activo,orden) VALUES
('hero','Hero principal','Formación integral para transformar tu bienestar','Capacitaciones, certificaciones y conocimientos enfocados en naturopatía y bienestar holístico.',NULL,'src/website/assets/img/hero-bg.jpg','Ver capacitaciones','src/website/courses.php',1,10),
('nosotros','Nosotros','Conocimiento natural para una vida en equilibrio','Atenea Escuela de Naturopatía Holística impulsa una formación responsable, práctica y humana.',NULL,'src/website/assets/img/about.jpg','Conocer más','src/website/about.php',1,20),
('cifras','Cifras',NULL,NULL,NULL,NULL,NULL,NULL,1,30),
('propuesta','Propuesta de valor','¿Por qué formarte con Atenea?',NULL,'Integramos fundamentos de naturopatía, acompañamiento docente y experiencias prácticas para ayudarte a comprender el bienestar desde una visión completa.',NULL,'Conocer más','src/website/about.php',1,40),
('areas','Áreas de formación',NULL,NULL,NULL,NULL,NULL,NULL,1,50),
('capacitaciones','Capacitaciones','Capacitaciones','Programas destacados',NULL,NULL,'Ver todas las capacitaciones','src/website/courses.php',1,60),
('noticias','Noticias','Noticias','Actualidad de Atenea',NULL,NULL,NULL,NULL,1,70)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

INSERT INTO elementos_seccion (seccion_id,titulo,subtitulo,descripcion,imagen,icono,enlace,texto_boton,activo,orden)
SELECT s.id,x.titulo,x.subtitulo,x.descripcion,x.imagen,x.icono,x.enlace,x.texto_boton,1,x.orden
FROM secciones s JOIN (
 SELECT 'nosotros' clave,'Programas orientados al cuidado integral y preventivo.' titulo,NULL subtitulo,NULL descripcion,NULL imagen,'bi-check-circle' icono,NULL enlace,NULL texto_boton,10 orden
 UNION ALL SELECT 'nosotros','Docentes con experiencia en terapias naturales y bienestar.',NULL,NULL,NULL,'bi-check-circle',NULL,NULL,20
 UNION ALL SELECT 'nosotros','Aprendizaje aplicable a la vida personal y al desarrollo profesional.',NULL,NULL,NULL,'bi-check-circle',NULL,NULL,30
 UNION ALL SELECT 'cifras','Estudiantes','1200',NULL,NULL,NULL,NULL,NULL,10
 UNION ALL SELECT 'cifras','Capacitaciones','64',NULL,NULL,NULL,NULL,NULL,20
 UNION ALL SELECT 'cifras','Eventos','42',NULL,NULL,NULL,NULL,NULL,30
 UNION ALL SELECT 'cifras','Docentes','24',NULL,NULL,NULL,NULL,NULL,40
 UNION ALL SELECT 'propuesta','Formación integral',NULL,'Contenidos que relacionan conocimientos tradicionales, hábitos saludables y práctica consciente.',NULL,'bi-mortarboard',NULL,NULL,10
 UNION ALL SELECT 'propuesta','Acompañamiento',NULL,'Docentes comprometidos con un proceso de aprendizaje cercano y orientado a resultados.',NULL,'bi-people',NULL,NULL,20
 UNION ALL SELECT 'propuesta','Visión holística',NULL,'Herramientas para promover equilibrio físico, emocional y ambiental de forma responsable.',NULL,'bi-flower1',NULL,NULL,30
 UNION ALL SELECT 'areas','Fundamentos de naturopatía',NULL,NULL,NULL,'bi-flower2','src/website/courses.php',NULL,10
 UNION ALL SELECT 'areas','Bienestar integral',NULL,NULL,NULL,'bi-heart-pulse','src/website/courses.php',NULL,20
 UNION ALL SELECT 'areas','Nutrición consciente',NULL,NULL,NULL,'bi-cup-hot','src/website/courses.php',NULL,30
 UNION ALL SELECT 'areas','Plantas y recursos naturales',NULL,NULL,NULL,'bi-tree','src/website/courses.php',NULL,40
 UNION ALL SELECT 'areas','Terapias manuales',NULL,NULL,NULL,'bi-person-arms-up','src/website/courses.php',NULL,50
 UNION ALL SELECT 'areas','Equilibrio energético',NULL,NULL,NULL,'bi-wind','src/website/courses.php',NULL,60
 UNION ALL SELECT 'areas','Certificaciones',NULL,NULL,NULL,'bi-journal-check','src/website/courses.php',NULL,70
 UNION ALL SELECT 'areas','Comunidad de aprendizaje',NULL,NULL,NULL,'bi-people','src/website/courses.php',NULL,80
 UNION ALL SELECT 'capacitaciones','Fundamentos de Naturopatía','Naturopatía','Bases para comprender el bienestar y el cuidado natural desde una perspectiva integral.','src/website/assets/img/course-1.jpg',NULL,'src/website/course-details.php','Ver detalles',10
 UNION ALL SELECT 'capacitaciones','Bienestar y Equilibrio','Terapias holísticas','Herramientas prácticas para acompañar procesos de autocuidado y hábitos saludables.','src/website/assets/img/course-2.jpg',NULL,'src/website/course-details.php','Ver detalles',20
 UNION ALL SELECT 'capacitaciones','Recursos Naturales Aplicados','Especialización','Conocimientos para utilizar recursos naturales de manera informada, ética y responsable.','src/website/assets/img/course-3.jpg',NULL,'src/website/course-details.php','Ver detalles',30
 UNION ALL SELECT 'noticias','Nuevas oportunidades de formación',NULL,'Conoce nuestros próximos programas, talleres y actividades para la comunidad.',NULL,'bi-megaphone','src/website/events.php','Leer más',10
 UNION ALL SELECT 'noticias','Agenda de eventos holísticos',NULL,'Participa en encuentros diseñados para compartir conocimientos y experiencias de bienestar.',NULL,'bi-calendar-event','src/website/events.php','Leer más',20
) x ON x.clave=s.clave
WHERE NOT EXISTS (SELECT 1 FROM elementos_seccion e WHERE e.seccion_id=s.id);

INSERT INTO menu_sitio (texto,url,nueva_pestana,activo,orden)
SELECT x.texto,x.url,0,1,x.orden FROM (
 SELECT 'Inicio' texto,'index.php' url,10 orden UNION ALL
 SELECT 'Nosotros','src/website/about.php',20 UNION ALL
 SELECT 'Capacitaciones','src/website/courses.php',30 UNION ALL
 SELECT 'Docentes','src/website/trainers.php',40 UNION ALL
 SELECT 'Eventos','src/website/events.php',50 UNION ALL
 SELECT 'Productos','src/website/pricing.php',60 UNION ALL
 SELECT 'Noticias','index.php#noticias',70 UNION ALL
 SELECT 'Contacto','src/website/contact.php',80
) x WHERE NOT EXISTS (SELECT 1 FROM menu_sitio);

