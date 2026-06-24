-- Cabinet SMC - Base de données
-- Version 1.0 | 2025

CREATE DATABASE IF NOT EXISTS cabinet_smc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cabinet_smc;

-- Utilisateurs (admin + collaborateurs)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'collaborateur', 'superviseur') DEFAULT 'collaborateur',
    avatar VARCHAR(255) NULL,
    telephone VARCHAR(20) NULL,
    actif TINYINT(1) DEFAULT 1,
    derniere_connexion DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Entreprises clientes (dossiers)
CREATE TABLE entreprises (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code_dossier VARCHAR(20) UNIQUE NOT NULL,
    raison_sociale VARCHAR(200) NOT NULL,
    forme_juridique ENUM('SA','SARL','SAS','GIE','EI','SUARL','Autre') DEFAULT 'SARL',
    ninea VARCHAR(50) NULL,
    rccm VARCHAR(50) NULL,
    secteur_activite VARCHAR(100) NULL,
    adresse TEXT NULL,
    telephone VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    exercice_courant INT DEFAULT (YEAR(CURDATE())),
    date_creation DATE NULL,
    regime_fiscal ENUM('reel','simplifie','forfait') DEFAULT 'reel',
    statut ENUM('actif','archive','suspendu') DEFAULT 'actif',
    couleur VARCHAR(7) DEFAULT '#1e3a5f',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Assignation collaborateurs → entreprises
CREATE TABLE user_entreprises (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    entreprise_id INT NOT NULL,
    role ENUM('lecteur','saisie','superviseur') DEFAULT 'saisie',
    date_assignation DATE DEFAULT (CURRENT_DATE),
    actif TINYINT(1) DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_entreprise (user_id, entreprise_id)
);

-- Plan comptable OHADA
CREATE TABLE comptes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entreprise_id INT NOT NULL,
    numero VARCHAR(10) NOT NULL,
    intitule VARCHAR(200) NOT NULL,
    type_compte ENUM('actif','passif','charge','produit','bilan') NOT NULL,
    classe TINYINT NOT NULL,
    compte_parent VARCHAR(10) NULL,
    solde_debit DECIMAL(15,2) DEFAULT 0.00,
    solde_credit DECIMAL(15,2) DEFAULT 0.00,
    actif TINYINT(1) DEFAULT 1,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE,
    UNIQUE KEY unique_compte_entreprise (entreprise_id, numero)
);

-- Journaux comptables
CREATE TABLE journaux (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entreprise_id INT NOT NULL,
    code VARCHAR(10) NOT NULL,
    libelle VARCHAR(100) NOT NULL,
    type ENUM('achat','vente','banque','caisse','operations_diverses','paie') NOT NULL,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE,
    UNIQUE KEY unique_journal_entreprise (entreprise_id, code)
);

-- Écritures comptables
CREATE TABLE ecritures (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entreprise_id INT NOT NULL,
    journal_id INT NOT NULL,
    user_id INT NOT NULL,
    numero_piece VARCHAR(50) NULL,
    date_ecriture DATE NOT NULL,
    date_valeur DATE NULL,
    libelle VARCHAR(255) NOT NULL,
    exercice INT NOT NULL,
    periode TINYINT NOT NULL,
    statut ENUM('brouillon','validee','cloturee') DEFAULT 'brouillon',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id),
    FOREIGN KEY (journal_id) REFERENCES journaux(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Lignes d'écritures
CREATE TABLE lignes_ecritures (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ecriture_id INT NOT NULL,
    compte_id INT NOT NULL,
    libelle VARCHAR(255) NOT NULL,
    debit DECIMAL(15,2) DEFAULT 0.00,
    credit DECIMAL(15,2) DEFAULT 0.00,
    tiers VARCHAR(100) NULL,
    FOREIGN KEY (ecriture_id) REFERENCES ecritures(id) ON DELETE CASCADE,
    FOREIGN KEY (compte_id) REFERENCES comptes(id)
);

-- Exercices comptables
CREATE TABLE exercices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entreprise_id INT NOT NULL,
    annee INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut ENUM('ouvert','cloture','archive') DEFAULT 'ouvert',
    date_cloture DATETIME NULL,
    user_cloture INT NULL,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id),
    UNIQUE KEY unique_exercice (entreprise_id, annee)
);

-- Échéances fiscales
CREATE TABLE echeances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entreprise_id INT NOT NULL,
    type ENUM('TVA','IS','IPRES','CSS','IR','CFCE','Autre') NOT NULL,
    libelle VARCHAR(200) NOT NULL,
    date_echeance DATE NOT NULL,
    montant DECIMAL(15,2) NULL,
    statut ENUM('en_attente','regle','en_retard') DEFAULT 'en_attente',
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE
);

-- Journal d'audit
CREATE TABLE audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    entreprise_id INT NULL,
    action VARCHAR(100) NOT NULL,
    table_cible VARCHAR(50) NULL,
    enregistrement_id INT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Notifications
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    titre VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info','warning','danger','success') DEFAULT 'info',
    lu TINYINT(1) DEFAULT 0,
    lien VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- DONNÉES INITIALES
-- =============================================

-- Admin par défaut (password: Admin@SMC2025)
INSERT INTO users (nom, prenom, email, password, role) VALUES
('SECK', 'Mor', 'admin@cabinet-smc.sn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uZutLfNUi', 'admin');

-- Entreprises exemples
INSERT INTO entreprises (code_dossier, raison_sociale, forme_juridique, ninea, secteur_activite, exercice_courant, couleur) VALUES
('DSS-001', 'Dakar Services SARL', 'SARL', '12345678-2-D5', 'Commerce général', 2025, '#1e3a5f'),
('AGR-002', 'AgroSénégal SA', 'SA', '87654321-1-A3', 'Agriculture', 2025, '#2d6a4f'),
('IMP-003', 'Import Express SUARL', 'SUARL', '11223344-3-B2', 'Import/Export', 2025, '#c9630e'),
('BTP-004', 'BuildSen Construction', 'SA', '44332211-2-C1', 'BTP', 2025, '#7b2d8b'),
('REST-005', 'Les Délices de Dakar', 'SARL', '55667788-1-E4', 'Restauration', 2025, '#c0392b');
