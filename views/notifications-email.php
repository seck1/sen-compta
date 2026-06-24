<?php
$moisLabels = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$moisPrec   = date('n') == 1 ? 12 : date('n') - 1;
$anneePrec  = date('n') == 1 ? (int)date('Y') - 1 : (int)date('Y');
?>
<div class="page-header">
    <div>
        <div class="page-title">Notifications email</div>
        <div class="page-subtitle">Envoyer des alertes aux collaborateurs</div>
    </div>
</div>

<?php if($message === 'sent'): ?>
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:14px 20px;margin-bottom:20px;color:#166534;font-weight:600">
    ✓ Notifications envoyées avec succès.
</div>
<?php endif; ?>

<!-- Alerte TVA -->
<?php if(!empty($alertes_tva)): ?>
<div class="card" style="margin-bottom:20px;overflow:hidden">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-weight:700;font-size:15px;display:flex;align-items:center;gap:8px">
                <span style="background:#fef3c7;color:#92400e;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700">TVA</span>
                Déclarations TVA en retard — <?= $moisLabels[$moisPrec] ?> <?= $anneePrec ?>
            </div>
            <div style="font-size:13px;color:var(--text-muted);margin-top:3px"><?= count($alertes_tva) ?> dossier(s) sans déclaration</div>
        </div>
    </div>
    <div style="padding:20px">
        <form onsubmit="envoyerNotif(event, 'tva')">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:16px">
                <div>
                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:10px">Dossiers concernés</div>
                    <?php foreach($alertes_tva as $ent): ?>
                    <label style="display:flex;align-items:center;gap:8px;margin-bottom:8px;cursor:pointer">
                        <input type="checkbox" name="ent_ids_tva[]" value="<?= $ent['id'] ?>" checked style="width:15px;height:15px;accent-color:var(--navy-dark)">
                        <span style="font-size:13px;font-weight:500"><?= e($ent['raison_sociale']) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div>
                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:10px">Destinataires</div>
                    <?php foreach($collaborateurs as $u): ?>
                    <label style="display:flex;align-items:center;gap:8px;margin-bottom:8px;cursor:pointer">
                        <input type="checkbox" name="user_ids_tva[]" value="<?= $u['id'] ?>" style="width:15px;height:15px;accent-color:var(--navy-dark)">
                        <span style="font-size:13px"><?= e($u['prenom'].' '.$u['nom']) ?> <span style="color:var(--text-muted);font-size:11px">&lt;<?= e($u['email']) ?>&gt;</span></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div id="msg_tva" style="display:none;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:10px"></div>
            <button type="submit" style="padding:10px 24px;background:var(--navy-dark);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:600">
                📧 Envoyer les alertes TVA
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Alerte brouillons -->
<?php if(!empty($brouillons)): ?>
<div class="card" style="margin-bottom:20px;overflow:hidden">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
        <div style="font-weight:700;font-size:15px;display:flex;align-items:center;gap:8px">
            <span style="background:#fed7aa;color:#9a3412;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700">BROUILLON</span>
            Écritures en brouillon à valider
        </div>
        <div style="font-size:13px;color:var(--text-muted);margin-top:3px"><?= count($brouillons) ?> dossier(s) avec des écritures non validées</div>
    </div>
    <div style="padding:20px">
        <form onsubmit="envoyerNotif(event, 'brouillon')">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:16px">
                <div>
                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:10px">Dossiers concernés</div>
                    <?php foreach($brouillons as $ent): ?>
                    <label style="display:flex;align-items:center;gap:8px;margin-bottom:8px;cursor:pointer">
                        <input type="checkbox" name="ent_ids_br[]" value="<?= $ent['id'] ?>" checked style="width:15px;height:15px;accent-color:var(--navy-dark)">
                        <span style="font-size:13px;font-weight:500"><?= e($ent['raison_sociale']) ?> <span style="color:#f59e0b;font-size:11px">(<?= $ent['nb_brouillons'] ?> en attente)</span></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div>
                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin-bottom:10px">Destinataires</div>
                    <?php foreach($collaborateurs as $u): ?>
                    <label style="display:flex;align-items:center;gap:8px;margin-bottom:8px;cursor:pointer">
                        <input type="checkbox" name="user_ids_br[]" value="<?= $u['id'] ?>" style="width:15px;height:15px;accent-color:var(--navy-dark)">
                        <span style="font-size:13px"><?= e($u['prenom'].' '.$u['nom']) ?> <span style="color:var(--text-muted);font-size:11px">&lt;<?= e($u['email']) ?>&gt;</span></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div id="msg_br" style="display:none;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:10px"></div>
            <button type="submit" style="padding:10px 24px;background:var(--navy-dark);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:600">
                📧 Envoyer les alertes brouillons
            </button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if(empty($alertes_tva) && empty($brouillons)): ?>
