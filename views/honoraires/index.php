<style>
.hon-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:24px; }
.hon-stat { background:#fff; border:1px solid var(--border); border-radius:14px; padding:20px; position:relative; overflow:hidden; }
.hon-stat::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; }
.hon-stat-label { font-size:11px; text-transform:uppercase; letter-spacing:1px; color:var(--text-muted); margin-bottom:8px; font-weight:500; }
.hon-stat-val { font-family:'Cormorant Garamond',serif; font-size:32px; font-weight:600; color:var(--navy-dark); }
.hon-stat-sub { font-size:12px; color:var(--text-muted); margin-top:4px; }
.hon-stat.navy::before  { background:linear-gradient(90deg,#1e3a5f,#2a4f7c); }
.hon-stat.green::before { background:linear-gradient(90deg,#166534,#1f6e4e); }
.hon-stat.red::before   { background:linear-gradient(90deg,#b91c1c,#ef4444); }
.hon-stat.gold::before  { background:linear-gradient(90deg,#c9a96e,#f59e0b); }
</style>

<div class="page-header">
    <div>
        <div class="page-title" style="font-family:'Cormorant Garamond',serif;font-size:28px">Honoraires</div>
        <div style="font-size:13px;color:var(--text-muted);margin-top:3px">Facturation du cabinet — <?= date('Y') ?></div>
    </div>
    <a href="<?= APP_URL ?>/honoraires/creer" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Nouvelle facture
    </a>
</div>

<!-- Stats -->
<div class="hon-stats">
    <div class="hon-stat navy">
        <div class="hon-stat-label">CA ce mois</div>
        <div class="hon-stat-val"><?= number_format($ca_mois/1000,0,',',' ') ?> K</div>
        <div class="hon-stat-sub"><?= formatMontant($ca_mois) ?></div>
    </div>
    <div class="hon-stat green">
        <div class="hon-stat-label">CA <?= date('Y') ?></div>
        <div class="hon-stat-val"><?= number_format($ca_annee/1000,0,',',' ') ?> K</div>
        <div class="hon-stat-sub"><?= formatMontant($ca_annee) ?></div>
    </div>
    <div class="hon-stat <?= $impayes > 0 ? 'red' : 'gold' ?>">
        <div class="hon-stat-label">Impayés</div>
        <div class="hon-stat-val" style="<?= $impayes > 0 ? 'color:var(--danger)' : '' ?>"><?= number_format($impayes/1000,0,',',' ') ?> K</div>
        <div class="hon-stat-sub"><?= formatMontant($impayes) ?></div>
    </div>
</div>

<!-- Filtres -->
<form method="GET" style="display:flex;gap:10px;margin-bottom:16px;align-items:flex-end;flex-wrap:wrap">
    <div class="form-field">
        <label>Statut</label>
        <select name="statut" onchange="this.form.submit()">
            <option value="">Tous</option>
            <option value="emise" <?= $filtre_statut==='emise'?'selected':'' ?>>Émise</option>
            <option value="payee" <?= $filtre_statut==='payee'?'selected':'' ?>>Payé</option>
            <option value="impaye" <?= $filtre_statut==='impaye'?'selected':'' ?>>Impayé</option>
        </select>
    </div>
    <div class="form-field">
        <label>Client</label>
        <select name="entreprise_id" onchange="this.form.submit()">
            <option value="">Tous</option>
            <?php foreach($entreprises as $ent): ?>
            <option value="<?= $ent['id'] ?>" <?= $filtre_entreprise==$ent['id']?'selected':'' ?>><?= e($ent['raison_sociale']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-field">
        <label>Période</label>
        <input type="month" name="periode" value="<?= e($filtre_mois) ?>" onchange="this.form.submit()">
    </div>
    <a href="<?= APP_URL ?>/honoraires/missions" class="btn btn-outline btn-sm" style="margin-bottom:0">Missions</a>
</form>

<div class="table-wrap">
    <div class="table-header"><span class="table-title"><?= count($honoraires) ?> facture(s)</span></div>
    <?php if(empty($honoraires)): ?>
    <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
        <h3>Aucune facture</h3>
    </div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>N° Facture</th>
                <th>Client</th>
                <th>Date</th>
                <th>Échéance</th>
                <th style="text-align:right">HT</th>
                <th style="text-align:right">TTC</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($honoraires as $h): ?>
            <tr>
                <td><code style="font-size:12px;background:var(--bg);padding:2px 8px;border-radius:5px"><?= e($h['numero_facture']) ?></code></td>
                <td style="font-weight:500"><?= e($h['raison_sociale']) ?></td>
                <td style="font-size:13px"><?= date('d/m/Y', strtotime($h['date_facture'])) ?></td>
                <td style="font-size:13px">
                    <?php if($h['date_echeance']): ?>
                        <?php $retard = strtotime($h['date_echeance']) < time() && $h['statut'] !== 'paye'; ?>
                        <span style="<?= $retard ? 'color:var(--danger);font-weight:600' : '' ?>">
                            <?= date('d/m/Y', strtotime($h['date_echeance'])) ?>
                        </span>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td style="text-align:right;font-family:monospace"><?= number_format($h['montant_ht'],0,',',' ') ?></td>
                <td style="text-align:right;font-family:monospace;font-weight:600"><?= number_format($h['montant_ttc'],0,',',' ') ?></td>
                <td>
                    <?php
                    $sb = ['emise'=>'badge-warning','en_attente'=>'badge-warning','paye'=>'badge-success','payee'=>'badge-success','impaye'=>'badge-danger'];
                    $sl = ['emise'=>'Émise','en_attente'=>'En attente','paye'=>'Payé','payee'=>'Payé','impaye'=>'Impayé'];
                    ?>
                    <span class="badge <?= $sb[$h['statut']] ?? 'badge-navy' ?>"><?= $sl[$h['statut']] ?? $h['statut'] ?></span>
                </td>
                <td>
                    <div style="display:flex;gap:6px">
                        <a href="<?= APP_URL ?>/honoraires/voir?id=<?= $h['id'] ?>" class="btn btn-outline btn-sm">Voir</a>
                        <a href="<?= APP_URL ?>/honoraires/pdf?id=<?= $h['id'] ?>" class="btn btn-sm" style="background:rgba(30,58,95,0.07);color:var(--navy)" target="_blank">PDF</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
