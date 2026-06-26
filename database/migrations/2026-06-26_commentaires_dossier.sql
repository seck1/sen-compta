-- Table des notes & suivi du dossier (panneau latéral "Notes & Suivi").
-- Idempotent : ne fait rien si la table existe déjà.
CREATE TABLE IF NOT EXISTS `commentaires_dossier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entreprise_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` enum('note','alerte','info','tache') DEFAULT 'note',
  `priorite` enum('normale','haute','urgente') DEFAULT 'normale',
  `resolu` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `entreprise_id` (`entreprise_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
