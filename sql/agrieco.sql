CREATE DATABASE IF NOT EXISTS agrieco CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE agrieco;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('agriculteur','client','association','admin') NOT NULL DEFAULT 'client',
    can_write TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO users (username, email, password, role, can_write)
VALUES (
    'admin',
    'admin@agrieco.tn',
    '$2b$10$5K4DgXhuS5A3oAQAs7VGVukB/gZYaDijfN26n8F2sy.8ZK6W7Qete',
    'admin',
    1
);