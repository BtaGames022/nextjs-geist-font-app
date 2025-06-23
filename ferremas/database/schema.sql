CREATE DATABASE IF NOT EXISTS ferremas_db;
USE ferremas_db;

CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Insert default roles
INSERT INTO roles (name) VALUES 
('administrator'),
('seller'),
('warehouse'),
('accountant');

-- Insert default categories
INSERT INTO categories (name, description) VALUES 
('Herramientas Manuales', 'Martillos, destornilladores, llaves, etc.'),
('Herramientas Eléctricas', 'Taladros, sierras, lijadoras, etc.'),
('Materiales Básicos', 'Cemento, arena, ladrillos, pinturas'),
('Equipos de Seguridad', 'Cascos, guantes, lentes de seguridad');
