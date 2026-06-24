USE cabinet_smc;

-- ============================================================
-- MODULE RH & PAIE
-- ============================================================

CREATE TABLE IF NOT EXISTS employes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entreprise_id INT NOT NULL,
    matricule VARCHAR(20) NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE NULL,
    lieu_naissance VARCHAR(100) NULL,
    nationalite VARCHAR(50) DEFAULT 'Sénégalaise',
    sexe ENUM('M','F') DEFAULT 'M',
    situation_familiale ENUM('celibataire','marie','divorce','veuf') DEFAULT 'celibataire',
    nombre_enfants INT DEFAULT 0,
    adresse TEXT NULL,
    telephone VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    -- Contrat
    type_contrat ENUM('CDI','CDD','Stage','Interim') DEFAULT 'CDI',
    date_embauche DATE NOT NULL,
    date_fin_contrat DATE NULL,
    poste VARCHAR(100) NULL,
    departement VARCHAR(100) NULL,
    categorie VARCHAR(50) NULL,
    -- Rémunération
    salaire_base DECIMAL(12,2) DEFAULT 0,
    sursalaire DECIMAL(12,2) DEFAULT 0,
    indemnite_logement DECIMAL(12,2) DEFAULT 0,
    indemnite_transport DECIMAL(12,2) DEFAULT 0,
    indemnite_representation DECIMAL(12,2) DEFAULT 0,
    autres_indemnites DECIMAL(12,2) DEFAULT 0,
    -- Organismes sociaux
    num_ipres VARCHAR(30) NULL,
    num_css VARCHAR(30) NULL,
    num_ipm VARCHAR(30) NULL,
    -- Banque
    banque VARCHAR(100) NULL,
    iban VARCHAR(50) NULL,
    statut ENUM('actif','inactif','suspendu') DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bulletins_paie (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entreprise_id INT NOT NULL,
    employe_id INT NOT NULL,
    user_id INT NOT NULL,
    periode_mois TINYINT NOT NULL,
    periode_annee INT NOT NULL,
    -- Éléments brut
    salaire_base DECIMAL(12,2) DEFAULT 0,
    sursalaire DECIMAL(12,2) DEFAULT 0,
    indemnite_logement DECIMAL(12,2) DEFAULT 0,
    indemnite_transport DECIMAL(12,2) DEFAULT 0,
    indemnite_representation DECIMAL(12,2) DEFAULT 0,
    autres_indemnites DECIMAL(12,2) DEFAULT 0,
    heures_supp DECIMAL(12,2) DEFAULT 0,
    primes DECIMAL(12,2) DEFAULT 0,
    salaire_brut DECIMAL(12,2) DEFAULT 0,
    -- Cotisations salariales
    ipres_salarie DECIMAL(12,2) DEFAULT 0,
    trimf DECIMAL(12,2) DEFAULT 0,
    ir_salarie DECIMAL(12,2) DEFAULT 0,
    ipm_salarie DECIMAL(12,2) DEFAULT 0,
    total_retenues DECIMAL(12,2) DEFAULT 0,
    net_a_payer DECIMAL(12,2) DEFAULT 0,
    -- Cotisations patronales
    ipres_patronal DECIMAL(12,2) DEFAULT 0,
    css_accident DECIMAL(12,2) DEFAULT 0,
    css_prestation DECIMAL(12,2) DEFAULT 0,
    css_total DECIMAL(12,2) DEFAULT 0,
    cfce DECIMAL(12,2) DEFAULT 0,
    ipm_patronal DECIMAL(12,2) DEFAULT 0,
    total_charges_patronales DECIMAL(12,2) DEFAULT 0,
    cout_total_employeur DECIMAL(12,2) DEFAULT 0,
    statut ENUM('brouillon','valide','paye') DEFAULT 'brouillon',
    date_paiement DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id),
    FOREIGN KEY (employe_id) REFERENCES employes(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_bulletin (employe_id, periode_mois, periode_annee)
);

-- ============================================================
-- MODULE FISCALITÉ
-- ============================================================

