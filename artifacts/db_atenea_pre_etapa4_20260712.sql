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
-- Table structure for table `departamentos`
--

DROP TABLE IF EXISTS `departamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departamentos` (
  `id` smallint(5) unsigned NOT NULL,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departamentos`
--

LOCK TABLES `departamentos` WRITE;
/*!40000 ALTER TABLE `departamentos` DISABLE KEYS */;
INSERT INTO `departamentos` VALUES (1,'Ahuachapán'),(6,'Cabañas'),(4,'Chalatenango'),(5,'Cuscatlán'),(3,'La Libertad'),(7,'La Paz'),(8,'La Unión'),(14,'Morazán'),(13,'San Miguel'),(2,'San Salvador'),(12,'San Vicente'),(11,'Santa Ana'),(10,'Sonsonate'),(9,'Usulután');
/*!40000 ALTER TABLE `departamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `distritos`
--

DROP TABLE IF EXISTS `distritos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `distritos` (
  `id` smallint(5) unsigned NOT NULL,
  `municipio_id` smallint(5) unsigned NOT NULL,
  `nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_distrito_municipio_nombre` (`municipio_id`,`nombre`),
  CONSTRAINT `fk_distritos_municipio` FOREIGN KEY (`municipio_id`) REFERENCES `municipios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `distritos`
--

LOCK TABLES `distritos` WRITE;
/*!40000 ALTER TABLE `distritos` DISABLE KEYS */;
INSERT INTO `distritos` VALUES (1,1,'Atiquizaya'),(2,1,'El Refugio'),(3,1,'San Lorenzo'),(4,1,'Turín'),(5,2,'Ahuachapán'),(6,2,'Apaneca'),(7,2,'Concepción de Ataco'),(8,2,'Tacuba'),(9,3,'Guaymango'),(10,3,'Jujutla'),(11,3,'San Francisco Menéndez'),(12,3,'San Pedro Puxtla'),(13,4,'Aguilares'),(14,4,'El Paisnal'),(15,4,'Guazapa'),(16,5,'Apopa'),(17,5,'Nejapa'),(18,6,'Ilopango'),(19,6,'San Martín'),(20,6,'Soyapango'),(21,6,'Tonacatepeque'),(22,7,'Ayutuxtepeque'),(26,7,'Ciudad Delgado'),(25,7,'Cuscatancingo'),(23,7,'Mejicanos'),(24,7,'San Salvador'),(27,8,'Panchimalco'),(28,8,'Rosario de Mora'),(29,8,'San Marcos'),(31,8,'Santiago Texacuangos'),(30,8,'Santo Tomás'),(32,9,'Quezaltepeque'),(33,9,'San Matías'),(34,9,'San Pablo Tacachico'),(36,10,'Ciudad Arce'),(35,10,'San Juan Opico'),(37,11,'Colón'),(38,11,'Jayaque'),(39,11,'Sacacoyo'),(41,11,'Talnique'),(40,11,'Tepecoyo'),(42,12,'Antiguo Cuscatlán'),(43,12,'Huizúcar'),(44,12,'Nuevo Cuscatlán'),(45,12,'San José Villanueva'),(46,12,'Zaragoza'),(47,13,'Chiltuipán'),(48,13,'Jicalapa'),(49,13,'La Libertad'),(50,13,'Tamanique'),(51,13,'Teotepeque'),(52,14,'Comasagua'),(53,14,'Santa Tecla'),(55,15,'Citalá'),(54,15,'La Palma'),(56,15,'San Ignacio'),(60,16,'Agua Caliente'),(61,16,'Dulce Nombre de María'),(62,16,'El Paraíso'),(59,16,'La Reina'),(57,16,'Nueva Concepción'),(66,16,'San Fernando'),(63,16,'San Francisco Morazán'),(64,16,'San Rafael'),(65,16,'Santa Rita'),(58,16,'Tejutla'),(68,17,'Arcatao'),(69,17,'Azacualpa'),(67,17,'Chalatenango'),(70,17,'Comalapa'),(71,17,'Concepción Quezaltepeque'),(72,17,'El Carrizal'),(73,17,'La Laguna'),(74,17,'Las Vueltas'),(75,17,'Nombre de Jesús'),(76,17,'Nueva Trinidad'),(77,17,'Ojos de Agua'),(78,17,'Potonico'),(79,17,'San Antonio de La Cruz'),(80,17,'San Antonio Los Ranchos'),(81,17,'San Francisco Lempa'),(82,17,'San Isidro Labrador'),(83,17,'San José Cancasque'),(85,17,'San José Las Flores'),(86,17,'San Luis del Carmen'),(84,17,'San Miguel de Mercedes'),(89,18,'Oratorio de Concepción'),(90,18,'San Bartolomé Perulapán'),(88,18,'San José Guayabal'),(91,18,'San Pedro Perulapán'),(87,18,'Suchitoto'),(94,19,'Candelaria'),(92,19,'Cojutepeque'),(96,19,'El Carmen'),(100,19,'El Rosario'),(95,19,'Monte San Juan'),(97,19,'San Cristóbal'),(93,19,'San Rafael Cedros'),(99,19,'San Ramón'),(101,19,'Santa Cruz Analquito'),(98,19,'Santa Cruz Michapa'),(102,19,'Tenancingo'),(105,20,'Dolores'),(106,20,'Guacotecti'),(107,20,'San Isidro'),(103,20,'Sensuntepeque'),(104,20,'Victoria'),(111,21,'Cinquera'),(110,21,'Jutiapa'),(108,21,'llobasco'),(109,21,'Tejutepeque'),(112,22,'Cuyultitán'),(113,22,'Olocuilta'),(118,22,'San Francisco Chinameca'),(114,22,'San Juan Talpa'),(115,22,'San Luis Talpa'),(116,22,'San Pedro Masahuat'),(117,22,'Tapalhuaca'),(119,23,'El Rosario'),(120,23,'Jerusalén'),(121,23,'Mercedes La Ceiba'),(122,23,'Paraíso de Osorio'),(123,23,'San Antonio Masahuat'),(124,23,'San Emigdio'),(125,23,'San Juan Tepezontes'),(126,23,'San Luis La Herradura'),(127,23,'San Miguel Tepezontes'),(128,23,'San Pedro Nonualco'),(129,23,'Santa María Ostuma'),(130,23,'Santiago Nonualco'),(131,24,'San Juan Nonualco'),(132,24,'San Rafael Obrajuelo'),(133,24,'Zacatecoluca'),(134,25,'Anamorós'),(135,25,'Bolívar'),(136,25,'Concepción de Oriente'),(137,25,'El Sauce'),(138,25,'Lislique'),(139,25,'Nueva Esparta'),(140,25,'Pasaquina'),(141,25,'Polorós'),(142,25,'San José La Fuente'),(143,25,'Santa Rosa de Lima'),(144,26,'Conchagua'),(145,26,'El Carmen'),(146,26,'Intipucá'),(147,26,'La Unión'),(148,26,'Meanguera del Golfo'),(149,26,'San Alejo'),(150,26,'Yayantique'),(151,26,'Yucuaiquín'),(153,27,'Alegría'),(154,27,'Berlín'),(157,27,'El Triunfo'),(158,27,'Estanzuelas'),(156,27,'Jucuapa'),(155,27,'Mercedes Umaña'),(160,27,'Nueva Granada'),(159,27,'San Buenaventura'),(152,27,'Santiago de María'),(169,28,'California'),(164,28,'Concepción Batres'),(170,28,'Ereguayquín'),(162,28,'Jucuarán'),(166,28,'Ozatlán'),(163,28,'San Dionisio'),(168,28,'Santa Elena'),(165,28,'Santa María'),(167,28,'Tecapán'),(161,28,'Usulután'),(171,29,'Jiquilisco'),(172,29,'Puerto El Triunfo'),(173,29,'San Agustín'),(174,29,'San Francisco Javier'),(175,30,'Juayúa'),(176,30,'Nahuizalco'),(177,30,'Salcoatitán'),(178,30,'Santa Catarina Masahuat'),(181,31,'Nahulingo'),(182,31,'San Antonio del Monte'),(183,31,'Santo Domingo de Guzmán'),(179,31,'Sonsonate'),(180,31,'Sonzacate'),(185,32,'Armenia'),(186,32,'Caluco'),(188,32,'Cuisnahuat'),(184,32,'Izalco'),(187,32,'San Julián'),(189,32,'Santa Isabel Ishuatán'),(190,33,'Acajutla'),(191,34,'Masahuat'),(192,34,'Metapán'),(193,34,'Santa Rosa Guachipilín'),(194,34,'Texistepeque'),(195,35,'Santa Ana'),(196,36,'Coatepeque'),(197,36,'El Congo'),(198,37,'Candelaria de la Frontera'),(199,37,'Chalchuapa'),(200,37,'El Porvenir'),(201,37,'San Antonio Pajonal'),(202,37,'San Sebastián Salitrillo'),(203,37,'Santiago de La Frontera'),(204,38,'Apastepeque'),(207,38,'San Esteban Catarina'),(206,38,'San Ildefonso'),(209,38,'San Lorenzo'),(208,38,'San Sebastián'),(205,38,'Santa Clara'),(210,38,'Santo Domingo'),(212,39,'Guadalupe'),(216,39,'San Cayetano lstepeque'),(211,39,'San Vicente'),(215,39,'Tecoluca'),(214,39,'Tepetitán'),(213,39,'Verapaz'),(222,40,'Carolina'),(224,40,'Chapeltique'),(217,40,'Ciudad Barrios'),(219,40,'Nuevo Edén de San Juan'),(223,40,'San Antonio del Mosco'),(220,40,'San Gerardo'),(221,40,'San Luis de La Reina'),(218,40,'Sesori'),(230,41,'Chirilagua'),(226,41,'Comacarán'),(228,41,'Moncagua'),(229,41,'Quelepa'),(225,41,'San Miguel'),(227,41,'Uluazapa'),(231,42,'Chinameca'),(236,42,'El Tránsito'),(233,42,'Lolotique'),(232,42,'Nueva Guadalupe'),(234,42,'San Jorge'),(235,42,'San Rafael Oriente'),(237,43,'Arambala'),(238,43,'Cacaopera'),(239,43,'Corinto'),(240,43,'El Rosario'),(241,43,'Joateca'),(242,43,'Jocoaitique'),(243,43,'Meanguera'),(244,43,'Perquín'),(245,43,'San Fernando'),(246,43,'San Isidro'),(247,43,'Torola'),(248,44,'Chilanga'),(249,44,'Delicias de Concepción'),(250,44,'El Divisadero'),(251,44,'Gualococti'),(252,44,'Guatajiagua'),(253,44,'Jocoro'),(254,44,'Lolotiquillo'),(255,44,'Osicala'),(256,44,'San Carlos'),(257,44,'San Francisco Gotera'),(258,44,'San Simón'),(259,44,'Sensembra'),(260,44,'Sociedad'),(261,44,'Yamabal'),(262,44,'Yoloaiquín');
/*!40000 ALTER TABLE `distritos` ENABLE KEYS */;
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
-- Table structure for table `municipios`
--

DROP TABLE IF EXISTS `municipios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `municipios` (
  `id` smallint(5) unsigned NOT NULL,
  `departamento_id` smallint(5) unsigned NOT NULL,
  `nombre` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_municipio_departamento_nombre` (`departamento_id`,`nombre`),
  CONSTRAINT `fk_municipios_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `municipios`
--

LOCK TABLES `municipios` WRITE;
/*!40000 ALTER TABLE `municipios` DISABLE KEYS */;
INSERT INTO `municipios` VALUES (2,1,'Ahuachapán Centro'),(1,1,'Ahuachapán Norte'),(3,1,'Ahuachapán Sur'),(7,2,'San Salvador Centro'),(6,2,'San Salvador Este'),(4,2,'San Salvador Norte'),(5,2,'San Salvador Oeste'),(8,2,'San Salvador Sur'),(10,3,'La Libertad Centro'),(13,3,'La Libertad Costa'),(12,3,'La Libertad Este'),(9,3,'La Libertad Norte'),(11,3,'La Libertad Oeste'),(14,3,'La Libertad Sur'),(16,4,'Chalatenango Centro'),(15,4,'Chalatenango Norte'),(17,4,'Chalatenango Sur'),(18,5,'Cuscatlán Norte'),(19,5,'Cuscatlán Sur'),(20,6,'Cabañas Este'),(21,6,'Cabañas Oeste'),(23,7,'La Paz Centro'),(24,7,'La Paz Este'),(22,7,'La Paz Oeste'),(25,8,'La Unión Norte'),(26,8,'La Unión Sur'),(28,9,'Usulután Este'),(27,9,'Usulután Norte'),(29,9,'Usulután Oeste'),(31,10,'Sonsonate Centro'),(32,10,'Sonsonate Este'),(30,10,'Sonsonate Norte'),(33,10,'Sonsonate Oeste'),(35,11,'Santa Ana Centro'),(36,11,'Santa Ana Este'),(34,11,'Santa Ana Norte'),(37,11,'Santa Ana Oeste'),(38,12,'San Vicente Norte'),(39,12,'San Vicente Sur'),(41,13,'San Miguel Centro'),(40,13,'San Miguel Norte'),(42,13,'San Miguel Oeste'),(43,14,'Morazán Norte'),(44,14,'Morazán Sur');
/*!40000 ALTER TABLE `municipios` ENABLE KEYS */;
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
  `fecha_nacimiento` date DEFAULT NULL,
  `dui` char(10) DEFAULT NULL,
  `codigo_telefono` varchar(5) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `departamento_id` smallint(5) unsigned DEFAULT NULL,
  `municipio_id` smallint(5) unsigned DEFAULT NULL,
  `distrito_id` smallint(5) unsigned DEFAULT NULL,
  `direccion` varchar(500) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `ultimo_acceso` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`),
  UNIQUE KEY `uq_usuarios_google_id` (`google_id`),
  UNIQUE KEY `uq_usuarios_dui` (`dui`),
  KEY `idx_usuarios_rol_estado` (`rol`,`estado`),
  KEY `fk_usuarios_departamento` (`departamento_id`),
  KEY `fk_usuarios_municipio` (`municipio_id`),
  KEY `fk_usuarios_distrito` (`distrito_id`),
  CONSTRAINT `fk_usuarios_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`),
  CONSTRAINT `fk_usuarios_distrito` FOREIGN KEY (`distrito_id`) REFERENCES `distritos` (`id`),
  CONSTRAINT `fk_usuarios_municipio` FOREIGN KEY (`municipio_id`) REFERENCES `municipios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Administrador','Atenea','admin@atenea.local','$2y$10$tZYxPvtkyhKnO/2VpQnQc.YYpwQeE7AN9TZD15bQbhvMjIv34Qd3a',NULL,'local',0,'admin',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'activo','2026-07-12 21:31:47','2026-07-12 06:10:24','2026-07-13 03:31:47'),(2,'Estudiante','Prueba','usuario@atenea.local','$2y$10$LvTAn24ohthhMmndRScxueAOqiAkJBkt8PO/F2U2qZHh0AV3s/qhy',NULL,'local',0,'usuario',NULL,'2005-08-24','06956257-2','+503','61156808',3,14,53,NULL,'activo','2026-07-12 21:32:29','2026-07-12 06:10:24','2026-07-13 03:32:29'),(3,'Docente','Prueba','docente@atenea.local','$2y$10$TnN5bvlQa/Z3ACzvbVQLHu0fUJ5.Xt20sj1uSb/XgEu6ulKTyBj..',NULL,'local',0,'docente',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'activo','2026-07-12 10:44:10','2026-07-12 06:10:24','2026-07-12 16:44:10'),(4,'Usuario','Inactivo','inactivo@atenea.local','$2y$10$RKMdNPYoO3k7QyezTPt4ueD8SL1bQHccw6UAGEFivxKTI6t78RVXe',NULL,'local',0,'usuario',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'inactivo',NULL,'2026-07-12 06:10:24','2026-07-12 06:10:24');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-12 21:34:47
