-- Comptabilité analytique : sections analytiques (centres de coûts / activités / projets)
-- et rattachement optionnel des lignes d'écriture à une section.
-- Idempotent : peut être rejoué sans risque.

CREATE TABLE IF NOT EXISTS `sections_analytiques` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entreprise_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `libelle` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_section_code` (`entreprise_id`,`code`),
  KEY `idx_section_ent` (`entreprise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Colonne de rattachement sur les lignes d'écriture (optionnelle, NULL = non ventilé).
-- MySQL 8 ne supporte pas "ADD COLUMN IF NOT EXISTS" : on teste via information_schema.
SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'lignes_ecritures'
    AND COLUMN_NAME = 'section_analytique_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `lignes_ecritures` ADD COLUMN `section_analytique_id` INT NULL, ADD KEY `idx_ligne_section` (`section_analytique_id`)',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
