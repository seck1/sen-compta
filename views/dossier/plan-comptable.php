<?php
$classes = [1=>'Capitaux propres',2=>'Actif immobilisé',3=>'Stocks',4=>'Tiers',5=>'Trésorerie',6=>'Charges',7=>'Produits'];
$grouped = [];
foreach ($comptes as $c) $grouped[$c['classe']][] = $c;
?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Plan comptable OHADA</h1>
        <p class="page-subtitle"><?= count($comptes) ?> comptes · Exercice <?= $entreprise['exercice_courant'] ?></p>
    </div>
</div>

<?php if ($error ?? null): ?>
<div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:10px;padding:12px 18px;color:var(--danger);margin-bottom:20px;font-size:16px"><?= e($error) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

    <!-- Plan comptable -->
    <div>
        <!-- Filtres classes -->
        <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
            <a href="<?= APP_URL ?>/dossier/plan-comptable?id=<?= $entreprise['id'] ?>"
               class="btn btn-sm <?= !isset($_GET['classe']) ? 'btn-primary' : 'btn-outline' ?>">Toutes</a>
            <?php foreach ($classes as $num => $label): ?>
            <a href="<?= APP_URL ?>/dossier/plan-comptable?id=<?= $entreprise['id'] ?>&classe=<?= $num ?>"
               class="btn btn-sm <?= ($_GET['classe'] ?? '') == $num ? 'btn-primary' : 'btn-outline' ?>">
                Classe <?= $num ?>
            </a>
            <?php endforeach; ?>
        </div>

        <?php foreach ($grouped as $classe => $comptes_classe): ?>
        <div class="table-wrap" style="margin-bottom:16px">
            <div class="table-header" style="background:rgba(30,58,95,0.04)">
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:34px;height:34px;border-radius:9px;background:var(--ent-color);display:flex;align-items:center;justify-content:center;font-size:17px;font-weight:700;color:white">
                        <?= $classe ?>
                    </div>
                    <div>
                        <div style="font-size:17px;font-weight:600;color:var(--navy-dark)">Classe <?= $classe ?> — <?= $classes[$classe] ?? '' ?></div>
                        <div style="font-size:15px;color:var(--text-muted)"><?= count($comptes_classe) ?> compte<?= count($comptes_classe)>1?'s':'' ?></div>
                    </div>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width:120px">Numéro</th>
                        <th>Intitulé</th>
                        <th>Type</th>
                        <th style="text-align:right">Mvt Débit</th>
                        <th style="text-align:right">Mvt Crédit</th>
                        <th style="text-align:right">Solde</th>
                        <th style="width:60px"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($comptes_classe as $c): ?>
                <?php $solde = $c['mvt_debit'] - $c['mvt_credit']; ?>
                <tr>
                    <td>
                        <code style="background:var(--bg);padding:3px 9px;border-radius:6px;font-size:15px;font-weight:600;color:var(--navy)">
                            <?= e($c['numero']) ?>
                        </code>
                    </td>
                    <td style="font-size:16px"><?= e($c['intitule']) ?></td>
                    <td>
                        <?php $types = ['actif'=>'badge-info','passif'=>'badge-warning','charge'=>'badge-danger','produit'=>'badge-success','bilan'=>'badge-navy']; ?>
                        <span class="badge <?= $types[$c['type_compte']] ?? 'badge-navy' ?>"><?= ucfirst($c['type_compte']) ?></span>
                    </td>
                    <td style="text-align:right;font-family:monospace;font-size:16px;color:var(--danger)">
                        <?= $c['mvt_debit']>0 ? number_format($c['mvt_debit'],0,',',' ') : '—' ?>
                    </td>
                    <td style="text-align:right;font-family:monospace;font-size:16px;color:var(--success)">
                        <?= $c['mvt_credit']>0 ? number_format($c['mvt_credit'],0,',',' ') : '—' ?>
                    </td>
                    <td style="text-align:right;font-family:monospace;font-size:16px;font-weight:600;color:<?= $solde>0?'var(--danger)':($solde<0?'var(--success)':'var(--text-muted)') ?>">
                        <?= $solde!=0 ? number_format(abs($solde),0,',',' ').' '.($solde>0?'D':'C') : '—' ?>
                    </td>
                    <td style="text-align:center">
                        <?php if ($c['mvt_debit']==0 && $c['mvt_credit']==0): ?>
                        <a href="<?= APP_URL ?>/dossier/plan-comptable/store?action=delete&compte_id=<?= $c['id'] ?>&id=<?= $entreprise['id'] ?>"
                           style="color:var(--danger);font-size:19px;text-decoration:none;opacity:0.5"
                           onclick="return confirm('Supprimer ce compte ?')" title="Supprimer">×</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Formulaire ajout compte -->
    <?php if (isAdmin()): ?>
    <div class="card" style="position:sticky;top:80px">
        <div style="font-size:17px;font-weight:600;color:var(--navy-dark);margin-bottom:16px">Ajouter un compte</div>
        <form method="POST" action="<?= APP_URL ?>/dossier/plan-comptable/store">
            <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
            <div style="display:flex;flex-direction:column;gap:14px">
                <div class="form-field">
                    <label>Numéro de compte <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="numero" placeholder="Ex: 6014" required
                           style="text-transform:uppercase" maxlength="10">
                    <small style="color:var(--text-muted);font-size:14px">La classe est détectée automatiquement (1er chiffre)</small>
                </div>
                <div class="form-field">
                    <label>Intitulé <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="intitule" placeholder="Ex: Achats de carburant" required>
                </div>
                <div class="form-field">
                    <label>Type de compte</label>
                    <select name="type_compte">
                        <option value="actif">Actif</option>
                        <option value="passif">Passif</option>
                        <option value="charge" selected>Charge</option>
                        <option value="produit">Produit</option>
                        <option value="bilan">Bilan</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-ent" style="width:100%;justify-content:center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Ajouter le compte
                </button>
            </div>
        </form>

        <hr style="border:none;border-top:1px solid var(--border);margin:20px 0">

        <div style="font-size:15px;color:var(--text-muted);line-height:1.7">
            <div style="font-weight:600;color:var(--navy-dark);margin-bottom:8px;font-size:16px">Classes OHADA</div>
            <?php foreach ($classes as $n => $l): ?>
            <div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid rgba(228,233,240,0.5)">
                <span style="font-weight:600;color:var(--navy)">Classe <?= $n ?></span>
                <span><?= $l ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
