-- Avoirs commerciaux (notes de crédit) : annulent tout ou partie d'une facture.
-- Idempotent : peut être rejoué sans risque.

CREATE TABLE IF NOT EXISTS `avoirs_commercial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(30) NOT NULL,
  `facture_id` int(11) NOT NULL,
  `prospect_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_avoir` date NOT NULL,
  `motif` enum('retour','remboursement','geste_commercial','erreur','autre') DEFAULT 'autre',
  `raison` text DEFAULT NULL,
  `montant_ht` decimal(15,2) DEFAULT 0.00,
  `taux_tva` decimal(5,2) DEFAULT 18.00,
  `montant_tva` decimal(15,2) DEFAULT 0.00,
  `montant_ttc` decimal(15,2) DEFAULT 0.00,
  `statut` enum('emis','annule') DEFAULT 'emis',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`),
  KEY `idx_avoir_facture` (`facture_id`),
  KEY `idx_avoir_prospect` (`prospect_id`),
  CONSTRAINT `avoirs_fk_facture` FOREIGN KEY (`facture_id`) REFERENCES `factures_commercial` (`id`) ON DELETE CASCADE,
  CONSTRAINT `avoirs_fk_prospect` FOREIGN KEY (`prospect_id`) REFERENCES `prospects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `avoirs_commercial_lignes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `avoir_id` int(11) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quantite` decimal(10,3) DEFAULT 1.000,
  `unite` varchar(30) DEFAULT 'forfait',
  `prix_unitaire` decimal(15,2) DEFAULT 0.00,
  `remise` decimal(5,2) DEFAULT 0.00,
  `tva_taux` decimal(5,2) DEFAULT 18.00,
  `montant_ht` decimal(15,2) DEFAULT 0.00,
  `ordre` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_avoirligne_avoir` (`avoir_id`),
  CONSTRAINT `avoirs_lignes_fk` FOREIGN KEY (`avoir_id`) REFERENCES `avoirs_commercial` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
