<?php
$error_msgs = [
    'desequilibre'    => 'Les écritures ne sont pas équilibrées. Veuillez régulariser avant de procéder à la clôture.',
    'already_closed'  => "L'exercice a déjà été clôturé.",
    'forbidden'       => 'Seuls les administrateurs peuvent effectuer la clôture.',
    'exercice_mismatch' => 'Exercice invalide.',
    'system'          => 'Erreur système. Veuillez réessayer.',
];
?>
<div style="max-width:800px">
    <div class="page-header">
        <div>
            <div class="page-title">Clôture d'exercice</div>
            <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> — Exercice <?= e($exercice) ?></div>
        </div>
    </div>

    <?php if(isset($_GET['message']) && $_GET['message']==='cloture_ok'): ?>
    <div style="background:rgba(31,110,78,0.1);border:1px solid rgba(31,110,78,0.25);border-radius:10px;padding:16px 20px;margin-bottom:20px;color:#1f6e4e">
        <strong>Clôture effectuée avec succès.</strong><br>
        <span style="font-size:14px">Les écritures de report à nouveau ont été générées pour l'exercice <?= e($_GET['new_exercice'] ?? '') ?>.</span>
    </div>
    <?php elseif(isset($_GET['error'])): ?>
    <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);border-radius:10px;padding:16px 20px;margin-bottom:20px;color:#dc2626">
        <strong>Erreur :</strong> <?= e($error_msgs[$_GET['error']] ?? 'Erreur inconnue.') ?>
    </div>
    <?php endif; ?>

    <!-- Statut exercice -->
    <div class="card" style="margin-bottom:20px">
        <div style="font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:400;color:var(--navy-dark);margin-bottom:16px">
            Statut de l'exercice <?= e($exercice) ?>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
            <!-- Équilibre -->
            <div style="padding:18px;border-radius:12px;border:1px solid <?= $equilibre ? 'rgba(31,110,78,0.3)' : 'rgba(239,68,68,0.3)' ?>;background:<?= $equilibre ? 'rgba(31,110,78,0.06)' : 'rgba(239,68,68,0.06)' ?>">
                <div style="font-size:14px;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;font-weight:600;color:<?= $equilibre ? '#1f6e4e' : '#dc2626' ?>">
                    Équilibre des écritures
                </div>
                <?php if($equilibre): ?>
                    <div style="font-size:20px;font-weight:700;color:#1f6e4e">✓ Équilibré</div>
                    <div style="font-size:13px;color:var(--text-muted);margin-top:4px">Toutes les écritures sont équilibrées.</div>
                <?php else: ?>
                    <div style="font-size:20px;font-weight:700;color:#dc2626">✗ Déséquilibré</div>
                    <div style="font-size:13px;color:#dc2626;margin-top:4px">Des écritures ne sont pas équilibrées. La clôture est impossible.</div>
                <?php endif; ?>
            </div>

            <!-- Statut clôture -->
            <div style="padding:18px;border-radius:12px;border:1px solid var(--border);background:var(--bg)">
                <div style="font-size:14px;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;font-weight:600;color:var(--text-muted)">
                    État de la clôture
                </div>
                <?php if($cloture_actuelle): ?>
                    <div style="font-size:13px;font-weight:700;color:var(--navy-dark)">Clôturé</div>
                    <div style="font-size:13px;color:var(--text-muted);margin-top:4px">Le <?= date('d/m/Y', strtotime($cloture_actuelle['date_cloture'])) ?></div>
                <?php else: ?>
                    <div style="font-size:13px;font-weight:700;color:var(--warning)">En cours</div>
                    <div style="font-size:13px;color:var(--text-muted);margin-top:4px">Exercice <?= e($exercice) ?> ouvert.</div>
                <?php endif; ?>
            </div>
        </div>

        <?php if(!$cloture_actuelle && $equilibre && isAdmin()): ?>
        <!-- Bouton clôture -->
        <div style="background:rgba(239,68,68,0.04);border:1px solid rgba(239,68,68,0.15);border-radius:12px;padding:18px">
            <div style="font-size:14px;color:var(--text);margin-bottom:14px">
                <strong style="color:var(--danger)">Attention :</strong> La clôture est une opération <strong>irréversible</strong>.
                Elle va :
                <ul style="margin-top:8px;margin-left:20px;font-size:14px;color:var(--text-muted);line-height:2">
                    <li>Verrouiller les écritures de l'exercice <?= e($exercice) ?></li>
                    <li>Générer les écritures de Report à Nouveau (RAN) pour l'exercice <?= e($exercice+1) ?></li>
                    <li>Ouvrir le nouvel exercice <?= e($exercice+1) ?></li>
                </ul>
            </div>
            <form method="POST" action="<?= APP_URL ?>/dossier/cloture/store"
                  onsubmit="return confirm('Confirmez-vous la clôture définitive de l\'exercice <?= e($exercice) ?> ?')">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
                <input type="hidden" name="exercice" value="<?= e($exercice) ?>">
                <button type="submit" class="btn" style="background:var(--danger);color:white;box-shadow:0 4px 12px rgba(239,68,68,0.25)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                    Procéder à la clôture de l'exercice <?= e($exercice) ?>
                </button>
            </form>
        </div>
        <?php elseif(!isAdmin()): ?>
        <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:10px;padding:14px 18px;font-size:14px;color:#92400e">
            Seuls les administrateurs peuvent effectuer la clôture d'exercice.
        </div>
        <?php endif; ?>
    </div>

    <!-- Historique -->
    <?php if(!empty($historique)): ?>
    <div class="table-wrap">
        <div class="table-header"><span class="table-title">Historique des clôtures</span></div>
        <table>
            <thead>
                <tr>
                    <th>Exercice</th>
                    <th style="text-align:right">Résultat net</th>
                    <th>Date de clôture</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($historique as $h): ?>
                <tr>
                    <td><strong><?= e($h['exercice']) ?></strong></td>
                    <td style="text-align:right;font-family:monospace;font-weight:600;color:<?= $h['resultat_net']>=0 ? '#1f6e4e' : '#dc2626' ?>">
                        <?= formatMontant($h['resultat_net']) ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($h['date_cloture'])) ?></td>
                    <td><span class="badge badge-success">Clôturé</span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
