-- =====================================================================
--  Base de datos: proyecto_multiframework
--  Empresa: VoltRider Motors International (vehículos y motos eléctricas)
--  Motor: MySQL / MariaDB (XAMPP)  |  Cotejamiento: utf8mb4_unicode_ci
--  Compartida por: Laravel (admin-core) y Slim (api-servicios)
--
--  USO: phpMyAdmin > pestaña "Importar" > seleccionar este archivo > Continuar
--  (el script crea la base de datos, las tablas y los datos de ejemplo)
-- =====================================================================
CREATE DATABASE IF NOT EXISTS proyecto_multiframework
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE proyecto_multiframework;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS movimientos_inventario;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE usuarios (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL,
  password VARCHAR(255) NOT NULL,
  rol ENUM('admin','operador') NOT NULL DEFAULT 'operador',
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY usuarios_email_unique (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categorias (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  descripcion VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY categorias_nombre_unique (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE productos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  sku VARCHAR(40) NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  categoria_id BIGINT UNSIGNED NOT NULL,
  descripcion TEXT NULL,
  precio DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  stock_minimo INT UNSIGNED NOT NULL DEFAULT 5,
  planta VARCHAR(80) NOT NULL,
  autonomia_km SMALLINT UNSIGNED NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY productos_sku_unique (sku),
  KEY productos_categoria_id_index (categoria_id),
  CONSTRAINT productos_categoria_fk FOREIGN KEY (categoria_id)
    REFERENCES categorias (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE movimientos_inventario (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  producto_id BIGINT UNSIGNED NOT NULL,
  usuario_id BIGINT UNSIGNED NULL,
  tipo ENUM('salida','entrada','ajuste') NOT NULL DEFAULT 'salida',
  cantidad INT UNSIGNED NOT NULL,
  stock_resultante INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY mov_producto_idx (producto_id),
  KEY mov_usuario_idx (usuario_id),
  CONSTRAINT mov_producto_fk FOREIGN KEY (producto_id)
    REFERENCES productos (id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT mov_usuario_fk FOREIGN KEY (usuario_id)
    REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------- DATOS DE EJEMPLO ----------------------------
-- Contraseñas (hash bcrypt compatible con Laravel y password_verify de PHP):
--   admin@voltrider.com    ->  Admin12345
--   operador@voltrider.com ->  Operador12345
INSERT INTO usuarios (nombre,email,password,rol,created_at,updated_at) VALUES
('Administrador General','admin@voltrider.com','$2y$12$IIs38HLXGkWKM2fiIY3jd.ruuzxmkprjovorWcauldpkWgy.S9F1u','admin',NOW(),NOW()),
('Operador de Bodega','operador@voltrider.com','$2y$12$/Hu797KrugEII3YS/AZ3Hecb1gZSCj6lCBBrlGXRJ9pd5kkjnrjDO','operador',NOW(),NOW());

INSERT INTO categorias (nombre,descripcion,created_at,updated_at) VALUES
('Automóviles eléctricos','Sedanes, SUV y pickups 100% eléctricos',NOW(),NOW()),
('Motocicletas eléctricas','Motos y scooters eléctricos urbanos y de carretera',NOW(),NOW()),
('Baterías y celdas','Paquetes de baterías de litio y celdas de repuesto',NOW(),NOW()),
('Cargadores y energía','Cargadores de pared, rápidos y portátiles',NOW(),NOW()),
('Repuestos y accesorios','Motores, controladores, neumáticos y accesorios',NOW(),NOW());

INSERT INTO productos (sku,nombre,categoria_id,descripcion,precio,stock,stock_minimo,planta,autonomia_km,created_at,updated_at) VALUES
('VR-AUT-001','VoltRider Aero S Sedán',1,'Sedán eléctrico de 5 plazas, tracción trasera',38900.00,42,10,'Medellín, Colombia',520,NOW(),NOW()),
('VR-AUT-002','VoltRider Terra X SUV',1,'SUV eléctrico AWD con batería de 95 kWh',54900.00,27,8,'Monterrey, México',610,NOW(),NOW()),
('VR-AUT-003','VoltRider Cargo T Pickup',1,'Pickup eléctrica para flotas comerciales',61500.00,12,5,'Stuttgart, Alemania',480,NOW(),NOW()),
('VR-AUT-004','VoltRider City Mini',1,'Citycar eléctrico compacto de 4 plazas',24900.00,63,15,'Shenzhen, China',330,NOW(),NOW()),
('VR-MOT-001','Pulse 3000 Urbana',2,'Moto eléctrica urbana de 3 kW',3490.00,150,30,'Pune, India',110,NOW(),NOW()),
('VR-MOT-002','Pulse 8000 Sport',2,'Moto eléctrica deportiva de 8 kW',7990.00,64,15,'Medellín, Colombia',190,NOW(),NOW()),
('VR-MOT-003','Pulse Delivery Pro',2,'Moto eléctrica para reparto con batería doble',4590.00,98,20,'Pune, India',160,NOW(),NOW()),
('VR-MOT-004','Scooter Neo 1500',2,'Scooter eléctrico plegable de 1.5 kW',1890.00,220,40,'Shenzhen, China',70,NOW(),NOW()),
('VR-BAT-001','Batería LFP 60 kWh',3,'Paquete de baterías LFP para automóviles',6200.00,35,10,'Shenzhen, China',NULL,NOW(),NOW()),
('VR-BAT-002','Batería Li-ion 3 kWh Moto',3,'Paquete extraíble para motocicletas',780.00,310,60,'Pune, India',NULL,NOW(),NOW()),
('VR-CAR-001','Cargador de pared 11 kW',4,'Wallbox monofásico/trifásico para hogar',620.00,180,40,'Stuttgart, Alemania',NULL,NOW(),NOW()),
('VR-CAR-002','Cargador rápido DC 60 kW',4,'Estación de carga rápida comercial',14800.00,9,5,'Stuttgart, Alemania',NULL,NOW(),NOW()),
('VR-REP-001','Motor eléctrico 150 kW',5,'Motor síncrono de imanes permanentes',2950.00,4,6,'Monterrey, México',NULL,NOW(),NOW()),
('VR-REP-002','Controlador inversor 400 V',5,'Inversor de potencia para vehículos',1350.00,52,12,'Shenzhen, China',NULL,NOW(),NOW());
