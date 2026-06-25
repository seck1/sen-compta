<?php
$statut_cfg = [
    'soumise'   => ['Soumise',   '#f59e0b'],
    'approuvee' => ['Approuvée', '#2563eb'],
    'rejetee'   => ['Rejetée',   '#dc2626'],
    'remboursee'=> ['Remboursée','#1f6e4e'],
];
?>
<div class="page-header">
    <div>
        <div class="page-title">Notes de frais</div>
        <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> — Exercice <?= $exercice ?></div>
    </div>
    <button onclick="document.getElementById('modalNF').style.display='flex'" class="btn btn-primary">
        + Nouvelle note de frais
    </button>
</div>

<?php if(!empty($_GET['saved'])): ?>
<div style="background:#dcfce7;border:1px solid #86efac;border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#1f6e4e;font-weight:600">Note de frais enregistrée avec succès.</div>
<?php endif; ?>

<!-- KPIs -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px">
    <div class="card" style="padding:16px 20px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">En attente</div>
        <div style="font-size:20px;font-weight:700;color:#f59e0b;font-family:monospace"><?= formatMontant($total_soumis) ?></div>
    </div>
    <div class="card" style="padding:16px 20px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">Approuvées</div>
        <div style="font-size:20px;font-weight:700;color:#2563eb;font-family:monospace"><?= formatMontant($total_approuve) ?></div>
    </div>
    <div class="card" style="padding:16px 20px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">Remboursées</div>
        <div style="font-size:20px;font-weight:700;color:#1f6e4e;font-family:monospace"><?= formatMontant($total_rembourse) ?></div>
    </div>
</div>

<!-- Filtres -->
<div class="card" style="padding:14px 20px;margin-bottom:16px">
    <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
        <select name="statut" onchange="this.form.submit()" style="padding:7px 10px;border-radius:8px;border:1px solid var(--border);font-size:14px">
            <option value="">Tous les statuts</option>
            <?php foreach($statut_cfg as $k=>[$l,$c]): ?>
            <option value="<?= $k ?>" <?= $filtre_statut===$k?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>
        <select name="mois" onchange="this.form.submit()" style="padding:7px 10px;border-radius:8px;border:1px solid var(--border);font-size:14px">
            <option value="">Tous les mois</option>
            <?php foreach($mois_labels as $i=>$ml): if(!$i) continue; ?>
            <option value="<?= $i ?>" <?= $filtre_mois===$i?'selected':'' ?>><?= $ml ?></option>
            <?php endforeach; ?>
        </select>
        <?php if($filtre_statut||$filtre_mois): ?>
        <a href="<?= APP_URL ?>/dossier/notes-frais?id=<?= $entreprise['id'] ?>" style="font-size:14px;color:var(--text-muted)">✕ Effacer</a>
        <?php endif; ?>
    </form>
</div>

<!-- Tableau -->
<div class="card" style="padding:0;overflow:hidden">
    <?php if(empty($notes)): ?>
    <div style="text-align:center;padding:50px;color:var(--text-muted)">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:40px;height:40px;margin:0 auto 12px;display:block;opacity:.3"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
        Aucune note de frais enregistrée.
    </div>
    <?php else: ?>
    <table style="width:100%;border-collapse:collapse;font-size:14px">
        <thead>
            <tr style="background:var(--bg-secondary);border-bottom:2px solid var(--border)">
                <th style="padding:10px 16px;text-align:left;font-size:14px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Date</th>
                <th style="padding:10px 16px;text-align:left;font-size:14px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Catégorie</th>
                <th style="padding:10px 16px;text-align:left;font-size:14px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Libellé</th>
                <th style="padding:10px 16px;text-align:left;font-size:14px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Employé</th>
                <th style="padding:10px 16px;text-align:right;font-size:14px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Montant</th>
                <th style="padding:10px 16px;text-align:center;font-size:14px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Justificatif</th>
                <th style="padding:10px 16px;text-align:center;font-size:14px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Statut</th>
                <?php if(isSuperviseur()): ?>
                <th style="padding:10px 16px;text-align:center;font-size:14px;font-weight:600;text-transform:uppercase;background:#f1f5f9;color:#4a554f">Action</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach($notes as $n):
            $sc = $statut_cfg[$n['statut']] ?? ['?','#999'];
        ?>
        <tr style="border-bottom:1px solid var(--border)">
            <td style="padding:11px 16px;font-family:monospace;color:var(--text-muted)"><?= date('d/m/Y', strtotime($n['date_depense'])) ?></td>
            <td style="padding:11px 16px">
                <span style="padding:3px 10px;border-radius:20px;font-size:13px;font-weight:600;background:var(--bg-secondary);color:var(--text-muted)">
                    <?= $categories[$n['categorie']] ?? $n['categorie'] ?>
                </span>
            </td>
            <td style="padding:11px 16px;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e($n['libelle']) ?>"><?= e($n['libelle']) ?></td>
            <td style="padding:11px 16px;color:var(--text-muted)"><?= e($n['employe_nom'] ?: ($n['prenom'].' '.$n['user_nom'])) ?></td>
            <td style="padding:11px 16px;text-align:right;font-family:monospace;font-weight:700;color:#dc2626"><?= formatMontant($n['montant']) ?></td>
            <td style="padding:11px 16px;text-align:center">
                <?php if($n['justificatif']): ?>
                <a href="<?= APP_URL ?>/public/uploads/justificatifs/<?= e($n['justificatif']) ?>" target="_blank"
                   style="font-size:13px;color:#2563eb;text-decoration:none;font-weight:600">📎 Voir</a>
                <?php else: ?>
                <span style="color:var(--text-muted);font-size:13px">—</span>
                <?php endif; ?>
            </td>
            <td style="padding:11px 16px;text-align:center">
                <span style="padding:3px 10px;border-radius:20px;font-size:13px;font-weight:600;background:<?= $sc[1] ?>22;color:<?= $sc[1] ?>"><?= $sc[0] ?></span>
            </td>
            <?php if(isSuperviseur()): ?>
            <td style="padding:11px 16px;text-align:center">
                <select onchange="changerStatut(<?= $n['id'] ?>, this.value)"
                        style="padding:4px 8px;border-radius:6px;border:1px solid var(--border);font-size:13px;cursor:pointer">
                    <?php foreach($statut_cfg as $k=>[$l,$c]): ?>
                    <option value="<?= $k ?>" <?= $n['statut']===$k?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Modal nouvelle note -->
