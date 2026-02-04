-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-02-2026 a las 07:37:46
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `srguerrabdd`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almacenes`
--

CREATE TABLE `almacenes` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `costo_empaque` decimal(10,2) DEFAULT 0.00,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `almacenes`
--

INSERT INTO `almacenes` (`id`, `empresa_id`, `nombre`, `ubicacion`, `costo_empaque`, `activo`) VALUES
(1, 1, 'Almacén Local (Casa)', NULL, 50.00, 1),
(2, 1, 'Almacén China', NULL, 15.00, 1),
(3, 1, 'Depósito Zona Oriental', NULL, 30.00, 1),
(4, 2, 'Almacén Principal', NULL, 0.00, 1),
(5, 3, 'Almacén Principal', NULL, 0.00, 1),
(6, 1, 'Almacén Principal', NULL, 0.00, 1),
(7, 4, 'Almacén Principal', NULL, 0.00, 1),
(8, 2, 'Merquix', 'Av. Rómulo Betancourt 323, Santo Domingo, edificio MR.B Self Storage - Rómulo Betancourt', 44.00, 1),
(9, 2, 'Mary', 'Avenida ecolgica, brisas del este', 0.00, 1),
(10, 2, 'Taxi Yorlin', 'Carro', 0.00, 1),
(11, 5, 'Almacén Principal', '', 50.00, 1),
(12, 6, 'Almacén Principal', '', 50.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `telefono` varchar(50) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `shopify_customer_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `empresa_id`, `nombre`, `telefono`, `email`, `provincia`, `ciudad`, `direccion`, `shopify_customer_id`) VALUES
(1, 1, 'prueba1 -', '+18094454545', '', 'Santo Domingo', 'Santo Domingo Este', 'alla', NULL),
(2, 1, 'maria', '8294389999', NULL, 'Santo Domingo', 'Santo Domingo Este', 'wsas', NULL),
(3, 2, 'maria', 'sss', NULL, 'Santo Domingo', 'Santo Domingo Norte', 'wsas', NULL),
(4, 4, 'Juan', '8494589867', NULL, 'Santiago', 'Santiago (Centro)', 'Alli', NULL),
(5, 1, 'Jose esteves', '+18292229192', NULL, NULL, 'SANTO DOMINGO OESTE', '27 de febrero 478', NULL),
(6, 2, 'Varios Clientes', '8094411000', NULL, 'Santo Domingo', 'Santo Domingo Este', 'Varios Lugares', NULL),
(7, 2, 'RAUL MARTINEZ', '8493795263', NULL, 'Santiago', 'Tamboril', 'URBANIZACION DON SE RESIDENCIAL DOÑA VICTORIA', NULL),
(8, 2, 'ISMAEL SIMON', '8098364541', NULL, 'La Altagracia', 'Higüey', 'EN SANTANA', NULL),
(9, 2, 'ANA RAMIREZ ROSARIO', '8299862324', NULL, 'Hermanas Mirabal', 'Salcedo', 'JUANA NUÑEZ', NULL),
(10, 2, 'YAHAIRA BATISTA', '8097539297', NULL, 'Monseñor Nouel', 'Bonao', 'LOS BARROS', NULL),
(11, 2, 'jolito alcantara diaz', '18293593068', NULL, 'San Juan', 'San Juan de la Maguana', 'san juan las carcas del maria novel ditrito municipal calle principal nuimero 18 ', NULL),
(12, 2, 'fior pimentel', '8297916033', NULL, 'Dajabón', 'Dajabón', 'callel duearte 57 centro de la ciudsad dajabon al lado de fondesa', NULL),
(13, 2, 'JUAN CARLOS', '8493886116', NULL, 'Santo Domingo', 'Santo Domingo Este', 'CASA', NULL),
(14, 2, 'Geidy Garcia', '+18293481017', NULL, 'Pedernales', 'Pedernales', 'Av. Duarte las multis barrio miramar', NULL),
(15, 2, 'Cristina Prefils', '+18299698023', NULL, 'Puerto Plata', 'Villa Montellano', 'Cangrejo detras del hotel euro, apartamento de 3 pisos', NULL),
(16, 2, 'Estefany -', '+18099622392', NULL, 'Puerto Plata', 'Sosúa', 'Calle Los castillos', NULL),
(17, 2, 'CRISTOBAL', '8097697816', NULL, 'Santo Domingo', 'Santo Domingo Este', 'CASA', NULL),
(18, 2, 'ANA MINERVA', '8099637575', NULL, 'Santo Domingo', 'Santo Domingo Este', 'losmina los jardines del ozama', NULL),
(19, 2, 'Pamela -', '+18099833931', NULL, 'Distrito Nacional', 'Distrito Nacional', 'C/1era La Agustina # 3', NULL),
(20, 2, 'Domingo peralta', '+18294312453', NULL, 'Puerto Plata', 'Puerto Plata', 'Barrio nuebo', NULL),
(21, 2, 'Yaneth Acuña', '+18296412968', NULL, 'La Romana', 'La Romana', 'Calle guacanagarix, edificio teresa marcelo piso 1-1A', NULL),
(22, 2, 'Gabriela María', '+18292840572', NULL, 'Santo Domingo', 'Santo Domingo Este', 'boichio esquina sarasota', NULL),
(23, 2, 'gaisha', '8496503611', NULL, 'La Romana', 'La Romana', 'bayahibe', NULL),
(24, 2, 'carlos', '8095645645', NULL, 'La Altagracia', 'San Rafael del Yuma', 'l', NULL),
(25, 2, 'carlos 2', '95959595959', NULL, 'La Altagracia', 'Higüey', 'ali', NULL),
(26, 5, 'zabala1', '809545666', NULL, 'María Trinidad Sánchez', 'Cabrera', 'aaa', NULL),
(27, 5, 'zabala2', '809456789', NULL, 'La Altagracia', 'Higüey', 'aqui', NULL),
(28, 5, 'juan', '798484', NULL, 'La Vega', 'Constanza', 'wwww', NULL),
(29, 5, 'pedro', '515541', NULL, 'María Trinidad Sánchez', 'Cabrera', 'rrr', NULL),
(30, 5, 'juanito', '222', NULL, 'María Trinidad Sánchez', 'Cabrera', 'wwd', NULL),
(31, 5, 'c1', '8294389999', NULL, 'Dajabón', 'Dajabón', 'ee', NULL),
(32, 6, 'juan', '8094411000', NULL, 'La Vega', 'Constanza', 'alli', NULL),
(33, 6, 'maria', '684864856', NULL, 'La Vega', 'Jima Abajo', 'kkl', NULL),
(34, 6, 'prueba1 -', '8294389999', NULL, 'La Romana', 'Guaymate', 'jhhg', NULL),
(35, 6, 'juan', '554443', NULL, 'La Vega', 'Jarabacoa', 'dfsf', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `almacen_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `costo_unitario` decimal(10,2) NOT NULL,
  `proveedor` varchar(150) DEFAULT NULL,
  `fecha_compra` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id`, `empresa_id`, `producto_id`, `almacen_id`, `cantidad`, `costo_unitario`, `proveedor`, `fecha_compra`) VALUES
