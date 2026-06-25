<?php
function fmtMin(int $min): string {
    $h = intdiv($min, 60); $m = $min % 60;
    return $h.'h'.($m > 0 ? sprintf('%02d', $m) : '');
}
?>
<div class="page-header">
    <div>
        <div class="page-title">Rapport temps — Cabinet</div>
        <div class="page-subtitle">Heures par collaborateur et par dossier — <?= $mois_labels[$mois] ?> <?= $annee ?></div>
    </div>
    <form method="get" style="display:flex;gap:8px;align-items:center">
        <select name="mois" onchange="this.form.submit()" style="padding:7px 12px;border-radius:8px;border:1px solid var(--border);font-size:13px">
            <?php for($m=1;$m<=12;$m++): ?>
            <option value="<?= $m ?>" <?= $m==$mois?'selected':'' ?>><?= $mois_labels[$m] ?></option>
            <?php endfor; ?>
        </select>
        <select name="annee" onchange="this.form.submit()" style="padding:7px 12px;border-radius:8px;border:1px solid var(--border);font-size:13px">
            <?php for($y=date('Y');$y>=date('Y')-2;$y--): ?>
            <option value="<?= $y ?>" <?= $y==$annee?'selected':'' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
    </form>
</div>

<!-- KPIs globaux -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px">
    <div class="card" style="padding:16px 20px">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">Total heures cabinet</div>
        <div style="font-size:26px;font-weight:700;color:var(--navy-dark);font-family:monospace"><?= fmtMin((int)$total_min_global) ?></div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:3px"><?= $mois_labels[$mois] ?> <?= $annee ?></div>
    </div>
    <div class="card" style="padding:16px 20px">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">Heures facturables</div>
        <div style="font-size:26px;font-weight:700;color:#1f6e4e;font-family:monospace"><?= fmtMin((int)$total_fact_global) ?></div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:3px"><?= $total_min_global > 0 ? round($total_fact_global/$total_min_global*100) : 0 ?>% du total</div>
    </div>
    <div class="card" style="padding:16px 20px">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">Collaborateurs actifs</div>
        <div style="font-size:26px;font-weight:700;color:var(--navy-dark)"><?= count($par_collab) ?></div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:3px">ce mois</div>
    </div>
    <div class="card" style="padding:16px 20px">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">Dossiers travaillés</div>
        <div style="font-size:26px;font-weight:700;color:var(--navy-dark)"><?= count($par_dossier) ?></div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:3px">ce mois</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">

    <!-- Par collaborateur -->
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border);font-weight:700;font-size:14px">Par collaborateur</div>
        <?php if(empty($par_collab)): ?>
        <div style="padding:40px;text-align:center;color:var(--text-muted)">Aucune saisie ce mois</div>
        <?php else: ?>
        <div style="padding:14px 18px;display:flex;flex-direction:column;gap:12px">
            <?php $max_min = max(array_column($par_collab,'total_min') ?: [1]); ?>
            <?php foreach($par_collab as $pc):
                $pct = round($pc['total_min']/$max_min*100);
                $fact_pct = $pc['total_min'] > 0 ? round($pc['fact_min']/$pc['total_min']*100) : 0;
            ?>
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
                    <div>
                        <span style="font-weight:600;font-size:13px"><?= e($pc['prenom'].' '.$pc['nom']) ?></span>
                        <span style="font-size:11px;color:var(--text-muted);margin-left:6px"><?= $pc['nb_dossiers'] ?> dossier(s)</span>
                    </div>
                    <div style="text-align:right">
                        <span style="font-family:monospace;font-weight:700;font-size:14px"><?= fmtMin((int)$pc['total_min']) ?></span>
                        <span style="font-size:11px;color:#1f6e4e;margin-left:6px"><?= fmtMin((int)$pc['fact_min']) ?> fact.</span>
                    </div>
                </div>
                <div style="height:8px;background:#f0f3f8;border-radius:4px;overflow:hidden">
                    <div style="height:8px;width:<?= $pct ?>%;background:var(--navy-dark);border-radius:4px;position:relative">
                        <div style="position:absolute;top:0;left:0;height:8px;width:<?= $fact_pct ?>%;background:#1f6e4e;border-radius:4px"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Par dossier -->
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border);font-weight:700;font-size:14px">Par dossier</div>
        <?php if(empty($par_dossier)): ?>
        <div style="padding:40px;text-align:center;color:var(--text-muted)">Aucune saisie ce mois</div>
        <?php else: ?>
        <div style="padding:14px 18px;display:flex;flex-direction:column;gap:12px">
            <?php $max_d = max(array_column($par_dossier,'total_min') ?: [1]); ?>
            <?php foreach($par_dossier as $pd):
                $pct = round($pd['total_min']/$max_d*100);
                $couleur = $pd['couleur'] ?? '#1e3a5f';
            ?>
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="width:10px;height:10px;border-radius:50%;background:<?= e($couleur) ?>"></div>
                        <span style="font-weight:600;font-size:13px"><?= e($pd['raison_sociale']) ?></span>
                        <span style="font-size:11px;color:var(--text-muted)"><?= $pd['nb_collabs'] ?> collab.</span>
                    </div>
                    <span style="font-family:monospace;font-weight:700;font-size:14px"><?= fmtMin((int)$pd['total_min']) ?></span>
                </div>
                <div style="height:8px;background:#f0f3f8;border-radius:4px">
                    <div style="height:8px;width:<?= $pct ?>%;background:<?= e($couleur) ?>;border-radius:4px;opacity:.8"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tableau détaillé -->
