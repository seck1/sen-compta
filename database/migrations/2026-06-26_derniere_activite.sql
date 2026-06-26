-- Suivi d'activité temps réel : timestamp de la dernière requête authentifiée (heartbeat).
-- Idempotent : ajoute la colonne seulement si elle n'existe pas.
SET @col := (SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = 'users' AND column_name = 'derniere_activite');
SET @sql := IF(@col = 0,
  'ALTER TABLE users ADD COLUMN derniere_activite DATETIME NULL DEFAULT NULL AFTER derniere_connexion',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index pour les requêtes de tri/filtre par activité
SET @idx := (SELECT COUNT(*) FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = 'users' AND index_name = 'idx_derniere_activite');
SET @sql2 := IF(@idx = 0,
  'ALTER TABLE users ADD INDEX idx_derniere_activite (derniere_activite)',
  'SELECT 1');
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;
