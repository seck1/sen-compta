-- Paramètres applicatifs globaux (clé/valeur) — ex. configuration SMTP. Idempotent.
CREATE TABLE IF NOT EXISTS `app_settings` (
  `cle` varchar(64) NOT NULL,
  `valeur` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`cle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