<div id="modalNF" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:28px;width:520px;max-width:95vw;max-height:90vh;overflow-y:auto">
        <div style="font-size:14px;font-weight:700;margin-bottom:20px">Nouvelle note de frais</div>
        <form method="POST" action="<?= APP_URL ?>/dossier/notes-frais/store" enctype="multipart/form-data">
            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div>
                    <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Date dépense</label>
                    <input type="date" name="date_depense" value="<?= date('Y-m-d') ?>" required style="width:100%;padding:9px;border-radius:8px;border:1px solid var(--border);font-size:14px">
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Catégorie</label>
                    <select name="categorie" style="width:100%;padding:9px;border-radius:8px;border:1px solid var(--border);font-size:14px">
                        <?php foreach($categories as $k=>$v): ?>
                        <option value="<?= $k ?>"><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="margin-bottom:14px">
                <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Libellé</label>
                <input type="text" name="libelle" required placeholder="Description de la dépense" style="width:100%;padding:9px;border-radius:8px;border:1px solid var(--border);font-size:14px">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                <div>
                    <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Montant (FCFA)</label>
                    <input type="number" name="montant" required min="0" step="1" placeholder="0" style="width:100%;padding:9px;border-radius:8px;border:1px solid var(--border);font-size:14px">
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Employé concerné</label>
                    <select name="employe_id" style="width:100%;padding:9px;border-radius:8px;border:1px solid var(--border);font-size:14px">
                        <option value="">— Aucun —</option>
                        <?php foreach($employes as $emp): ?>
                        <option value="<?= $emp['id'] ?>"><?= e($emp['nom_complet']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="margin-bottom:14px">
                <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Justificatif (photo/PDF)</label>
                <input type="file" name="justificatif" accept=".jpg,.jpeg,.png,.pdf" style="width:100%;padding:9px;border-radius:8px;border:1px solid var(--border);font-size:14px">
            </div>
            <div style="margin-bottom:20px">
                <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px">Notes</label>
                <textarea name="notes" rows="2" style="width:100%;padding:9px;border-radius:8px;border:1px solid var(--border);font-size:14px;resize:vertical" placeholder="Informations complémentaires..."></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('modalNF').style.display='none'" style="padding:9px 20px;border-radius:8px;border:1px solid var(--border);background:none;cursor:pointer">Annuler</button>
                <button type="submit" style="padding:9px 20px;border-radius:8px;background:#2563eb;color:#fff;border:none;cursor:pointer;font-weight:600">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
function changerStatut(id, statut) {
    var fd=new FormData();
    fd.append('entreprise_id','<?= $entreprise['id'] ?>');
    fd.append('note_id',id);
    fd.append('statut',statut);
    fetch('<?= APP_URL ?>/dossier/notes-frais/statut',{method:'POST',body:fd})
        .then(()=>location.reload());
}
</script>
