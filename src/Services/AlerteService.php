<?php
/**
 * Service de génération automatique des alertes fiscales et RH.
 */
class AlerteService {

    public static function genererAlertes(int $entreprise_id, int $user_id): void {
        try {
            self::alertesEcheancesFiscales($entreprise_id, $user_id);
            self::alertesCDDExpirants($entreprise_id, $user_id);
            self::alertesIPRES($entreprise_id, $user_id);
            self::alertesCongesEnAttente($entreprise_id, $user_id);
        } catch (\Exception $e) {}
    }

    private static function alertesEcheancesFiscales(int $entreprise_id, int $user_id): void {
        $db = getDB();

        // Échéances dans les 15 prochains jours
        $stmt = $db->prepare("
            SELECT * FROM echeances_fiscales
            WHERE entreprise_id = ?
              AND statut = 'a_venir'
              AND date_echeance BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)
            ORDER BY date_echeance ASC
        ");
        $stmt->execute([$entreprise_id]);
        $echeances = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($echeances as $ech) {
            $jours = (int)floor((strtotime($ech['date_echeance']) - time()) / 86400);
            if (self::dejaNotifie($user_id, 'ech_fisc_' . $ech['id'])) continue;
            $type  = $jours <= 3 ? 'danger' : ($jours <= 7 ? 'warning' : 'info');
            $delai = $jours === 0 ? "aujourd'hui" : ($jours === 1 ? 'demain' : "dans {$jours} jours");
            NotificationService::creer($user_id,
                "⚠️ Échéance {$ech['type']} — {$delai}",
                "[ech_fisc_{$ech['id']}] {$ech['libelle']} — avant le " . date('d/m/Y', strtotime($ech['date_echeance'])),
                $type,
                APP_URL . "/dossier/fiscalite/calendrier?id={$entreprise_id}"
            );
        }

        // Mettre en retard les échéances passées
        $stmt2 = $db->prepare("UPDATE echeances_fiscales SET statut='en_retard' WHERE entreprise_id=? AND statut='a_venir' AND date_echeance < CURDATE()");
        $stmt2->execute([$entreprise_id]);

        // Alertes retards
        $stmt3 = $db->prepare("SELECT * FROM echeances_fiscales WHERE entreprise_id=? AND statut='en_retard' AND date_echeance >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
        $stmt3->execute([$entreprise_id]);
        foreach ($stmt3->fetchAll(PDO::FETCH_ASSOC) as $ech) {
            if (self::dejaNotifie($user_id, 'ech_retard_' . $ech['id'])) continue;
            NotificationService::creer($user_id,
                "🔴 Échéance EN RETARD — {$ech['type']}",
                "[ech_retard_{$ech['id']}] {$ech['libelle']} — due le " . date('d/m/Y', strtotime($ech['date_echeance'])),
                'danger',
                APP_URL . "/dossier/fiscalite/calendrier?id={$entreprise_id}"
            );
        }
    }

    private static function alertesIPRES(int $entreprise_id, int $user_id): void {
        $jour = (int)date('j');
        $mois = (int)date('n');
        $annee = (int)date('Y');
        if ($jour < 10 || $jour > 14) return;

        $db = getDB();
        $mois_prec  = $mois === 1 ? 12 : $mois - 1;
        $annee_prec = $mois === 1 ? $annee - 1 : $annee;

        $stmt = $db->prepare("SELECT COUNT(*) FROM bulletins_paie WHERE entreprise_id=? AND periode_mois=? AND periode_annee=?");
        $stmt->execute([$entreprise_id, $mois_prec, $annee_prec]);
        if ((int)$stmt->fetchColumn() === 0) return;

        $cle = "ipres_{$entreprise_id}_{$annee}_{$mois}";
        if (self::dejaNotifie($user_id, $cle)) return;

        $mois_noms = ['','Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
        NotificationService::creer($user_id,
            "📋 Déclaration IPRES à envoyer avant le 15",
            "[{$cle}] Cotisations IPRES de {$mois_noms[$mois_prec]} {$annee_prec} à déclarer avant le 15/{$mois}/{$annee}.",
            'warning',
            APP_URL . "/dossier/rh/declarations-sociales?id={$entreprise_id}"
        );
    }

    private static function alertesCDDExpirants(int $entreprise_id, int $user_id): void {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT * FROM employes
            WHERE entreprise_id=? AND type_contrat IN ('CDD','Stage','Interim')
              AND statut='actif' AND date_fin_contrat IS NOT NULL
              AND date_fin_contrat BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ORDER BY date_fin_contrat ASC
        ");
        $stmt->execute([$entreprise_id]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $emp) {
            $jours = (int)floor((strtotime($emp['date_fin_contrat']) - time()) / 86400);
            $cle   = 'cdd_' . $emp['id'] . '_' . date('Ym');
            if (self::dejaNotifie($user_id, $cle)) continue;
            $delai = $jours === 0 ? "expire aujourd'hui" : ($jours === 1 ? 'expire demain' : "expire dans {$jours} jours");
            NotificationService::creer($user_id,
                "👤 Contrat {$emp['type_contrat']} — {$emp['prenom']} {$emp['nom']}",
                "[{$cle}] Le contrat {$delai} (le " . date('d/m/Y', strtotime($emp['date_fin_contrat'])) . "). Renouvelez ou clôturez.",
                $jours <= 7 ? 'danger' : 'warning',
                APP_URL . "/dossier/rh/employe?id={$entreprise_id}&employe_id={$emp['id']}"
            );
        }
    }

    private static function alertesCongesEnAttente(int $entreprise_id, int $user_id): void {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT c.*, e.nom, e.prenom FROM conges c
            JOIN employes e ON e.id=c.employe_id
            WHERE c.entreprise_id=? AND c.statut='en_attente'
              AND c.created_at < DATE_SUB(NOW(), INTERVAL 3 DAY)
        ");
        $stmt->execute([$entreprise_id]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $cle = 'conge_' . $c['id'];
            if (self::dejaNotifie($user_id, $cle)) continue;
            NotificationService::creer($user_id,
                "🌴 Congé en attente — {$c['prenom']} {$c['nom']}",
                "[{$cle}] Demande du " . date('d/m/Y', strtotime($c['date_debut'])) . " au " . date('d/m/Y', strtotime($c['date_fin'])) . " en attente depuis +3 jours.",
                'warning',
                APP_URL . "/dossier/rh/conges?id={$entreprise_id}"
            );
        }
    }

    private static function dejaNotifie(int $user_id, string $cle): bool {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND message LIKE ? AND created_at > DATE_SUB(NOW(), INTERVAL 12 HOUR)");
        $stmt->execute([$user_id, "%[{$cle}]%"]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function genererEcheancesAnnee(int $entreprise_id, int $annee): void {
        $db = getDB();
        $mois_noms = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

        $liste = [];
        for ($m = 1; $m <= 12; $m++) {
            $d = sprintf('%04d-%02d-15', $annee, $m);
            $liste[] = ['IPRES', "Déclaration IPRES — {$mois_noms[$m]} {$annee}", $d];
            $liste[] = ['TVA',   "Déclaration TVA — {$mois_noms[$m]} {$annee}",   $d];
            $liste[] = ['IR',    "Déclaration IR/ITS — {$mois_noms[$m]} {$annee}", $d];
        }
        $liste[] = ['IS',      "Déclaration IS — Exercice {$annee}",   ($annee+1).'-04-30'];
        $liste[] = ['CFCE',    "CFCE — {$annee}",                       $annee.'-06-30'];
        $liste[] = ['Patente', "Patente — {$annee}",                    $annee.'-02-28'];

        $check = $db->prepare("SELECT COUNT(*) FROM echeances_fiscales WHERE entreprise_id=? AND type=? AND date_echeance=?");
        $ins   = $db->prepare("INSERT INTO echeances_fiscales (entreprise_id, type, libelle, date_echeance, statut) VALUES (?,?,?,?,?)");

        foreach ($liste as [$type, $libelle, $date]) {
            $check->execute([$entreprise_id, $type, $date]);
            if ((int)$check->fetchColumn() > 0) continue;
            $statut = $date < date('Y-m-d') ? 'en_retard' : 'a_venir';
            $ins->execute([$entreprise_id, $type, $libelle, $date, $statut]);
        }
    }
}
