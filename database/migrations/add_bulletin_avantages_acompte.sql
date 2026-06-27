-- Colonnes de la fiche de paie : avantages en nature, transport exonéré, brut imposable, acompte, retenues diverses.
ALTER TABLE bulletins_paie
  ADD COLUMN avantages_nature   INT DEFAULT 0,
  ADD COLUMN transport_exonere  INT DEFAULT 0,
  ADD COLUMN brut_imposable     INT DEFAULT 0,
  ADD COLUMN acompte            INT DEFAULT 0,
  ADD COLUMN retenues_diverses  INT DEFAULT 0;
