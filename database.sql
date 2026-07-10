CREATE DATABASE IF NOT EXISTS streammatch;
USE streammatch;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('usuario', 'administrador') DEFAULT 'usuario',
    tema VARCHAR(10) DEFAULT 'light',
    intentos_fallidos INT DEFAULT 0,
    bloqueado_hasta DATETIME NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS generos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS preferencias_usuario (
    usuario_id INT NOT NULL,
    genero_id INT NOT NULL,
    PRIMARY KEY (usuario_id, genero_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (genero_id) REFERENCES generos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS contenido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    tipo ENUM('pelicula', 'serie') NOT NULL,
    descripcion TEXT,
    poster_url VARCHAR(255),
    api_id VARCHAR(50), 
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contenido_generos (
    contenido_id INT NOT NULL,
    genero_id INT NOT NULL,
    PRIMARY KEY (contenido_id, genero_id),
    FOREIGN KEY (contenido_id) REFERENCES contenido(id) ON DELETE CASCADE,
    FOREIGN KEY (genero_id) REFERENCES generos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS historial_vistas (
    usuario_id INT NOT NULL,
    contenido_id INT NOT NULL,
    visto_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, contenido_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (contenido_id) REFERENCES contenido(id) ON DELETE CASCADE
);

-- Insertar usuario administrador por defecto (password: admin123)
INSERT INTO usuarios (nombre, email, password, rol) VALUES 
('Administrador', 'admin@streammatch.com', '$2y$10$wO8q9rS3w9c7Z0hE.2vI6uyg0K.hP3L4G.O2rT0d1Z.zN4P4V4HMO', 'administrador')
ON DUPLICATE KEY UPDATE id=id;

-- Insertar géneros iniciales
INSERT IGNORE INTO generos (nombre) VALUES 
('Action'), ('Comedy'), ('Drama'), ('Fantasy'), ('Horror'), ('Mystery'), ('Romance'), ('Sci-Fi'), ('Thriller'), ('Crime');
