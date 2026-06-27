-- Paramètres paie avancés : SMIG, congés, BRS, et barèmes TRIMF/IR éditables (JSON).
ALTER TABLE paie_parametres
  ADD COLUMN smig_mensuel          INT          DEFAULT 76827,   -- SMIG SN (décret 2023-1375)
  ADD COLUMN conges_base_jours     INT          DEFAULT 30,
  ADD COLUMN conges_taux_maladie   DECIMAL(5,2) DEFAULT 100.00,
  ADD COLUMN conges_droits_annuels DECIMAL(4,1) DEFAULT 18.0,    -- 1,5 j/mois (Code du travail SN)
  ADD COLUMN brs_mode              VARCHAR(20)  DEFAULT 'desactive',
  ADD COLUMN bareme_trimf          JSON         NULL,            -- [{min,max,montant,libelle}]
  ADD COLUMN bareme_ir             JSON         NULL;            -- [{min,max,taux,libelle}]
