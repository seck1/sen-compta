<?php
$niveaux = [1 => ['label'=>'Amiable','color'=>'#f59e0b'], 2 => ['label'=>'Formelle','color'=>'#ef4444'], 3 => ['label'=>'Mise en demeure','color'=>'#b8923f']];
?>
<div class="page-header">
    <div>
        <div class="page-title">Suivi des relances</div>
        <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> — Créances clients en retard</div>
    </div>
</div>

<!-- KPIs -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px">
    <div class="card" style="padding:16px 20px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">Total créances</div>
        <div style="font-size:20px;font-weight:700;color:#dc2626;font-family:monospace"><?= formatMontant($total_creances) ?></div>
    </div>
    <div class="card" style="padding:16px 20px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">> 30 jours</div>
        <div style="font-size:20px;font-weight:700;color:#f59e0b"><?= $nb_retard_30 ?> client<?= $nb_retard_30>1?'s':'' ?></div>
    </div>
    <div class="card" style="padding:16px 20px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">> 60 jours</div>
        <div style="font-size:20px;font-weight:700;color:#ef4444"><?= $nb_retard_60 ?> client<?= $nb_retard_60>1?'s':'' ?></div>
    </div>
    <div class="card" style="padding:16px 20px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">> 90 jours</div>
        <div style="font-size:20px;font-weight:700;color:#b8923f"><?= $nb_retard_90 ?> client<?= $nb_retard_90>1?'s':'' ?></div>
    </div>
</div>

