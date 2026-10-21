-- =============================================================
-- MÓDULO DE PEDIDOS - UVA3 LOGÍSTICA
-- Script SQL: Creación de tablas y datos de prueba
-- =============================================================

CREATE DATABASE IF NOT EXISTS modulo_pedidos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE modulo_pedidos;

-- -------------------------------------------------------------
-- Tabla: clientes
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(150)        NOT NULL,
    email       VARCHAR(200)        NOT NULL UNIQUE,
    telefono    VARCHAR(20)         NULL,
    direccion   VARCHAR(255)        NULL,
    ciudad      VARCHAR(100)        NULL,
    creado_en   TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- Tabla: pedidos
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pedidos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id      INT UNSIGNED        NOT NULL,
    descripcion     VARCHAR(500)        NOT NULL,
    total           DECIMAL(12, 2)      NOT NULL DEFAULT 0.00,
    estado          ENUM('pendiente','despachado','entregado') NOT NULL DEFAULT 'pendiente',
    costo_envio     DECIMAL(10, 2)      NULL COMMENT 'Se calcula al despachar',
    ciudad_destino  VARCHAR(100)        NULL,
    creado_en       TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en  TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedidos_cliente
        FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- Tabla: usuarios (para manejo básico de admin)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(150)    NOT NULL,
    email       VARCHAR(200)    NOT NULL UNIQUE,
    password    VARCHAR(255)    NOT NULL,
    rol         ENUM('admin','operador') NOT NULL DEFAULT 'operador',
    creado_en   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================================
-- DATOS DE PRUEBA
-- =============================================================

-- Clientes de ejemplo
INSERT INTO clientes (nombre, email, telefono, direccion, ciudad) VALUES
('Almacenes El Progreso',    'progreso@ejemplo.com',     '3101234567', 'Cra 15 #45-23',    'Bogotá'),
('Distribuidora Norte S.A.', 'norte@ejemplo.com',        '3207654321', 'Calle 80 #12-45',  'Medellín'),
('Textiles del Valle',       'textiles@ejemplo.com',     '3154443322', 'Av. 4N #24-80',    'Cali'),
('Ferretería Central',       'ferreteria@ejemplo.com',   '3009876543', 'Cra 5 #30-10',     'Barranquilla'),
('Supermercado La Cosecha',  'cosecha@ejemplo.com',      '3118765432', 'Calle 10 #5-55',   'Bucaramanga');

-- Pedidos de ejemplo (varios meses para probar el reporte)
INSERT INTO pedidos (cliente_id, descripcion, total, estado, ciudad_destino, creado_en) VALUES
-- Mes actual - pendientes
(1, 'Caja de electrodomésticos mixtos (50 unidades)',    1500000.00, 'pendiente',   'Medellín',     NOW()),
(2, 'Paquete ropa temporada otoño (200 prendas)',         850000.00, 'pendiente',   'Bogotá',       NOW()),
(3, 'Artículos de ferretería varios (1 pallet)',          320000.00, 'pendiente',   'Cali',         NOW()),
-- Mes actual - despachados
(1, 'Televisores 55" (10 unidades)',                     4200000.00, 'despachado',  'Cali',         NOW()),
(4, 'Tornillos y herrajes varios (500 kg)',               175000.00, 'despachado',  'Bogotá',       NOW()),
-- Mes actual - entregados
(1, 'Neveras doble puerta (5 unidades)',                 6750000.00, 'entregado',   'Medellín',     NOW()),
(2, 'Colchones dobles (20 unidades)',                    2100000.00, 'entregado',   'Barranquilla', NOW()),
(5, 'Alimentos no perecederos (3 toneladas)',             980000.00, 'entregado',   'Bucaramanga',  NOW()),
(3, 'Telas importadas (500 metros)',                     1350000.00, 'entregado',   'Bogotá',       NOW()),
(4, 'Pintura industrial (100 galones)',                   690000.00, 'entregado',   'Medellín',     NOW()),
-- Mes anterior (para que no aparezca en el reporte del mes actual)
(1, 'Lavadoras automáticas (8 unidades)',                3200000.00, 'entregado',   'Cali',         DATE_SUB(NOW(), INTERVAL 40 DAY)),
(2, 'Muebles de oficina (set completo)',                 1850000.00, 'entregado',   'Bogotá',       DATE_SUB(NOW(), INTERVAL 35 DAY));

-- Usuario administrador (password: admin123)
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Administrador',   'admin@uva3.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uAiGSP9o2', 'admin'),
('Operador UVA3',   'operador@uva3.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uAiGSP9o2', 'operador');
-- Nota: El hash corresponde a la contraseña "password" (bcrypt)
-- Para producción: generar hash real con password_hash()
