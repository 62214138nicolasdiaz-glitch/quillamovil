-- Base de datos para Quilla Móvil
-- Sistema de gestión de conductores y vehículos

-- Agregar columna de contraseña a la tabla conductor
-- Ejecutar este script en phpMyAdmin o MySQL

ALTER TABLE conductor ADD COLUMN password VARCHAR(255) DEFAULT NULL AFTER foto;

-- Cambiar restricción UNIQUE de DNI para permitir duplicados entre tablas
-- (los conductores pueden tener el mismo DNI que otros registros en otras tablas)


CREATE DATABASE IF NOT EXISTS quillamovil;
USE quillamovil;

-- ============================================
-- TABLA DE REGISTRO DE CAMIONES
-- ============================================
CREATE TABLE IF NOT EXISTS camion (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dni VARCHAR(12) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    telefono VARCHAR(15) NOT NULL,
    estado VARCHAR(10) NOT NULL,
    direccion_actual TEXT NULL,
    latitud DECIMAL(10,7) DEFAULT NULL,
    longitud DECIMAL(10,7) DEFAULT NULL,
    consentimiento_ubicacion TINYINT(1) DEFAULT 0,
    fecha_consentimiento DATETIME NULL
);

-- ============================================
-- TABLA DE CONDUCTORES
-- ============================================
CREATE TABLE IF NOT EXISTS conductor (
    id INT PRIMARY KEY AUTO_INCREMENT,
     foto VARCHAR(255) NULL,
    dni VARCHAR(12) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    telefono VARCHAR(15) NOT NULL,
    vehiculo VARCHAR(50),
    tipo_lic VARCHAR(10),
    numero_censo VARCHAR(50),
    color VARCHAR(50),
    estado VARCHAR(10) NOT NULL
);

-- ============================================
-- TABLA DE REGISTRO DE MOTOCARGA
-- ============================================
CREATE TABLE IF NOT EXISTS motocarga (
    
    id INT PRIMARY KEY AUTO_INCREMENT,
    dni VARCHAR(12) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    telefono VARCHAR(15) NOT NULL,
    estado VARCHAR(10) NOT NULL,
    direccion_actual TEXT NULL,
    latitud DECIMAL(10,7) DEFAULT NULL,
    longitud DECIMAL(10,7) DEFAULT NULL,
    consentimiento_ubicacion TINYINT(1) DEFAULT 0,
    fecha_consentimiento DATETIME NULL
);

-- ============================================
CREATE TABLE IF NOT EXISTS toro (
    
    id INT PRIMARY KEY AUTO_INCREMENT,
    dni VARCHAR(12) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    telefono VARCHAR(15) NOT NULL,
    estado VARCHAR(10) NOT NULL,
    direccion_actual TEXT NULL,
    latitud DECIMAL(10,7) DEFAULT NULL,
    longitud DECIMAL(10,7) DEFAULT NULL,
    consentimiento_ubicacion TINYINT(1) DEFAULT 0,
    fecha_consentimiento DATETIME NULL
);

-- Si ya tiene la tabla toro existente, ejecute estas consultas en MySQL para agregar las columnas de ubicación:
-- ALTER TABLE toro ADD COLUMN latitud DECIMAL(10,7) DEFAULT NULL AFTER estado;
-- ALTER TABLE toro ADD COLUMN longitud DECIMAL(10,7) DEFAULT NULL AFTER latitud;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `oauth_provider` varchar(50) DEFAULT NULL,
  `oauth_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$IV34ImcNyzfnvU6TCrSCpOihOIyu0cVrM5lDoF0zXJJr9ihgByv0O');

-- ============================================
-- TABLA DE RESERVAS
-- ============================================
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(150) NOT NULL,
    origen VARCHAR(150) NOT NULL,
    destino VARCHAR(150) NOT NULL,
    fecha_reserva DATE NOT NULL,
    hora_reserva TIME NOT NULL,
    conductor_nombre VARCHAR(150) NOT NULL,
    conductor_apellido VARCHAR(150) NOT NULL,
    conductor_telefono VARCHAR(20),
    status VARCHAR(20) NOT NULL DEFAULT 'Pendiente'
);

