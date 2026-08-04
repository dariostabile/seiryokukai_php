CREATE DATABASE IF NOT EXISTS seiryokukai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE seiryokukai;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  role VARCHAR(80) NOT NULL DEFAULT 'Amministratore',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS clients (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(140) NOT NULL,
  plan ENUM('Mensile', 'Trimestrale', 'Annuale') NOT NULL DEFAULT 'Mensile',
  status ENUM('Attivo', 'Sospeso') NOT NULL DEFAULT 'Attivo',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO users (username, password_hash, full_name, role)
SELECT 'admin', '$2y$10$VBrViS/PlPGHhdaW0YyUXevmo2usN/4emZbeckXryeulmGF7.EvsG', 'Admin Seiryokukai', 'Amministratore'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');

INSERT INTO clients (name, plan, status)
SELECT 'Mario Rossi', 'Mensile', 'Attivo'
WHERE NOT EXISTS (SELECT 1 FROM clients WHERE name = 'Mario Rossi' AND plan = 'Mensile');

INSERT INTO clients (name, plan, status)
SELECT 'Laura Bianchi', 'Trimestrale', 'Attivo'
WHERE NOT EXISTS (SELECT 1 FROM clients WHERE name = 'Laura Bianchi' AND plan = 'Trimestrale');

INSERT INTO clients (name, plan, status)
SELECT 'Giulia Verdi', 'Mensile', 'Sospeso'
WHERE NOT EXISTS (SELECT 1 FROM clients WHERE name = 'Giulia Verdi' AND plan = 'Mensile');
