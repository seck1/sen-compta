<?php
$cats = [
    'saisie'      => ['label'=>'Saisie comptable',   'color'=>'#2563eb'],
    'revision'    => ['label'=>'Révision / Contrôle','color'=>'#b8923f'],
    'declaration' => ['label'=>'Déclaration fiscale', 'color'=>'#dc2626'],
    'reunion'     => ['label'=>'Réunion client',      'color'=>'#059669'],
    'rapport'     => ['label'=>'Rapport / Bilan',     'color'=>'#c9a96e'],
    'autre'       => ['label'=>'Autre',               'color'=>'#6b7280'],
];
function fmtMin(int $min): string {
    $h = intdiv($min, 60); $m = $min % 60;
    return $h.'h'.($m > 0 ? sprintf('%02d', $m) : '');
}
?>
<div class="page-header">
    <div>
        <div class="page-title">Suivi du temps</div>
        <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> — <?= $mois_labels[$mois] ?> <?= $annee ?></div>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
        <form method="get" style="display:flex;gap:8px;align-items:center">
            <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">
            <select name="mois" onchange="this.form.submit()" style="padding:7px 12px;border-radius:8px;border:1px solid var(--border);font-size:16px">
                <?php for($m=1;$m<=12;$m++): ?>
                <option value="<?= $m ?>" <?= $m==$mois?'selected':'' ?>><?= $mois_labels[$m] ?></option>
                <?php endfor; ?>
            </select>
            <select name="annee" onchange="this.form.submit()" style="padding:7px 12px;border-radius:8px;border:1px solid var(--border);font-size:16px">
                <?php for($y=date('Y');$y>=date('Y')-3;$y--): ?>
                <option value="<?= $y ?>" <?= $y==$annee?'selected':'' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>
        <button onclick="document.getElementById('modal-saisie').style.display='flex'" class="btn btn-primary">
            + Saisir du temps
        </button>
    </div>
</div>

<?php if(isset($_GET['ok'])): ?>
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:12px 18px;margin-bottom:20px;color:#166534;font-weight:600">✓ Temps enregistré.</div>
<?php endif; ?>

<!-- KPIs -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px">
    <div class="card" style="padding:16px 20px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">Total ce mois</div>
        <div style="font-size:24px;font-weight:700;color:var(--navy-dark);font-family:monospace"><?= fmtMin($total_minutes) ?></div>
        <div style="font-size:15px;color:var(--text-muted);margin-top:3px"><?= count($saisies) ?> saisie(s)</div>
    </div>
    <div class="card" style="padding:16px 20px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">Facturable</div>
        <div style="font-size:24px;font-weight:700;color:#1f6e4e;font-family:monospace"><?= fmtMin($total_fact_min) ?></div>
        <div style="font-size:15px;color:var(--text-muted);margin-top:3px"><?= $total_minutes > 0 ? round($total_fact_min/$total_minutes*100) : 0 ?>% du total</div>
    </div>
    <div class="card" style="padding:16px 20px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">Non facturable</div>
        <div style="font-size:24px;font-weight:700;color:#f59e0b;font-family:monospace"><?= fmtMin($total_minutes - $total_fact_min) ?></div>
    </div>
    <div class="card" style="padding:16px 20px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">Cumul annuel <?= $annee ?></div>
        <div style="font-size:24px;font-weight:700;color:var(--navy-dark);font-family:monospace"><?= fmtMin($total_annee_min) ?></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
    <!-- Par collaborateur -->
    <?php if(!empty($par_collab)): ?>
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border);font-weight:700;font-size:17px">Par collaborateur</div>
        <table style="width:100%;border-collapse:collapse;font-size:16px">
            <?php foreach($par_collab as $pc): ?>
            <tr style="border-bottom:1px solid var(--border)">
                <td style="padding:10px 18px;font-weight:600"><?= e($pc['prenom'].' '.$pc['nom']) ?></td>
                <td style="padding:10px 18px;text-align:right;font-family:monospace;font-weight:700"><?= fmtMin((int)$pc['total_min']) ?></td>
                <td style="padding:10px 18px;text-align:right;font-size:15px;color:#1f6e4e"><?= fmtMin((int)$pc['fact_min']) ?> fact.</td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- Par catégorie -->
    <?php if(!empty($par_categorie)): ?>
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border);font-weight:700;font-size:17px">Par catégorie</div>
        <div style="padding:14px 18px;display:flex;flex-direction:column;gap:10px">
            <?php foreach($par_categorie as $pc):
                $cat = $cats[$pc['categorie']] ?? ['label'=>$pc['categorie'],'color'=>'#6b7280'];
                $pct = $total_minutes > 0 ? round($pc['total_min']/$total_minutes*100) : 0;
            ?>
            <div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                    <span style="font-size:15px;font-weight:600;color:<?= $cat['color'] ?>"><?= $cat['label'] ?></span>
                    <span style="font-size:15px;font-family:monospace"><?= fmtMin((int)$pc['total_min']) ?> (<?= $pct ?>%)</span>
                </div>
                <div style="height:6px;background:#f0f3f8;border-radius:3px">
                    <div style="height:6px;width:<?= $pct ?>%;background:<?= $cat['color'] ?>;border-radius:3px"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Liste des saisies -->
