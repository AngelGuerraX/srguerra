-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 31-01-2026 a las 04:43:01
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
  `costo_empaque` decimal(10,2) DEFAULT 0.00,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `almacenes`
--

INSERT INTO `almacenes` (`id`, `empresa_id`, `nombre`, `costo_empaque`, `activo`) VALUES
(1, 1, 'Almacén Local (Casa)', 50.00, 1),
(2, 1, 'Almacén China', 15.00, 1),
(3, 1, 'Depósito Zona Oriental', 30.00, 1);

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
(2, 1, 'maria', '8294389999', NULL, 'Santo Domingo', 'Santo Domingo Este', 'wsas', NULL);

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
(1, 'El Clavito', '', '', '', '', '', 'logo_1_1769752016.png', NULL, NULL, 'Pro', 'Activo', '2026-01-29 04:01:01');

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
(1, 1, 1, 8, NULL),
(2, 1, 2, 21, NULL),
(3, 1, 3, 22, NULL),
(4, 2, 1, 9, NULL),
(5, 2, 2, 20, NULL),
(6, 2, 3, 30, NULL);

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
  `estado_interno` enum('Nuevo','Confirmado','En Ruta','Entregado','Devuelto','Cancelado') DEFAULT 'Nuevo',
  `total_venta` decimal(10,2) NOT NULL,
  `costo_envio_real` decimal(10,2) DEFAULT 0.00,
  `costo_empaque_real` decimal(10,2) DEFAULT 0.00,
  `notas_internas` text DEFAULT NULL,
  `guia_transporte` varchar(100) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_entrega` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `empresa_id`, `cliente_id`, `transportadora_id`, `almacen_id`, `shopify_order_id`, `origen`, `numero_orden`, `estado_interno`, `total_venta`, `costo_envio_real`, `costo_empaque_real`, `notas_internas`, `guia_transporte`, `fecha_creacion`, `fecha_entrega`) VALUES
(1, 1, 1, 5, 3, 6823866859766, 'Manual', '#1038', 'Entregado', 2100.00, 350.00, 30.00, 'Shopify Gateway: N/A', NULL, '2026-01-29 00:03:05', '2026-01-29 23:42:18'),
(2, 1, 2, 5, 1, 1769660393, 'Manual', 'MAN-290505-26', 'En Ruta', 100.00, 350.00, 50.00, '', NULL, '2026-01-29 00:05:26', NULL),
(3, 1, 2, 5, 1, 1769707047, 'Manual', 'MAN-291806-12', 'Confirmado', 100.00, 350.00, 50.00, '', NULL, '2026-01-29 13:06:26', '2026-01-29 13:16:41'),
(4, 1, 2, 5, 1, 1769706974, 'Manual', 'MAN-291807-79', 'Nuevo', 35.00, 350.00, 50.00, 'addad', NULL, '2026-01-29 13:07:04', NULL),
(5, 1, 2, 5, 2, 1769745032, 'Manual', 'MAN-292342-67', 'Devuelto', 100.00, 350.00, 15.00, '', NULL, '2026-01-29 23:42:58', '2026-01-29 23:54:29'),
(6, 1, 1, 5, 1, 1769745317, 'Manual', 'MAN-292343-30', 'Cancelado', 350.00, 350.00, 50.00, 'bkk', NULL, '2026-01-29 23:43:23', '2026-01-29 23:44:18'),
(7, 1, 2, 3, 1, 1769830653, 'Manual', 'MAN-302333-35', 'Nuevo', 100.00, 300.00, 50.00, '', NULL, '2026-01-30 23:33:42', NULL);

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
(1, 1, NULL, 'URO PROBOTIC', 1, 2100.00, 0.00),
(2, 2, 1, 'reloj', 1, 100.00, 0.00),
(3, 3, 1, 'reloj', 1, 100.00, 0.00),
(4, 4, NULL, 'ash', 1, 35.00, 0.00),
(5, 5, 1, 'reloj', 1, 100.00, 0.00),
(6, 6, 2, 'galas', 1, 350.00, 0.00),
(7, 7, 1, 'reloj', 1, 100.00, 0.00);

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
(1, 1, '122', 'reloj', 'skamdksad', '697adbba95b21.gif', 100.00, 50.00, 51, NULL),
(2, 1, '555', 'galas', 'descr', '697add9988da9.png', 350.00, 100.00, 59, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transportadoras`
--

CREATE TABLE `transportadoras` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `costo_envio_fijo` decimal(10,2) DEFAULT 0.00,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `transportadoras`
--

INSERT INTO `transportadoras` (`id`, `empresa_id`, `nombre`, `costo_envio_fijo`, `activo`) VALUES
(1, 1, 'Metro Pac', 250.00, 1),
(2, 1, 'Vimenpaq', 200.00, 1),
(3, 1, 'Caribe Tours', 300.00, 1),
(4, 1, 'Mensajero Privado', 150.00, 1),
(5, 1, 'Flash Cargo', 350.00, 1);

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
(1, 1, 'Administrador', 'admin@srguerra.com', '$2y$10$f2tQQO3NZGHdjHtdsnxU7Ob/9oOoAT28V7.AZpdVfnycaG83EmVgW', 'SuperAdmin', '2026-01-30 23:40:31', 1);

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
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inventario_almacen`
--
ALTER TABLE `inventario_almacen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_prod_alm` (`producto_id`,`almacen_id`),
  ADD KEY `fk_inv_alm` (`almacen_id`);

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
  ADD KEY `fk_trans_emp` (`empresa_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `inventario_almacen`
--
ALTER TABLE `inventario_almacen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `pedidos_detalle`
--
ALTER TABLE `pedidos_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `transportadoras`
--
ALTER TABLE `transportadoras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
