-- Tracabilite des demandes RGPD (droit a l'oubli, acces, portabilite)
-- Preuve de conformite : chaque demande d'un utilisateur est journalisee.
CREATE TABLE IF NOT EXISTS rgpd_demandes (
    id           INT(11) NOT NULL AUTO_INCREMENT,
    user_id      INT(11) DEFAULT NULL,        -- compte cabinet concerne
    client_id    INT(11) DEFAULT NULL,        -- ou compte portail_clients concerne
    email        VARCHAR(150) NOT NULL,       -- email du demandeur (trace meme si compte supprime)
    type         ENUM('suppression','export','rectification','opposition') NOT NULL,
    statut       ENUM('nouvelle','en_cours','traitee','refusee') DEFAULT 'nouvelle',
    message      TEXT DEFAULT NULL,           -- precision du demandeur
    note_admin   TEXT DEFAULT NULL,           -- traitement par l'editeur
    ip           VARCHAR(45) DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    traitee_at   DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_statut (statut),
    KEY idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