<div class="card" style="padding:0;overflow:hidden">
    <div style="padding:14px 18px;border-bottom:1px solid var(--border);font-weight:700;font-size:17px">
        Détail des saisies — <?= $mois_labels[$mois] ?> <?= $annee ?>
    </div>
    <?php if(empty($saisies)): ?>
    <div style="text-align:center;padding:48px;color:var(--text-muted)">
        <div style="font-size:32px;margin-bottom:12px">⏱</div>
        <div style="font-weight:600;margin-bottom:6px">Aucune saisie ce mois</div>
        <div style="font-size:16px">Cliquez sur "Saisir du temps" pour commencer</div>
    </div>
    <?php else: ?>
    <table style="width:100%;border-collapse:collapse;font-size:16px">
        <thead>
            <tr style="background:var(--bg);border-bottom:2px solid var(--border)">
                <th style="padding:10px 16px;text-align:left;font-size:14px;color:var(--text-muted);text-transform:uppercase">Date</th>
                <th style="padding:10px 16px;text-align:left;font-size:14px;color:var(--text-muted);text-transform:uppercase">Collaborateur</th>
                <th style="padding:10px 16px;text-align:left;font-size:14px;color:var(--text-muted);text-transform:uppercase">Catégorie</th>
                <th style="padding:10px 16px;text-align:left;font-size:14px;color:var(--text-muted);text-transform:uppercase">Description</th>
                <th style="padding:10px 16px;text-align:right;font-size:14px;color:var(--text-muted);text-transform:uppercase">Durée</th>
                <th style="padding:10px 16px;text-align:center;font-size:14px;color:var(--text-muted);text-transform:uppercase">Statut</th>
                <th style="padding:10px 16px;text-align:center;font-size:14px;color:var(--text-muted);text-transform:uppercase">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($saisies as $s):
            $cat = $cats[$s['categorie']] ?? ['label'=>$s['categorie'],'color'=>'#6b7280'];
        ?>
        <tr style="border-bottom:1px solid var(--border)" id="row-temps-<?= $s['id'] ?>">
            <td style="padding:11px 16px;font-family:monospace;color:var(--text-muted)"><?= date('d/m/Y', strtotime($s['date_travail'])) ?></td>
            <td style="padding:11px 16px;font-weight:600"><?= e($s['prenom'].' '.$s['user_nom']) ?></td>
            <td style="padding:11px 16px">
                <span style="background:<?= $cat['color'] ?>18;color:<?= $cat['color'] ?>;padding:2px 8px;border-radius:10px;font-size:15px;font-weight:600"><?= $cat['label'] ?></span>
            </td>
            <td style="padding:11px 16px;color:var(--text-muted);font-size:15px;max-width:200px"><?= e($s['description'] ?? '') ?></td>
            <td style="padding:11px 16px;text-align:right;font-family:monospace;font-weight:700;font-size:17px"><?= fmtMin((int)$s['duree_minutes']) ?></td>
            <td style="padding:11px 16px;text-align:center">
                <?php if($s['facture']): ?>
                <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:10px;font-size:14px;font-weight:700">Facturé</span>
                <?php elseif($s['facturable']): ?>
                <span style="background:#eff6ff;color:#2563eb;padding:2px 8px;border-radius:10px;font-size:14px;font-weight:700">Facturable</span>
                <?php else: ?>
                <span style="background:#f3f4f6;color:#6b7280;padding:2px 8px;border-radius:10px;font-size:14px;font-weight:700">Non fact.</span>
                <?php endif; ?>
            </td>
            <td style="padding:11px 16px;text-align:center">
                <?php if(!$s['facture'] && isSuperviseur()): ?>
                <button onclick="marquerFacture(<?= $s['id'] ?>)" style="padding:4px 10px;border-radius:6px;background:#1f6e4e18;color:#1f6e4e;border:1px solid #1f6e4e44;cursor:pointer;font-size:14px;font-weight:600;margin-right:4px">✓ Facturé</button>
                <?php endif; ?>
                <?php if((int)$s['user_id'] === (int)(auth()['id'] ?? 0) || isAdmin()): ?>
                <button onclick="supprimer(<?= $s['id'] ?>)" style="padding:4px 10px;border-radius:6px;background:#fee2e218;color:#dc2626;border:1px solid #dc262644;cursor:pointer;font-size:14px;font-weight:600">Suppr.</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background:var(--navy-dark);color:#fff">
                <td colspan="4" style="padding:11px 16px;font-weight:700">TOTAL</td>
                <td style="padding:11px 16px;text-align:right;font-family:monospace;font-weight:700;font-size:18px"><?= fmtMin($total_minutes) ?></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>
