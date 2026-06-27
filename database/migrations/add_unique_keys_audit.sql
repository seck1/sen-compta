-- Clés uniques manquantes (audit) : rendent les ON DUPLICATE KEY UPDATE effectifs,
-- évitant les doublons qui faussaient soldes de rapprochement et niveaux de relance.
ALTER TABLE rapprochements_lignes ADD UNIQUE KEY uniq_rap_ligne (rapprochement_id, ligne_ecriture_id);
ALTER TABLE relances ADD UNIQUE KEY uniq_relance (entreprise_id, tiers_id, ecriture_id, niveau);