CREATE TABLE IF NOT EXISTS declarations_tva (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entreprise_id INT NOT NULL,
    user_id INT NOT NULL,
    periode_mois TINYINT NOT NULL,
    periode_annee INT NOT NULL,
    -- TVA collectée
    ca_taxable DECIMAL(15,2) DEFAULT 0,
    tva_collectee DECIMAL(15,2) DEFAULT 0,
    -- TVA déductible
    achats_taxables DECIMAL(15,2) DEFAULT 0,
    tva_deductible_biens DECIMAL(15,2) DEFAULT 0,
    tva_deductible_services DECIMAL(15,2) DEFAULT 0,
    tva_deductible_immo DECIMAL(15,2) DEFAULT 0,
    tva_deductible_total DECIMAL(15,2) DEFAULT 0,
    -- Résultat
    tva_nette DECIMAL(15,2) DEFAULT 0,
    credit_tva_anterieur DECIMAL(15,2) DEFAULT 0,
    tva_a_payer DECIMAL(15,2) DEFAULT 0,
    credit_reportable DECIMAL(15,2) DEFAULT 0,
    statut ENUM('brouillon','depose','paye') DEFAULT 'brouillon',
    date_depot DATE NULL,
    date_paiement DATE NULL,
    reference_paiement VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_tva (entreprise_id, periode_mois, periode_annee)
);

CREATE TABLE IF NOT EXISTS declarations_is (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entreprise_id INT NOT NULL,
    user_id INT NOT NULL,
    exercice INT NOT NULL,
    resultat_comptable DECIMAL(15,2) DEFAULT 0,
    reintegrations DECIMAL(15,2) DEFAULT 0,
    deductions DECIMAL(15,2) DEFAULT 0,
    resultat_fiscal DECIMAL(15,2) DEFAULT 0,
    is_du DECIMAL(15,2) DEFAULT 0,
    acomptes_verses DECIMAL(15,2) DEFAULT 0,
    is_net DECIMAL(15,2) DEFAULT 0,
    statut ENUM('brouillon','depose','paye') DEFAULT 'brouillon',
    date_depot DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id),
    UNIQUE KEY unique_is (entreprise_id, exercice)
);

CREATE TABLE IF NOT EXISTS echeances_fiscales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entreprise_id INT NOT NULL,
    type ENUM('TVA','IS','CFCE','IPRES','CSS','IR','TF','Patente','Autre') NOT NULL,
    libelle VARCHAR(200) NOT NULL,
    date_echeance DATE NOT NULL,
    montant_estime DECIMAL(15,2) NULL,
    montant_reel DECIMAL(15,2) NULL,
    statut ENUM('a_venir','en_retard','regle') DEFAULT 'a_venir',
    date_reglement DATE NULL,
    reference VARCHAR(50) NULL,
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE
);

-- ============================================================
-- MODULE MISSIONS & HONORAIRES CABINET
-- ============================================================

CREATE TABLE IF NOT EXISTS missions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entreprise_id INT NOT NULL,
    user_id INT NOT NULL,
    reference VARCHAR(30) NOT NULL,
    libelle VARCHAR(200) NOT NULL,
    type ENUM('comptabilite','audit','fiscalite','paie','conseil','autre') DEFAULT 'comptabilite',
    date_debut DATE NOT NULL,
    date_fin_prevue DATE NULL,
    date_fin_reelle DATE NULL,
    budget_heures DECIMAL(8,2) NULL,
    heures_passees DECIMAL(8,2) DEFAULT 0,
    taux_horaire DECIMAL(10,2) DEFAULT 0,
    montant_forfait DECIMAL(12,2) NULL,
    statut ENUM('planifiee','en_cours','terminee','facturee','annulee') DEFAULT 'planifiee',
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS honoraires (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entreprise_id INT NOT NULL,
    mission_id INT NULL,
    user_id INT NOT NULL,
    numero_facture VARCHAR(30) NOT NULL,
    date_facture DATE NOT NULL,
    date_echeance DATE NOT NULL,
    libelle VARCHAR(200) NOT NULL,
    montant_ht DECIMAL(12,2) DEFAULT 0,
    taux_tva DECIMAL(5,2) DEFAULT 18.00,
    montant_tva DECIMAL(12,2) DEFAULT 0,
    montant_ttc DECIMAL(12,2) DEFAULT 0,
    statut ENUM('brouillon','emise','payee','annulee') DEFAULT 'emise',
    date_paiement DATE NULL,
    mode_paiement ENUM('virement','cheque','especes','mobile_money') NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id),
    FOREIGN KEY (mission_id) REFERENCES missions(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_numero_facture (numero_facture)
);