<!-- Tableau créances -->
<div class="card" style="padding:0;overflow:hidden;margin-bottom:24px">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div style="font-weight:700;font-size:13px">Créances en retard</div>
        <div style="font-size:14px;color:var(--text-muted)"><?= count($creances) ?> client<?= count($creances)>1?'s':'' ?></div>
    </div>
    <?php if(empty($creances)): ?>
    <div style="text-align:center;padding:40px;color:var(--text-muted)">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;display:block;opacity:.3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Aucune créance en retard — tous les clients sont à jour !
    </div>
    <?php else: ?>
    <table style="width:100%;border-collapse:collapse;font-size:14px">
        <thead>
            <tr style="background:var(--bg-secondary);border-bottom:2px solid var(--border)">
                <th style="padding:11px 16px;text-align:left;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;background:#f1f5f9;color:#4a554f">Client</th>
                <th style="padding:11px 16px;text-align:right;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;background:#f1f5f9;color:#4a554f">Solde dû</th>
                <th style="padding:11px 16px;text-align:center;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;background:#f1f5f9;color:#4a554f">Retard</th>
                <th style="padding:11px 16px;text-align:center;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;background:#f1f5f9;color:#4a554f">Niveau relance</th>
                <th style="padding:11px 16px;text-align:center;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;background:#f1f5f9;color:#4a554f">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($creances as $c):
            $jr = (int)$c['jours_retard'];
            $couleur = $jr > 90 ? '#b8923f' : ($jr > 60 ? '#ef4444' : ($jr > 30 ? '#f59e0b' : '#6b7280'));
            $niveau_actuel = $c['relance_niveau'] ?? 0;
        ?>
        <tr style="border-bottom:1px solid var(--border)" id="row-tiers-<?= $c['tiers_id'] ?>">
            <td style="padding:12px 16px">
                <div style="font-weight:600"><?= e($c['tiers_nom']) ?></div>
                <?php if($c['telephone'] || $c['email']): ?>
                <div style="font-size:13px;color:var(--text-muted);margin-top:2px">
                    <?= e($c['telephone'] ?? '') ?><?= $c['telephone']&&$c['email']?' · ':'' ?><?= e($c['email'] ?? '') ?>
                </div>
                <?php endif; ?>
            </td>
            <td style="padding:12px 16px;text-align:right;font-family:monospace;font-weight:700;color:#dc2626;font-size:13px">
                <?= formatMontant($c['solde']) ?>
            </td>
            <td style="padding:12px 16px;text-align:center">
                <span style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:13px;font-weight:700;background:<?= $couleur ?>22;color:<?= $couleur ?>">
                    <?= $jr ?> j
                </span>
            </td>
            <td style="padding:12px 16px;text-align:center">
                <?php if($niveau_actuel): ?>
                <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:13px;font-weight:600;background:<?= $niveaux[$niveau_actuel]['color'] ?>22;color:<?= $niveaux[$niveau_actuel]['color'] ?>">
                    <?= $niveaux[$niveau_actuel]['label'] ?>
                </span>
                <?php else: ?>
                <span style="color:var(--text-muted);font-size:13px">Pas encore relancé</span>
                <?php endif; ?>
            </td>
            <td style="padding:12px 16px;text-align:center">
                <button onclick="ouvrirRelance(<?= $c['tiers_id'] ?>, '<?= e(addslashes($c['tiers_nom'])) ?>', <?= $c['solde'] ?>, <?= $niveau_actuel+1 ?>, '<?= e(addslashes($c['email'] ?? '')) ?>')"
                        style="padding:5px 12px;border-radius:8px;background:#2563eb;color:#fff;border:none;cursor:pointer;font-size:13px;font-weight:600;margin-right:6px">
                    ✉ Relancer
                </button>
                <button onclick="marquerReglee(<?= $c['tiers_id'] ?>)"
                        style="padding:5px 12px;border-radius:8px;background:#1f6e4e22;color:#1f6e4e;border:1px solid #1f6e4e44;cursor:pointer;font-size:13px;font-weight:600">
                    ✓ Réglée
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Historique -->
<?php if(!empty($historique)): ?>
<div class="card" style="padding:0;overflow:hidden">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);font-weight:700;font-size:13px">Historique des relances</div>
    <table style="width:100%;border-collapse:collapse;font-size:14px">
        <thead>
            <tr style="background:var(--bg-secondary);border-bottom:1px solid var(--border)">
                <th style="padding:9px 16px;text-align:left;font-size:14px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Date</th>
                <th style="padding:9px 16px;text-align:left;font-size:14px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Client</th>
                <th style="padding:9px 16px;text-align:right;font-size:14px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Montant</th>
                <th style="padding:9px 16px;text-align:center;font-size:14px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Niveau</th>
                <th style="padding:9px 16px;text-align:center;font-size:14px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Statut</th>
                <th style="padding:9px 16px;text-align:left;font-size:14px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Par</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($historique as $h): ?>
        <tr style="border-bottom:1px solid var(--border)">
            <td style="padding:9px 16px;font-family:monospace;color:var(--text-muted)"><?= date('d/m/Y', strtotime($h['date_relance'] ?? $h['created_at'])) ?></td>
            <td style="padding:9px 16px;font-weight:600"><?= e($h['tiers_nom']) ?></td>
            <td style="padding:9px 16px;text-align:right;font-family:monospace;color:#dc2626"><?= formatMontant($h['montant']) ?></td>
            <td style="padding:9px 16px;text-align:center">
                <?php $niv = $niveaux[$h['niveau']] ?? ['label'=>'?','color'=>'#999']; ?>
                <span style="padding:2px 8px;border-radius:20px;font-size:14px;font-weight:600;background:<?= $niv['color'] ?>22;color:<?= $niv['color'] ?>"><?= $niv['label'] ?></span>
            </td>
            <td style="padding:9px 16px;text-align:center">
                <?php $sc=['relancee'=>['Relancée','#2563eb'],'reglee'=>['Réglée','#1f6e4e'],'en_attente'=>['En attente','#f59e0b'],'contentieux'=>['Contentieux','#b8923f']]; $s=$sc[$h['statut']]??['?','#999']; ?>
                <span style="padding:2px 8px;border-radius:20px;font-size:14px;font-weight:600;background:<?= $s[1] ?>22;color:<?= $s[1] ?>"><?= $s[0] ?></span>
            </td>
            <td style="padding:9px 16px;color:var(--text-muted)"><?= e($h['prenom'].' '.$h['user_nom']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Modal relance -->
<div id="modalRelance" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9000;display:none;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:28px;width:480px;max-width:95vw">
        <div style="font-size:14px;font-weight:700;margin-bottom:20px">Enregistrer une relance</div>
        <div style="margin-bottom:14px">
            <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Client</label>
            <div id="relance_nom" style="font-weight:600;font-size:13px"></div>
        </div>
        <div style="margin-bottom:14px">
            <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Niveau de relance</label>
            <select id="relance_niveau" style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid var(--border);font-size:14px">
                <option value="1">1 — Amiable (rappel courtois)</option>
                <option value="2">2 — Formelle (lettre recommandée)</option>
                <option value="3">3 — Mise en demeure</option>
            </select>
        </div>
        <div style="margin-bottom:14px">
            <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Notes</label>
            <textarea id="relance_notes" rows="3" style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid var(--border);font-size:14px;resize:vertical" placeholder="Canal utilisé, réponse obtenue..."></textarea>
        </div>
        <div id="relance_email_row" style="margin-bottom:14px;padding:10px 14px;background:#eff6ff;border-radius:8px;font-size:14px;color:#1d4ed8;display:none">
            📧 Un email sera envoyé à : <strong id="relance_email_addr"></strong>
        </div>
        <div id="relance_msg" style="display:none;padding:10px 14px;border-radius:8px;font-size:14px;margin-bottom:10px"></div>
        <div style="display:flex;gap:10px;justify-content:flex-end">
            <button onclick="fermerModal()" style="padding:9px 20px;border-radius:8px;border:1px solid var(--border);background:none;cursor:pointer;font-size:14px">Annuler</button>
            <button id="btn_envoyer_email" onclick="confirmerRelanceEmail()" style="padding:9px 20px;border-radius:8px;background:#2563eb22;color:#2563eb;border:1px solid #2563eb44;cursor:pointer;font-size:14px;font-weight:600;display:none">
                📧 Envoyer par email
            </button>
            <button onclick="confirmerRelance()" style="padding:9px 20px;border-radius:8px;background:#2563eb;color:#fff;border:none;cursor:pointer;font-size:14px;font-weight:600">Enregistrer</button>
        </div>
    </div>
</div>

<script>
var _tiersId=0, _montant=0, _email='';
function ouvrirRelance(tiersId, nom, montant, niveau, email) {
    _tiersId=tiersId; _montant=montant; _email=email||'';
    document.getElementById('relance_nom').textContent=nom;
    document.getElementById('relance_niveau').value=Math.min(niveau,3);
    document.getElementById('relance_notes').value='';
    document.getElementById('relance_msg').style.display='none';
    if (_email) {
        document.getElementById('relance_email_row').style.display='block';
        document.getElementById('relance_email_addr').textContent=_email;
        document.getElementById('btn_envoyer_email').style.display='inline-block';
    } else {
        document.getElementById('relance_email_row').style.display='none';
        document.getElementById('btn_envoyer_email').style.display='none';
    }
    document.getElementById('modalRelance').style.display='flex';
}
function fermerModal() { document.getElementById('modalRelance').style.display='none'; }
function showMsg(txt, ok) {
    var el=document.getElementById('relance_msg');
    el.style.display='block';
    el.style.background=ok?'#f0fdf4':'#fef2f2';
    el.style.color=ok?'#166534':'#991b1b';
    el.textContent=txt;
}
function confirmerRelance() {
    var fd=new FormData();
    fd.append('entreprise_id','<?= $entreprise['id'] ?>');
    fd.append('tiers_id',_tiersId);
    fd.append('montant',_montant);
    fd.append('niveau',document.getElementById('relance_niveau').value);
    fd.append('notes',document.getElementById('relance_notes').value);
    fetch('<?= APP_URL ?>/dossier/relances/enregistrer',{method:'POST',body:fd})
        .then(()=>{ fermerModal(); location.reload(); });
}
function confirmerRelanceEmail() {
    var fd=new FormData();
    fd.append('entreprise_id','<?= $entreprise['id'] ?>');
    fd.append('tiers_id',_tiersId);
    fd.append('montant',_montant);
    fd.append('niveau',document.getElementById('relance_niveau').value);
    fd.append('notes',document.getElementById('relance_notes').value);
    document.getElementById('btn_envoyer_email').textContent='Envoi...';
    document.getElementById('btn_envoyer_email').disabled=true;
    fetch('<?= APP_URL ?>/dossier/relances/email',{method:'POST',body:fd})
        .then(r=>r.json())
        .then(d=>{
            if(d.ok){ showMsg('✓ '+d.message, true); setTimeout(()=>{ fermerModal(); location.reload(); },1500); }
            else { showMsg('✗ '+d.error, false); document.getElementById('btn_envoyer_email').textContent='📧 Envoyer par email'; document.getElementById('btn_envoyer_email').disabled=false; }
        });
}
function marquerReglee(tiersId) {
    if(!confirm('Marquer cette créance comme réglée ?')) return;
    var fd=new FormData();
    fd.append('entreprise_id','<?= $entreprise['id'] ?>');
    fd.append('tiers_id',tiersId);
    fetch('<?= APP_URL ?>/dossier/relances/reglee',{method:'POST',body:fd})
        .then(()=>{ document.getElementById('row-tiers-'+tiersId).remove(); });
}
</script>
