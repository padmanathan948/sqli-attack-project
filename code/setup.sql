-- Mini App Database Setup
-- Creates a small standalone database + table used by both
-- vulnerable-app and fixed-app, so the same data can be attacked
-- in one version and safely queried in the other.

CREATE DATABASE IF NOT EXISTS miniapp;
USE miniapp;

CREATE TABLE IF NOT EXISTS app_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user'
);

-- Sample accounts (plaintext on purpose, to mirror a simple
-- teaching example; a real app would always hash passwords)
INSERT INTO app_users (username, password, role) VALUES
('admin',  'S3cur3P@ss!', 'admin'),
('john',   'password123', 'user'),
('mary',   'letmein2024', 'user');

CREATE USER IF NOT EXISTS 'miniapp'@'127.0.0.1' IDENTIFIED BY 'miniapp_pw';
GRANT ALL PRIVILEGES ON miniapp.* TO 'miniapp'@'127.0.0.1';
FLUSH PRIVILEGES;
