-- Module Portail Client — acces securise des clients du cabinet a leur dossier
-- Un client = un acces lie a UNE entreprise (dossier). Lecture seule + depot de pieces.

-- 1) Comptes clients du portail (separes des users cabinet)
CREATE TABLE IF NOT EXISTS portail_clients (
    id              INT(11) NOT NULL AUTO_INCREMENT,
    entreprise_id   INT(11) NOT NULL,
    nom             VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NOT NULL,
    password        VARCHAR(255) NOT NULL,
    telephone       VARCHAR(20) DEFAULT NULL,
    actif           TINYINT(1) DEFAULT 1,
    -- ce que le cabinet autorise a partager (1=visible cote client)
    voir_etats      TINYINT(1) DEFAULT 1,   -- etats financiers (bilan, CR)
    voir_honoraires TINYINT(1) DEFAULT 1,   -- factures d'honoraires
    permet_depot    TINYINT(1) DEFAULT 1,   -- depot de pieces
    derniere_connexion DATETIME DEFAULT NULL,
    cree_par        INT(11) DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_email (email),
    KEY idx_entreprise (entreprise_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Pieces deposees par les clients (a traiter par le cabinet)
CREATE TABLE IF NOT EXISTS portail_depots (
    id              INT(11) NOT NULL AUTO_INCREMENT,
    entreprise_id   INT(11) NOT NULL,
    client_id       INT(11) NOT NULL,
    fichier         VARCHAR(255) NOT NULL,   -- nom du fichier stocke
    nom_original    VARCHAR(255) NOT NULL,
    libelle         VARCHAR(255) DEFAULT NULL,
    taille          INT(11) DEFAULT 0,
    statut          ENUM('nouveau','traite','rejete') DEFAULT 'nouveau',
    note_cabinet    TEXT DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_entreprise (entreprise_id),
    KEY idx_client (client_id),
    KEY idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
