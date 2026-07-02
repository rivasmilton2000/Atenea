-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-07-2026 a las 22:11:54
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `atenea`
--
CREATE DATABASE IF NOT EXISTS `atenea` DEFAULT CHARACTER SET utf8 COLLATE utf8_spanish2_ci;
USE `atenea`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `about`
--

CREATE TABLE `about` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion_corta` text NOT NULL,
  `descripcion` text NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `imagen2` varchar(255) NOT NULL,
  `imagen3` varchar(255) NOT NULL,
  `caracteristica1` varchar(150) NOT NULL,
  `caracteristica2` varchar(150) NOT NULL,
  `caracteristica3` varchar(150) NOT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish2_ci;

--
-- Volcado de datos para la tabla `about`
--

INSERT INTO `about` (`id`, `titulo`, `descripcion_corta`, `descripcion`, `imagen`, `imagen2`, `imagen3`, `caracteristica1`, `caracteristica2`, `caracteristica3`, `estado`) VALUES
(1, 'Formacion consciente para una salud integral', 'En ATENEA Escuela, somos una opcion educativa comprometida con la formacion integral en Naturopatia Holistica. Brindamos educacion de calidad con un enfoque consciente, etico y humano, creando un entorno de aprendizaje que impulsa el conocimiento, el crecimiento personal y el compromiso con la salud natural y el bienestar integral.', 'En ATENEA Escuela de Naturopatia Holistica, somos una institucion dedicada a la formacion integral en terapias naturales y salud holistica. Nos enfocamos en brindar educacion de calidad que combine conocimiento academico, conciencia humana y etica profesional, creando un entorno de aprendizaje orientado al bienestar y al respeto por la vida.\r\n\r\nNuestra propuesta educativa se basa en los principios de la Naturopatia, promoviendo una vision del ser humano como un todo: cuerpo, mente y energia. Acompanamos a nuestros estudiantes en su desarrollo profesional y personal, preparandolos para ejercer con responsabilidad, criterio y compromiso social.\r\n\r\nEn ATENEA, creemos que la salud se construye desde el conocimiento y la conciencia. Por ello, nuestro compromiso va mas alla de la ensenanza tecnica: buscamos formar terapeutas capaces de comprender, acompanar y contribuir positivamente al bienestar integral de las personas y las comunidades.\r\n\r\nCon un equipo formativo comprometido y un enfoque educativo consciente, ATENEA Escuela es un espacio donde el aprendizaje se transforma en vocacion y servicio.', 'Cara.jpeg', 'about_69758b52aee2a_1769311058.jpg', 'Conoterapia2.png', 'Formacion Integral Holistica.', 'Excelencia Academica en Naturopatia.', 'Etica, Conciencia y Salud Natural.', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades`
--

