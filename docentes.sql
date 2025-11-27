-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 27-11-2025 a las 08:46:17
-- Versión del servidor: 5.7.23-23
-- Versión de PHP: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de datos: `iedeocci_occidente`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes`
--

CREATE TABLE `docentes` (
  `identificacion` bigint(20) DEFAULT NULL,
  `nombres` varchar(80) CHARACTER SET utf8 DEFAULT NULL,
  `correo` varchar(255) COLLATE utf8_spanish2_ci NOT NULL,
  `asignacion` varchar(15) COLLATE utf8_spanish2_ci DEFAULT NULL,
  `activo` varchar(1) CHARACTER SET utf8 DEFAULT NULL,
  `pass` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `codigoTemporal` varchar(100) COLLATE utf8_spanish2_ci NOT NULL,
  `solocitaCodigo` varchar(1) COLLATE utf8_spanish2_ci NOT NULL,
  `verEstudiantes` varchar(1) COLLATE utf8_spanish2_ci NOT NULL DEFAULT 'N',
  `maestra` varchar(20) CHARACTER SET utf8 NOT NULL,
  `banda` varchar(1) COLLATE utf8_spanish2_ci NOT NULL,
  `acceso_total` varchar(1) COLLATE utf8_spanish2_ci NOT NULL,
  `idn` longtext COLLATE utf8_spanish2_ci NOT NULL,
  `soloexcusas` varchar(1) COLLATE utf8_spanish2_ci NOT NULL DEFAULT 'N',
  `fechaactualizacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `clave_acceso` varchar(255) COLLATE utf8_spanish2_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `docentes`
--
ALTER TABLE `docentes`
  ADD UNIQUE KEY `identificacion_2` (`identificacion`),
  ADD KEY `identificacion` (`identificacion`),
  ADD KEY `identificacion_3` (`identificacion`),
  ADD KEY `asignacion` (`asignacion`),
  ADD KEY `nombres` (`nombres`);
COMMIT;
