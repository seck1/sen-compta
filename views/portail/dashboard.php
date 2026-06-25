<?php $fmt = fn($n) => number_format((float)$n, 0, ',', ' '); ?>

<h1 style="font-size:18pt;font-weight:800;letter-spacing:-.4px;margin-bottom:4px">Bonjour, <?= e(explode(' ', $c['nom'])[0]) ?> 👋</h1>
<p style="font-size:10.5pt;color:var(--muted);margin-bottom:26px">Voici l'état de votre dossier comptable chez votre cabinet.</p>

<?php if (($msg ?? '') === 'depose'): ?><div class="pc-flash flash-ok">✓ Votre pièce a bien été transmise au cabinet.</div>
<?php elseif (($msg ?? '') === 'type'): ?><div class="pc-flash flash-err">Format non accepté (PDF, JPG, PNG uniquement).</div>
<?php elseif (($msg ?? '') === 'taille'): ?><div class="pc-flash flash-err">Fichier trop volumineux (max 8 Mo).</div>
<?php elseif (($msg ?? '') === 'erreur'): ?><div class="pc-flash flash-err">Échec de l'envoi. Réessayez.</div>
<?php endif; ?>

<!-- Cartes de synthese -->
<div class="pc-grid">
  <div class="pc-stat">
    <div class="lbl">État du dossier</div>
    <?php if ($cloture && $cloture['statut'] === 'cloture'): ?>
      <div class="val" style="color:var(--green);font-size:14pt">À jour</div>
      <div class="hint">Exercice <?= e($cloture['exercice']) ?> clôturé</div>
    <?php elseif ($cloture): ?>
      <div class="val" style="color:var(--gold);font-size:14pt">En cours</div>
      <div class="hint">Exercice <?= e($cloture['exercice']) ?> en traitement</div>
    <?php else: ?>
      <div class="val" style="color:var(--navy);font-size:14pt">Actif</div>
      <div class="hint">Comptabilité en cours de tenue</div>
    <?php endif; ?>
  </div>

  <?php if ($c['voir_honoraires']): ?>
  <div class="pc-stat">
    <div class="lbl">Honoraires impayés</div>
    <div class="val" style="color:<?= $totalImpaye > 0 ? 'var(--red)' : 'var(--green)' ?>"><?= $fmt($totalImpaye) ?></div>
    <div class="hint">FCFA · <?= $totalImpaye > 0 ? 'à régler' : 'tout est à jour' ?></div>
  </div>
  <?php endif; ?>

  <div class="pc-stat">
    <div class="lbl">Pièces transmises</div>
    <div class="val"><?= count($depots) ?></div>
    <div class="hint"><?= count(array_filter($depots, fn($d)=>$d['statut']==='nouveau')) ?> en attente de traitement</div>
  </div>
</div>

<!-- Etats financiers -->
<?php if ($c['voir_etats']): ?>
<div class="pc-card">
  <h2>📊 Mes états financiers</h2>
  <p class="sub">Consultez les documents comptables publiés par votre cabinet.</p>
  <div style="display:flex;gap:12px;flex-wrap:wrap">
    <a href="<?= APP_URL ?>/portail/etat?type=bilan" class="btn btn-green" style="background:linear-gradient(180deg,#2a8a63,#1f6e4e)">Bilan</a>
    <a href="<?= APP_URL ?>/portail/etat?type=resultat" class="btn btn-green" style="background:linear-gradient(180deg,#2a8a63,#1f6e4e)">Compte de résultat</a>
  </div>
  <p style="font-size:9pt;color:var(--muted);margin-top:12px">Ces états reflètent la dernière situation arrêtée par votre cabinet.</p>
</div>
<?php endif; ?>

<!-- Factures honoraires -->
<?php if ($c['voir_honoraires']): ?>
<div class="pc-card">
  <h2>🧾 Factures d'honoraires</h2>
  <p class="sub">Les factures émises par votre cabinet.</p>
  <?php if (empty($factures)): ?>
    <p style="color:var(--muted);font-size:10.5pt;padding:8px 0">Aucune facture pour le moment.</p>
  <?php else: ?>
  <div style="overflow-x:auto">
  <table>
    <thead><tr><th>N° Facture</th><th>Date</th><th>Libellé</th><th style="text-align:right">Montant TTC</th><th>Statut</th></tr></thead>
    <tbody>
    <?php foreach ($factures as $f): ?>
      <tr>
        <td style="font-family:monospace;font-weight:600"><?= e($f['numero_facture']) ?></td>
        <td style="white-space:nowrap"><?= date('d/m/Y', strtotime($f['date_facture'])) ?></td>
        <td><?= e($f['libelle'] ?? '—') ?></td>
        <td style="text-align:right;font-weight:700"><?= $fmt($f['montant_ttc']) ?></td>
        <td>
          <?php if ($f['statut'] === 'payee'): ?><span class="badge b-green">Payée</span>
          <?php elseif ($f['statut'] === 'en_retard'): ?><span class="badge b-red">En retard</span>
          <?php else: ?><span class="badge b-amber">En attente</span><?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Depot de pieces -->
<?php if ($c['permet_depot']): ?>
<div class="pc-card">
  <h2>📁 Transmettre une pièce</h2>
  <p class="sub">Envoyez vos justificatifs (factures, relevés...) directement à votre cabinet.</p>
  <form method="post" action="<?= APP_URL ?>/portail/deposer" enctype="multipart/form-data" class="pc-depot-form" style="display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end">
    <?= csrfField() ?>
    <div>
      <label>Document (PDF, JPG, PNG)</label>
      <input type="file" name="fichier" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
    </div>
    <div>
      <label>Description (optionnel)</label>
      <input type="text" name="libelle" placeholder="Ex: Facture fournisseur mars">
    </div>
    <button type="submit" class="btn btn-green">Envoyer</button>
  </form>

  <?php if (!empty($depots)): ?>
  <div style="margin-top:22px;overflow-x:auto">
    <table>
      <thead><tr><th>Date</th><th>Document</th><th>Description</th><th>Statut</th></tr></thead>
      <tbody>
      <?php foreach ($depots as $d): ?>
        <tr>
          <td style="white-space:nowrap"><?= date('d/m/Y', strtotime($d['created_at'])) ?></td>
          <td><?= e($d['nom_original']) ?></td>
          <td style="color:var(--muted)"><?= e($d['libelle'] ?? '—') ?></td>
          <td>
            <?php if ($d['statut'] === 'traite'): ?><span class="badge b-green">Traité</span>
            <?php elseif ($d['statut'] === 'rejete'): ?><span class="badge b-red">Rejeté</span>
            <?php else: ?><span class="badge b-navy">Reçu</span><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>