CREATE TABLE `actividades` (
  `ACTIVIDAD_ID` int(11) NOT NULL,
  `ACT_TITULO` varchar(255) DEFAULT NULL,
  `ACT_DESCRIPCION` text DEFAULT NULL,
  `ACT_FECHA` date DEFAULT NULL,
  `ACT_ESTADO` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos`
--

CREATE TABLE `archivos` (
  `a_id` int(11) NOT NULL,
  `nombre_archivo` varchar(255) DEFAULT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `permisos` varchar(10) DEFAULT NULL,
  `fecha_subida` varchar(255) DEFAULT NULL,
  `a_estado` varchar(10) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `archivos`
--

INSERT INTO `archivos` (`a_id`, `nombre_archivo`, `archivo`, `permisos`, `fecha_subida`, `a_estado`) VALUES
(1, 'Presupuesto Anual 2024', '66899903a0a74_Presupuesto Anual 2024.pdf', '2', '2024-07-06', '0'),
(2, 'Plan Estrategico 2023-2025', '668998f50427c_Plan Estrategico 2023-2025.pdf', '2', '2024-07-06', '1'),
(3, 'Informe de Gestion Semestral', '668998cf9301f_Informe de Gestion Semestral.docx', '3', '2024-07-06', '1'),
(5, 'Manual de Procedimientos Administrativos', '6689a0f39a015_PRUEBA EDIT.xlsx', '3', '2024-07-06', '0'),
(6, 'Plan anual de trabajo', '6689ae2037f8e_Plan anual de trabajo.ppt', '1', '2024-07-06', '1'),
(7, 'Reglamento interno', '6689c36d99a8a_Reglamento interno.pptx', '4', '2024-07-06', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas`
--

CREATE TABLE `asignaturas` (
  `ASIGNATURA_ID` int(11) NOT NULL,
  `A_NAME` varchar(50) DEFAULT NULL,
  `A_ESTADO` varchar(10) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `asignaturas`
--

INSERT INTO `asignaturas` (`ASIGNATURA_ID`, `A_NAME`, `A_ESTADO`) VALUES
(1, 'Matematicas', '1'),
(2, 'Lenguaje', '1'),
(3, 'Ingles', '1'),
(4, 'Sociales', '0'),
(5, 'Ciencias', '0'),
(6, 'Religion', '0'),
(7, 'Informatica', '1'),
(8, 'Conoterapia', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id` int(11) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish2_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`id`, `session_id`, `producto_id`, `cantidad`, `fecha_agregado`) VALUES
(7, 'cart_697e827d6d5c23.09353965', 3, 2, '2026-01-31 23:45:17'),
(25, 'cart_6a30ac8fcf3076.62274945', 1, 1, '2026-06-16 01:53:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_productos`
--

CREATE TABLE `categorias_productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish2_ci;

--
-- Volcado de datos para la tabla `categorias_productos`
--

INSERT INTO `categorias_productos` (`id`, `nombre`, `descripcion`, `estado`, `fecha_creacion`) VALUES
(1, 'Suplementos Naturales', 'Suplementos y vitaminas naturales para tu bienestar', 1, '2026-01-31 22:28:03'),
(2, 'Terapias', 'Productos para terapias naturales', 1, '2026-01-31 22:28:03'),
(3, 'Libros y Guias', 'Material educativo sobre naturopatia', 1, '2026-01-31 22:28:03'),
(4, 'Aceites Esenciales', 'Aceites esenciales puros y naturales', 1, '2026-01-31 22:28:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `category`
--

CREATE TABLE `category` (
  `CATEGORY_ID` int(11) NOT NULL,
  `CNAME` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `category`
--

INSERT INTO `category` (`CATEGORY_ID`, `CNAME`) VALUES
(0, 'Cables'),
(1, 'Electrica'),
(2, 'Herramienta'),
(3, 'Construcción '),
(4, 'Equipo de seguridad'),
(5, 'Fuente de energia'),
(6, 'Auriculares'),
(7, 'CPU');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configmail`
--

CREATE TABLE `configmail` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `token` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `configmail`
--

INSERT INTO `configmail` (`id`, `email`, `token`) VALUES
(1, 'ateneanaturopatia@gmail.com', 'dozf fbqt mqjh pibh');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactos`
--

CREATE TABLE `contactos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `asunto` varchar(150) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish2_ci;

--
-- Volcado de datos para la tabla `contactos`
--

INSERT INTO `contactos` (`id`, `nombre`, `email`, `asunto`, `mensaje`, `fecha`) VALUES
(1, 'Jeremy Menjivar', 'Hola@gmail.com', 'Sabersfsrg', 'efdwfeg', '2026-02-04 22:56:29'),
(2, 'Jeremy Menjivar', 'Jejejeje@gmail.com', 'Importante', 'Practica', '2026-02-04 23:04:04'),
(3, 'Jeremy Menjivar', 'Abuelo@gmail.com', 'Saber', 'practifa', '2026-02-04 23:12:36'),
(4, 'Jeremy SebastiÃ¡n', 'julian@gmail.com', 'Prueba', 'Prueba', '2026-02-07 04:58:52'),
(5, 'Jeremy SebastiÃ¡n', 'julian@gmail.com', 'Prueba', 'Prueba', '2026-02-07 04:59:20'),
(6, 'Jeremy Menjivar', 'jeremysebastian2006@gmail.com', 'Saber', 'Prueba', '2026-02-13 20:48:03'),
(7, 'Jeremy Menjivar', 'jeremysebastian2006@gmail.com', 'Saber', 'Prueba', '2026-02-13 20:50:58'),
(8, 'Jeremy Menjivar', 'jeremysebastian2006@gmail.com', 'Saber', 'hgghjh', '2026-02-13 21:02:24'),
(9, 'Jeremy Menjivar', 'jeremysebastian2006@gmail.com', 'Saber', 'prueba', '2026-02-14 22:37:30'),
(10, 'Jeremy Menjivar', 'jeremysebastian2006@gmail.com', 'Saber', 'dvxfv', '2026-02-14 22:57:34'),
(11, 'Jeremy Menjivar', 'jeremysebastian2006@gmail.com', 'Prueba', 'Prueba', '2026-02-14 22:58:13'),
(12, 'Jeremy Sebastian', 'jeremysebastian2006@gmail.com', 'Saber mas', 'Necesito que me den mas informacion sobre la escuela atena', '2026-05-04 16:48:48'),
(13, 'Jeremy Menjivar', 'guevarajeremy682@gmail.com', 'Costo del curso', 'Buenas tardes, me gustaria saber cuales serian los costos del curso de conoterapia porfavor', '2026-06-14 23:08:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contenidos`
--

CREATE TABLE `contenidos` (
  `contenido_id` int(11) NOT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `material` varchar(255) DEFAULT NULL,
  `da_id` int(11) NOT NULL,
  `c_estado` varchar(10) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `contenidos`
--

INSERT INTO `contenidos` (`contenido_id`, `titulo`, `descripcion`, `material`, `da_id`, `c_estado`) VALUES
(1, 'Taller de Lectura Comprensiva', 'Este taller tiene como objetivo desarrollar las habilidades de comprension lectora de los estudiantes de cuarto grado. Se enfocara en estrategias para identificar ideas principales, hacer inferencias, y analizar personajes.', '665f189b47c51_PRIMERA PRUEBA EDITAR.xlsx', 3, '1'),
(2, 'Narrativa Infantil Salvadorena', 'Este modulo introduce a los estudiantes de cuarto grado a las principales obras de narrativa infantil producidas por autores de El Salvador. ', '668c5c10aed41_Antologia de Cuentos Infantiles Salvadorenos.docx', 3, '1'),
(3, 'Gramatica Inglesa Septimo', 'Lecciones interactivas que cubren los conceptos gramaticales fundamentales del idioma ingles, como tiempos verbales, estructura de oraciones y reglas ortograficas, dirigido a estudiantes de Septimo Grado.', '6694a6a601774_Gramatica Inglesa Septimo.pdf', 4, '0'),
(4, 'Conceptos de Matematicas', 'Lecciones interactivas que cubren los conceptos fundamentales de matematicas para estudiantes de Primer Grado, incluyendo numeros, operaciones basicas, figuras geometricas y mediciones.', '669475ebe07e2_Conceptos Basicos Matematicas.pdf', 1, '1'),
(6, 'Comprension Lectora Cuarto', 'Serie de ejercicios interactivos y actividades de practica que ayudan a desarrollar la comprension lectora en estudiantes de Cuarto Grado, cubriendo diferentes generos y niveles de complejidad.', '6696913d38eb3_Comprension Lectora Cuarto.pdf', 3, '0'),
(7, 'Conceptos de Sociales', 'Historia de El Salvador', '669f4e21aa986_conversación.docx', 4, '0'),
(8, 'Limpieza de oidos', 'Realiza el estudio de las imagens ', '69d2ca9752ae6_PENSUN DE MATERIA DE ATENEA ESCUELA.pdf', 10, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `customer`
--

CREATE TABLE `customer` (
  `CUST_ID` int(11) NOT NULL,
  `FIRST_NAME` varchar(50) DEFAULT NULL,
  `LAST_NAME` varchar(500) DEFAULT NULL,
  `PHONE_NUMBER` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `customer`
--

INSERT INTO `customer` (`CUST_ID`, `FIRST_NAME`, `LAST_NAME`, `PHONE_NUMBER`) VALUES
(39, 'Tigo', 'Km. 16.5 Carretera al Puerto de La Libertad, Tuscania Bussiness Park, Campus Tigo, Zaragoza, La Libertad, El Salvador', ' 2500-4600'),
(40, 'Grupo Digicel', 'Calle Chaparrastique lote 2A, Zona Industrial Santa Elena Antiguo Cuscatlan, La Libertad', '2504-3444'),
(41, 'Telefonica ', '63 Av Sur Y Alam Roosvelt Centro Financiero Gigante Torre B San Salvador - San Salvador.', '2257-4000'),
(42, 'Phoenix Group El Salvador', 'Col. Santa Matilde, Calle Las Mercedes, Casa No. 98, San Ramón, Mejicanos, San Salvador', '2556-9976'),
(43, 'Huawei Telecommunications El Salvador Ltd De C.V.', 'Edificio Avante Blvd. Luis Poma 6O.Nivel Torre Avante 6O.Nivel Antiguo Cuscatlán - La Libertad.', '2525-2600'),
(44, 'Gasolinera Uno', 'Colonia Jardines de Guadalupe Av Albert Einstein Cl Mediterráneo Santa Tecla - La Libertad.', '2273-9534'),
(48, 'Claro', 'Carretera Santa Tecla Km 10 1/2 Complejo Ex-incatel Edif B 1 Nivel Santa Tecla - La Libertad.', '2271 7166'),
(49, 'Delsur', 'Final 17 Av Norte y Cl al Boquerón la Libertad Primer Nivel Santa Tecla - La Libertad.', '2233 5600'),
(50, 'Excel Automotriz', 'Col Miramonte 51 Av Nte entre Alam Juan Pablo II y Cl Los Andes - Taller Excel Toyota Los Héroes San Salvador - San Salvador.', '7920 6192'),
(51, 'Grupo Q El Salvador', 'Blvd Los Próceres Col San Mateo Av Las Amapolas San Salvador - San Salvador.', '2248-6400'),
(52, 'ASESUISA', 'Torre Corporativa, Bambu City Center, Bulevar El Hipódromo y Avenida Las Magnolias, Colonia San Benito, San Salvador, El Salvador', '2298-8888'),
(53, 'GRUPO NSV.LTDA DE C.V.', 'PQ43+W39, 7a. Calle Pte. Bis, San Salvador', '2512-9632');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes_asignaturas`
--

CREATE TABLE `docentes_asignaturas` (
  `da_id` int(11) NOT NULL,
  `grado_id` varchar(100) DEFAULT NULL,
  `profesor_id` varchar(100) DEFAULT NULL,
  `materia_id` varchar(100) DEFAULT NULL,
  `periodo_id` varchar(100) DEFAULT NULL,
  `da_estado` varchar(10) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `docentes_asignaturas`
--

INSERT INTO `docentes_asignaturas` (`da_id`, `grado_id`, `profesor_id`, `materia_id`, `periodo_id`, `da_estado`) VALUES
(1, '1', '35', '1', '1', '1'),
(3, '2', '29', '2', '2', '1'),
(4, '3', '30', '3', '3', '1'),
(5, '3', '30', '7', '1', '0'),
(7, '1', '35', '1', '2', '0'),
(8, '2', '29', '2', '1', '0'),
(9, '1', '35', '1', '1', '0'),
(10, '2', '29', '8', '2', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dte_documents`
--

CREATE TABLE `dte_documents` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tipo_dte` varchar(10) NOT NULL DEFAULT '01',
  `numero_control` varchar(50) NOT NULL,
  `codigo_generacion` varchar(50) NOT NULL,
  `sello_recibido` varchar(120) DEFAULT NULL,
  `modelo_facturacion` varchar(50) NOT NULL DEFAULT 'Previo',
  `tipo_transmision` varchar(50) NOT NULL DEFAULT 'Normal',
  `version_json` int(11) NOT NULL DEFAULT 1,
  `ambiente` varchar(10) NOT NULL DEFAULT '00',
  `estado` varchar(50) NOT NULL DEFAULT 'PENDIENTE',
  `codigo_msg` varchar(20) DEFAULT NULL,
  `descripcion_msg` varchar(255) DEFAULT NULL,
  `fecha_emision` date NOT NULL,
  `hora_emision` time NOT NULL,
  `total_pagar` decimal(10,2) NOT NULL DEFAULT 0.00,
  `json_path` varchar(255) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `response_path` varchar(255) DEFAULT NULL,
  `modo` enum('simulation','test','production') NOT NULL DEFAULT 'simulation',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `dte_documents`
--

INSERT INTO `dte_documents` (`id`, `order_id`, `user_id`, `tipo_dte`, `numero_control`, `codigo_generacion`, `sello_recibido`, `modelo_facturacion`, `tipo_transmision`, `version_json`, `ambiente`, `estado`, `codigo_msg`, `descripcion_msg`, `fecha_emision`, `hora_emision`, `total_pagar`, `json_path`, `pdf_path`, `response_path`, `modo`, `created_at`, `updated_at`) VALUES
(2, 12, 29, '01', 'DTE-01-S001P001-000000000000001', 'E33EC8B3-9FA8-45DF-A0A0-82A47E2039EC', '5BCC9352C2E68E66D3DC345F8C24CC503074209C', 'Previo', 'Normal', 1, '00', 'PROCESADO SIMULADO', '001', 'RECIBIDO - SIMULADO', '2026-06-30', '00:00:21', 10.00, 'storage/dte/json/E33EC8B3-9FA8-45DF-A0A0-82A47E2039EC.json', 'storage/dte/pdf/E33EC8B3-9FA8-45DF-A0A0-82A47E2039EC.pdf', 'storage/dte/responses/E33EC8B3-9FA8-45DF-A0A0-82A47E2039EC_response.json', 'simulation', '2026-06-30 21:08:08', '2026-06-30 21:08:08'),
(4, 14, 29, '01', 'DTE-01-S001P001-000000000000002', '04EFEF80-63FA-4A22-AE6A-19196DF7978D', '25873BC19F5D4AED8665B763E08CAE8DA247F80B', 'Previo', 'Normal', 1, '00', 'PROCESADO SIMULADO', '001', 'RECIBIDO - SIMULADO', '2026-07-01', '00:00:11', 415.00, 'storage/dte/json/04EFEF80-63FA-4A22-AE6A-19196DF7978D.json', 'storage/dte/pdf/04EFEF80-63FA-4A22-AE6A-19196DF7978D.pdf', 'storage/dte/responses/04EFEF80-63FA-4A22-AE6A-19196DF7978D_response.json', 'simulation', '2026-07-01 11:16:57', '2026-07-01 11:16:57'),
(5, 15, 29, '01', 'DTE-01-S001P001-000000000000003', '815ABAB3-D60B-415E-89F2-12E253C1D900', 'F09AB473185FBA2D583CCC142048D867A4270E08', 'Previo', 'Normal', 1, '00', 'ERROR', '001', 'The number of elements in the type definition string must match the number of bind variables', '2026-07-01', '00:00:12', 10.00, 'storage/dte/json/815ABAB3-D60B-415E-89F2-12E253C1D900.json', 'storage/dte/pdf/815ABAB3-D60B-415E-89F2-12E253C1D900.pdf', 'storage/dte/responses/815ABAB3-D60B-415E-89F2-12E253C1D900_response.json', 'simulation', '2026-07-01 12:16:38', '2026-07-01 12:29:16'),
(6, 16, 29, '01', 'DTE-01-S001P001-000000000000004', '51E96BD5-506D-431E-9B49-24E6C18CC5B7', 'C96A000922D6D38FB5C3A00DB66754D685D88A8B', 'Previo', 'Normal', 1, '00', 'PROCESADO SIMULADO', '001', 'RECIBIDO - SIMULADO', '2026-07-01', '00:00:13', 45.00, 'storage/dte/json/51E96BD5-506D-431E-9B49-24E6C18CC5B7.json', 'storage/dte/pdf/51E96BD5-506D-431E-9B49-24E6C18CC5B7.pdf', 'storage/dte/responses/51E96BD5-506D-431E-9B49-24E6C18CC5B7_response.json', 'simulation', '2026-07-01 13:56:58', '2026-07-01 13:56:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dte_sequences`
--

CREATE TABLE `dte_sequences` (
  `id` int(11) NOT NULL,
  `tipo_dte` varchar(10) NOT NULL,
  `cod_estable` varchar(20) NOT NULL,
  `cod_punto_venta` varchar(20) NOT NULL,
  `current_number` bigint(20) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `dte_sequences`
--

INSERT INTO `dte_sequences` (`id`, `tipo_dte`, `cod_estable`, `cod_punto_venta`, `current_number`, `created_at`, `updated_at`) VALUES
(2, '01', 'S001', 'P001', 4, '2026-06-30 21:08:08', '2026-07-01 13:56:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dte_settings`
--

CREATE TABLE `dte_settings` (
  `id` int(11) NOT NULL,
  `mode` enum('simulation','test','production') NOT NULL DEFAULT 'simulation',
  `ambiente` varchar(10) NOT NULL DEFAULT '00',
  `emisor_nit` varchar(20) NOT NULL,
  `emisor_nrc` varchar(20) NOT NULL,
  `emisor_nombre` varchar(255) NOT NULL,
  `emisor_nombre_comercial` varchar(255) NOT NULL,
  `emisor_cod_actividad` varchar(20) NOT NULL,
  `emisor_desc_actividad` varchar(255) NOT NULL,
  `emisor_tipo_establecimiento` varchar(50) NOT NULL,
  `emisor_departamento` varchar(100) NOT NULL,
  `emisor_municipio` varchar(100) NOT NULL,
  `emisor_direccion` varchar(255) NOT NULL,
  `emisor_telefono` varchar(30) NOT NULL,
  `emisor_correo` varchar(150) NOT NULL,
  `cod_estable_mh` varchar(20) NOT NULL,
  `cod_estable` varchar(20) NOT NULL,
  `cod_punto_venta_mh` varchar(20) NOT NULL,
  `cod_punto_venta` varchar(20) NOT NULL,
  `api_user` varchar(150) DEFAULT NULL,
  `api_password_encrypted` text DEFAULT NULL,
  `certificate_path` varchar(255) DEFAULT NULL,
  `certificate_password_encrypted` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `dte_settings`
--

INSERT INTO `dte_settings` (`id`, `mode`, `ambiente`, `emisor_nit`, `emisor_nrc`, `emisor_nombre`, `emisor_nombre_comercial`, `emisor_cod_actividad`, `emisor_desc_actividad`, `emisor_tipo_establecimiento`, `emisor_departamento`, `emisor_municipio`, `emisor_direccion`, `emisor_telefono`, `emisor_correo`, `cod_estable_mh`, `cod_estable`, `cod_punto_venta_mh`, `cod_punto_venta`, `api_user`, `api_password_encrypted`, `certificate_path`, `certificate_password_encrypted`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'simulation', '00', '00000000000000', '0000000', 'ATENEA SIMULACION', 'ATENEA', '99999', 'FORMACION DIGITAL SIMULADA', 'CASA MATRIZ', 'SAN SALVADOR', 'SAN SALVADOR CENTRO', 'CONFIGURACION INICIAL DE SIMULACION', '0000-0000', 'simulacion@atenea.local', 'S001', 'S001', 'P001', 'P001', NULL, NULL, NULL, NULL, 1, '2026-06-30 20:07:10', '2026-06-30 20:07:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `employee`
--

CREATE TABLE `employee` (
  `EMPLOYEE_ID` int(11) NOT NULL,
  `FIRST_NAME` varchar(100) NOT NULL,
  `LAST_NAME` varchar(100) NOT NULL,
  `GENDER` varchar(50) NOT NULL,
  `EMAIL` varchar(150) NOT NULL,
  `PHONE_NUMBER` varchar(150) NOT NULL,
  `JOB_ID` int(11) NOT NULL,
  `HIRED_DATE` varchar(150) NOT NULL,
  `LOCATION_ID` varchar(150) NOT NULL,
  `E_ESTADO` varchar(10) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `employee`
--

INSERT INTO `employee` (`EMPLOYEE_ID`, `FIRST_NAME`, `LAST_NAME`, `GENDER`, `EMAIL`, `PHONE_NUMBER`, `JOB_ID`, `HIRED_DATE`, `LOCATION_ID`, `E_ESTADO`) VALUES
(11, 'Claudia Antonia', 'Serrano Mercado', 'Mujer', 'claudiaserrano@yahoo.com', '7152-6912', 3, '2024-01-15', '100', '1'),
(12, 'Aurora Celeste', 'Oliva Medina', 'Mujer', 'auroramedina@hotmail.com', '7892-7828', 3, '2024-01-16', '123', '1'),
(17, 'Alba Rosario ', 'Ortega Serrano', 'Mujer', 'albaortega@gmail.com', '6115-6821', 2, '2024-08-12', '194', '1'),
(27, 'Gabriela Estefania', 'Campos Morales', 'Mujer', 'gabriela.campos@yahoo.com', '7831-1234', 2, '2024-07-02', '151', '1'),
(28, 'Juliana Alejandra', 'Vargas Serrano', 'Mujer', 'juliana.vargas@gmail.com', '7854-2222', 3, '2024-05-17', '20', '1'),
(29, 'Diego Alejandro', 'Flores Ramos', 'Hombre', 'diego.flores@outlook.com', '7312-9900', 1, '2022-02-23', '145', '1'),
(30, 'Roberto Carlos', 'Cortez Guzman', 'Hombre', 'roberto.cortez@protonmail.com', '6677-1213', 1, '20-03-2024', '17', '1'),
(35, 'Ana Cristina', 'Hernandez Vega', 'Mujer', 'ana.hernandez@icloud.com', '2535-5854', 1, '2024-05-06', '224', '1'),
(36, 'Ana Lucia', 'Ramirez Gutierrez', 'Mujer', 'analucia.ramirez@hotmail.com', '4567-8901', 2, '2023-09-12', '62', '0'),
(37, 'Maria Fernanda', 'Gomez Rodriguez', 'Mujer', 'mariafernanda.gomez@gmail.com', '2234-5678', 3, '2024-12-12', '230', '0'),
(39, 'Jose Enrique', 'Mendez Flores', 'Hombre', 'joseenrique.mendez@outlook.com', '7890-1234', 2, '2023-06-01', '232', '1'),
(41, 'Juan Pablo', 'Diaz Palma', 'Hombre', 'juanpablo.diaz@email.com', '2345-6789', 1, '2023-05-10', '234', '0'),
(43, 'Sofia Alejandra', 'Sanchez Martinez', 'Mujer', 'sofia.sanchez@gmail.com', '7890-2345', 2, '2023-03-01', '236', '0'),
(44, 'Enrique Ernesto', 'Flores Gomez', 'Hombre', 'enrique.flores@outlook.com', '4567-8912', 1, '15-07-2023', '237', '0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes` (
  `ESTUDIANTE_ID` int(11) NOT NULL,
  `nombres_estudiante` varchar(100) DEFAULT NULL,
  `apellidos_estudiante` varchar(100) DEFAULT NULL,
  `direccion_estudiante` varchar(200) DEFAULT NULL,
  `correo_estudiante` varchar(200) DEFAULT NULL,
  `foto_estudiante` varchar(500) DEFAULT NULL,
  `fecha_nac_estudiante` varchar(100) DEFAULT NULL,
  `edad_estudiante` varchar(100) DEFAULT NULL,
  `genero_estudiante` varchar(100) DEFAULT NULL,
  `grado_id_estudiante` varchar(100) DEFAULT NULL,
  `carnet_estudiante` varchar(300) DEFAULT NULL,
  `numero_lista_estudiante` varchar(500) DEFAULT NULL,
  `info_medica_estudiante` varchar(500) DEFAULT NULL,
  `fecha_reg_estudiante` varchar(100) DEFAULT NULL,
  `u_acceso_estudiante` varchar(100) DEFAULT NULL,
  `nombres_encargado` varchar(100) DEFAULT NULL,
  `apellidos_encargado` varchar(100) DEFAULT NULL,
  `dui_encargado` varchar(300) DEFAULT NULL,
  `direccion_encargado` varchar(300) DEFAULT NULL,
  `correo_encargado` varchar(500) DEFAULT NULL,
  `trabajo_encargado` varchar(300) DEFAULT NULL,
  `numero_cel_encargado` varchar(100) DEFAULT NULL,
  `numero_tel_encargado` varchar(100) DEFAULT NULL,
  `genero_encargado` varchar(100) DEFAULT NULL,
  `fecha_nac_encargado` varchar(100) DEFAULT NULL,
  `estado_estudiante` varchar(10) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`ESTUDIANTE_ID`, `nombres_estudiante`, `apellidos_estudiante`, `direccion_estudiante`, `correo_estudiante`, `foto_estudiante`, `fecha_nac_estudiante`, `edad_estudiante`, `genero_estudiante`, `grado_id_estudiante`, `carnet_estudiante`, `numero_lista_estudiante`, `info_medica_estudiante`, `fecha_reg_estudiante`, `u_acceso_estudiante`, `nombres_encargado`, `apellidos_encargado`, `dui_encargado`, `direccion_encargado`, `correo_encargado`, `trabajo_encargado`, `numero_cel_encargado`, `numero_tel_encargado`, `genero_encargado`, `fecha_nac_encargado`, `estado_estudiante`) VALUES
(1, 'Anthony Odir', 'Lopez Guzman', 'Residencial Palo Alto, Casa L-26, Pasaje Azuara, Zaragoza', 'odirmiranda9@gmail.com', 'odirlopex.jpg', '2006-01-01', '18', 'Hombre', '1', '20220179', '1', 'Alergia al mani.', '28-05-2024', '01-06-2024', 'Claudia Patricia', 'Guzman de Lopez', '07094598-1', 'Residencial Palo Alto, Casa L-26, Pasaje Azuara, Zaragoza', 'clauclau118@yahoo.com', 'Jefa de control alimenticio - CODEX', '7841-7178', '2545-2547', 'Mujer', '1988-10-01', '1'),
(4, 'Luis Antonio', 'Ortiz Palacios', 'Barrio San Miguel, Calle a la Cascada, Huizucar', 'laopolo90@gmail.com', 'luisortiz.png', '2006-02-19', '18', 'Hombre', '2', '20220038', '3', 'N/A', '29-05-2024', NULL, 'Flor de Maria', 'Palacios Mejia', '1928391-4', 'Barrio San Miguel, Calle a la Cascada, Huizucar', 'florpalacios1209@hotmail.com', 'Secretaria Alcaldia de Zaragoza', '7850-6811', '2203-5655', 'Mujer', '1980-09-19', '1'),
(5, 'Milton Guillermo', 'Rivas Palacios', 'Residencial Girasoles, Senda 8, Casa 15', 'guillermorivas927@yahoo.com', 'miltonrivas.jpg', '2005-08-04', '18', 'Hombre', '3', '20220083', '1', 'N/A', '29-05-2024', NULL, 'Francisco Guillermo', 'Rivas Gomez', '01695390-7', 'Residencial Girasoles, Senda 8, Casa 15', 'guillermorivas4@gmail.com', 'Ingeniero electrico en SIETELSA', '6180-3145', '2217-9047', 'Hombre', '1974-08-08', '0'),
(6, 'Jose Alejandro', 'Martinez Hernandez', 'Colonia Las Flores, Pasaje 3, Casa #15, San Salvador', 'jose.martinez@gmail.com', 'josemartinez.jpg', '2009-04-12', '14', 'Hombre', '2', '63463634', '2', 'Alergia a los huevos.', '06-06-2024', NULL, 'Maria Luisa', 'Gomez de Martinez', '12345678-9', 'Colonia Las Flores, Pasaje 3, Casa #15, San Salvador', 'marialuisa@gmail.com', 'Ama de casa', '7123-4567', '2234-5678', 'Mujer', '1975-06-20', '1'),
(9, 'Maria Jose', 'Gomez Hernandez', 'Colonia Las Flores, Calle Principal, Casa #45, San Salvador', 'mariajose@gmail.com', 'mariajose.png', '2012-05-20', '12', 'Mujer', '2', '12345678', '1', 'Alergia al polen', '07-07-2024', NULL, 'Juan Carlos', 'Hernandez Perez', '12345678-9', 'Colonia Las Flores, Calle Principal, Casa #45, San Salvador', 'juancarlos@gmail.com', 'Empleado', '7890-1234', '2222-3333', 'Hombre', '1975-11-10', '1'),
(10, 'Oscar Fernando ', 'Herrera Rivera', 'Colonia Europa, Psj 5, Casa #1131', 'elprimobrawl14@gmail.com', 'blue gem.png', '2006-10-04', '18', 'Hombre', '3', '53363444', NULL, 'alergico', '23-07-2024', NULL, 'Wendy ', 'Rosales', '42423424-4', 'Colonia Europa, Psj 5, Casa #1131', 'wlisset15@gmail.com', 'Trabajadora', '4242-4242', '1231-3213', 'Mujer', '1987-03-04', '0'),
(11, 'Jeremy', 'Menjivar', 'Calle Chiltiupan Casa #27', 'jeremysebastian2006@gmail.com', 'iii2.jpg', '2006-07-29', '19', 'Hombre', '3', '88990087', NULL, 'Un crack para hacer cualquier cosa ', '19-01-2026', NULL, 'DAVID', 'MENJIVAR', '09097897-5', 'Calle Chiltiupan Casa #27', 'davidfrancisco@gmail.com', 'Administrador de oficinas', '4546-6465', '4543-5354', 'Hombre', '1992-12-12', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes_docentes`
--

CREATE TABLE `estudiantes_docentes` (
  `ed_id` int(11) NOT NULL,
  `estudiante_id` varchar(100) NOT NULL,
  `doc_asi_id` varchar(100) NOT NULL,
  `periodo_id` varchar(100) NOT NULL,
  `ed_estado` varchar(10) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `estudiantes_docentes`
--

INSERT INTO `estudiantes_docentes` (`ed_id`, `estudiante_id`, `doc_asi_id`, `periodo_id`, `ed_estado`) VALUES
(3, '5', '4', '3', '0'),
(5, '4', '3', '2', '1'),
(6, '6', '3', '2', '1'),
(8, '1', '1', '1', '0'),
(10, '9', '3', '2', '1'),
(12, '9', '4', '3', '1'),
(13, '9', '10', '2', '0'),
(14, '6', '10', '2', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluaciones`
--

CREATE TABLE `evaluaciones` (
  `evaluacion_id` int(11) NOT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha` varchar(100) DEFAULT NULL,
  `porcentaje` varchar(100) DEFAULT NULL,
  `contenido_id` int(11) NOT NULL,
  `evaluacion_estado` varchar(10) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `evaluaciones`
--

INSERT INTO `evaluaciones` (`evaluacion_id`, `titulo`, `descripcion`, `fecha`, `porcentaje`, `contenido_id`, `evaluacion_estado`) VALUES
(1, 'Prueba de Comprension Lectora', 'Esta prueba evaluara las habilidades de comprension lectora desarrolladas por los estudiantes en el Taller de Lectura Comprensiva. Incluira preguntas sobre ideas principales, inferencias y analisis de personajes.', '2024-07-21', '15', 1, '1'),
(2, 'Examen de Narrativa Infantil Salvadorena', 'Esta evaluacion pondra a prueba los conocimientos y habilidades de los estudiantes de cuarto grado adquiridos durante el modulo de \"Narrativa Infantil Salvadorena\". Incluira preguntas de comprension lectora sobre los cuentos y leyendas revisados en clase.', '2024-08-15', '20', 2, '1'),
(4, 'Prueba de Mitad de Periodo', 'Examen a mitad de periodo para evaluar el progreso en los conceptos basicos de matematicas para estudiantes de Primer Grado.', '2024-10-15', '25', 4, '1'),
(5, 'Evaluacion de Repaso', 'Examen de repaso integral para reforzar los conceptos fundamentales de matematicas cubiertos durante el periodo escolar para estudiantes de Primer Grado.', '2024-11-30', '15', 4, '0'),
(6, 'Corto de Lectura Comprensiva', 'Examen practico para evaluar las habilidades de comprension lectora de los estudiantes de Cuarto Grado, a traves de la lectura y analisis de diversos textos.', '2024-11-05', '30', 1, '0'),
(7, 'Conceptos de Matematicass', 'asdafasffsafafaf', '2025-10-05', '50', 2, ''),
(8, 'Conceptos de Matematicas', 'fsfdssdfdsffd', '2025-10-05', '50', 4, ''),
(9, 'Conceptos de Ciencias', 'Teorías y Conspiraciones sobre los animales y el universo.', '2024-10-15', '50', 2, '0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ev_entregadas`
--

CREATE TABLE `ev_entregadas` (
  `ev_entregada_id` int(11) NOT NULL,
  `evaluacion_id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `material` varchar(255) DEFAULT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `ev_entregada_estado` varchar(10) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `ev_entregadas`
--

INSERT INTO `ev_entregadas` (`ev_entregada_id`, `evaluacion_id`, `alumno_id`, `material`, `observacion`, `ev_entregada_estado`) VALUES
(1, 1, 4, '6695ed44af0f4_Prueba de Comprension - Ortiz.pdf', 'Observacion de la evaluacion.', '1'),
(3, 2, 4, '6695ed8450d71_Examen de Narrativa - Ortiz.pdf', 'Observacion Luis.', '1'),
(4, 2, 6, '6695ea58a0522_Prueba de Mitad - Jose.pdf', 'Adjunto archivo con las respuestas.', '1'),
(7, 4, 1, '6695ef224450d_Prueba de Mitad - Odir.pdf', 'Adjunto respuestas.', '0'),
(8, 2, 9, '6695ef77171ee_Prueba de Mitad - Majo.pdf', 'Adjunto respuestas.', '1'),
(9, 1, 9, '6696956a1ec2f_Prueba de Comprension - Majo.pdf', 'Adjunto respuestas.', '1'),
(13, 1, 6, '669737eceeb49_Prueba de Comprension - Jose.docx', 'Adjunto respuestas en el documento.', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facilities`
--

CREATE TABLE `facilities` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `orden` int(11) DEFAULT 1,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish2_ci;

--
-- Volcado de datos para la tabla `facilities`
--

INSERT INTO `facilities` (`id`, `titulo`, `descripcion`, `orden`, `estado`) VALUES
(1, 'Visión', 'Ser una institución educativa referente en la formación de profesionales en Naturopatía Holística, promoviendo el conocimiento responsable, ético y consciente de las terapias naturales, con una visión integral del ser humano y respeto por la salud y la vida.', 1, 1),
(2, 'Misión', 'Formar profesionales en Naturopatía Holística con una visión integral del ser humano, brindando educación ética, consciente y de calidad en terapias naturales. Nuestra misión es transmitir conocimiento sólido, responsable y aplicable, que contribuya al bienestar, la prevención y el cuidado de la salud desde un enfoque natural y humano.', 2, 1),
(3, 'Valores', 'Nos guiamos por valores fundamentales que constituyen el núcleo de nuestra formación. Promovemos el respeto por la vida y la naturaleza, fomentamos una visión integral del ser humano y cultivamos la ética, la conciencia y la responsabilidad en el ejercicio de las terapias naturales. En nuestra comunidad impulsamos el conocimiento con sentido humano, el respeto mutuo y el compromiso con una salud natural, consciente y digna.', 3, 1),
(4, 'Servicios', 'Ofrecemos formación integral en Naturopatía Holística mediante programas académicos, cursos y capacitaciones terapéuticas, orientados al desarrollo profesional y humano del estudiante. Brindamos educación teórica y práctica en terapias naturales, acompañada de formación ética, legal y deontológica, promoviendo un aprendizaje consciente en un entorno de respeto, responsabilidad y compromiso con la salud integral.', 4, 1),
(5, 'Historia', 'ATENEA Escuela de Naturopatía Holística nace como resultado de un proceso de búsqueda, aprendizaje y evolución en el campo de la salud natural. Desde sus inicios, surge con el propósito de ofrecer una formación consciente y responsable en terapias naturales, integrando conocimiento, ética y una visión holística del ser humano. Cada paso de su creación ha sido parte de un crecimiento constante orientado al bienestar integral y a la profesionalización de la naturopatía.', 5, 1),
(6, 'Equipo Educativo', 'Nuestro equipo educativo está conformado por profesionales capacitados en diversas áreas de la Naturopatía y las terapias holísticas, comprometidos con una enseñanza integral, ética y consciente. Trabajamos de manera cercana para acompañar a cada estudiante en su proceso de aprendizaje, promoviendo el conocimiento, la responsabilidad profesional y el respeto por la salud y la vida.', 6, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `galeria`
--

CREATE TABLE `galeria` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `categoria` varchar(50) NOT NULL DEFAULT 'general',
  `orden` int(11) NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish2_ci;

--
-- Volcado de datos para la tabla `galeria`
--

INSERT INTO `galeria` (`id`, `titulo`, `imagen`, `categoria`, `orden`, `estado`, `fecha_creacion`) VALUES
(1, 'Conoterapia', 'conoterapia_cajuela.jpeg', 'terapias', 1, 1, '2026-01-22 17:13:03'),
(2, 'Masaje Terapeutico', 'Masaje.jpeg', 'terapias', 2, 1, '2026-01-22 17:13:03'),
(3, 'Nutricion', '69725ec2808fa_1769103042.jpg', 'nutricion', 3, 1, '2026-01-22 17:13:03'),
(4, 'Naturismo', 'Naturismo.jpeg', 'general', 4, 1, '2026-01-22 17:13:03'),
(5, 'Digitopuntura', '69725fb23467c_1769103282.jpg', 'general', 5, 1, '2026-01-22 17:34:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grados`
--

CREATE TABLE `grados` (
  `G_ID` int(11) NOT NULL,
  `G_NAME` varchar(100) DEFAULT NULL,
  `G_ESTADO` varchar(10) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `grados`
--

INSERT INTO `grados` (`G_ID`, `G_NAME`, `G_ESTADO`) VALUES
(1, 'Primer Grado', '1'),
(2, 'Cuarto Grado', '1'),
(3, 'Septimo Grado', '1'),
(4, 'Octavo Grado', '0'),
(6, 'Noveno Grado', '0'),
(7, 'Segundo Grado \"B\"', '0'),
(8, 'Quinto Grado \"A\"', '0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `i_id` int(11) NOT NULL,
  `articulo` varchar(255) DEFAULT NULL,
  `cantidad` varchar(100) DEFAULT NULL,
  `i_estado` varchar(10) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`i_id`, `articulo`, `cantidad`, `i_estado`) VALUES
(1, 'Mesas', '20', '1'),
(2, 'Pupitres', '300', '1'),
(3, 'Sillas', '30', '1'),
(4, 'Computadora portatil', '25', '0'),
(5, 'Proyector', '12', '0'),
(7, 'Libros de texto', '150', '0'),
(8, 'Cables', '1300', '0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job`
--

CREATE TABLE `job` (
  `JOB_ID` int(11) NOT NULL,
  `JOB_TITLE` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `job`
--

INSERT INTO `job` (`JOB_ID`, `JOB_TITLE`) VALUES
(1, 'Docente'),
(2, 'Administrativo'),
(3, 'Logistica');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `employee` varchar(100) NOT NULL,
  `job` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL,
  `status` varchar(100) NOT NULL,
  `hour` varchar(100) NOT NULL,
  `date` varchar(100) NOT NULL,
  `maxhour` varchar(100) NOT NULL,
  `maxdate` varchar(100) NOT NULL,
  `j_estado` varchar(10) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `jobs`
--

INSERT INTO `jobs` (`id`, `employee`, `job`, `description`, `status`, `hour`, `date`, `maxhour`, `maxdate`, `j_estado`) VALUES
(30, '28', 'Guardar cables', 'Recolectar y guardar todos los cables HDMI del salon de Informatica.', 'Completado', '22:11', '2024-05-21', '06:00', '2024-05-31', '1'),
(35, '28', 'Ordenar laptops', 'Ordenar laptops de los distintos salones', 'Tiempo Excedido', '22:12', '2024-05-21', '20:00', '2024-05-18', '0'),
(36, '28', 'Armar muebles', 'Armar escritorios y armarios de 1er y 2do grado.', 'Tiempo Excedido', '18:07', '2024-07-05', '19:00', '2024-05-30', '1'),
(38, '27', 'Organizar inventario de equipos', 'Realizar el conteo e inventario de los equipos tecnologicos de la institucion.', 'Incompleto', 'Incompleto', 'Incompleto', '17:00', '2024-10-16', '0'),
(39, '11', 'Coordinar transporte de materiales', 'Coordinar el transporte y entrega de los materiales didacticos a las sedes remotas.', 'Incompleto', 'Incompleto', 'Incompleto', '16:30', '2024-11-01', '0'),
(40, '27', 'Armar muebles', 'fdsfsdffsd', 'Incompleto', 'Incompleto', 'Incompleto', '02:00', '2024-05-10', '0'),
(41, '27', 'Armar muebles', 'armar bmubels de manera correcta', 'Incompleto', 'Incompleto', 'Incompleto', '05:00', '2024-08-10', '0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `location`
--

CREATE TABLE `location` (
  `LOCATION_ID` int(11) NOT NULL,
  `PROVINCE` varchar(100) DEFAULT NULL,
  `CITY` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `location`
--

INSERT INTO `location` (`LOCATION_ID`, `PROVINCE`, `CITY`) VALUES
(1, 'Ahuachapan', 'Ahuachapan'),
(2, 'Ahuachapan', 'Apaneca'),
(3, 'Ahuachapan', 'Atiquizaya'),
(4, 'Ahuachapan', 'Concepcion de Ataco'),
(5, 'Ahuachapan', 'El Refugio'),
(6, 'Ahuachapan', 'Guaymango'),
(7, 'Ahuachapan', 'Jujutla'),
(8, 'Ahuachapan', 'San Francisco Menendez'),
(9, 'Ahuachapan', 'San Lorenzo'),
(10, 'Ahuachapan', 'San Pedro Puxtla'),
(11, 'Ahuachapan', 'Tacuba'),
(12, 'Ahuachapan', 'Turin'),
(13, 'Cabanas', 'Cinquera'),
(14, 'Cabanas', 'Dolores'),
(15, 'Cabanas', 'Guacotecti'),
(16, 'Cabanas', 'Ilobasco'),
(17, 'Cabanas', 'Jutiapa'),
(18, 'Cabanas', 'San Isidro'),
(19, 'Cabanas', 'Sensuntepeque'),
(20, 'Cabanas', 'Tejutepeque'),
(21, 'Cabanas', 'Victoria'),
(22, 'Chalatenango', 'Agua Caliente'),
(23, 'Chalatenango', 'Arcatao'),
(24, 'Chalatenango', 'Azacualpa'),
(25, 'Chalatenango', 'Chalatenango'),
(26, 'Chalatenango', 'Citala'),
(27, 'Chalatenango', 'Comalapa'),
(28, 'Chalatenango', 'Concepcion Quezaltepeque'),
(29, 'Chalatenango', 'Dulce Nombre de Maria'),
(30, 'Chalatenango', 'El Carrizal'),
(31, 'Chalatenango', 'El Paraiso'),
(32, 'Chalatenango', 'La Laguna'),
(33, 'Chalatenango', 'La Palma'),
(34, 'Chalatenango', 'La Reina'),
(35, 'Chalatenango', 'Las Flores'),
(36, 'Chalatenango', 'Las Vueltas'),
(37, 'Chalatenango', 'Nombre de Jesus'),
(38, 'Chalatenango', 'Nueva Concepcion'),
(39, 'Chalatenango', 'Nueva Trinidad'),
(40, 'Chalatenango', 'Ojos de Agua'),
(41, 'Chalatenango', 'Potonico'),
(42, 'Chalatenango', 'San Antonio de la Cruz'),
(43, 'Chalatenango', 'San Antonio Los Ranchos'),
(44, 'Chalatenango', 'San Fernando'),
(45, 'Chalatenango', 'San Francisco Lempa'),
(46, 'Chalatenango', 'San Francisco Morazan'),
(47, 'Chalatenango', 'San Ignacio'),
(48, 'Chalatenango', 'San Isidro Labrador'),
(49, 'Chalatenango', 'San Luis del Carmen'),
(50, 'Chalatenango', 'San Miguel de Mercedes'),
(51, 'Chalatenango', 'San Rafael'),
(52, 'Chalatenango', 'Santa Rita'),
(53, 'Chalatenango', 'Tejutla'),
(54, 'Cuscatlan', 'Candelaria'),
(55, 'Cuscatlan', 'Cojutepeque'),
(56, 'Cuscatlan', 'El Carmen'),
(57, 'Cuscatlan', 'El Rosario'),
(58, 'Cuscatlan', 'Monte San Juan'),
(59, 'Cuscatlan', 'Oratorio de Concepcion'),
(60, 'Cuscatlan', 'San Bartolome Perulapia'),
(61, 'Cuscatlan', 'San Cristobal'),
(62, 'Cuscatlan', 'San Jose Guayabal'),
(63, 'Cuscatlan', 'San Pedro Perulapan'),
(64, 'Cuscatlan', 'San Rafael Cedros'),
(65, 'Cuscatlan', 'San Ramon'),
(66, 'Cuscatlan', 'Santa Cruz Analquito'),
(67, 'Cuscatlan', 'Santa Cruz Michapa'),
(68, 'Cuscatlan', 'Suchitoto'),
(69, 'Cuscatlan', 'Tenancingo'),
(70, 'La Libertad', 'Antiguo Cuscatlan'),
(71, 'La Libertad', 'Chiltiupan'),
(72, 'La Libertad', 'Ciudad Arce'),
(73, 'La Libertad', 'Colon'),
(74, 'La Libertad', 'Comasagua'),
(75, 'La Libertad', 'Huizucar'),
(76, 'La Libertad', 'Jayaque'),
(77, 'La Libertad', 'Jicalapa'),
(78, 'La Libertad', 'La Libertad'),
(79, 'La Libertad', 'Nueva San Salvador'),
(80, 'La Libertad', 'Nuevo Cuscatlan'),
(81, 'La Libertad', 'San Juan Opico'),
(82, 'La Libertad', 'Quezaltepeque'),
(83, 'La Libertad', 'Sacacoyo'),
(84, 'La Libertad', 'San Jose Villanueva'),
(85, 'La Libertad', 'San Matias'),
(86, 'La Libertad', 'San Pablo Tacachico'),
(87, 'La Libertad', 'Talnique'),
(88, 'La Libertad', 'Tamanique'),
(89, 'La Libertad', 'Teotepeque'),
(90, 'La Libertad', 'Tepecoyo'),
(91, 'La Libertad', 'Zaragoza'),
(92, 'La Paz', 'Cuyultitan'),
(93, 'La Paz', 'El Rosario'),
(94, 'La Paz', 'Jerusalen'),
(95, 'La Paz', 'Mercedes La Ceiba'),
(96, 'La Paz', 'Olocuilta'),
(97, 'La Paz', 'Paraiso de Osorio'),
(98, 'La Paz', 'San Antonio Masahuat'),
(99, 'La Paz', 'San Emigdio'),
(100, 'La Paz', 'San Francisco Chinameca'),
(101, 'La Paz', 'San Juan Nonualco'),
(102, 'La Paz', 'San Juan Talpa'),
(103, 'La Paz', 'San Juan Tepezontes'),
(104, 'La Paz', 'San Luis La Herradura'),
(105, 'La Paz', 'San Luis Talpa'),
(106, 'La Paz', 'San Miguel Tepezontes'),
(107, 'La Paz', 'San Pedro Masahuat'),
(108, 'La Paz', 'San Pedro Nonualco'),
(109, 'La Paz', 'San Rafael Obrajuelo'),
(110, 'La Paz', 'Santa Maria Ostuma'),
(111, 'La Paz', 'Santiago Nonualco'),
(112, 'La Paz', 'Tapalhuaca'),
(113, 'La Paz', 'Zacatecoluca'),
(114, 'La Union', 'Anamoros'),
(115, 'La Union', 'Bolivar'),
(116, 'La Union', 'Concepcion de Oriente'),
(117, 'La Union', 'Conchagua'),
(118, 'La Union', 'El Carmen'),
(119, 'La Union', 'El Sauce'),
(120, 'La Union', 'Intipuca'),
(121, 'La Union', 'La Union'),
(122, 'La Union', 'Lislique'),
(123, 'La Union', 'Meanguera del Golfo'),
(124, 'La Union', 'Nueva Esparta'),
(125, 'La Union', 'Pasaquina'),
(126, 'La Union', 'Poloros'),
(127, 'La Union', 'San Alejo'),
(128, 'La Union', 'San Jose'),
(129, 'La Union', 'Santa Rosa de Lima'),
(130, 'La Union', 'Yayantique'),
(131, 'La Union', 'Yucuaiquin'),
(132, 'Morazan', 'Arambala'),
(133, 'Morazan', 'Cacaopera'),
(134, 'Morazan', 'Chilanga'),
(135, 'Morazan', 'Corinto'),
(136, 'Morazan', 'Delicias de Concepcion'),
(137, 'Morazan', 'El Divisadero'),
(138, 'Morazan', 'El Rosario'),
(139, 'Morazan', 'Gualococti'),
(140, 'Morazan', 'Guatajiagua'),
(141, 'Morazan', 'Joateca'),
(142, 'Morazan', 'Jocoaitique'),
(143, 'Morazan', 'Jocoro'),
(144, 'Morazan', 'Lolotiquillo'),
(145, 'Morazan', 'Meanguera'),
(146, 'Morazan', 'Osicala'),
(147, 'Morazan', 'Perquin'),
(148, 'Morazan', 'San Carlos'),
(149, 'Morazan', 'San Fernando'),
(150, 'Morazan', 'San Francisco Gotera'),
(151, 'Morazan', 'San Isidro'),
(152, 'Morazan', 'San Simon'),
(153, 'Morazan', 'Sensembra'),
(154, 'Morazan', 'Sociedad'),
(155, 'Morazan', 'Torola'),
(156, 'Morazan', 'Yamabal'),
(157, 'Morazan', 'Yoloaiquin'),
(158, 'San Miguel', 'Carolina'),
(159, 'San Miguel', 'Chapeltique'),
(160, 'San Miguel', 'Chinameca'),
(161, 'San Miguel', 'Chirilagua'),
(162, 'San Miguel', 'Ciudad Barrios'),
(163, 'San Miguel', 'Comacaran'),
(164, 'San Miguel', 'El Transito'),
(165, 'San Miguel', 'Lolotique'),
(166, 'San Miguel', 'Moncagua'),
(167, 'San Miguel', 'Nueva Guadalupe'),
(168, 'San Miguel', 'Nuevo Eden de San Juan'),
(169, 'San Miguel', 'Quelepa'),
(170, 'San Miguel', 'San Antonio del Mosco'),
(171, 'San Miguel', 'San Gerardo'),
(172, 'San Miguel', 'San Jorge'),
(173, 'San Miguel', 'San Luis de la Reina'),
(174, 'San Miguel', 'San Miguel'),
(175, 'San Miguel', 'San Rafael Oriente'),
(176, 'San Miguel', 'Sesori'),
(177, 'San Miguel', 'Uluazapa'),
(178, 'San Salvador', 'Aguilares'),
(179, 'San Salvador', 'Apopa'),
(180, 'San Salvador', 'Ayutuxtepeque'),
(181, 'San Salvador', 'Cuscatancingo'),
(182, 'San Salvador', 'Delgado'),
(183, 'San Salvador', 'El Paisnal'),
(184, 'San Salvador', 'Guazapa'),
(185, 'San Salvador', 'Ilopango'),
(186, 'San Salvador', 'Mejicanos'),
(187, 'San Salvador', 'Nejapa'),
(188, 'San Salvador', 'Panchimalco'),
(189, 'San Salvador', 'Rosario de Mora'),
(190, 'San Salvador', 'San Marcos'),
(191, 'San Salvador', 'San Martin'),
(192, 'San Salvador', 'San Salvador'),
(193, 'San Salvador', 'Santiago Texacuangos'),
(194, 'San Salvador', 'Santo Tomas'),
(195, 'San Salvador', 'Soyapango'),
(196, 'San Salvador', 'Tonacatepeque'),
(197, 'San Vicente', 'Apastepeque'),
(198, 'San Vicente', 'Guadalupe'),
(199, 'San Vicente', 'San Cayetano Istepeque'),
(200, 'San Vicente', 'San Esteban Catarina'),
(201, 'San Vicente', 'San Ildefonso'),
(202, 'San Vicente', 'San Lorenzo'),
(203, 'San Vicente', 'San Sebastian'),
(204, 'San Vicente', 'San Vicente'),
(205, 'San Vicente', 'Santa Clara'),
(206, 'San Vicente', 'Santo Domingo'),
(207, 'San Vicente', 'Tecoluca'),
(208, 'San Vicente', 'Tepetitan'),
(209, 'San Vicente', 'Verapaz'),
(210, 'Santa Ana', 'Candelaria de la Frontera'),
(211, 'Santa Ana', 'Chalchuapa'),
(212, 'Santa Ana', 'Coatepeque'),
(213, 'Santa Ana', 'El Congo'),
(214, 'Santa Ana', 'El Porvenir'),
(215, 'Santa Ana', 'Masahuat'),
(216, 'Santa Ana', 'Metapan'),
(217, 'Santa Ana', 'San Antonio Pajonal'),
(218, 'Santa Ana', 'San Sebastian Salitrillo'),
(219, 'Santa Ana', 'Santa Ana'),
(220, 'Santa Ana', 'Santa Rosa Guachipilin'),
(221, 'Santa Ana', 'Santiago de la Frontera'),
(222, 'Santa Ana', 'Texistepeque'),
(223, 'Sonsonate', 'Acajutla'),
(224, 'Sonsonate', 'Armenia'),
(225, 'Sonsonate', 'Caluco'),
(226, 'Sonsonate', 'Cuisnahuat'),
(227, 'Sonsonate', 'Izalco'),
(228, 'CabaÃ±as', 'Dolores'),
(229, 'Chalatenango', 'Nueva ConcepciÃ³n'),
(230, 'San Salvador', 'San Salvador'),
(231, 'La Libertad', 'Jayaque'),
(232, 'La Libertad', 'Jayaque'),
(233, 'Chalatenango', 'Chalatenango'),
(234, 'Ahuachapán', 'Ahuachapán'),
(235, 'Chalatenango', 'Chalatenango'),
(236, 'Chalatenango', 'Chalatenango'),
(237, 'Santa Ana', 'Santa Ana');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `mensaje_id` int(11) NOT NULL,
  `asignatura_id` int(11) DEFAULT NULL,
  `docente_id` int(11) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `tipo` varchar(20) DEFAULT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `mensajes`
--

INSERT INTO `mensajes` (`mensaje_id`, `asignatura_id`, `docente_id`, `mensaje`, `tipo`, `archivo`, `fecha`, `estado`) VALUES
(1, 8, 23, 'Hola chicos, les escribo para recordarle que el dia de hoy tenemos la clase en linea teorica', NULL, NULL, '2026-04-18 19:45:35', 1),
(2, 8, 23, 'Recuerden que es a las 8:00 pm hora salvadoreña', NULL, NULL, '2026-04-18 20:06:33', 1),
(3, 8, 23, 'Por cierto les compartire un documento para que se informen bien', NULL, NULL, '2026-04-18 20:07:07', 1),
(4, 8, 23, 'Les mando informacion de la imagen que promocionaremos para el curso de conoterapia', NULL, '1776650888_Mar 30, 2026, 06_38_47 PM.png', '2026-04-20 02:08:08', 1),
(5, 8, 23, 'Hola chicos les dejare un documento leanlo.', NULL, '1776652803_parcial_v1_minitienda.docx', '2026-04-20 02:40:03', 1),
(6, 8, 23, 'Hola chicos les comparto este video de youtube sobre la importancia de masticar bien los alimentos:\r\nhttps://youtu.be/HukOr1EXJQ8?si=zE1hbmt_6YklwhXL', NULL, NULL, '2026-04-20 02:56:45', 1),
(7, 8, 23, 'Esta pagina tambien\r\nhttps://pmc.ncbi.nlm.nih.gov/articles/PMC12348602/', NULL, NULL, '2026-04-20 02:58:12', 1),
(8, 2, 23, 'Holaaaaaaaaa, manana labo amigos', NULL, NULL, '2026-06-07 21:01:27', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas`
--

CREATE TABLE `notas` (
  `nota_id` int(11) NOT NULL,
  `id_ev_entregada` int(11) NOT NULL,
  `valor_nota` varchar(100) DEFAULT NULL,
  `fecha` varchar(255) DEFAULT NULL,
  `nota_estado` varchar(10) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `notas`
--

INSERT INTO `notas` (`nota_id`, `id_ev_entregada`, `valor_nota`, `fecha`, `nota_estado`) VALUES
(4, 1, '9.0', '2024-07-08 07:34:51', '1'),
(7, 3, '8.0', '2024-07-07 12:28:44', '1'),
(8, 8, '7.6', '2024-07-16 10:09:19', '1'),
(11, 7, '4.4', '2024-07-16 09:08:33', '0'),
(12, 9, '7.7', '2024-07-16 09:45:37', '1'),
(13, 4, '7.1', '2024-07-16 20:43:51', '1'),
(14, 13, '8.5', '2024-07-16 21:49:12', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias`
--

CREATE TABLE `noticias` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion_corta` text NOT NULL,
  `descripcion_completa` text NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `fecha_publicacion` date NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish2_ci;

--
-- Volcado de datos para la tabla `noticias`
--

INSERT INTO `noticias` (`id`, `titulo`, `descripcion_corta`, `descripcion_completa`, `imagen`, `fecha_publicacion`, `estado`, `fecha_creacion`) VALUES
(1, 'Naturopatia ', 'Calidad de la escuela', 'En ATENEA Escuela de NaturopatÃ­a HolÃ­stica, somos una instituciÃ³n dedicada a la formaciÃ³n integral en terapias naturales y salud holÃ­stica. Nos enfocamos en brindar educaciÃ³n de calidad que combine conocimiento acadÃ©mico, conciencia humana y Ã©tica profesional, creando un entorno de aprendizaje orientado al bienestar y al respeto por la vida.\r\n\r\nNuestra propuesta educativa se basa en los principios de la NaturopatÃ­a, promoviendo una visiÃ³n del ser humano como un todo: cuerpo, mente y energÃ­a. AcompaÃ±amos a nuestros estudiantes en su desarrollo profesional y personal, preparÃ¡ndolos para ejercer con responsabilidad, criterio y compromiso social.\"', 'noticia_6972a279886dc_1769120377.jpg', '2026-01-22', 1, '2026-01-22 18:29:15'),
(2, 'Conoterapia', 'Tus oidos limpios', 'La conoterapia es una prÃ¡ctica no respaldada mÃ©dicamente que implica introducir el extremo de una vela hueca (a menudo de cera de abeja) en el conducto auditivo, encender el otro extremo con la idea de que el fuego cree un vacÃ­o y extraiga la cera, aunque los mÃ©dicos advierten que es peligrosa y puede causar quemaduras, infecciones o daÃ±o permanente al oÃ­do, siendo la limpieza mÃ©dica profesional la forma segura de tratar el exceso de cerumen. ', 'noticia_6972a39b01db9_1769120667.jpg', '2024-05-15', 1, '2026-01-22 18:29:15'),
(4, 'Escuela Atenea', 'Escuela de naturopatia holistica', 'La naturopatÃ­a holÃ­stica es un sistema de medicina alternativa que trata a la persona en su totalidad (mente, cuerpo y espÃ­ritu), enfocÃ¡ndose en la capacidad de autocuraciÃ³n del cuerpo mediante mÃ©todos naturales como la dieta, el ejercicio, la fitoterapia y el manejo del estrÃ©s para restaurar el equilibrio y prevenir enfermedades, no solo tratar sÃ­ntomas. Se basa en principios como tratar la causa raÃ­z, apoyar las defensas naturales del cuerpo y usar terapias menos invasivas. ', 'noticia_6972a485aa6e4_1769120901.jpg', '2026-01-21', 1, '2026-01-22 22:28:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes`
--

CREATE TABLE `ordenes` (
  `id` int(11) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `stripe_session_id` varchar(255) DEFAULT NULL,
  `stripe_payment_intent` varchar(255) DEFAULT NULL,
  `billing_name` varchar(120) NOT NULL,
  `billing_email` varchar(150) NOT NULL,
  `billing_address` varchar(255) NOT NULL,
  `billing_tipo_documento` varchar(10) NOT NULL DEFAULT '',
  `billing_numero_documento` varchar(25) NOT NULL DEFAULT '',
  `billing_telefono` varchar(20) DEFAULT NULL,
  `billing_departamento` varchar(100) DEFAULT NULL,
  `billing_municipio` varchar(100) DEFAULT NULL,
  `billing_distrito` varchar(100) DEFAULT NULL,
  `billing_nrc` varchar(20) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('pending_payment','paid','cancelled','failed') NOT NULL DEFAULT 'pending_payment',
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ordenes`
--

INSERT INTO `ordenes` (`id`, `session_id`, `stripe_session_id`, `stripe_payment_intent`, `billing_name`, `billing_email`, `billing_address`, `billing_tipo_documento`, `billing_numero_documento`, `billing_telefono`, `billing_departamento`, `billing_municipio`, `billing_distrito`, `billing_nrc`, `subtotal`, `shipping_amount`, `total_amount`, `estado`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 'cart_698ca33f1fa363.07861548', 'cs_test_b1FksO2Pp6vCuqglyLNEV7bKzrjRjWr8hEDZQ5CHpt06IJouvaI22h4t4V', NULL, 'Milton', 'rivasmilton513@gmail.com', 'fasfsefsafs', '', '', NULL, NULL, NULL, NULL, NULL, 20.00, 5.00, 25.00, 'pending_payment', NULL, '2026-02-12 18:29:45', '2026-02-12 18:29:47'),
(2, 'cart_698ca33f1fa363.07861548', 'cs_test_b1AhluLySc1fKZoojVRkHyiAynBNhEL2PnnxvoLxoDLomq3MpNtEW65E30', NULL, 'Milton', 'rivasmilton513@gmail.com', 'tbt', '', '', NULL, NULL, NULL, NULL, NULL, 35.00, 5.00, 40.00, 'pending_payment', NULL, '2026-02-12 18:44:13', '2026-02-12 18:44:14'),
(3, 'cart_698ca33f1fa363.07861548', 'cs_test_b11e2bAQz6vvBXEqIbZYvJ5DsIF5GfQRp2MMmUF7Ubmtnv5cr5KSNBc9Dc', 'pi_3T0AWbE8YH5P1jJk0jc3co9V', 'Milton', 'rivasmilton513@gmail.com', 'chi', '', '', NULL, NULL, NULL, NULL, NULL, 140.00, 5.00, 145.00, 'paid', '2026-02-12 18:46:28', '2026-02-12 18:45:49', '2026-02-12 18:46:28'),
(4, 'cart_698ca33f1fa363.07861548', 'cs_test_b1DoRU08QVr2bSp7YfNvuZdKjTlxtgYchJSBQlw5Jbg98TNjSzZLom34A4', 'pi_3T0BAwE8YH5P1jJk1YrHmfiM', 'Guillermo', 'rivasmilton513@gmail.com', 'aja', '', '', NULL, NULL, NULL, NULL, NULL, 340.00, 5.00, 345.00, 'paid', '2026-02-12 19:28:09', '2026-02-12 19:27:31', '2026-02-12 19:28:09'),
(5, 'cart_698ca33f1fa363.07861548', 'cs_test_b170sjKJWQf9dqGaw8LiEUyqoobqQRnNdwbhl8oMGmCYd0my6YjWXGoHWd', 'pi_3T0BEkE8YH5P1jJk011boveB', 'Milton', 'rivasmilton513@gmail.com', 'ghtht', '', '', NULL, NULL, NULL, NULL, NULL, 20.00, 5.00, 25.00, 'paid', '2026-02-12 19:32:05', '2026-02-12 19:31:33', '2026-02-12 19:32:05'),
(6, 'cart_698ca33f1fa363.07861548', 'cs_test_b1amzh64KrR1hMZ9M5O3kN4MiNwqMjZThiodrOORBOW79RV1NdgTTQA7Eg', 'pi_3T0BPFE8YH5P1jJk0vZFpsn4', 'Milton', 'rivasmilton513@gmail.com', 'gtgdrgaer', '', '', NULL, NULL, NULL, NULL, NULL, 60.00, 5.00, 65.00, 'paid', '2026-02-12 19:42:56', '2026-02-12 19:42:14', '2026-02-12 19:42:56'),
(8, 'cart_698e9d3f8703b2.77187843', 'cs_test_b1deSVZDBucfpDYmCeUcWxMvseNPnva65kru3rhm1XNjdBI0nQghbZ86lL', 'pi_3T0DGrE8YH5P1jJk1lxLM0Ed', 'Jeremy Menjivar', 'jeremysebastian2006@gmail.com', 'ssssasa', '', '', NULL, NULL, NULL, NULL, NULL, 55.00, 5.00, 60.00, 'paid', '2026-02-12 21:42:22', '2026-02-12 21:41:31', '2026-02-12 21:42:22'),
(9, 'cart_698f918eccad74.60484986', 'cs_test_b1Fgx1scjL1m8t3iIdG4MEhwqOMYf8RHIYXcdPONHbmnlBWVjQDdRwx31L', 'pi_3T0TXaE8YH5P1jJk0lbLVlI1', 'Jeremy Menjivar', 'jeremysebastian2006@gmail.com', 'La libertad santa tecla', '', '', NULL, NULL, NULL, NULL, NULL, 190.00, 5.00, 195.00, 'paid', '2026-02-13 15:04:45', '2026-02-13 15:03:54', '2026-02-13 15:04:45'),
(10, 'cart_6a2f3493750b80.32781720', 'cs_test_b1sYCq7GVOGvQHLUWoMRQgqNHjc39QbL2dJ9qyDJNhE3weaoDelqn8XepE', NULL, 'Jeremy Menjivar', 'jeremysebastian2006@gmail.com', 'll', '', '', NULL, NULL, NULL, NULL, NULL, 50.00, 5.00, 55.00, 'pending_payment', NULL, '2026-06-14 17:10:11', '2026-06-14 17:10:13'),
(12, 'cart_6a448437594774.97720015', 'cs_test_b11Q7WvY3nhiMe1SdK1BxLcMwEGhWjdvWRATSLsBor5Lbn9DNT42RfaIaM', 'pi_3ToEVNE8YH5P1jJk1I484DD2', 'Milton Rivas', 'rivasmilton513@gmail.com', 'Santa tecla', '', '', NULL, NULL, NULL, NULL, NULL, 5.00, 5.00, 10.00, 'paid', '2026-06-30 21:08:08', '2026-06-30 21:07:11', '2026-06-30 21:08:08'),
(14, 'cart_6a454a2a73a4e1.77017237', 'cs_test_b1cUz7Gy2qADmHE9BUpM4RnbDQfahJ3JhpqdeVfWfTNTmKqK3IkBzbH0kB', 'pi_3ToRknE8YH5P1jJk1VpOk9Qd', 'Milton Rivas', 'rivasmilton513@gmail.com', 'Santa Tecla', 'DUI', '06956257-2', '61156808', 'La Libertad', 'Santa Tecla', NULL, NULL, 410.00, 5.00, 415.00, 'paid', '2026-07-01 11:16:57', '2026-07-01 11:16:19', '2026-07-01 11:16:57'),
(15, 'cart_6a455945107713.37565808', 'cs_test_b1yb37r4ZtekCadyp0KYoewLbpRsmpleGeYyk92welUaTgtfaMbrZKYl6o', 'pi_3ToSgYE8YH5P1jJk1UPqTmWa', 'Milton Rivas', 'rivasmilton513@gmail.com', 'Santa Tecla', 'DUI', '06956257-2', '61156808', 'La Libertad', 'La Libertad Sur', '', NULL, 5.00, 5.00, 10.00, 'paid', '2026-07-01 12:16:38', '2026-07-01 12:16:04', '2026-07-01 12:16:38'),
(16, 'cart_6a4570d41d0173.54545239', 'cs_test_b1f5Fa5P3wlU4d25riSSuoZ0NNPpSSG50jvwGLrvj7wIRjCbUob38zEALt', 'pi_3ToUFeE8YH5P1jJk0SfTAajS', 'Milton Rivas', 'rivasmilton513@gmail.com', 'Santa Tecla', 'DUI', '06695625-7', '61156808', 'La Libertad', 'La Libertad Sur', '', NULL, 40.00, 5.00, 45.00, 'paid', '2026-07-01 13:56:58', '2026-07-01 13:56:23', '2026-07-01 13:56:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_detalles`
--

CREATE TABLE `orden_detalles` (
  `id` int(11) NOT NULL,
  `orden_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `producto_nombre` varchar(255) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cantidad` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `orden_detalles`
--

INSERT INTO `orden_detalles` (`id`, `orden_id`, `producto_id`, `producto_nombre`, `precio_unitario`, `cantidad`, `subtotal`, `created_at`) VALUES
(1, 1, 1, 'Cajuela', 20.00, 1, 20.00, '2026-02-12 18:29:45'),
(2, 2, 2, 'Multivitaminico Natural', 35.00, 1, 35.00, '2026-02-12 18:44:13'),
(3, 3, 1, 'Cajuela', 20.00, 7, 140.00, '2026-02-12 18:45:49'),
(4, 4, 3, 'Guia Completa de Naturopatia', 40.00, 3, 120.00, '2026-02-12 19:27:31'),
(5, 4, 2, 'Multivitaminico Natural', 35.00, 4, 140.00, '2026-02-12 19:27:31'),
(6, 4, 1, 'Cajuela', 20.00, 4, 80.00, '2026-02-12 19:27:31'),
(7, 5, 1, 'Cajuela', 20.00, 1, 20.00, '2026-02-12 19:31:33'),
(8, 6, 1, 'Cajuela', 20.00, 1, 20.00, '2026-02-12 19:42:14'),
(9, 6, 3, 'Guia Completa de Naturopatia', 40.00, 1, 40.00, '2026-02-12 19:42:14'),
(12, 8, 1, 'Cajuela', 20.00, 1, 20.00, '2026-02-12 21:41:31'),
(13, 8, 2, 'Multivitaminico Natural', 35.00, 1, 35.00, '2026-02-12 21:41:31'),
(14, 9, 1, 'Cajuela', 20.00, 2, 40.00, '2026-02-13 15:03:54'),
(15, 9, 2, 'Multivitaminico Natural', 35.00, 2, 70.00, '2026-02-13 15:03:54'),
(16, 9, 3, 'Guia Completa de Naturopatia', 40.00, 2, 80.00, '2026-02-13 15:03:54'),
(17, 10, 1, 'Cajuela', 5.00, 3, 15.00, '2026-06-14 17:10:11'),
(18, 10, 2, 'Candelas', 35.00, 1, 35.00, '2026-06-14 17:10:11'),
(20, 12, 1, 'Cajuela', 5.00, 1, 5.00, '2026-06-30 21:07:11'),
(22, 14, 1, 'Cajuela', 5.00, 12, 60.00, '2026-07-01 11:16:19'),
(23, 14, 2, 'Candelas', 35.00, 10, 350.00, '2026-07-01 11:16:19'),
(24, 15, 1, 'Cajuela', 5.00, 1, 5.00, '2026-07-01 12:16:04'),
(25, 16, 3, 'Guia Completa de Naturopatia', 40.00, 1, 40.00, '2026-07-01 13:56:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_facturas`
--

CREATE TABLE `orden_facturas` (
  `id` int(11) NOT NULL,
  `orden_id` int(11) NOT NULL,
  `billing_email` varchar(150) NOT NULL,
  `pdf_path` varchar(255) NOT NULL,
  `email_status` enum('sent','failed') NOT NULL DEFAULT 'failed',
  `error_message` varchar(500) DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `orden_facturas`
--

INSERT INTO `orden_facturas` (`id`, `orden_id`, `billing_email`, `pdf_path`, `email_status`, `error_message`, `sent_at`, `created_at`, `updated_at`) VALUES
(1, 4, 'rivasmilton513@gmail.com', '', 'failed', 'Class \"PHPMailer\\PHPMailer\\PHPMailer\" not found', NULL, '2026-02-12 19:28:09', '2026-02-12 19:28:09'),
(2, 5, 'rivasmilton513@gmail.com', 'uploads/facturas/factura_orden_5_20260213_023205.pdf', 'sent', NULL, '2026-02-12 19:32:08', '2026-02-12 19:32:08', '2026-02-12 19:32:08'),
(3, 6, 'rivasmilton513@gmail.com', 'uploads/facturas/factura_orden_6_20260213_024256.pdf', 'sent', NULL, '2026-02-12 19:43:00', '2026-02-12 19:43:00', '2026-02-12 19:43:00'),
(4, 8, 'jeremysebastian2006@gmail.com', 'uploads/facturas/factura_orden_8_20260213_044223.pdf', 'sent', NULL, '2026-02-12 21:42:31', '2026-02-12 21:42:31', '2026-02-12 21:42:31'),
(5, 9, 'jeremysebastian2006@gmail.com', 'uploads/facturas/factura_orden_9_20260213_220449.pdf', 'sent', NULL, '2026-02-13 15:04:59', '2026-02-13 15:04:59', '2026-02-13 15:04:59'),
(6, 12, 'rivasmilton513@gmail.com', 'storage/dte/pdf/E33EC8B3-9FA8-45DF-A0A0-82A47E2039EC.pdf', 'sent', NULL, '2026-07-01 05:08:13', '2026-06-30 21:08:13', '2026-06-30 21:08:13'),
(7, 14, 'rivasmilton513@gmail.com', 'storage/dte/pdf/04EFEF80-63FA-4A22-AE6A-19196DF7978D.pdf', 'sent', NULL, '2026-07-01 19:17:00', '2026-07-01 11:17:00', '2026-07-01 11:17:00'),
(8, 15, 'rivasmilton513@gmail.com', 'storage/dte/pdf/815ABAB3-D60B-415E-89F2-12E253C1D900.pdf', 'sent', NULL, '2026-07-01 20:16:42', '2026-07-01 12:16:42', '2026-07-01 12:16:42'),
(9, 16, 'rivasmilton513@gmail.com', 'storage/dte/pdf/51E96BD5-506D-431E-9B49-24E6C18CC5B7.pdf', 'sent', NULL, '2026-07-01 21:57:01', '2026-07-01 13:57:01', '2026-07-01 13:57:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodo`
--

CREATE TABLE `periodo` (
  `p_id` int(11) NOT NULL,
  `p_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `periodo`
--

INSERT INTO `periodo` (`p_id`, `p_name`) VALUES
(1, 'Primer Trimestre'),
(2, 'Segundo Trimeste'),
(3, 'Tercer Trimestre');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `product`
--

CREATE TABLE `product` (
  `PRODUCT_ID` int(11) NOT NULL,
  `PRODUCT_CODE` varchar(20) NOT NULL,
  `NAME` varchar(50) DEFAULT NULL,
  `DESCRIPTION` varchar(250) NOT NULL,
  `QTY_STOCK` int(50) DEFAULT NULL,
  `ON_HAND` int(250) NOT NULL,
  `PRICE` int(50) DEFAULT NULL,
  `CATEGORY_ID` int(11) DEFAULT NULL,
  `SUPPLIER_ID` int(11) DEFAULT NULL,
  `DATE_STOCK_IN` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `product`
--

INSERT INTO `product` (`PRODUCT_ID`, `PRODUCT_CODE`, `NAME`, `DESCRIPTION`, `QTY_STOCK`, `ON_HAND`, `PRICE`, `CATEGORY_ID`, `SUPPLIER_ID`, `DATE_STOCK_IN`) VALUES
(178, '621002', 'Transformadores', 'Transformadores', 1, 1, NULL, 5, NULL, '2024-01-09'),
(179, '621002', 'Transformadores', 'Transformadores', 1, 1, NULL, 5, NULL, '2024-01-09'),
(180, '621002', 'Transformadores', 'Transformadores', 1, 1, NULL, 5, NULL, '2024-01-09'),
(181, '621002', 'Transformadores', 'Transformadores', 1, 1, NULL, 5, NULL, '2024-01-09'),
(182, '621002', 'Transformadores', 'Transformadores', 1, 1, NULL, 5, NULL, '2024-01-09'),
(183, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(184, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(185, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(186, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(187, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(188, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(189, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(190, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(191, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(192, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(193, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(194, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(195, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(196, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(197, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(198, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(199, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(200, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(201, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(202, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(203, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(204, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(205, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(206, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(207, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(208, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(209, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(210, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(211, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(212, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(213, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(214, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(215, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(216, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(217, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(218, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(219, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(220, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(221, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(222, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(223, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(224, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(225, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(226, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(227, '304833', 'Cableado eléctrico', 'Cableado eléctrico', 1, 1, NULL, 1, NULL, '2024-02-01'),
(228, '546447', 'Dispositivos de protección contra incendios.', 'Dispositivos de protección contra incendios.', 1, 1, NULL, 4, NULL, '2023-12-26'),
(229, '546447', 'Dispositivos de protección contra incendios.', 'Dispositivos de protección contra incendios.', 1, 1, NULL, 4, NULL, '2023-12-26'),
(230, '546447', 'Dispositivos de protección contra incendios.', 'Dispositivos de protección contra incendios.', 1, 1, NULL, 4, NULL, '2023-12-26'),
(231, '546447', 'Dispositivos de protección contra incendios.', 'Dispositivos de protección contra incendios.', 1, 1, NULL, 4, NULL, '2023-12-26'),
(232, '546447', 'Dispositivos de protección contra incendios.', 'Dispositivos de protección contra incendios.', 1, 1, NULL, 4, NULL, '2023-12-26'),
(233, '546447', 'Dispositivos de protección contra incendios.', 'Dispositivos de protección contra incendios.', 1, 1, NULL, 4, NULL, '2023-12-26'),
(234, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(235, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(236, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(237, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(238, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(239, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(240, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(241, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(242, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(243, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(244, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(245, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(246, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(247, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(248, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(249, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(250, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(251, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(252, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(253, '215489', 'Instrumentos de medición', 'Instrumentos de medición', 1, 1, NULL, 2, NULL, '2024-01-01'),
(254, '463061', 'Maquinaria de trabajo pesado', 'Maquinaria de trabajo pesado', 1, 1, NULL, 2, NULL, '2023-11-14'),
(255, '463061', 'Maquinaria de trabajo pesado', 'Maquinaria de trabajo pesado', 1, 1, NULL, 2, NULL, '2023-11-14'),
(256, '463061', 'Maquinaria de trabajo pesado', 'Maquinaria de trabajo pesado', 1, 1, NULL, 2, NULL, '2023-11-14'),
(257, '463061', 'Maquinaria de trabajo pesado', 'Maquinaria de trabajo pesado', 1, 1, NULL, 2, NULL, '2023-11-14'),
(258, '463061', 'Maquinaria de trabajo pesado', 'Maquinaria de trabajo pesado', 1, 1, NULL, 2, NULL, '2023-11-14'),
(259, '463061', 'Maquinaria de trabajo pesado', 'Maquinaria de trabajo pesado', 1, 1, NULL, 2, NULL, '2023-11-14'),
(260, '463061', 'Maquinaria de trabajo pesado', 'Maquinaria de trabajo pesado', 1, 1, NULL, 2, NULL, '2023-11-14'),
(261, '463061', 'Maquinaria de trabajo pesado', 'Maquinaria de trabajo pesado', 1, 1, NULL, 2, NULL, '2023-11-14'),
(262, '463061', 'Maquinaria de trabajo pesado', 'Maquinaria de trabajo pesado', 1, 1, NULL, 2, NULL, '2023-11-14'),
(263, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(264, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(265, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(266, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(267, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(268, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(269, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(270, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(271, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(272, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(273, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(274, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(275, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(276, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(277, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(278, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(279, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(280, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(281, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(282, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(283, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(284, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(285, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(286, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(287, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(288, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(289, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(290, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(291, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(292, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(293, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(294, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(295, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(296, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(297, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(298, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(299, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(300, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(301, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(302, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(303, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(304, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(305, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(306, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(307, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(308, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(309, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(310, '175477', 'Linternas y lámparas de cabeza.', 'Linternas y lámparas de cabeza.', 1, 1, NULL, 2, NULL, '2023-11-21'),
(311, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(312, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(313, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(314, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(315, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(316, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(317, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(318, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(319, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(320, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(321, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(322, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(323, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(324, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(325, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(326, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(327, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(328, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(329, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(330, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(331, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(332, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(333, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(334, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(335, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(336, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(337, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(338, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(339, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(340, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(341, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(342, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(343, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(344, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(345, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(346, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(347, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(348, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(349, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(350, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(351, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(352, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(353, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(354, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(355, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(356, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(357, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(358, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(359, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(360, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(361, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(362, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(363, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(364, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(365, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(366, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(367, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(368, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(369, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(370, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(371, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(372, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(373, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(374, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(375, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(376, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(377, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(378, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(379, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(380, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(381, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(382, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(383, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(384, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(385, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(386, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(387, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(388, '195214', 'Equipo de protección personal', 'cascos, guantes, gafas, etc.', 1, 1, NULL, 4, NULL, '2023-12-13'),
(389, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(390, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(391, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(392, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(393, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(394, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(395, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(396, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(397, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(398, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(399, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(400, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(401, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(402, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(403, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(404, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(405, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(406, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(407, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(408, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(409, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(410, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(411, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(412, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(413, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(414, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(415, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(416, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(417, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(418, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(419, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(420, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(421, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(422, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(423, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(424, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(425, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(426, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(427, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(428, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(429, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(430, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(431, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(432, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(433, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(434, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(435, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(436, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(437, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(438, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(439, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(440, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(441, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(442, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(443, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(444, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(445, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(446, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(447, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(448, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(449, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(450, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(451, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(452, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(453, '127770', 'Materiales para instalaciones de energía ininterru', 'Materiales para instalaciones de energía ininterrumpida (UPS).', 1, 1, NULL, 1, NULL, '2024-01-11'),
(454, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(455, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(456, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(457, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(458, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(459, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(460, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(461, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(462, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(463, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(464, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(465, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(466, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(467, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(468, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(469, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(470, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(471, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(472, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(473, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(474, '335105', 'Tableros de distribución eléctrica.', 'Tableros de distribución eléctrica.', 1, 1, NULL, 1, NULL, '2023-12-20'),
(475, '977560', 'Protectores de sobretensión.', 'Protectores de sobretensión.', 1, 1, NULL, 4, NULL, '2024-01-01'),
(476, '977560', 'Protectores de sobretensión.', 'Protectores de sobretensión.', 1, 1, NULL, 4, NULL, '2024-01-01'),
(477, '977560', 'Protectores de sobretensión.', 'Protectores de sobretensión.', 1, 1, NULL, 4, NULL, '2024-01-01'),
(478, '977560', 'Protectores de sobretensión.', 'Protectores de sobretensión.', 1, 1, NULL, 4, NULL, '2024-01-01'),
(479, '977560', 'Protectores de sobretensión.', 'Protectores de sobretensión.', 1, 1, NULL, 4, NULL, '2024-01-01'),
(480, '977560', 'Protectores de sobretensión.', 'Protectores de sobretensión.', 1, 1, NULL, 4, NULL, '2024-01-01'),
(481, '977560', 'Protectores de sobretensión.', 'Protectores de sobretensión.', 1, 1, NULL, 4, NULL, '2024-01-01'),
(482, '977560', 'Protectores de sobretensión.', 'Protectores de sobretensión.', 1, 1, NULL, 4, NULL, '2024-01-01'),
(483, '977560', 'Protectores de sobretensión.', 'Protectores de sobretensión.', 1, 1, NULL, 4, NULL, '2024-01-01'),
(484, '977560', 'Protectores de sobretensión.', 'Protectores de sobretensión.', 1, 1, NULL, 4, NULL, '2024-01-01'),
(485, '977560', 'Protectores de sobretensión.', 'Protectores de sobretensión.', 1, 1, NULL, 4, NULL, '2024-01-01'),
(486, '977560', 'Protectores de sobretensión.', 'Protectores de sobretensión.', 1, 1, NULL, 4, NULL, '2024-01-01'),
(487, '977560', 'Protectores de sobretensión.', 'Protectores de sobretensión.', 1, 1, NULL, 4, NULL, '2024-01-01'),
(488, '977560', 'Protectores de sobretensión.', 'Protectores de sobretensión.', 1, 1, NULL, 4, NULL, '2024-01-01'),
(489, '977560', 'Protectores de sobretensión.', 'Protectores de sobretensión.', 1, 1, NULL, 4, NULL, '2024-01-01'),
(490, '977560', 'Protectores de sobretensión.', 'Protectores de sobretensión.', 1, 1, NULL, 4, NULL, '2024-01-01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `descripcion_corta` varchar(250) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `precio_descuento` decimal(10,2) DEFAULT NULL,
  `imagen` varchar(255) NOT NULL,
  `imagen2` varchar(255) DEFAULT NULL,
  `imagen3` varchar(255) DEFAULT NULL,
  `categoria_id` int(11) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `destacado` tinyint(1) DEFAULT 0,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish2_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `descripcion_corta`, `precio`, `precio_descuento`, `imagen`, `imagen2`, `imagen3`, `categoria_id`, `stock`, `destacado`, `estado`, `fecha_creacion`) VALUES
(1, 'Cajuela', 'Cajuela para usarla en las conoterapias, producto innovador y de varios usos. ', 'Uso para la conoterapia', 5.00, NULL, 'producto_6997b1e59b8c4_1771549157.jpg', NULL, NULL, 2, 20, 1, 1, '2026-01-31 22:28:03'),
(2, 'Candelas', 'Suplemento multivitaminico elaborado con extractos naturales de frutas y vegetales. Fortalece el sistema inmunologico y aporta energia.', 'Complejo vitaminico de origen natural', 35.00, NULL, 'producto_vitaminas.jpg', NULL, NULL, 1, 83, 1, 1, '2026-01-31 22:28:03'),
(3, 'Guia Completa de Naturopatia', 'Libro completo sobre los principios de la naturopatia, con tecnicas practicas, recetas y tratamientos naturales para diversas afecciones.', 'Manual practico de naturopatia holistica', 45.00, 40.00, 'producto_libro.jpg', NULL, NULL, 3, 23, 0, 1, '2026-01-31 22:28:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programas_educativos`
--

CREATE TABLE `programas_educativos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion_corta` text NOT NULL,
  `descripcion_completa` text NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `nivel` varchar(50) NOT NULL,
  `instructor` varchar(100) NOT NULL,
  `orden` int(11) NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish2_ci;

--
-- Volcado de datos para la tabla `programas_educativos`
--

INSERT INTO `programas_educativos` (`id`, `titulo`, `descripcion_corta`, `descripcion_completa`, `imagen`, `nivel`, `instructor`, `orden`, `estado`, `fecha_creacion`) VALUES
(1, 'Introduccion a la Naturopatia', 'Curso fundamental que introduce los principios basicos de la naturopatia holistica y las bases del autocuidado natural.', 'Curso fundamental que introduce los principios basicos de la naturopatia holistica y las bases del autocuidado natural. Aprenderas sobre la filosofia de la medicina natural, los pilares de la salud integral y como aplicar estos conocimientos en tu vida diaria. Este programa es ideal para quienes desean iniciar su camino en la naturopatia.', 'programa_6976e84aafd1d_1769400394.jpg', 'Basico', 'Dra. Maria Rodriguez', 1, 1, '2026-01-26 03:50:29'),
(2, 'Terapias Naturales Avanzadas', 'Programa integral que profundiza en tecnicas terapeuticas naturales, fitoterapia y tratamientos holisticos.', 'Programa integral que profundiza en tecnicas terapeuticas naturales, fitoterapia y tratamientos holisticos. Combina conocimiento teorico con practica aplicada, preparando a los estudiantes para ejercer como terapeutas naturales con responsabilidad, criterio y compromiso social. Incluye modulos de nutricion holistica y terapias complementarias.', 'programa_6976e886e2b54_1769400454.jpg', 'Intermedio', 'Lic. Carlos Mendez', 2, 1, '2026-01-26 03:50:29'),
(3, 'Especializacion en Naturopatia Holistica', 'Formacion profesional avanzada en naturopatia, desarrollando habilidades de diagnostico y tratamiento integral.', 'Formacion profesional avanzada en naturopatia, desarrollando habilidades de diagnostico y tratamiento integral. Este programa prepara a terapeutas especializados para comprender, acompanar y contribuir positivamente al bienestar integral de las personas. Fomentamos el pensamiento critico, la responsabilidad profesional y la autonomia terapeutica.', 'programa_6976e8bae2ba5_1769400506.jpg', 'Avanzado', 'Dr. Juan Perez', 3, 1, '2026-01-26 03:50:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `public_users`
--

CREATE TABLE `public_users` (
  `PUBLIC_USER_ID` int(11) NOT NULL,
  `USER_ID` int(11) NOT NULL,
  `FIRST_NAME` varchar(100) NOT NULL,
  `LAST_NAME` varchar(100) NOT NULL,
  `EMAIL` varchar(150) NOT NULL,
  `PHONE_NUMBER` varchar(25) DEFAULT NULL,
  `BIRTHDATE` date DEFAULT NULL,
  `PROFILE_PHOTO` varchar(255) DEFAULT NULL,
  `GOOGLE_ID` varchar(191) DEFAULT NULL,
  `GOOGLE_EMAIL` varchar(150) DEFAULT NULL,
  `BILLING_NAME` varchar(150) DEFAULT NULL,
  `BILLING_EMAIL` varchar(150) DEFAULT NULL,
  `TIPO_DOCUMENTO` varchar(10) DEFAULT NULL,
  `NUMERO_DOCUMENTO` varchar(25) DEFAULT NULL,
  `BILLING_DEPARTAMENTO` varchar(100) DEFAULT NULL,
  `BILLING_MUNICIPIO` varchar(100) DEFAULT NULL,
  `BILLING_DISTRITO` varchar(100) DEFAULT NULL,
  `BILLING_DIRECCION` text DEFAULT NULL,
  `BILLING_NRC` varchar(20) DEFAULT NULL,
  `BILLING_PROFILE_COMPLETED` tinyint(1) NOT NULL DEFAULT 0,
  `PLAN_STATUS` varchar(30) NOT NULL DEFAULT 'pending',
  `ACCOUNT_STATUS` tinyint(1) NOT NULL DEFAULT 1,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `public_users`
--

INSERT INTO `public_users` (`PUBLIC_USER_ID`, `USER_ID`, `FIRST_NAME`, `LAST_NAME`, `EMAIL`, `PHONE_NUMBER`, `BIRTHDATE`, `PROFILE_PHOTO`, `GOOGLE_ID`, `GOOGLE_EMAIL`, `BILLING_NAME`, `BILLING_EMAIL`, `TIPO_DOCUMENTO`, `NUMERO_DOCUMENTO`, `BILLING_DEPARTAMENTO`, `BILLING_MUNICIPIO`, `BILLING_DISTRITO`, `BILLING_DIRECCION`, `BILLING_NRC`, `BILLING_PROFILE_COMPLETED`, `PLAN_STATUS`, `ACCOUNT_STATUS`, `CREATED_AT`, `UPDATED_AT`) VALUES
(1, 29, 'Milton', 'Rivas', 'rivasmilton513@gmail.com', '61156808', '2005-08-24', 'uploads/perfiles/perfil_usuario_29_20260701_201520_2e78a090.jpg', '113123385244599858097', 'rivasmilton513@gmail.com', 'Milton Rivas', 'rivasmilton513@gmail.com', 'DUI', '06695625-7', 'La Libertad', 'La Libertad Sur', '', 'Santa Tecla', NULL, 1, 'pending', 1, '2026-06-16 01:48:08', '2026-07-01 19:56:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transaction_details`
--

CREATE TABLE `transaction_details` (
  `ID` int(11) NOT NULL,
  `TRANS_D_ID` varchar(250) NOT NULL,
  `PRODUCTS` varchar(250) NOT NULL,
  `QTY` varchar(250) NOT NULL,
  `PRICE` varchar(250) NOT NULL,
  `EMPLOYEE` varchar(250) NOT NULL,
  `ROLE` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `transaction_details`
--

INSERT INTO `transaction_details` (`ID`, `TRANS_D_ID`, `PRODUCTS`, `QTY`, `PRICE`, `EMPLOYEE`, `ROLE`) VALUES
(7, '0318160336', 'Lenovo ideapad 20059', '2', '32999', 'Prince Ly', 'Manager'),
(8, '0318160336', 'Predator Helios 300 Gaming Laptop', '5', '77850', 'Prince Ly', 'Manager'),
(9, '0318160336', 'A4tech OP-720', '6', '289', 'Prince Ly', 'Manager'),
(10, '0318160622', 'Newmen E120', '2', '550', 'Prince Ly', 'Manager'),
(11, '0318160622', 'A4tech OP-720', '3', '289', 'Prince Ly', 'Manager'),
(12, '0318170309', 'Newmen E120', '1', '550', 'Prince Ly', 'Manager'),
(13, '0318170352', 'Predator Helios 300 Gaming Laptop', '1', '77850', 'Prince Ly', 'Manager'),
(14, '0318170511', 'Fantech EG1', '2', '859', 'Prince Ly', 'Manager'),
(15, '0318170524', 'Fantech EG1', '2', '859', 'Prince Ly', 'Manager'),
(16, '0318170551', 'Fantech EG1', '2', '859', 'Prince Ly', 'Manager'),
(17, '0318170624', 'A4tech OP-720', '1', '289', 'Prince Ly', 'Manager'),
(18, '0318170825', 'A4tech OP-720', '1', '289', 'Prince Ly', 'Manager'),
(19, '0318170825', 'Fantech EG1', '1', '859', 'Prince Ly', 'Manager'),
(20, '0318194016', 'Newmen E120', '10', '550', 'Josuey', 'Cashier'),
(21, '0714141333', 'Newmen E120', '1', '550', 'Prince Ly', 'Manager'),
(22, '0714155515', 'Newmen E120', '1', '550', 'Erick', 'Manager'),
(23, '0714160904', 'Newmen E120', '1', '550', 'Erick', 'Manager'),
(24, '0714160904', 'A4tech OP-720', '2', '289', 'Erick', 'Manager'),
(25, '0714161034', 'Newmen E120', '1', '550', 'Josuey', 'Cashier'),
(26, '110851516', 'Newmen E120', '1', '550', 'Milton', 'Manager');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `type`
--

CREATE TABLE `type` (
  `TYPE_ID` int(11) NOT NULL,
  `TYPE` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `type`
--

INSERT INTO `type` (`TYPE_ID`, `TYPE`) VALUES
(1, 'Admin'),
(2, 'Personal'),
(3, 'Estudiante'),
(4, 'Docente'),
(5, 'SuperAdmin');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `EMPLOYEE_ID` int(11) DEFAULT NULL,
  `USERNAME` varchar(50) DEFAULT NULL,
  `PASSWORD` varchar(50) DEFAULT NULL,
  `TYPE_ID` int(11) DEFAULT NULL,
  `ESTUDIANTE_ID` int(11) DEFAULT NULL,
  `U_ESTADO` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`ID`, `EMPLOYEE_ID`, `USERNAME`, `PASSWORD`, `TYPE_ID`, `ESTUDIANTE_ID`, `U_ESTADO`) VALUES
(14, 17, 'admin', 'd033e22ae348aeb5660fc2140aec35850c4da997', 1, NULL, 1),
(22, 28, 'personal', 'db69db5fb56cc44b69cd510978cb3277ce3a4102', 2, NULL, 1),
(23, 29, 'docente', '40a0ef5ed7906a72ffd24c86ed6ba43c2b8735e8', 4, NULL, 1),
(26, NULL, 'alumno', '684b10ab8da41b83690bd96f9a846b9814d8a288', 3, 6, 1),
(27, NULL, 'alumnotest', 'd033e22ae348aeb5660fc2140aec35850c4da997', 3, 4, 1),
(28, 27, 'superadmin', '889a3a791b3875cfae413574b53da4bb8a90d53e', 5, NULL, 1),
(29, NULL, 'Miltonrivas206', '100dd657322ba3977989d16ce6033097af2af001', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `vehicle_license` varchar(100) NOT NULL,
  `vehicle_model` varchar(100) NOT NULL,
  `vehicle_attendant` varchar(100) DEFAULT NULL,
  `vehicle_image` varchar(500) NOT NULL,
  `v_estado` varchar(10) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `vehicles`
--

INSERT INTO `vehicles` (`id`, `vehicle_license`, `vehicle_model`, `vehicle_attendant`, `vehicle_image`, `v_estado`) VALUES
(25, 'P234-56', 'Toyota Corolla', NULL, 'imagenes_vehiculos/Toyota Corolla.png', '1'),
(28, 'P012329', 'Ford Mustang', '28', 'imagenes_vehiculos/Ford Mustang.jpg', '1'),
(30, 'P345672', 'Chevrolet Camaro', '28', 'imagenes_vehiculos/Chevrolet Camaro.jpg', '0'),
(33, 'HZ8A02B', 'Volkswagen Golf', NULL, 'imagenes_vehiculos/Volkswagen Golf.jpg', '0'),
(34, 'LS1234', 'Toyota Corolla 2020', '12', 'imagenes_vehiculos/Toyota Corolla 2020.jpg', '1'),
(35, 'BQ9012', 'Hyundai Tucson 2022', NULL, 'imagenes_vehiculos/Hyundai Tucson 2022.jpg', '0'),
(36, 'FT3456', 'Nissan Versa 2021', '27', 'imagenes_vehiculos/Nissan Versa 2021 - 2.jpg', '1'),
(39, '9129JSD', 'Chevrolet 22', NULL, 'imagenes_vehiculos/image.png', '0'),
(40, '9129JAS', 'Chevrolet 23', NULL, 'imagenes_vehiculos/mochila.png', '0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `videos`
--

CREATE TABLE `videos` (
  `video_id` int(11) NOT NULL,
  `contenido_id` int(11) NOT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `youtube_id` varchar(50) NOT NULL,
  `estado` tinyint(4) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `videos`
--

INSERT INTO `videos` (`video_id`, `contenido_id`, `titulo`, `descripcion`, `youtube_id`, `estado`, `fecha_creacion`) VALUES
(2, 2, 'La conoterapia', 'Video explicativo de la importancia de la conoterapia', 'HJQbsBJBfhM', 1, '2026-04-05 04:26:24'),
(3, 8, 'Estudio de limpieza', 'Ver el video y sacar los datos mas importantes', 'U3GtmsyHKWQ', 1, '2026-04-05 20:51:11');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `about`
--
ALTER TABLE `about`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD PRIMARY KEY (`ACTIVIDAD_ID`);

--
-- Indices de la tabla `archivos`
--
ALTER TABLE `archivos`
  ADD PRIMARY KEY (`a_id`);

--
-- Indices de la tabla `asignaturas`
--
ALTER TABLE `asignaturas`
  ADD PRIMARY KEY (`ASIGNATURA_ID`);

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `categorias_productos`
--
ALTER TABLE `categorias_productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`CATEGORY_ID`);

--
-- Indices de la tabla `configmail`
--
ALTER TABLE `configmail`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `contenidos`
--
ALTER TABLE `contenidos`
  ADD PRIMARY KEY (`contenido_id`),
  ADD KEY `pm_id` (`da_id`);

--
-- Indices de la tabla `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`CUST_ID`);

--
-- Indices de la tabla `docentes_asignaturas`
--
ALTER TABLE `docentes_asignaturas`
  ADD PRIMARY KEY (`da_id`);

--
-- Indices de la tabla `dte_documents`
--
ALTER TABLE `dte_documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_dte_documents_order_id` (`order_id`),
  ADD UNIQUE KEY `uq_dte_documents_numero_control` (`numero_control`),
  ADD UNIQUE KEY `uq_dte_documents_codigo_generacion` (`codigo_generacion`),
  ADD KEY `idx_dte_documents_estado` (`estado`),
  ADD KEY `idx_dte_documents_modo` (`modo`),
  ADD KEY `idx_dte_documents_fecha` (`fecha_emision`);

--
-- Indices de la tabla `dte_sequences`
--
ALTER TABLE `dte_sequences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_dte_sequences_scope` (`tipo_dte`,`cod_estable`,`cod_punto_venta`);

--
-- Indices de la tabla `dte_settings`
--
ALTER TABLE `dte_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dte_settings_active` (`is_active`);

--
-- Indices de la tabla `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`EMPLOYEE_ID`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`ESTUDIANTE_ID`);

--
-- Indices de la tabla `estudiantes_docentes`
--
ALTER TABLE `estudiantes_docentes`
  ADD PRIMARY KEY (`ed_id`);

--
-- Indices de la tabla `evaluaciones`
--
ALTER TABLE `evaluaciones`
  ADD PRIMARY KEY (`evaluacion_id`),
  ADD KEY `contenido_id` (`contenido_id`);

--
-- Indices de la tabla `ev_entregadas`
--
ALTER TABLE `ev_entregadas`
  ADD PRIMARY KEY (`ev_entregada_id`),
  ADD KEY `evaluacion_id` (`evaluacion_id`),
  ADD KEY `alumno_id` (`alumno_id`);

--
-- Indices de la tabla `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `galeria`
--
ALTER TABLE `galeria`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `grados`
--
ALTER TABLE `grados`
  ADD PRIMARY KEY (`G_ID`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`i_id`);

--
-- Indices de la tabla `job`
--
ALTER TABLE `job`
  ADD PRIMARY KEY (`JOB_ID`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`LOCATION_ID`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`mensaje_id`);

--
-- Indices de la tabla `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`nota_id`),
  ADD KEY `id_ev_entregada` (`id_ev_entregada`);

--
-- Indices de la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ordenes_session_id` (`session_id`),
  ADD KEY `idx_ordenes_stripe_session_id` (`stripe_session_id`),
  ADD KEY `idx_ordenes_estado` (`estado`);

--
-- Indices de la tabla `orden_detalles`
--
ALTER TABLE `orden_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orden_detalles_orden_id` (`orden_id`),
  ADD KEY `idx_orden_detalles_producto_id` (`producto_id`);

--
-- Indices de la tabla `orden_facturas`
--
ALTER TABLE `orden_facturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_orden_facturas_orden_id` (`orden_id`),
  ADD KEY `idx_orden_facturas_email_status` (`email_status`);

--
-- Indices de la tabla `periodo`
--
ALTER TABLE `periodo`
  ADD PRIMARY KEY (`p_id`);

--
-- Indices de la tabla `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`PRODUCT_ID`),
  ADD KEY `CATEGORY_ID` (`CATEGORY_ID`),
  ADD KEY `SUPPLIER_ID` (`SUPPLIER_ID`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `programas_educativos`
--
ALTER TABLE `programas_educativos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `public_users`
--
ALTER TABLE `public_users`
  ADD PRIMARY KEY (`PUBLIC_USER_ID`),
  ADD UNIQUE KEY `uq_public_users_user` (`USER_ID`),
  ADD UNIQUE KEY `uq_public_users_email` (`EMAIL`);

--
-- Indices de la tabla `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `TRANS_D_ID` (`TRANS_D_ID`) USING BTREE;

--
-- Indices de la tabla `type`
--
ALTER TABLE `type`
  ADD PRIMARY KEY (`TYPE_ID`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `TYPE_ID` (`TYPE_ID`),
  ADD KEY `EMPLOYEE_ID` (`EMPLOYEE_ID`);

--
-- Indices de la tabla `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`video_id`),
  ADD KEY `contenido_id` (`contenido_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `about`
--
ALTER TABLE `about`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `actividades`
--
ALTER TABLE `actividades`
  MODIFY `ACTIVIDAD_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `archivos`
--
ALTER TABLE `archivos`
  MODIFY `a_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `asignaturas`
--
ALTER TABLE `asignaturas`
  MODIFY `ASIGNATURA_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `categorias_productos`
--
ALTER TABLE `categorias_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `category`
--
ALTER TABLE `category`
  MODIFY `CATEGORY_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `configmail`
--
ALTER TABLE `configmail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `contenidos`
--
ALTER TABLE `contenidos`
  MODIFY `contenido_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `customer`
--
ALTER TABLE `customer`
  MODIFY `CUST_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `docentes_asignaturas`
--
ALTER TABLE `docentes_asignaturas`
  MODIFY `da_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `dte_documents`
--
ALTER TABLE `dte_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `dte_sequences`
--
ALTER TABLE `dte_sequences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `dte_settings`
--
ALTER TABLE `dte_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `employee`
--
ALTER TABLE `employee`
  MODIFY `EMPLOYEE_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `ESTUDIANTE_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `estudiantes_docentes`
--
ALTER TABLE `estudiantes_docentes`
  MODIFY `ed_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `evaluaciones`
--
ALTER TABLE `evaluaciones`
  MODIFY `evaluacion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `ev_entregadas`
--
ALTER TABLE `ev_entregadas`
  MODIFY `ev_entregada_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `galeria`
--
ALTER TABLE `galeria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `grados`
--
ALTER TABLE `grados`
  MODIFY `G_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `i_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `location`
--
ALTER TABLE `location`
  MODIFY `LOCATION_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=238;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `mensaje_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
  MODIFY `nota_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `orden_detalles`
--
ALTER TABLE `orden_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `orden_facturas`
--
ALTER TABLE `orden_facturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `periodo`
--
ALTER TABLE `periodo`
  MODIFY `p_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `product`
--
ALTER TABLE `product`
  MODIFY `PRODUCT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=491;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `programas_educativos`
--
ALTER TABLE `programas_educativos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `public_users`
--
ALTER TABLE `public_users`
  MODIFY `PUBLIC_USER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `transaction_details`
--
ALTER TABLE `transaction_details`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `videos`
--
ALTER TABLE `videos`
  MODIFY `video_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `dte_documents`
--
ALTER TABLE `dte_documents`
  ADD CONSTRAINT `fk_dte_documents_order` FOREIGN KEY (`order_id`) REFERENCES `ordenes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `orden_detalles`
--
ALTER TABLE `orden_detalles`
  ADD CONSTRAINT `fk_orden_detalles_orden` FOREIGN KEY (`orden_id`) REFERENCES `ordenes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `orden_facturas`
--
ALTER TABLE `orden_facturas`
  ADD CONSTRAINT `fk_orden_facturas_orden` FOREIGN KEY (`orden_id`) REFERENCES `ordenes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_productos` (`id`);

--
-- Filtros para la tabla `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `videos_ibfk_1` FOREIGN KEY (`contenido_id`) REFERENCES `contenidos` (`contenido_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
