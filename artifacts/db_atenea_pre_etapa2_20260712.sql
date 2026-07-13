-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: db_atenea
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `configuracion_portal_estudiante`
--

DROP TABLE IF EXISTS `configuracion_portal_estudiante`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuracion_portal_estudiante` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `tipo` enum('texto','imagen') NOT NULL DEFAULT 'texto',
  `grupo` enum('login','registro','panel','general') NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion_portal_estudiante`
--

LOCK TABLES `configuracion_portal_estudiante` WRITE;
/*!40000 ALTER TABLE `configuracion_portal_estudiante` DISABLE KEYS */;
INSERT INTO `configuracion_portal_estudiante` VALUES (1,'login_titulo','Iniciar sesion','texto','login','2026-07-12 17:08:49'),(2,'login_subtitulo','Accede a tu cuenta de estudiante de Atenea','texto','login','2026-07-12 16:39:56'),(3,'login_texto_boton','Iniciar sesion','texto','login','2026-07-12 17:08:49'),(4,'login_imagen_fondo','','imagen','login','2026-07-12 16:39:56'),(5,'login_imagen_lateral','src/estudiantes/assets/images/auth/01.png','imagen','login','2026-07-12 16:39:56'),(6,'registro_titulo','Crear una cuenta','texto','registro','2026-07-12 16:39:56'),(7,'registro_subtitulo','Reg├¡strate como estudiante de Atenea','texto','registro','2026-07-12 16:39:56'),(8,'registro_texto_boton','Crear cuenta','texto','registro','2026-07-12 16:39:56'),(9,'registro_imagen_fondo','','imagen','registro','2026-07-12 16:39:56'),(10,'registro_imagen_lateral','src/estudiantes/assets/images/auth/02.png','imagen','registro','2026-07-12 16:39:56'),(11,'panel_titulo','Portal del estudiante','texto','panel','2026-07-12 16:39:56'),(12,'panel_subtitulo','Tu espacio de aprendizaje en Atenea','texto','panel','2026-07-12 16:39:56'),(13,'panel_texto_bienvenida','Bienvenido a tu portal','texto','panel','2026-07-12 16:39:56'),(14,'panel_imagen_banner','','imagen','panel','2026-07-12 16:39:56'),(15,'panel_imagen_fondo','','imagen','panel','2026-07-12 16:39:56'),(16,'portal_logo','img/atenea-logo.png','imagen','general','2026-07-12 16:39:56'),(17,'avatar_predeterminado','src/estudiantes/assets/images/avatars/01.png','imagen','general','2026-07-12 16:39:56'),(18,'texto_pie_pagina','Atenea Escuela de Naturopat├¡a Hol├¡stica','texto','general','2026-07-12 16:39:56');
/*!40000 ALTER TABLE `configuracion_portal_estudiante` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracion_sitio`
--

DROP TABLE IF EXISTS `configuracion_sitio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuracion_sitio` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `tipo` enum('texto','email','telefono','url','imagen') NOT NULL DEFAULT 'texto',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion_sitio`
--

LOCK TABLES `configuracion_sitio` WRITE;
/*!40000 ALTER TABLE `configuracion_sitio` DISABLE KEYS */;
INSERT INTO `configuracion_sitio` VALUES (1,'nombre_sitio','Atenea Escuela de Naturopatía Holística','texto','2026-07-12 15:44:33'),(2,'logo','img/atenea-logo.png','imagen','2026-07-12 15:44:33'),(3,'favicon','img/atenea-logo.png','imagen','2026-07-12 15:44:33'),(4,'correo','info@atenea.edu.sv','email','2026-07-12 15:44:33'),(5,'telefono','','telefono','2026-07-12 15:47:01'),(6,'direccion','El Salvador','texto','2026-07-12 15:44:33'),(7,'facebook','#','url','2026-07-12 15:44:33'),(8,'instagram','#','url','2026-07-12 15:44:33'),(9,'whatsapp','','url','2026-07-12 15:44:33');
/*!40000 ALTER TABLE `configuracion_sitio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elementos_seccion`
--

DROP TABLE IF EXISTS `elementos_seccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `elementos_seccion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `seccion_id` int(10) unsigned NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `subtitulo` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `enlace` varchar(500) DEFAULT NULL,
  `texto_boton` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_elementos_seccion_activo_orden` (`seccion_id`,`activo`,`orden`),
  CONSTRAINT `fk_elementos_seccion` FOREIGN KEY (`seccion_id`) REFERENCES `secciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elementos_seccion`
--

LOCK TABLES `elementos_seccion` WRITE;
/*!40000 ALTER TABLE `elementos_seccion` DISABLE KEYS */;
INSERT INTO `elementos_seccion` VALUES (1,2,'Programas orientados al cuidado integral y preventivo.',NULL,NULL,NULL,'bi-check-circle',NULL,NULL,1,10,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(2,2,'Docentes con experiencia en terapias naturales y bienestar.',NULL,NULL,NULL,'bi-check-circle',NULL,NULL,1,20,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(3,2,'Aprendizaje aplicable a la vida personal y al desarrollo profesional.',NULL,NULL,NULL,'bi-check-circle',NULL,NULL,1,30,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(4,3,'Estudiantes','1200',NULL,NULL,NULL,NULL,NULL,1,10,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(5,3,'Capacitaciones','64',NULL,NULL,NULL,NULL,NULL,1,20,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(6,3,'Eventos','42',NULL,NULL,NULL,NULL,NULL,1,30,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(7,3,'Docentes','24',NULL,NULL,NULL,NULL,NULL,1,40,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(8,4,'Formación integral',NULL,'Contenidos que relacionan conocimientos tradicionales, hábitos saludables y práctica consciente.',NULL,'bi-mortarboard',NULL,NULL,1,10,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(9,4,'Acompañamiento',NULL,'Docentes comprometidos con un proceso de aprendizaje cercano y orientado a resultados.',NULL,'bi-people',NULL,NULL,1,20,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(10,4,'Visión holística',NULL,'Herramientas para promover equilibrio físico, emocional y ambiental de forma responsable.',NULL,'bi-flower1',NULL,NULL,1,30,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(11,5,'Fundamentos de naturopatía',NULL,NULL,NULL,'bi-flower2','src/website/courses.php',NULL,1,10,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(12,5,'Bienestar integral',NULL,NULL,NULL,'bi-heart-pulse','src/website/courses.php',NULL,1,20,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(13,5,'Nutrición consciente',NULL,NULL,NULL,'bi-cup-hot','src/website/courses.php',NULL,1,30,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(14,5,'Plantas y recursos naturales',NULL,NULL,NULL,'bi-tree','src/website/courses.php',NULL,1,40,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(15,5,'Terapias manuales',NULL,NULL,NULL,'bi-person-arms-up','src/website/courses.php',NULL,1,50,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(16,5,'Equilibrio energético',NULL,NULL,NULL,'bi-wind','src/website/courses.php',NULL,1,60,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(17,5,'Certificaciones',NULL,NULL,NULL,'bi-journal-check','src/website/courses.php',NULL,1,70,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(18,5,'Comunidad de aprendizaje',NULL,NULL,NULL,'bi-people','src/website/courses.php',NULL,1,80,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(19,6,'Fundamentos de Naturopatía','Naturopatía','Bases para comprender el bienestar y el cuidado natural desde una perspectiva integral.','src/website/assets/img/course-1.jpg',NULL,'src/website/course-details.php','Ver detalles',1,10,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(20,6,'Bienestar y Equilibrio','Terapias holísticas','Herramientas prácticas para acompañar procesos de autocuidado y hábitos saludables.','src/website/assets/img/course-2.jpg',NULL,'src/website/course-details.php','Ver detalles',1,20,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(21,6,'Recursos Naturales Aplicados','Especialización','Conocimientos para utilizar recursos naturales de manera informada, ética y responsable.','src/website/assets/img/course-3.jpg',NULL,'src/website/course-details.php','Ver detalles',1,30,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(22,7,'Nuevas oportunidades de formación',NULL,'Conoce nuestros próximos programas, talleres y actividades para la comunidad.',NULL,'bi-megaphone','src/website/events.php','Leer más',1,10,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(23,7,'Agenda de eventos holísticos',NULL,'Participa en encuentros diseñados para compartir conocimientos y experiencias de bienestar.',NULL,'bi-calendar-event','src/website/events.php','Leer más',1,20,'2026-07-12 15:44:33','2026-07-12 15:44:33');
/*!40000 ALTER TABLE `elementos_seccion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_sitio`
--

DROP TABLE IF EXISTS `menu_sitio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_sitio` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `texto` varchar(100) NOT NULL,
  `url` varchar(500) NOT NULL,
  `nueva_pestana` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_menu_activo_orden` (`activo`,`orden`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_sitio`
--

LOCK TABLES `menu_sitio` WRITE;
/*!40000 ALTER TABLE `menu_sitio` DISABLE KEYS */;
INSERT INTO `menu_sitio` VALUES (1,'Inicio','index.php',0,1,10,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(2,'Nosotros','src/website/about.php',0,1,20,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(3,'Capacitaciones','src/website/courses.php',0,1,30,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(4,'Docentes','src/website/trainers.php',0,1,40,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(5,'Eventos','src/website/events.php',0,1,50,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(6,'Productos','src/website/pricing.php',0,1,60,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(7,'Noticias','index.php#noticias',0,1,70,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(8,'Contacto','src/website/contact.php',0,1,80,'2026-07-12 15:44:33','2026-07-12 15:44:33');
/*!40000 ALTER TABLE `menu_sitio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `secciones`
--

DROP TABLE IF EXISTS `secciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `secciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `subtitulo` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `boton_texto` varchar(100) DEFAULT NULL,
  `boton_url` varchar(500) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`),
  KEY `idx_secciones_activo_orden` (`activo`,`orden`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `secciones`
--

LOCK TABLES `secciones` WRITE;
/*!40000 ALTER TABLE `secciones` DISABLE KEYS */;
INSERT INTO `secciones` VALUES (1,'hero','Hero principal','Formación integral para transformar tu bienestar','Capacitaciones, certificaciones y conocimientos enfocados en naturopatía y bienestar holístico.',NULL,'src/website/assets/img/hero-bg.jpg','Ver capacitaciones','src/website/courses.php',1,10,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(2,'nosotros','Nosotros','Conocimiento natural para una vida en equilibrio','Atenea Escuela de Naturopatía Holística impulsa una formación responsable, práctica y humana.',NULL,'src/website/assets/img/about.jpg','Conocer más','src/website/about.php',1,20,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(3,'cifras','Cifras',NULL,NULL,NULL,NULL,NULL,NULL,1,30,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(4,'propuesta','Propuesta de valor','¿Por qué formarte con Atenea?',NULL,'Integramos fundamentos de naturopatía, acompañamiento docente y experiencias prácticas para ayudarte a comprender el bienestar desde una visión completa.',NULL,'Conocer más','src/website/about.php',1,40,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(5,'areas','Áreas de formación',NULL,NULL,NULL,NULL,NULL,NULL,1,50,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(6,'capacitaciones','Capacitaciones','Capacitaciones','Programas destacados',NULL,NULL,'Ver todas las capacitaciones','src/website/courses.php',1,60,'2026-07-12 15:44:33','2026-07-12 15:44:33'),(7,'noticias','Noticias','Noticias','Actualidad de Atenea',NULL,NULL,NULL,NULL,1,70,'2026-07-12 15:44:33','2026-07-12 15:44:33');
/*!40000 ALTER TABLE `secciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `correo` varchar(190) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `proveedor` enum('local','google','mixto') NOT NULL DEFAULT 'local',
  `email_verificado` tinyint(1) NOT NULL DEFAULT 0,
  `rol` enum('admin','usuario','docente') NOT NULL DEFAULT 'usuario',
  `foto` varchar(500) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `ultimo_acceso` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`),
  UNIQUE KEY `uq_usuarios_google_id` (`google_id`),
  KEY `idx_usuarios_rol_estado` (`rol`,`estado`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Administrador','Atenea','admin@atenea.local','$2y$10$tZYxPvtkyhKnO/2VpQnQc.YYpwQeE7AN9TZD15bQbhvMjIv34Qd3a',NULL,'local',0,'admin',NULL,'activo','2026-07-12 20:34:28','2026-07-12 06:10:24','2026-07-13 02:34:28'),(2,'Estudiante','Prueba','usuario@atenea.local','$2y$10$LvTAn24ohthhMmndRScxueAOqiAkJBkt8PO/F2U2qZHh0AV3s/qhy',NULL,'local',0,'usuario',NULL,'activo','2026-07-12 19:46:44','2026-07-12 06:10:24','2026-07-13 01:46:44'),(3,'Docente','Prueba','docente@atenea.local','$2y$10$TnN5bvlQa/Z3ACzvbVQLHu0fUJ5.Xt20sj1uSb/XgEu6ulKTyBj..',NULL,'local',0,'docente',NULL,'activo','2026-07-12 10:44:10','2026-07-12 06:10:24','2026-07-12 16:44:10'),(4,'Usuario','Inactivo','inactivo@atenea.local','$2y$10$RKMdNPYoO3k7QyezTPt4ueD8SL1bQHccw6UAGEFivxKTI6t78RVXe',NULL,'local',0,'usuario',NULL,'inactivo',NULL,'2026-07-12 06:10:24','2026-07-12 06:10:24');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'db_atenea'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-12 20:41:03