<div class="card" style="padding:60px;text-align:center;color:var(--text-muted)">
    <div style="font-size:40px;margin-bottom:16px">✅</div>
    <div style="font-size:16px;font-weight:600;margin-bottom:8px">Tout est à jour !</div>
    <div style="font-size:13px">Aucune alerte à envoyer pour le moment.</div>
</div>
<?php endif; ?>

<!-- Échéances proches -->
<?php if(!empty($echeances_proches)): ?>
<div class="card" style="margin-top:20px;overflow:hidden">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);font-weight:700;font-size:15px;display:flex;align-items:center;gap:8px">
        <span style="background:#fee2e2;color:#991b1b;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700">URGENT</span>
        Échéances fiscales dans les 15 jours
    </div>
    <table style="width:100%;border-collapse:collapse;font-size:13px">
        <thead><tr style="background:var(--bg-secondary)">
            <th style="padding:10px 16px;text-align:left;font-size:11px;color:var(--text-muted);text-transform:uppercase">Dossier</th>
            <th style="padding:10px 16px;text-align:left;font-size:11px;color:var(--text-muted);text-transform:uppercase">Échéance</th>
            <th style="padding:10px 16px;text-align:center;font-size:11px;color:var(--text-muted);text-transform:uppercase">Date</th>
            <th style="padding:10px 16px;text-align:center;font-size:11px;color:var(--text-muted);text-transform:uppercase">Jours restants</th>
        </tr></thead>
        <tbody>
        <?php foreach($echeances_proches as $ech):
            $jours = (int)((strtotime($ech['date_echeance']) - time()) / 86400);
            $couleur = $jours <= 3 ? '#dc2626' : ($jours <= 7 ? '#f59e0b' : '#2563eb');
        ?>
        <tr style="border-bottom:1px solid var(--border)">
            <td style="padding:10px 16px;font-weight:600"><?= e($ech['raison_sociale']) ?></td>
            <td style="padding:10px 16px"><?= e($ech['libelle'] ?? $ech['type_echeance'] ?? '—') ?></td>
            <td style="padding:10px 16px;text-align:center;font-family:monospace"><?= date('d/m/Y', strtotime($ech['date_echeance'])) ?></td>
            <td style="padding:10px 16px;text-align:center">
                <span style="background:<?= $couleur ?>22;color:<?= $couleur ?>;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700">
                    <?= $jours <= 0 ? 'Dépassé' : $jours.' j' ?>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
function envoyerNotif(e, type) {
    e.preventDefault();
    var form = e.target;
    var msgEl = document.getElementById('msg_'+type);
    var fd = new FormData();
    fd.append('type', type);

    // Collecter les checkboxes cochées
    var suffix = type === 'tva' ? '_tva' : '_br';
    form.querySelectorAll('input[name="ent_ids'+suffix+'[]"]:checked').forEach(cb => fd.append('entreprise_ids[]', cb.value));
    form.querySelectorAll('input[name="user_ids'+suffix+'[]"]:checked').forEach(cb => fd.append('user_ids[]', cb.value));

    var btn = form.querySelector('button[type=submit]');
    btn.disabled=true; btn.textContent='Envoi en cours...';

    fetch('<?= APP_URL ?>/notifications-email/envoyer', {method:'POST', body:fd})
        .then(r=>r.json())
        .then(d=>{
            msgEl.style.display='block';
            if(d.ok){
                msgEl.style.background='#f0fdf4'; msgEl.style.color='#166534';
                msgEl.textContent='✓ '+d.nb_ok+' email(s) envoyé(s)'+(d.nb_err>0?' — '+d.nb_err+' échec(s)':'');
            } else {
                msgEl.style.background='#fef2f2'; msgEl.style.color='#991b1b';
                msgEl.textContent='✗ '+d.error;
            }
            btn.disabled=false; btn.textContent='📧 Renvoyer';
        });
}
</script>
