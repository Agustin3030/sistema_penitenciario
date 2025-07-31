-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 31-07-2025 a las 21:14:06
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
-- Base de datos: `sistema_penitenciario`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial`
--

CREATE TABLE `historial` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `accion` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial`
--

INSERT INTO `historial` (`id`, `usuario_id`, `accion`, `fecha`) VALUES
(1, 2, 'REGISTRO_NOTA: observacion', '2025-07-30 10:21:31'),
(2, 2, 'REGISTRO_NOTA: visita', '2025-07-30 11:21:49'),
(3, 2, 'REGISTRO_NOTA: traslado', '2025-07-31 10:51:18'),
(4, 3, 'REGISTRO_NOTA: observacion', '2025-07-31 12:27:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_login`
--

CREATE TABLE `historial_login` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `accion` varchar(50) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_login`
--

INSERT INTO `historial_login` (`id`, `usuario_id`, `accion`, `fecha`) VALUES
(1, 2, 'LOGIN', '2025-07-29 14:37:22'),
(2, 2, 'LOGOUT', '2025-07-29 14:39:46'),
(3, 3, 'LOGIN', '2025-07-29 14:39:52'),
(4, 3, 'LOGIN', '2025-07-29 14:49:31'),
(5, 3, 'LOGOUT', '2025-07-29 14:49:47'),
(6, 2, 'LOGIN', '2025-07-29 14:49:55'),
(7, 2, 'LOGIN', '2025-07-30 09:16:23'),
(8, 2, 'LOGOUT', '2025-07-30 11:23:19'),
(9, 4, 'LOGIN', '2025-07-30 11:23:24'),
(10, 2, 'LOGIN', '2025-07-31 10:47:28'),
(11, 2, 'LOGOUT', '2025-07-31 11:48:30'),
(12, 3, 'LOGIN', '2025-07-31 11:48:35'),
(13, 3, 'LOGOUT', '2025-07-31 11:51:08'),
(14, 2, 'LOGIN', '2025-07-31 11:51:15'),
(15, 2, 'LOGOUT', '2025-07-31 12:06:01'),
(16, 5, 'LOGIN', '2025-07-31 12:06:07'),
(17, 2, 'LOGIN', '2025-07-31 12:21:05'),
(18, 2, 'LOGOUT', '2025-07-31 12:26:05'),
(19, 3, 'LOGIN', '2025-07-31 12:26:52'),
(20, 3, 'LOGOUT', '2025-07-31 12:45:11'),
(21, 2, 'LOGIN', '2025-07-31 12:45:33'),
(22, 2, 'LOGOUT', '2025-07-31 12:47:00'),
(23, 3, 'LOGIN', '2025-07-31 12:47:07'),
(24, 5, 'LOGIN', '2025-07-31 12:55:03'),
(25, 5, 'LOGOUT', '2025-07-31 13:06:30'),
(26, 2, 'LOGIN', '2025-07-31 13:06:36'),
(27, 2, 'LOGOUT', '2025-07-31 13:07:08'),
(28, 5, 'LOGIN', '2025-07-31 13:07:13'),
(29, 5, 'LOGOUT', '2025-07-31 13:17:55'),
(30, 2, 'LOGIN', '2025-07-31 13:18:06'),
(31, 2, 'LOGOUT', '2025-07-31 13:18:18'),
(32, 2, 'LOGIN', '2025-07-31 13:19:00'),
(33, 2, 'LOGOUT', '2025-07-31 13:19:28'),
(34, 6, 'LOGIN', '2025-07-31 13:19:38'),
(35, 6, 'LOGOUT', '2025-07-31 13:20:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas`
--

CREATE TABLE `notas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `nota` text NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `severidad` int(11) NOT NULL DEFAULT 1,
  `creado_por` int(11) NOT NULL,
  `fecha_incidente` datetime NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notas`
--

INSERT INTO `notas` (`id`, `usuario_id`, `nota`, `tipo`, `severidad`, `creado_por`, `fecha_incidente`, `fecha_registro`) VALUES
(1, NULL, 'no habia pintura', 'observacion', 2, 2, '2025-07-31 12:20:00', '2025-07-30 10:21:31'),
(2, 2, 'tiene visita el sabado', 'visita', 2, 2, '2025-07-30 13:21:00', '2025-07-30 11:21:49'),
(3, 3, 'peleo', 'traslado', 2, 2, '2025-07-31 12:50:00', '2025-07-31 10:51:18'),
(4, NULL, 'existete posibilidad de fuga', 'observacion', 3, 3, '2025-07-31 14:27:00', '2025-07-31 12:27:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

CREATE TABLE `personas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `edad` int(11) DEFAULT NULL CHECK (`edad` between 12 and 100),
  `causa` text NOT NULL,
  `estado` enum('condenado','procesado') NOT NULL,
  `sancionado` enum('sancionado','no_sancionado') DEFAULT 'no_sancionado',
  `fecha_sancion` date DEFAULT NULL,
  `historial_delictivo` text DEFAULT NULL,
  `fecha_ingreso` date DEFAULT curdate(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ubicacion` varchar(50) NOT NULL DEFAULT 'Pabellón A',
  `tiempo_condena` varchar(20) NOT NULL,
  `nivel_riesgo` enum('Bajo','Medio','Alto','Máximo') NOT NULL DEFAULT 'Medio',
  `sanciones` text DEFAULT NULL,
  `ranchograma` text DEFAULT NULL COMMENT 'Relaciones con otros internos (JSON)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personas`
--

INSERT INTO `personas` (`id`, `nombre`, `edad`, `causa`, `estado`, `sancionado`, `fecha_sancion`, `historial_delictivo`, `fecha_ingreso`, `created_at`, `ubicacion`, `tiempo_condena`, `nivel_riesgo`, `sanciones`, `ranchograma`) VALUES
(1, 'pepe', 18, 'le fiaron y no pago ', 'condenado', 'no_sancionado', NULL, NULL, '2025-07-29', '2025-07-29 15:34:03', 'pabellon', '2 dias ', 'Bajo', '0', '[]'),
(2, 'jose ', 23, 'no cruzo en la denda peatonal', 'condenado', 'no_sancionado', NULL, NULL, '2025-07-30', '2025-07-30 11:18:31', 'celda 1', '5 siglos', 'Máximo', 'no aplica ', '[]'),
(3, 'pedro', 32, 'caminaba descalso en zona publicas', 'condenado', 'no_sancionado', NULL, NULL, '2025-07-31', '2025-07-31 10:49:39', 'celda 3', '2 tardes', 'Máximo', 'no', '[{\"interno_id\":2,\"tipo_relacion\":\"Amistad\"},{\"interno_id\":1,\"tipo_relacion\":\"Conflictiva\"}]');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','celador','jefe','direccion') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `usuario`, `password`, `rol`, `created_at`) VALUES
(2, 'Administrador Principal', 'admin', '$2y$10$isz7V04FrnN/XKuq7xD/8OQHR/4Wx2eC0CDNqoir4VO6.r8gnuGdq', 'admin', '2025-07-29 14:35:11'),
(3, 'Juan', 'juan1', '$2y$10$g/XL56JKGhfREGZZkiX.e.ldH8lCwyQGJxCBEHnYd1ZTb68Ydv7Zq', 'celador', '2025-07-29 14:39:37'),
(4, 'bruno', 'bru', '$2y$10$B6LhVtZ4ImoA2rB0tquk8./3p2EBZBkbus94eYlV/G4wzocWgxaEy', 'celador', '2025-07-30 11:23:15'),
(5, 'jorge', 'jor', '$2y$10$/qG80KLv/sH2hxiibF1wXOfilHtb7oily2RkR2l4XPDts8W37h.xS', 'direccion', '2025-07-31 12:04:58'),
(6, 'celador', 'cel', '$2y$10$ADS/07jMeBy.xpk9AmAswOmcIMiVAjpbnEs3Y9fhMd/WtjFJePfWi', 'celador', '2025-07-31 13:19:19');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `historial`
--
ALTER TABLE `historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `historial_login`
--
ALTER TABLE `historial_login`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `creado_por` (`creado_por`);

--
-- Indices de la tabla `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `historial`
--
ALTER TABLE `historial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `historial_login`
--
ALTER TABLE `historial_login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `personas`
--
ALTER TABLE `personas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `historial`
--
ALTER TABLE `historial`
  ADD CONSTRAINT `historial_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `historial_login`
--
ALTER TABLE `historial_login`
  ADD CONSTRAINT `historial_login_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `notas`
--
ALTER TABLE `notas`
  ADD CONSTRAINT `notas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `personas` (`id`),
  ADD CONSTRAINT `notas_ibfk_2` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
