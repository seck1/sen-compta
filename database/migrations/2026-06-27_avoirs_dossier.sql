-- Avoirs de vente côté DOSSIER (entreprise gérée) : note de crédit comptable.
-- Un avoir = écriture d'extourne (inverse) de la facture de vente d'origine.
-- Idempotent.

CREATE TABLE IF NOT EXISTS `avoirs_dossier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entreprise_id` int(11) NOT NULL,
  `numero` varchar(30) NOT NULL,
  `ecriture_origine_id` int(11) DEFAULT NULL,   -- l'écriture de vente d'origine
  `ecriture_avoir_id` int(11) DEFAULT NULL,      -- la contre-écriture créée
  `numero_facture_origine` varchar(100) DEFAULT NULL,
  `exercice` int(11) NOT NULL,
  `date_avoir` date NOT NULL,
  `motif` enum('retour','remboursement','erreur','geste_commercial','autre') DEFAULT 'autre',
  `raison` text DEFAULT NULL,
  `taux` decimal(6,3) NOT NULL DEFAULT 100.000,  -- % de la facture extourné (100 = total)
  `montant` decimal(15,2) NOT NULL DEFAULT 0.00, -- montant TTC extourné
  `statut` enum('emis','annule') DEFAULT 'emis',
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_avoir_dossier` (`entreprise_id`,`numero`),
  KEY `idx_avd_ent` (`entreprise_id`),
  KEY `idx_avd_orig` (`ecriture_origine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
