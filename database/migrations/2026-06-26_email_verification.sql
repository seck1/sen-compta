-- Vérification d'email à l'inscription (code 4 chiffres). Idempotent.

CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `cabinet_id` int(11) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `code` char(4) NOT NULL,
  `tentatives` int(11) NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ev_user` (`user_id`),
  KEY `idx_ev_email` (`email`),
  KEY `idx_ev_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Colonne email_verifie sur users (0 = non vérifié). MySQL 8 : ajout conditionnel.
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='email_verifie');
SET @s := IF(@c=0, 'ALTER TABLE `users` ADD COLUMN `email_verifie` TINYINT(1) NOT NULL DEFAULT 1', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
