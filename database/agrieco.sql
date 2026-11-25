CREATE DATABASE IF NOT EXISTS agrieco
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE agrieco;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    fam_name VARCHAR(100) NOT NULL,
    phone_num VARCHAR(20) NOT NULL,
    address VARCHAR(255) DEFAULT NULL,
    mail VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    user_type ENUM('farmer', 'client', 'association') DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
