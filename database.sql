-- Database schema for Library Management System
CREATE DATABASE IF NOT EXISTS gestion_bibliotheque;
USE gestion_bibliotheque;

-- Books table
CREATE TABLE IF NOT EXISTS livres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    auteur VARCHAR(255) NOT NULL,
    isbn VARCHAR(50),
    annee_publication INT,
    categorie VARCHAR(100),
    nombre_exemplaires INT DEFAULT 1,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Emprunts table
CREATE TABLE IF NOT EXISTS emprunts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    livre_id INT NOT NULL,
    emprunteur VARCHAR(255) NOT NULL,
    date_emprunt DATE NOT NULL,
    date_retour_prevue DATE NOT NULL,
    date_retour_effective DATE NULL,
    statut ENUM('emprunté', 'retourné') DEFAULT 'emprunté',
    FOREIGN KEY (livre_id) REFERENCES livres(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Users table
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    nom_complet VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data
INSERT INTO livres (titre, auteur, isbn, annee_publication, categorie, nombre_exemplaires) VALUES
('Le Petit Prince', 'Antoine de Saint-Exupéry', '978-2070612758', 1943, 'Fiction', 5),
('1984', 'George Orwell', '978-2070368228', 1949, 'Science-Fiction', 3),
('L\'Étranger', 'Albert Camus', '978-2070360024', 1942, 'Fiction', 4);

-- Default users (password is 'admin' and 'user' respectively, hashed with password_hash)
INSERT INTO utilisateurs (username, password, role, nom_complet) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrateur'),
('user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'Utilisateur Test');
-- Note: Default password for both is 'password' (change it after first login!)