</div>

<!-- Modal saisie -->
<div id="modal-saisie" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:28px;width:480px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.2)">
        <div style="font-size:17px;font-weight:700;margin-bottom:20px;color:var(--navy-dark)">Saisir du temps</div>
        <form method="post" action="<?= APP_URL ?>/dossier/temps/store">
            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">

            <div class="form-field" style="margin-bottom:14px">
                <label>Date</label>
                <input type="date" name="date_travail" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                <div class="form-field">
                    <label>Heures</label>
                    <input type="number" name="heures" min="0" max="24" value="1" required>
                </div>
                <div class="form-field">
                    <label>Minutes</label>
                    <select name="minutes">
                        <option value="0">00</option>
                        <option value="15">15</option>
                        <option value="30">30</option>
                        <option value="45">45</option>
                    </select>
                </div>
            </div>

            <div class="form-field" style="margin-bottom:14px">
                <label>Catégorie</label>
                <select name="categorie">
                    <?php foreach($cats as $k => $cat): ?>
                    <option value="<?= $k ?>"><?= $cat['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field" style="margin-bottom:14px">
                <label>Description (optionnel)</label>
                <input type="text" name="description" placeholder="Ex: Saisie relevés mars, TVA Q1...">
            </div>

            <div style="margin-bottom:20px">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="checkbox" name="facturable" checked style="width:16px;height:16px;accent-color:var(--navy-dark)">
                    <span style="font-size:16px;font-weight:500">Temps facturable</span>
                </label>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('modal-saisie').style.display='none'" style="padding:9px 20px;border-radius:8px;border:1px solid var(--border);background:none;cursor:pointer">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
function supprimer(id) {
    if (!confirm('Supprimer cette saisie ?')) return;
    var fd = new FormData();
    fd.append('entreprise_id', '<?= $entreprise['id'] ?>');
    fd.append('saisie_id', id);
    fetch('<?= APP_URL ?>/dossier/temps/supprimer', {method:'POST', body:fd})
        .then(function(r){ return r.json(); })
        .then(function(d){ if(d.ok){ var row = document.getElementById('row-temps-'+id); if(row) row.remove(); }});
}
function marquerFacture(id) {
    var fd = new FormData();
    fd.append('entreprise_id', '<?= $entreprise['id'] ?>');
    fd.append('saisie_id', id);
    fetch('<?= APP_URL ?>/dossier/temps/facturer', {method:'POST', body:fd})
        .then(function(){ location.reload(); });
}
</script>
