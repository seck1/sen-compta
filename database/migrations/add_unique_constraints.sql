-- Migration : Ajout de contraintes UNIQUE anti-fraude
-- Fix K : unicité numero_facture dans honoraires
-- Fix K : unicité numero_piece par (entreprise, journal) dans ecritures

ALTER TABLE honoraires ADD UNIQUE KEY IF NOT EXISTS unique_numero_facture (numero_facture);
ALTER TABLE ecritures ADD UNIQUE KEY IF NOT EXISTS unique_numero_piece (entreprise_id, journal_id, numero_piece);
