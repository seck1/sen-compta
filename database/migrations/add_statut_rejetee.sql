ALTER TABLE ecritures MODIFY COLUMN statut ENUM('brouillon','validee','cloturee','rejetee') DEFAULT 'brouillon';
ALTER TABLE ecritures ADD COLUMN IF NOT EXISTS motif_rejet VARCHAR(255) NULL;
