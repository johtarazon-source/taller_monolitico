CREATE DATABASE IF NOT EXISTS alquiler CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE alquiler;

CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    contacto VARCHAR(120) NOT NULL,
    licencia VARCHAR(80) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS vehiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marca VARCHAR(80) NOT NULL,
    modelo VARCHAR(80) NOT NULL,
    anio INT NOT NULL,
    categoria VARCHAR(60) NOT NULL,
    estado ENUM('disponible', 'alquilado', 'mantenimiento') NOT NULL DEFAULT 'disponible'
);

CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_vehiculo INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    fecha_devolucion DATE NULL,
    estado ENUM('activa', 'finalizada', 'cancelada') NOT NULL DEFAULT 'activa',
    CONSTRAINT fk_reserva_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id),
    CONSTRAINT fk_reserva_vehiculo FOREIGN KEY (id_vehiculo) REFERENCES vehiculos(id)
);

INSERT INTO clientes (nombre, contacto, licencia)
VALUES
('Ana Lopez', 'ana@email.com', 'LIC-1001'),
('Carlos Perez', '3001234567', 'LIC-1002')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT INTO vehiculos (marca, modelo, anio, categoria, estado)
VALUES
('Toyota', 'Corolla', 2022, 'sedan', 'disponible'),
('Chevrolet', 'Tracker', 2023, 'suv', 'disponible'),
('Renault', 'Kwid', 2021, 'economico', 'mantenimiento');
