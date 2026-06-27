-- Plafond mensuel d'indemnité de transport exonérée d'IR et de cotisations (Sénégal).
ALTER TABLE paie_parametres
  ADD COLUMN transport_exonere_plafond INT DEFAULT 26000;