(1, 5, 4, 11, 10, 50.00, 'I AM IRONMAN', '2026-02-02 16:59:08'),
(2, 6, 6, 12, 1, 800.00, '', '2026-02-02 23:16:11'),
(3, 6, 5, 12, 10, 10.00, '', '2026-02-03 11:41:43'),
(4, 5, 4, 11, 1, 50.00, '', '2026-02-03 19:15:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL,
  `nombre_comercial` varchar(150) NOT NULL,
  `razon_social` varchar(150) DEFAULT NULL,
  `rnc` varchar(20) DEFAULT NULL,
  `telefono_contacto` varchar(20) DEFAULT NULL,
  `email_contacto` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `sitio_web` varchar(100) DEFAULT NULL,
  `shopify_secret` varchar(255) DEFAULT NULL,
  `plan_suscripcion` enum('Gratis','Pro','Enterprise') DEFAULT 'Pro',
  `estado` enum('Activo','Suspendido') DEFAULT 'Activo',
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`id`, `nombre_comercial`, `razon_social`, `rnc`, `telefono_contacto`, `email_contacto`, `direccion`, `logo`, `sitio_web`, `shopify_secret`, `plan_suscripcion`, `estado`, `fecha_registro`) VALUES
(1, 'superadmin', '', '', '', '', '', 'logo_1_1769752016.png', NULL, '', 'Pro', 'Activo', '2026-01-29 04:01:01'),
(2, 'El Clavito', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Gratis', 'Activo', '2026-01-31 01:14:24'),
(3, 'Empresa Prueba', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Gratis', 'Suspendido', '2026-01-31 01:18:09'),
(4, 'Todoclick', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Gratis', 'Activo', '2026-02-01 20:48:24'),
(5, 'demo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Gratis', 'Activo', '2026-02-02 16:46:39'),
(6, 'fuego', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Enterprise', 'Activo', '2026-02-02 20:27:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa_transportadora_rel`
--

CREATE TABLE `empresa_transportadora_rel` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `transportadora_id` int(11) NOT NULL,
  `precio_personalizado` decimal(10,2) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `finanzas_gastos`
--

CREATE TABLE `finanzas_gastos` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `categoria` enum('Publicidad','Nomina','Servicios','Software','Otros') NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `categoria` varchar(100) DEFAULT 'General'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_almacen`
--

CREATE TABLE `inventario_almacen` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `almacen_id` int(11) NOT NULL,
  `cantidad` int(11) DEFAULT 0,
  `ubicacion_fisica` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario_almacen`
--

INSERT INTO `inventario_almacen` (`id`, `producto_id`, `almacen_id`, `cantidad`, `ubicacion_fisica`) VALUES
(1, 1, 1, 7, NULL),
(2, 1, 2, 21, NULL),
(3, 1, 3, 22, NULL),
(4, 2, 1, 9, NULL),
(5, 2, 2, 20, NULL),
(6, 2, 3, 30, NULL),
(7, 3, 4, 24, NULL),
(8, 3, 8, 27, NULL),
(9, 3, 9, 12, NULL),
(16, 3, 10, 12, NULL),
(23, 4, 11, 14, NULL),
(32, 5, 12, 17, NULL),
(34, 6, 12, 5, NULL),
(37, 7, 12, 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marketing_campanas`
--

CREATE TABLE `marketing_campanas` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `nombre` varchar(150) NOT NULL,
  `plataforma` enum('Facebook Ads','TikTok Ads','Google Ads','Influencer') DEFAULT 'Facebook Ads',
  `estado` enum('Activa','Pausada','Archivada') DEFAULT 'Activa',
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marketing_campanas`
--

INSERT INTO `marketing_campanas` (`id`, `empresa_id`, `producto_id`, `nombre`, `plataforma`, `estado`, `creado_en`) VALUES
(1, 6, 6, 'Correa prueba', 'Facebook Ads', 'Activa', '2026-02-02 20:54:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marketing_gasto`
--

CREATE TABLE `marketing_gasto` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `plataforma` enum('Facebook Ads','TikTok Ads','Google Ads','Influencer') DEFAULT 'Facebook Ads',
  `monto` decimal(10,2) NOT NULL,
  `notas` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marketing_gasto`
--

INSERT INTO `marketing_gasto` (`id`, `empresa_id`, `fecha`, `plataforma`, `monto`, `notas`) VALUES
(1, 6, '2026-02-02', 'Facebook Ads', 1000.00, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `transportadora_id` int(11) DEFAULT NULL,
  `almacen_id` int(11) DEFAULT NULL,
  `shopify_order_id` bigint(20) DEFAULT NULL,
  `origen` varchar(50) DEFAULT 'Manual',
  `numero_orden` varchar(50) NOT NULL,
  `estado_interno` enum('Nuevo','Confirmado','En Ruta','Entregado','Devuelto','Rechazado','Cancelado') DEFAULT 'Nuevo',
  `total_venta` decimal(10,2) NOT NULL,
  `costo_envio_real` decimal(10,2) DEFAULT 0.00,
  `costo_empaque_real` decimal(10,2) DEFAULT 0.00,
  `notas_internas` text DEFAULT NULL,
  `motivo_rechazo` text DEFAULT NULL,
  `guia_transporte` varchar(100) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_entrega` datetime DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `empresa_id`, `cliente_id`, `transportadora_id`, `almacen_id`, `shopify_order_id`, `origen`, `numero_orden`, `estado_interno`, `total_venta`, `costo_envio_real`, `costo_empaque_real`, `notas_internas`, `motivo_rechazo`, `guia_transporte`, `fecha_creacion`, `fecha_entrega`, `fecha_actualizacion`) VALUES
(9, 1, 1, 5, 2, 1769965955, 'Manual', 'MAN-011311-66', 'Entregado', 100.00, 350.00, 15.00, ' | Chofer: todo si | Devolución: Motivo: Cliente no estaba. anjaaaaaaaaaaa | Chofer: ', NULL, NULL, '2026-02-01 13:11:17', '2026-02-01 15:15:30', '2026-02-01 15:11:36'),
(10, 4, 4, 12, 7, 1769994535, 'Manual', 'MAN-012055-42', 'Entregado', 1450.00, 250.00, 0.00, ' | Chofer: El quedo bacano', NULL, NULL, '2026-02-01 20:55:48', '2026-02-01 20:57:19', NULL),
(11, 2, 6, 6, 4, 1770007330, 'Manual', 'MAN-020030-54', 'Entregado', 5800.00, 0.00, 0.00, 'Regadas con yorlin', NULL, NULL, '2026-02-02 00:30:02', '2026-02-02 00:30:52', '2026-02-02 00:30:30'),
(12, 2, 7, 13, 8, 1770008182, 'Manual', 'MAN-020040-98', 'Entregado', 1450.00, 300.00, 44.00, ' | ', NULL, NULL, '2026-02-02 00:40:19', '2026-02-03 12:58:41', NULL),
(13, 2, 8, 13, 8, 1770007972, 'Manual', 'MAN-020041-99', 'Rechazado', 1450.00, 300.00, 44.00, ' | ', 'Sin dinero', NULL, '2026-02-02 00:41:36', NULL, '2026-02-03 12:58:08'),
(14, 2, 9, 13, 8, 1770007649, 'Manual', 'MAN-020043-70', 'Rechazado', 1450.00, 300.00, 44.00, ' | ', 'Rechazó producto', NULL, '2026-02-02 00:43:05', NULL, '2026-02-03 12:49:09'),
(15, 2, 10, 13, 8, 1770007590, 'Manual', 'MAN-020044-41', 'Rechazado', 1450.00, 0.00, 44.00, ' | ', 'Rechazó producto', NULL, '2026-02-02 00:44:22', NULL, '2026-02-03 12:49:01'),
(16, 2, 11, 14, 8, 1770008186, 'Manual', 'MAN-020054-44', 'Nuevo', 1450.00, 350.00, 44.00, '', NULL, NULL, '2026-02-02 00:54:40', NULL, NULL),
(17, 2, 12, 14, 8, 1770008272, 'Manual', 'MAN-020055-69', 'Nuevo', 1450.00, 350.00, 44.00, '', NULL, NULL, '2026-02-02 00:55:47', NULL, NULL),
(18, 2, 13, 15, 4, 1770009150, 'Manual', 'MAN-020057-14', 'Nuevo', 1450.00, 0.00, 0.00, '', NULL, NULL, '2026-02-02 00:57:43', '2026-02-02 00:58:08', NULL),
(19, 2, 14, 14, 8, 1770008764, 'Manual', 'MAN-020100-85', 'Nuevo', 1450.00, 350.00, 44.00, '', NULL, NULL, '2026-02-02 01:00:39', NULL, NULL),
(20, 2, 15, 14, 8, 1770009482, 'Manual', 'MAN-020101-90', 'Nuevo', 1450.00, 350.00, 44.00, '', NULL, NULL, '2026-02-02 01:01:43', NULL, NULL),
(21, 2, 16, 14, 8, 1770009557, 'Manual', 'MAN-020103-76', 'Nuevo', 1450.00, 350.00, 44.00, '', NULL, NULL, '2026-02-02 01:03:15', NULL, NULL),
(22, 2, 17, 15, 4, 1770009521, 'Manual', 'MAN-020104-21', 'Nuevo', 1450.00, 0.00, 0.00, '', NULL, NULL, '2026-02-02 01:04:21', NULL, NULL),
(23, 2, 18, 6, 4, 1770008783, 'Manual', 'MAN-020106-96', 'Confirmado', 1450.00, 250.00, 0.00, '', NULL, NULL, '2026-02-02 01:06:16', NULL, '2026-02-02 16:37:43'),
(24, 2, 19, 6, 4, 1770009378, 'Manual', 'MAN-020107-49', 'Entregado', 1450.00, 250.00, 0.00, '', NULL, NULL, '2026-02-02 01:07:27', '2026-02-02 01:08:07', NULL),
(25, 2, 20, 14, 8, 1770009823, 'Manual', 'MAN-020111-20', 'Nuevo', 1450.00, 350.00, 44.00, '', NULL, NULL, '2026-02-02 01:11:25', NULL, NULL),
(26, 2, 21, 14, 8, 1770009608, 'Manual', 'MAN-020112-39', 'Nuevo', 1450.00, 350.00, 44.00, '', NULL, NULL, '2026-02-02 01:12:59', NULL, NULL),
(27, 2, 22, 6, 4, 1770010208, 'Manual', 'MAN-020114-94', 'Confirmado', 1450.00, 250.00, 0.00, '', NULL, NULL, '2026-02-02 01:14:14', NULL, '2026-02-02 16:37:24'),
(28, 2, 23, 14, 8, 1770009849, 'Manual', 'MAN-020120-11', 'En Ruta', 1450.00, 350.00, 44.00, '', NULL, NULL, '2026-02-02 01:20:36', NULL, '2026-02-02 02:30:28'),
(30, 2, 25, 6, 4, 1770065304, 'Manual', 'MAN-021632-38', 'Confirmado', 1450.00, 250.00, 0.00, '', NULL, NULL, '2026-02-02 16:32:53', '2026-02-02 16:33:40', '2026-02-02 16:34:19'),
(39, 6, 34, 21, 12, 1770089378, 'Manual', 'MAN-022328-31', 'Entregado', 3000.00, 200.00, 50.00, '', NULL, NULL, '2026-02-02 23:28:49', '2026-02-03 11:57:38', '2026-02-03 11:57:38'),
(40, 6, 35, 21, 12, 1770089683, 'Manual', 'MAN-022329-89', 'Entregado', 3000.00, 200.00, 50.00, '', NULL, NULL, '2026-02-02 23:29:14', '2026-02-02 23:29:28', '2026-02-02 23:29:28'),
(41, 6, 34, 21, 12, 1770135350, 'Manual', 'MAN-031200-72', 'En Ruta', 2000.00, 200.00, 50.00, '', NULL, NULL, '2026-02-03 12:00:54', NULL, '2026-02-03 12:01:35'),
(42, 5, 31, 17, 11, 1770160210, 'Manual', 'MAN-031907-29', 'Entregado', 100.00, 250.00, 50.00, ' | ', NULL, NULL, '2026-02-03 19:07:38', '2026-02-04 00:53:37', '2026-02-04 00:53:37'),
(43, 5, 27, 17, 11, 1770161137, 'Manual', 'MAN-031916-25', 'Devuelto', 100.00, 250.00, 50.00, ' | ', 'Sin dinero', NULL, '2026-02-03 19:16:36', NULL, '2026-02-03 19:19:49'),
(44, 5, 31, 17, 11, 1770180953, 'Manual', 'MAN-040052-78', 'Devuelto', 100.00, 250.00, 50.00, ' | ', 'Rechazó producto', NULL, '2026-02-04 00:52:10', NULL, '2026-02-04 00:53:16'),
(45, 5, 29, 17, 11, 1770181763, 'Manual', 'MAN-040053-16', 'Entregado', 100.00, 250.00, 50.00, ' | ', NULL, NULL, '2026-02-04 00:53:53', '2026-02-04 00:54:21', '2026-02-04 00:54:02'),
(46, 5, 30, 17, 11, 1770182540, 'Manual', 'MAN-040115-93', 'Entregado', 100.00, 250.00, 50.00, '', NULL, NULL, '2026-02-04 01:15:20', '2026-02-04 01:22:28', '2026-02-04 01:22:28'),
(47, 5, 30, 17, 11, 1770182671, 'Manual', 'MAN-040124-22', 'Entregado', 100.00, 250.00, 50.00, ' | ', NULL, NULL, '2026-02-04 01:24:07', '2026-02-04 01:32:27', '2026-02-04 01:24:14'),
(48, 6, 33, 17, 12, 1770184695, 'Manual', 'MAN-040146-73', 'Entregado', 2000.00, 250.00, 50.00, ' | ', NULL, NULL, '2026-02-04 01:46:40', '2026-02-04 01:51:03', '2026-02-04 01:46:50'),
(50, 6, 33, 17, 12, 1770184393, 'Manual', 'MAN-040153-83', 'Nuevo', 20.00, 250.00, 50.00, '', NULL, NULL, '2026-02-04 01:53:03', NULL, NULL),
(51, 6, 33, 17, 12, 1770185015, 'Manual', 'MAN-040153-20', 'Confirmado', 20.00, 250.00, 50.00, '', NULL, NULL, '2026-02-04 01:53:03', NULL, '2026-02-04 02:05:18'),
(52, 6, 32, 17, 12, 1770184727, 'Manual', 'MAN-040156-51', 'Confirmado', 2000.00, 0.00, 50.00, '', NULL, NULL, '2026-02-04 01:56:32', NULL, '2026-02-04 02:05:12'),
(53, 6, 34, 17, 12, 1770184632, 'Manual', 'MAN-040156-33', 'Cancelado', 2000.00, 0.00, 50.00, '', NULL, NULL, '2026-02-04 01:56:45', NULL, '2026-02-04 02:24:53'),
(54, 6, 33, 17, 12, 1770185364, 'Manual', 'MAN-040156-71', 'Confirmado', 20.00, 0.00, 50.00, '', NULL, NULL, '2026-02-04 01:56:55', NULL, '2026-02-04 02:05:06'),
(55, 5, 30, 17, 11, 1770185943, 'Manual', 'MAN-040204-38', 'Confirmado', 100.00, 0.00, 50.00, '', NULL, NULL, '2026-02-04 02:04:37', NULL, '2026-02-04 02:04:41'),
(56, 5, 29, 17, 11, 1770186072, 'Manual', 'MAN-040204-51', 'Confirmado', 100.00, 0.00, 50.00, '', NULL, NULL, '2026-02-04 02:04:51', NULL, '2026-02-04 02:04:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos_detalle`
--

CREATE TABLE `pedidos_detalle` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `nombre_producto` varchar(255) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `costo_unitario` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos_detalle`
--

INSERT INTO `pedidos_detalle` (`id`, `pedido_id`, `producto_id`, `nombre_producto`, `cantidad`, `precio_unitario`, `costo_unitario`) VALUES
(9, 9, 1, 'reloj', 1, 100.00, 0.00),
(10, 10, NULL, 'Huevo', 1, 1450.00, 0.00),
(11, 11, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 4, 1450.00, 0.00),
(12, 12, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(13, 13, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(14, 14, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(15, 15, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(16, 16, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(17, 17, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(18, 18, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(19, 19, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(20, 20, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(21, 21, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(22, 22, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(23, 23, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(24, 24, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(25, 25, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(26, 26, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(27, 27, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(28, 28, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(30, 30, 3, 'Set Luces Inalámbricas x3 + Control Remoto', 1, 1450.00, 0.00),
(39, 39, 7, 'guineo', 1, 3000.00, 0.00),
(40, 40, 7, 'guineo', 1, 3000.00, 0.00),
(41, 41, 6, 'Correa prueba', 1, 2000.00, 0.00),
(42, 42, 4, 'pro1', 1, 100.00, 0.00),
(43, 43, 4, 'pro1', 1, 100.00, 0.00),
(44, 44, 4, 'pro1', 1, 100.00, 0.00),
(45, 45, 4, 'pro1', 1, 100.00, 0.00),
(46, 46, 4, 'pro1', 1, 100.00, 0.00),
(47, 47, 4, 'pro1', 1, 100.00, 0.00),
(48, 48, 6, 'Correa prueba', 1, 2000.00, 0.00),
(50, 50, 5, 'Reloj', 1, 20.00, 0.00),
(51, 51, 5, 'Reloj', 1, 20.00, 0.00),
(52, 52, 6, 'Correa prueba', 1, 2000.00, 0.00),
(53, 53, 6, 'Correa prueba', 1, 2000.00, 0.00),
(54, 54, 5, 'Reloj', 1, 20.00, 0.00),
(55, 55, 4, 'pro1', 1, 100.00, 0.00),
(56, 56, 4, 'pro1', 1, 100.00, 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `precio_venta` decimal(10,2) DEFAULT 0.00,
  `costo_compra` decimal(10,2) DEFAULT 0.00,
  `stock_actual` int(11) DEFAULT 0,
  `shopify_product_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `empresa_id`, `sku`, `nombre`, `descripcion`, `imagen`, `precio_venta`, `costo_compra`, `stock_actual`, `shopify_product_id`) VALUES
(1, 1, '122', 'reloj', 'skamdksad', '697adbba95b21.gif', 100.00, 50.00, 50, NULL),
(2, 1, '555', 'galas', 'descr', '697add9988da9.png', 350.00, 100.00, 59, NULL),
(3, 2, 'LAMX3', 'Set Luces Inalámbricas x3 + Control Remoto', 'Set Luces Inalámbricas x3 + Control Remoto', '698025b8cb88d.png', 1450.00, 210.00, 75, NULL),
(4, 5, 'p1', 'pro1', 'DESCRIPCON DEL PRODUCTO 1', '69810d6dce4e5.jpeg', 100.00, 50.00, 14, NULL),
(5, 6, 'REL1', 'Reloj', 'deloj de prueba', '69814147254a8.jpeg', 20.00, 10.00, 17, NULL),
(6, 6, 'corr', 'Correa prueba', '', NULL, 2000.00, 800.00, 5, NULL),
(7, 6, 'anja', 'guineo', '', NULL, 3000.00, 1000.00, 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transportadoras`
--

CREATE TABLE `transportadoras` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `codigo_acceso` varchar(50) DEFAULT NULL,
  `pin_acceso` varchar(255) DEFAULT NULL,
  `costo_envio_fijo` decimal(10,2) DEFAULT 0.00,
  `activo` tinyint(1) DEFAULT 1,
  `es_publica` tinyint(1) DEFAULT 0 COMMENT '1=Visible para todas las empresas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `transportadoras`
--

INSERT INTO `transportadoras` (`id`, `empresa_id`, `nombre`, `telefono`, `codigo_acceso`, `pin_acceso`, `costo_envio_fijo`, `activo`, `es_publica`) VALUES
(1, 1, 'Metro Pac', NULL, NULL, NULL, 250.00, 1, 0),
(2, 1, 'Vimenpaq', NULL, NULL, NULL, 200.00, 1, 0),
(3, 1, 'Caribe Tours', NULL, NULL, NULL, 300.00, 1, 0),
(4, 1, 'Mensajero Privado', NULL, NULL, NULL, 150.00, 1, 0),
(5, 1, 'Flash Cargo', NULL, 'FLASH', '$2y$10$rhnG5Er1IPHCjtzsC9eoqeMzXfg1JuYhN5zIvRS3xlY0GByMj4OoK', 350.00, 1, 0),
(6, 2, 'GUERRASHIPING', NULL, 'GS', '$2y$10$1HCnoqg9LXpU0b8asaEcOeeYx4O0cbKWG9XZg459bUtEtDJBZsBba', 250.00, 1, 0),
(7, 2, 'Anja', NULL, NULL, NULL, 200.00, 0, 0),
(8, 3, 'Metro Pac', NULL, NULL, NULL, 250.00, 1, 0),
(9, 3, 'Vimenpaq', NULL, NULL, NULL, 200.00, 1, 0),
(10, 4, 'Metro Pac', NULL, NULL, NULL, 250.00, 1, 0),
(11, 4, 'Vimenpaq', NULL, NULL, NULL, 200.00, 1, 0),
(12, 4, 'Lixandro', NULL, 'LIX1', '$2y$10$AtacLy5hlJy4Fx/.Liu0GOxqm8wZCRh5yLBit8bIPZIXhHpg9FzCy', 250.00, 1, 0),
(13, 2, 'FLASH CARGO', NULL, 'FL', '$2y$10$bewm6G1G9YIXpoVihdf.r.Yctjhe32IiVvjkffzthYatrx28ne8CG', 300.00, 1, 0),
(14, 2, 'GINTRACON', NULL, NULL, NULL, 350.00, 1, 0),
(15, 2, 'CASA', NULL, NULL, NULL, 0.00, 1, 0),
(16, 2, 'Taxi Yorlin', NULL, NULL, NULL, 0.00, 1, 0),
(17, 5, 'demo trans1', NULL, 'DEMO1', '$2y$10$zXxsY7XQiUyTeb8dsYC/xOrXbh7dnnkODDvTFYCcCnwEzxAMsP1C6', 250.00, 1, 1),
(18, 5, 'Vimenpaq', NULL, 'MEPA', '$2y$10$SAC7RuLVG9szzIGB6kz6xeBuEO5K1110rM1a/JkzeWOxfaGxCUzyK', 200.00, 1, 0),
(19, 6, 'Metro Pac', NULL, NULL, NULL, 250.00, 1, 0),
(20, 6, 'Vimenpaq', NULL, NULL, NULL, 200.00, 1, 0),
(21, 6, 'Juan Express', NULL, 'JUAN', '$2y$10$mJxrJuEcFIRaIzDb3ySvOueictWO7yvThvBXuJ93EHVQofp34bLCa', 200.00, 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transportadoras_pagos`
--

CREATE TABLE `transportadoras_pagos` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `transportadora_id` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `monto` decimal(10,2) NOT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `nota` text DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `transportadoras_pagos`
--

INSERT INTO `transportadoras_pagos` (`id`, `empresa_id`, `transportadora_id`, `fecha`, `monto`, `referencia`, `nota`, `creado_por`) VALUES
(1, 0, 17, '2026-02-04 01:13:42', 500.00, 'primer pago', 'Pago el dia tal a tal hora', NULL),
(2, 0, 17, '2026-02-04 01:22:51', 250.00, '', '', NULL),
(3, 6, 17, '2026-02-04 02:17:41', 250.00, '', '', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('SuperAdmin','Admin','Vendedor','Almacen','Mensajero') DEFAULT 'Admin',
  `ultimo_login` datetime DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `empresa_id`, `nombre_completo`, `email`, `password_hash`, `rol`, `ultimo_login`, `activo`) VALUES
(1, 1, 'Administrador', 'admin@srguerra.com', '$2y$10$f2tQQO3NZGHdjHtdsnxU7Ob/9oOoAT28V7.AZpdVfnycaG83EmVgW', 'SuperAdmin', '2026-02-04 00:49:49', 1),
(2, 2, 'Angel Guerra', 'angelguerraxcode@gmail.com', '$2y$10$rF5JQ71LyoL7RsOyqkv47uBLHbqf0sJcrPnTLmKPMbBci6c25lYQO', 'Admin', '2026-02-04 02:34:42', 1),
(3, 3, 'Nombre Prueba', 'Prueba@prueba.com', '$2y$10$IkhdtAJGWWnVxUokP7oRVewYvOL6jxv6jeBK1XDwUV5j1W2N4wQ9S', 'Admin', NULL, 1),
(4, 4, 'Yorlincuas', 'yorlincuas@gmail.com', '$2y$10$j2w5GjcPKAzPvos.qtUD/Ob.M.FzkVm4Xes4Daqy9JGqc63XTyE8a', 'Admin', '2026-02-01 20:49:32', 1),
(5, 5, 'prueba', 'P@p.com', '$2y$10$SMf.6W4tNySLVh/gekpKbui7b9rJDnPwkgoEoKfFAcSENj1b6ePDa', 'Admin', '2026-02-04 00:51:33', 1),
(6, 6, 'fuego', 'f@f.com', '$2y$10$vJoXA5o03aO4WqXFdXtCb.lLyya7dy5VVtUni0nODd3b7EMwAlYZq', 'Admin', '2026-02-02 20:27:23', 1),
(7, 6, 'agua', 'a@a.com', '$2y$10$yMF1OliFtlcpBWjZUdF.ou2pzVqaljebnuKwDVeRgkNKnj1KMA8lq', 'Vendedor', '2026-02-04 02:14:31', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `almacenes`
--
ALTER TABLE `almacenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_alm_emp` (`empresa_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cli_emp` (`empresa_id`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_prod_compra` (`producto_id`),
  ADD KEY `idx_alm_compra` (`almacen_id`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `empresa_transportadora_rel`
--
ALTER TABLE `empresa_transportadora_rel`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `finanzas_gastos`
--
ALTER TABLE `finanzas_gastos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_gastos_emp` (`empresa_id`);

--
-- Indices de la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inventario_almacen`
--
ALTER TABLE `inventario_almacen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_prod_alm` (`producto_id`,`almacen_id`),
  ADD UNIQUE KEY `stock_unico` (`producto_id`,`almacen_id`),
  ADD KEY `fk_inv_alm` (`almacen_id`);

--
-- Indices de la tabla `marketing_campanas`
--
ALTER TABLE `marketing_campanas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_camp_prod` (`producto_id`);

--
-- Indices de la tabla `marketing_gasto`
--
ALTER TABLE `marketing_gasto`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ped_emp` (`empresa_id`),
  ADD KEY `fk_ped_cli` (`cliente_id`),
  ADD KEY `fk_ped_trans` (`transportadora_id`);

--
-- Indices de la tabla `pedidos_detalle`
--
ALTER TABLE `pedidos_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_det_ped` (`pedido_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_prod_emp` (`empresa_id`);

--
-- Indices de la tabla `transportadoras`
--
ALTER TABLE `transportadoras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_acceso` (`codigo_acceso`),
  ADD KEY `fk_trans_emp` (`empresa_id`);

--
-- Indices de la tabla `transportadoras_pagos`
--
ALTER TABLE `transportadoras_pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pago_trans` (`transportadora_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_unico` (`email`),
  ADD KEY `fk_usr_emp` (`empresa_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `almacenes`
--
ALTER TABLE `almacenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `empresa_transportadora_rel`
--
ALTER TABLE `empresa_transportadora_rel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `finanzas_gastos`
--
ALTER TABLE `finanzas_gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `inventario_almacen`
--
ALTER TABLE `inventario_almacen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de la tabla `marketing_campanas`
--
ALTER TABLE `marketing_campanas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `marketing_gasto`
--
ALTER TABLE `marketing_gasto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `pedidos_detalle`
--
ALTER TABLE `pedidos_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `transportadoras`
--
ALTER TABLE `transportadoras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `transportadoras_pagos`
--
ALTER TABLE `transportadoras_pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `almacenes`
--
ALTER TABLE `almacenes`
  ADD CONSTRAINT `fk_alm_emp` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `fk_cli_emp` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `inventario_almacen`
--
ALTER TABLE `inventario_almacen`
  ADD CONSTRAINT `fk_inv_alm` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_inv_prod` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_ped_cli` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ped_emp` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ped_trans` FOREIGN KEY (`transportadora_id`) REFERENCES `transportadoras` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pedidos_detalle`
--
ALTER TABLE `pedidos_detalle`
  ADD CONSTRAINT `fk_det_ped` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_prod_emp` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `transportadoras`
--
ALTER TABLE `transportadoras`
  ADD CONSTRAINT `fk_trans_emp` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usr_emp` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