CREATE TABLE IF NOT EXISTS honoraires_lignes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    honoraire_id INT NOT NULL,
    designation VARCHAR(200) NOT NULL,
    quantite DECIMAL(8,2) DEFAULT 1,
    prix_unitaire DECIMAL(12,2) DEFAULT 0,
    montant DECIMAL(12,2) DEFAULT 0,
    FOREIGN KEY (honoraire_id) REFERENCES honoraires(id) ON DELETE CASCADE
);

-- ============================================================
-- MODULE LETTRAGE
-- ============================================================

CREATE TABLE IF NOT EXISTS lettrages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entreprise_id INT NOT NULL,
    compte_id INT NOT NULL,
    code_lettrage VARCHAR(10) NOT NULL,
    date_lettrage DATE NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id),
    FOREIGN KEY (compte_id) REFERENCES comptes(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

ALTER TABLE lignes_ecritures ADD COLUMN IF NOT EXISTS lettrage_id INT NULL,
    ADD COLUMN IF NOT EXISTS code_lettrage VARCHAR(10) NULL;

-- ============================================================
-- MODULE CLÔTURE
-- ============================================================

CREATE TABLE IF NOT EXISTS clotures (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entreprise_id INT NOT NULL,
    exercice INT NOT NULL,
    user_id INT NOT NULL,
    date_cloture DATETIME NOT NULL,
    resultat_net DECIMAL(15,2) DEFAULT 0,
    statut ENUM('en_cours','cloture') DEFAULT 'cloture',
    note TEXT NULL,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_cloture (entreprise_id, exercice)
);

-- Numérotation automatique honoraires
INSERT IGNORE INTO entreprises (code_dossier, raison_sociale, forme_juridique, secteur_activite, exercice_courant, couleur)
VALUES ('CAB-SMC', 'Cabinet SMC', 'SA', 'Expertise comptable', 2025, '#c9a96e')
ON DUPLICATE KEY UPDATE raison_sociale = raison_sociale;

-- ============================================================
-- PARAMÈTRES PAIE PAR ENTREPRISE
-- ============================================================
CREATE TABLE IF NOT EXISTS paie_parametres (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entreprise_id INT NOT NULL UNIQUE,
    -- IPRES
    ipres_salarie_a DECIMAL(6,4) DEFAULT 0.0560,
    ipres_patronal_a DECIMAL(6,4) DEFAULT 0.0840,
    ipres_salarie_b DECIMAL(6,4) DEFAULT 0.0240,
    ipres_patronal_b DECIMAL(6,4) DEFAULT 0.0360,
    plafond_ipres_a INT DEFAULT 768000,
    -- CSS
    css_accidents_travail DECIMAL(6,4) DEFAULT 0.0300,
    css_prestations_fam DECIMAL(6,4) DEFAULT 0.0700,
    css_plafond_pf INT DEFAULT 63000,
    -- CFCE
    cfce_taux DECIMAL(6,4) DEFAULT 0.0300,
    -- IPM
    ipm_salarie DECIMAL(6,4) DEFAULT 0.0050,
    ipm_patronal DECIMAL(6,4) DEFAULT 0.0300,
    -- Informations organisme
    num_ipres_entreprise VARCHAR(30) NULL,
    num_css_entreprise VARCHAR(30) NULL,
    num_ipm_entreprise VARCHAR(30) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE
);
