<?php
require_once APP_ROOT . '/config/ai.php';

class ScanIAController {

    public function analyser(): void {
        requireAuth();
        header('Content-Type: application/json');

        $entreprise_id = (int)($_POST['entreprise_id'] ?? 0);
        if (!userHasAccess($entreprise_id)) {
            echo json_encode(['error' => 'Accès refusé']); exit;
        }

        if (empty($_FILES['facture']['tmp_name'])) {
            echo json_encode(['error' => 'Aucun fichier reçu']); exit;
        }

        $file = $_FILES['facture'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp','pdf']) || $file['size'] > 10485760) {
            echo json_encode(['error' => 'Format invalide ou fichier trop lourd (max 10 Mo, formats: JPEG PNG WEBP PDF)']); exit;
        }

        // Convertir PDF en images (toutes les pages) via ImageMagick + Ghostscript
        $tmpImagePaths = [];
        $imageBlocks   = [];

        if ($ext === 'pdf') {
            $tmpPdf    = $file['tmp_name'];
            $uniqId    = uniqid('scan_');
            $tmpDir    = '/tmp';
            $tmpPrefix = $tmpDir . '/' . $uniqId . '_%02d.png';
            $globPat   = $tmpDir . '/' . $uniqId . '_*.png';
            // Ghostscript convertit toutes les pages en PNG.
            // Binaire résolu de façon portable (Linux/Docker, macOS Homebrew, etc.)
            $gs = $this->trouverGhostscript();
            if (!$gs) {
                echo json_encode(['error' => "Ghostscript (gs) introuvable sur le serveur. Installez-le pour scanner des PDF."]); exit;
            }
            $cmd = escapeshellarg($gs) . ' -dNOPAUSE -dBATCH -dSAFER -sDEVICE=png16m -r150 '
                 . '-sOutputFile=' . escapeshellarg($tmpPrefix) . ' '
                 . escapeshellarg($tmpPdf) . ' 2>&1';
            exec($cmd, $out, $ret);
            $pages = glob($globPat);
            sort($pages);
            $pages = array_slice($pages, 0, 5);
            if (empty($pages)) {
                echo json_encode(['error' => 'Impossible de lire le PDF. Vérifiez que le fichier est valide. Détail: ' . implode(' ', $out)]); exit;
            }
            foreach ($pages as $page) {
                $tmpImagePaths[] = $page;
                $imageBlocks[]   = ['type'=>'image','source'=>['type'=>'base64','media_type'=>'image/png','data'=>base64_encode(file_get_contents($page))]];
            }
        } else {
            $imageData   = base64_encode(file_get_contents($file['tmp_name']));
            $mimeType    = match($ext) {
                'jpg','jpeg' => 'image/jpeg',
                'png'  => 'image/png',
                'webp' => 'image/webp',
                default => 'image/jpeg'
            };
            $imageBlocks[] = ['type'=>'image','source'=>['type'=>'base64','media_type'=>$mimeType,'data'=>$imageData]];
        }

        $db = getDB();
        $stmtC = $db->prepare("SELECT numero, intitule FROM comptes WHERE entreprise_id=? ORDER BY numero LIMIT 200");
        $stmtC->execute([$entreprise_id]);
        $comptes = $stmtC->fetchAll(PDO::FETCH_ASSOC);
        $planStr = implode("\n", array_map(fn($c) => $c['numero'].' - '.$c['intitule'], $comptes));

        $stmtJ = $db->prepare("SELECT code, libelle, type FROM journaux WHERE entreprise_id=?");
        $stmtJ->execute([$entreprise_id]);
        $journaux = $stmtJ->fetchAll(PDO::FETCH_ASSOC);
        $journauxStr = implode("\n", array_map(fn($j) => $j['code'].' - '.$j['libelle'].' ('.$j['type'].')', $journaux));

        $stmtEnt = $db->prepare("SELECT raison_sociale, code_dossier FROM entreprises WHERE id=?");
        $stmtEnt->execute([$entreprise_id]);
        $entrepriseInfo = $stmtEnt->fetch(PDO::FETCH_ASSOC);
        $nomEntreprise = ($entrepriseInfo['code_dossier'] ?? '') . ' — ' . ($entrepriseInfo['raison_sociale'] ?? '');

        $prompt = <<<PROMPT
Tu es un expert-comptable OHADA SYSCOHADA Révisé, spécialisé au Sénégal, avec 20 ans d'expérience. Tu analyses des documents comptables et génères des écritures parfaitement conformes.

⚠️ ENTREPRISE QUI TIENT LA COMPTABILITÉ: $nomEntreprise
Tu analyses ce document DU POINT DE VUE DE CETTE ENTREPRISE UNIQUEMENT.
- Si cette entreprise apparaît comme CLIENT sur la facture → c'est une FACTURE ACHAT pour elle (elle achète)
- Si cette entreprise apparaît comme FOURNISSEUR/ÉMETTEUR → c'est une FACTURE VENTE pour elle (elle vend)
- Si l'entreprise n'apparaît pas du tout → c'est probablement une facture reçue donc FACTURE ACHAT

PLAN COMPTABLE DE L'ENTREPRISE:
$planStr

JOURNAUX DISPONIBLES:
$journauxStr

═══════════════════════════════════════════
ÉTAPE 1 — LIS ET COMPRENDS LE DOCUMENT
═══════════════════════════════════════════
Identifie:
• Type: facture achat / facture vente / reçu / relevé bancaire / autre
• Émetteur et destinataire
• Date, numéro de facture
• Détail des lignes: description, quantité, prix unitaire, total HT
• TVA appliquée (taux et montant)
• Total TTC
• Conditions de paiement (acompte, délai, échelonnement)

═══════════════════════════════════════════
ÉTAPE 2 — DÉTERMINE LA NATURE DE CHAQUE LIGNE
═══════════════════════════════════════════
Pour chaque article/service, détermine s'il s'agit de:
• IMMOBILISATION: bien durable > 1 an, valeur significative (matériel, mobilier, véhicule, logiciel...)
  → 231 si en cours de réception/livraison, sinon 216/218x selon nature
• CHARGE: consommable, service, frais récurrents
  → 60x achats, 61x transport, 62x services, 63x services B, 64x impôts, 65x autres
• PRESTATION DE SERVICE facturée: → 706 au crédit
• TRAVAUX facturés: → 705 au crédit

═══════════════════════════════════════════
ÉTAPE 3 — APPLIQUE LES RÈGLES SYSCOHADA
═══════════════════════════════════════════
RÈGLES ABSOLUES:
✓ Facture ACHAT  → EXACTEMENT "401" (JAMAIS 4011, 4012 ou autre sous-compte) au CRÉDIT = montant TTC total. JAMAIS au débit
✓ Facture VENTE  → EXACTEMENT "411" (JAMAIS 4111, 4112 ou autre sous-compte) au DÉBIT = montant TTC total. JAMAIS au crédit
✓ TVA déductible sur achats (si TVA > 0) → 4441 au débit
✓ TVA collectée sur ventes (si TVA > 0)  → 4431 au crédit
✓ TVA = 0% ou exonéré → AUCUNE ligne TVA du tout
✓ REGROUPE les lignes de même compte (additionne les montants)
✓ Jamais débit ET crédit sur la même ligne
✓ Σ débits DOIT STRICTEMENT égaler Σ crédits — vérifie avant de répondre
✓ NE PAS créer de ligne pour les acomptes/paiements déjà effectués mentionnés dans "historique des paiements" — l'écriture concerne uniquement la FACTURE, pas les règlements

CALCUL MONTANT HT CORRECT:
- Le montant HT = sous-total HT + honoraires + autres frais HT (AVANT TVA)
- Exemple: sous-total 5 625 000 + honoraires 10% (562 500) = HT total 6 187 500
- TVA 18% s'applique sur le HT total: 6 187 500 × 18% = 1 113 750
- TTC = HT total + TVA = 6 187 500 + 1 113 750 = 7 301 250
- Le débit charges = HT total (pas seulement le sous-total)

CORRESPONDANCES COMPTES (utilise UNIQUEMENT ceux du plan ci-dessus):
• Prestations audiovisuelles, production vidéo, tournage → 627
• Honoraires (ligne "honoraires X%" sur facture) → 6323
• Droits artistiques, cachets modèles/artistes → 632 ou 6323
• Personnel de production (charge production, cadreur) → 632
• Racks, rayonnages, étagères métalliques      → 231 (en cours) ou 216 (réceptionné)
• Matériel informatique (PC, serveur, imprimante) → 2183
• Mobilier de bureau                            → 2181
• Matériel de bureau                            → 2182
• Véhicules, camions, motos                     → 217
• Transport/livraison sur achat                 → 611
• Transport sur vente                           → 612
• Fournitures de bureau (papier, stylos...)     → 6042
• Carburant                                     → 6045
• Loyer, location                               → 622
• Entretien et réparation bâtiments             → 6241
• Entretien matériel                            → 6242
• Honoraires consultant/avocat/expert comptable → 6323
• Frais bancaires, commissions                  → 6311
• Publicité, annonces, insertions               → 6271
• Téléphone, internet                           → 628
• Formation                                     → 633
• Salaires bruts                                → 661
• Charges patronales CSS/IPRES                  → 664

═══════════════════════════════════════════
ÉTAPE 4 — GÉNÈRE LE JSON
═══════════════════════════════════════════
Réponds UNIQUEMENT avec ce JSON valide, sans texte avant ou après:
{"document_type":"facture_achat|facture_vente|recu|autre","fournisseur_client":"nom exact du fournisseur ou client","date":"YYYY-MM-DD","reference":"numéro de facture exact","libelle":"description concise de l'opération","journal_code":"code journal","montant_ht":0.00,"montant_tva":0.00,"montant_ttc":0.00,"lignes":[{"compte":"XXXXX","intitule":"intitulé du compte","debit":0.00,"credit":0.00}],"confiance":"haute|moyenne|faible","notes":"justification des choix de comptes"}
PROMPT;

        $payload = [
            'model'      => ANTHROPIC_MODEL,
            'max_tokens' => 2048,
            'messages'   => [[
                'role'    => 'user',
                'content' => array_merge($imageBlocks, [['type'=>'text','text'=>$prompt]])
            ]]
        ];

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'x-api-key: ' . ANTHROPIC_API_KEY,
                'anthropic-version: 2023-06-01',
                'content-type: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $err = json_decode($response, true);
            echo json_encode(['error' => 'Erreur API Claude : ' . ($err['error']['message'] ?? $httpCode)]);
            exit;
        }