<div class="card" style="padding:0;overflow:hidden">
    <div style="padding:14px 18px;border-bottom:1px solid var(--border);font-weight:700;font-size:14px">Détail — Collaborateur × Dossier</div>
    <?php if(empty($details)): ?>
    <div style="padding:48px;text-align:center;color:var(--text-muted)">
        <div style="font-size:32px;margin-bottom:12px">⏱</div>
        <div style="font-weight:600">Aucune saisie ce mois</div>
        <div style="font-size:13px;margin-top:6px">Les collaborateurs n'ont pas encore saisi de temps pour <?= $mois_labels[$mois] ?> <?= $annee ?></div>
    </div>
    <?php else: ?>
    <table style="width:100%;border-collapse:collapse;font-size:13px">
        <thead>
            <tr style="background:var(--bg);border-bottom:2px solid var(--border)">
                <th style="padding:10px 16px;text-align:left;font-size:11px;color:var(--text-muted);text-transform:uppercase">Collaborateur</th>
                <th style="padding:10px 16px;text-align:left;font-size:11px;color:var(--text-muted);text-transform:uppercase">Dossier</th>
                <th style="padding:10px 16px;text-align:right;font-size:11px;color:var(--text-muted);text-transform:uppercase">Total</th>
                <th style="padding:10px 16px;text-align:right;font-size:11px;color:var(--text-muted);text-transform:uppercase">Facturable</th>
                <th style="padding:10px 16px;text-align:right;font-size:11px;color:var(--text-muted);text-transform:uppercase">Facturé</th>
                <th style="padding:10px 16px;text-align:center;font-size:11px;color:var(--text-muted);text-transform:uppercase">Saisies</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $prev_user = null;
        foreach($details as $d):
            $is_new_user = $d['user_id'] !== $prev_user;
            $prev_user = $d['user_id'];
        ?>
        <?php if($is_new_user): ?>
        <tr style="background:#f8fafc;border-top:2px solid var(--border)">
            <td colspan="6" style="padding:8px 16px;font-weight:700;font-size:13px;color:var(--navy-dark)">
                <?= e($d['prenom'].' '.$d['nom']) ?>
            </td>
        </tr>
        <?php endif; ?>
        <tr style="border-bottom:1px solid var(--border)">
            <td style="padding:10px 16px;padding-left:32px;color:var(--text-muted)">—</td>
            <td style="padding:10px 16px">
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:8px;height:8px;border-radius:50%;background:<?= e($d['couleur'] ?? '#1e3a5f') ?>"></div>
                    <span style="font-weight:500"><?= e($d['raison_sociale']) ?></span>
                </div>
            </td>
            <td style="padding:10px 16px;text-align:right;font-family:monospace;font-weight:700"><?= fmtMin((int)$d['total_min']) ?></td>
            <td style="padding:10px 16px;text-align:right;font-family:monospace;color:#1f6e4e"><?= fmtMin((int)$d['fact_min']) ?></td>
            <td style="padding:10px 16px;text-align:right;font-family:monospace;color:#2563eb"><?= fmtMin((int)$d['facture_min']) ?></td>
            <td style="padding:10px 16px;text-align:center;color:var(--text-muted)"><?= $d['nb_saisies'] ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background:var(--navy-dark);color:#fff">
                <td colspan="2" style="padding:11px 16px;font-weight:700">TOTAL CABINET</td>
                <td style="padding:11px 16px;text-align:right;font-family:monospace;font-weight:700;font-size:15px"><?= fmtMin((int)$total_min_global) ?></td>
                <td style="padding:11px 16px;text-align:right;font-family:monospace"><?= fmtMin((int)$total_fact_global) ?></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>
</div>
