-- Migration v3 — Senegal Fiscal Regimes
-- Cabinet SMC — 2026

-- NOTE: If regime_fiscal already exists with old values (reel/simplifie/forfait), run:
-- ALTER TABLE entreprises MODIFY COLUMN regime_fiscal ENUM('CGI','CGU','RNS','MICRO','EXONERE','reel','simplifie','forfait') DEFAULT 'CGI';
-- UPDATE entreprises SET regime_fiscal='CGI' WHERE regime_fiscal='reel';
-- UPDATE entreprises SET regime_fiscal='CGU' WHERE regime_fiscal='simplifie';
-- UPDATE entreprises SET regime_fiscal='MICRO' WHERE regime_fiscal='forfait';
-- ALTER TABLE entreprises MODIFY COLUMN regime_fiscal ENUM('CGI','CGU','RNS','MICRO','EXONERE') DEFAULT 'CGI';

ALTER TABLE entreprises
  ADD COLUMN IF NOT EXISTS regime_fiscal ENUM('CGI','CGU','RNS','MICRO','EXONERE') DEFAULT 'CGI',
  ADD COLUMN IF NOT EXISTS ca_annuel_estime DECIMAL(15,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS secteur_activite_detail VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS numero_contribuable VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS numero_registre_commerce VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS regime_tva ENUM('mensuel','trimestriel','annuel','non_assujetti') DEFAULT 'mensuel',
  ADD COLUMN IF NOT EXISTS date_debut_exoneration DATE NULL,
  ADD COLUMN IF NOT EXISTS date_fin_exoneration DATE NULL;

-- Table for CGU declarations
CREATE TABLE IF NOT EXISTS declarations_cgu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entreprise_id INT NOT NULL,
    user_id INT NOT NULL,
    annee INT NOT NULL,
    ca_ttc DECIMAL(15,2) NOT NULL DEFAULT 0,
    ca_ht DECIMAL(15,2) NOT NULL DEFAULT 0,
    secteur VARCHAR(50) NOT NULL DEFAULT 'commerce',
    cgu_base DECIMAL(15,2) NOT NULL DEFAULT 0,
    minimum_secteur DECIMAL(15,2) NOT NULL DEFAULT 0,
    cgu_due DECIMAL(15,2) NOT NULL DEFAULT 0,
    acompte_t1 DECIMAL(15,2) NOT NULL DEFAULT 0,
    acompte_t2 DECIMAL(15,2) NOT NULL DEFAULT 0,
    acompte_t3 DECIMAL(15,2) NOT NULL DEFAULT 0,
    solde DECIMAL(15,2) NOT NULL DEFAULT 0,
    statut ENUM('brouillon','depose','paye') DEFAULT 'brouillon',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_entreprise_annee (entreprise_id, annee),
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