        $data    = json_decode($response, true);
        $content = $data['content'][0]['text'] ?? '';
        preg_match('/\{[\s\S]*\}/', $content, $matches);
        if (empty($matches[0])) { echo json_encode(['error' => 'Reponse IA invalide']); exit; }

        $ecriture = json_decode($matches[0], true);
        if (!$ecriture) { echo json_encode(['error' => 'Impossible de parser la reponse IA']); exit; }

        $totalDebit  = array_sum(array_column($ecriture['lignes'] ?? [], 'debit'));
        $totalCredit = array_sum(array_column($ecriture['lignes'] ?? [], 'credit'));
        $ecriture['equilibre']    = abs($totalDebit - $totalCredit) < 0.01;
        $ecriture['total_debit']  = $totalDebit;
        $ecriture['total_credit'] = $totalCredit;

        // Sauvegarder le fichier original comme justificatif
        $uploadDir   = APP_ROOT . '/public/uploads/justificatifs/';
        $filename    = uniqid('scan_', true) . '.' . $ext;
        $pieceJointe = null;
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $pieceJointe = $filename;
        }

        // Nettoyer les fichiers PNG temporaires créés pour les PDF
        foreach ($tmpImagePaths as $p) {
            if (file_exists($p)) unlink($p);
        }

        echo json_encode(['success' => true, 'ecriture' => $ecriture, 'piece_jointe' => $pieceJointe]);
    }

    public function valider(): void {
        requireAuth();
        header('Content-Type: application/json');

        $data          = json_decode(file_get_contents('php://input'), true);
        $entreprise_id = (int)($data['entreprise_id'] ?? 0);
        if (!userHasAccess($entreprise_id)) { echo json_encode(['error' => 'Acces refuse']); exit; }

        $db   = getDB();
        $user = auth();

        $stmtJ = $db->prepare("SELECT id, code FROM journaux WHERE entreprise_id=? AND code=?");
        $stmtJ->execute([$entreprise_id, $data['journal_code']]);
        $journal = $stmtJ->fetch();
        if (!$journal) { echo json_encode(['error' => 'Journal introuvable']); exit; }

        $entreprise  = getEntreprise($entreprise_id);
        $exercice    = $entreprise['exercice_courant'];
        $date        = $data['date'] ?: date('Y-m-d');
        $mois        = (int)date('m', strtotime($date));

        $pieceJointe   = !empty($data['piece_jointe']) ? $data['piece_jointe'] : null;
        $numeroFacture = !empty($data['numero_facture']) ? $data['numero_facture'] : null;
        // Auto-générer N° pièce si non saisi
        $numeroPiece = trim($data['reference'] ?? '');
        if (!$numeroPiece) {
            $jCode = strtoupper($journal['code']);
            $an    = date('Y', strtotime($date));
            $stmtSeq = $db->prepare("SELECT COUNT(*) FROM ecritures WHERE entreprise_id=? AND journal_id=? AND exercice=?");
            $stmtSeq->execute([$entreprise_id, $journal['id'], (int)$an]);
            $seq = ((int)$stmtSeq->fetchColumn()) + 1;
            $numeroPiece = $jCode . '-' . $an . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
        }
        $stmtE = $db->prepare("INSERT INTO ecritures (entreprise_id,journal_id,user_id,numero_piece,numero_facture,date_ecriture,date_valeur,libelle,piece_jointe,exercice,periode,statut) VALUES (?,?,?,?,?,?,?,?,?,?,?,'brouillon')");
        $stmtE->execute([$entreprise_id,$journal['id'],$user['id'],$numeroPiece,$numeroFacture,$date,$date,$data['libelle'],$pieceJointe,$exercice,$mois]);
        $ecriture_id = $db->lastInsertId();

        $tiers_id = !empty($data['tiers_id']) ? (int)$data['tiers_id'] : null;
        $stmtL = $db->prepare("INSERT INTO lignes_ecritures (ecriture_id,compte_id,libelle,debit,credit,tiers,tiers_id) VALUES (?,?,?,?,?,?,?)");
        foreach ($data['lignes'] as $ligne) {
            $stmtC = $db->prepare("SELECT id FROM comptes WHERE entreprise_id=? AND numero=?");
            $stmtC->execute([$entreprise_id, $ligne['compte']]);
            $compte = $stmtC->fetch();
            if (!$compte) continue;
            $libelleLigne = !empty($ligne['intitule']) ? $ligne['intitule'] : $data['libelle'];
            $stmtL->execute([$ecriture_id,$compte['id'],$libelleLigne,(float)$ligne['debit'],(float)$ligne['credit'],$data['fournisseur_client']??'',$tiers_id]);
        }

        echo json_encode(['success' => true, 'ecriture_id' => $ecriture_id]);
    }

    /** Localise le binaire Ghostscript de façon portable (Docker/Linux, macOS, Windows). */
    private function trouverGhostscript(): ?string {
        // 1) Variable d'environnement explicite si définie
        $env = getenv('GHOSTSCRIPT_BIN');
        if ($env && is_executable($env)) return $env;

        // 2) Emplacements courants
        $candidats = [
            '/usr/bin/gs', '/usr/local/bin/gs', '/bin/gs',
            '/opt/homebrew/bin/gs',            // macOS Apple Silicon
            '/usr/local/Cellar/ghostscript/bin/gs',
        ];
        foreach ($candidats as $c) {
            if (is_executable($c)) return $c;
        }

        // 3) Recherche dans le PATH via `command -v`
        $found = trim((string)@shell_exec('command -v gs 2>/dev/null'));
        if ($found !== '' && is_executable($found)) return $found;

        return null;
    }
}
