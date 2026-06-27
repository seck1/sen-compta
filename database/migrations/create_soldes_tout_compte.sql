-- Solde de tout compte enregistré et comptabilisé (départ d'un employé).
CREATE TABLE IF NOT EXISTS soldes_tout_compte (
  id INT AUTO_INCREMENT PRIMARY KEY,
  entreprise_id INT NOT NULL,
  employe_id INT NOT NULL,
  date_depart DATE NOT NULL,
  motif VARCHAR(50) DEFAULT 'licenciement',
  dernier_net INT DEFAULT 0,
  indemnite_licenciement INT DEFAULT 0,
  indemnite_conges INT DEFAULT 0,
  total_du INT DEFAULT 0,
  statut ENUM('brouillon','paye') DEFAULT 'brouillon',
  ecriture_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_emp (employe_id), INDEX idx_ent (entreprise_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
