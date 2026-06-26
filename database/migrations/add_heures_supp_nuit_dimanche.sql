-- Heures supplémentaires : taux nuit et dimanche/jours fériés (Code du travail SN, Art. L.198).
-- Jour 1-8h : +15% ; jour au-delà : +40% ; nuit : +60% ; dimanche/férié jour : +60% ; dimanche/férié nuit : +100%.
ALTER TABLE paie_parametres
  ADD COLUMN heures_supp_taux_nuit      DECIMAL(5,4) DEFAULT 1.6000 AFTER heures_supp_taux2,
  ADD COLUMN heures_supp_taux_dim       DECIMAL(5,4) DEFAULT 1.6000 AFTER heures_supp_taux_nuit,
  ADD COLUMN heures_supp_taux_dim_nuit  DECIMAL(5,4) DEFAULT 2.0000 AFTER heures_supp_taux_dim;
