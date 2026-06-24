-- Migration : ajout colonne match_type pour distinguer rapprochement auto/manuel
ALTER TABLE releve_bancaire_lignes
    ADD COLUMN IF NOT EXISTS match_type ENUM('auto','manuel') NULL AFTER ecriture_id;
