CREATE DATABASE IF NOT EXISTS auth_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE auth_demo;

CREATE TABLE IF NOT EXISTS usuarios (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  email       VARCHAR(255) NOT NULL UNIQUE,
  senha_hash  VARCHAR(255) NOT NULL,
  perfil      ENUM('admin','user') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- test user
INSERT INTO usuarios (email, senha_hash, perfil) VALUES
('test@example.com', '$2y$10$wH3x0bQp1H6qz2uQ9k9fR.8b4yQ6W7ZqG2i3x4y5z6A7b8c9d0eFG', 'admin');

CREATE TABLE IF NOT EXISTS items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT,
  created_by VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;