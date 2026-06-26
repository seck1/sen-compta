-- Statut cadre / non cadre de l'employé : pilote l'application de l'IPRES tranche B (régime cadre).
ALTER TABLE employes
  ADD COLUMN statut_cadre VARCHAR(10) NOT NULL DEFAULT 'non_cadre' AFTER categorie;
