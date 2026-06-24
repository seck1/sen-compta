CREATE TABLE IF NOT EXISTS releve_bancaire_lignes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entreprise_id INT NOT NULL,
    exercice INT NOT NULL,
    date_operation DATE NOT NULL,
    libelle VARCHAR(500) NOT NULL,
    montant DECIMAL(15,2) NOT NULL,
    sens ENUM('debit','credit') NOT NULL,
    import_ref VARCHAR(100) NULL,
    rapprochee TINYINT(1) DEFAULT 0,
    ecriture_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE,
    FOREIGN KEY (ecriture_id) REFERENCES ecritures(id) ON DELETE SET NULL,
    INDEX idx_entreprise_exercice (entreprise_id, exercice),
    INDEX idx_rapprochee (rapprochee)
);
