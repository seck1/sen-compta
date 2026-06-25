<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Portail Client</h1>
        <p class="page-subtitle">Donnez à votre client un accès sécurisé à son dossier · <?= e($entreprise['raison_sociale']) ?></p>
    </div>
</div>

<?php if ($message === 'cree'): ?>
<div class="badge-success" style="display:block;padding:12px 16px;border-radius:10px;margin-bottom:16px">✓ Accès client créé. Communiquez-lui l'email et le mot de passe.</div>
<?php elseif ($message === 'maj'): ?>
<div class="badge-success" style="display:block;padding:12px 16px;border-radius:10px;margin-bottom:16px">✓ Modifications enregistrées.</div>
<?php elseif ($message === 'depot'): ?>
<div class="badge-success" style="display:block;padding:12px 16px;border-radius:10px;margin-bottom:16px">✓ Pièce mise à jour.</div>
<?php endif; ?>
<?php if ($error === 'email'): ?>
<div style="background:#fdecec;border:1px solid #f5c2c2;color:#c0392b;padding:12px 16px;border-radius:10px;margin-bottom:16px">Cet email est déjà utilisé.</div>
<?php elseif ($error === 'champs'): ?>
<div style="background:#fdecec;border:1px solid #f5c2c2;color:#c0392b;padding:12px 16px;border-radius:10px;margin-bottom:16px">Vérifiez les champs (mot de passe ≥ 8 caractères, email valide).</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start">

  <!-- Liste des accès client -->
  <div>
    <div class="table-wrap">
      <div class="table-header"><div class="table-title">Accès clients</div></div>
      <?php if (empty($clients)): ?>
        <div class="empty-state"><h3>Aucun accès client</h3><p>Créez un accès pour que votre client consulte son dossier.</p></div>
      <?php else: ?>
      <table>
        <thead><tr><th>Client</th><th>Email</th><th>Partage</th><th>Dernière connexion</th><th>Statut</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($clients as $c): ?>
          <tr>
            <td style="font-weight:600"><?= e($c['nom']) ?></td>
            <td><?= e($c['email']) ?></td>
            <td style="font-size:9pt">
              <?= $c['voir_etats'] ? '📊 États ' : '' ?>
              <?= $c['voir_honoraires'] ? '🧾 Factures ' : '' ?>
              <?= $c['permet_depot'] ? '📁 Dépôt' : '' ?>
            </td>
            <td><?= $c['derniere_connexion'] ? date('d/m/Y H:i', strtotime($c['derniere_connexion'])) : '—' ?></td>
            <td>
              <span class="badge <?= $c['actif'] ? 'badge-success' : 'badge-navy' ?>"><?= $c['actif'] ? 'Actif' : 'Suspendu' ?></span>
            </td>
            <td style="white-space:nowrap">
              <form method="post" action="<?= APP_URL ?>/dossier/portail/update" style="display:inline">
                <?= csrfField() ?>
                <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
                <input type="hidden" name="client_id" value="<?= $c['id'] ?>">
                <input type="hidden" name="action" value="toggle">
                <button type="submit" class="btn btn-outline btn-sm"><?= $c['actif'] ? 'Suspendre' : 'Réactiver' ?></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

    <!-- Pièces déposées par les clients -->
    <div class="table-wrap" style="margin-top:24px">
      <div class="table-header"><div class="table-title">Pièces déposées par les clients</div></div>
      <?php if (empty($depots)): ?>
        <div class="empty-state"><h3>Aucune pièce déposée</h3></div>
      <?php else: ?>
      <table>
        <thead><tr><th>Date</th><th>Client</th><th>Document</th><th>Statut</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($depots as $d): ?>
          <tr>
            <td style="white-space:nowrap"><?= date('d/m/Y', strtotime($d['created_at'])) ?></td>
            <td><?= e($d['client_nom']) ?></td>
            <td>
              <a href="<?= APP_URL ?>/uploads/portail/<?= e($d['fichier']) ?>" target="_blank" style="color:var(--green);font-weight:600;text-decoration:none"><?= e($d['nom_original']) ?></a>
              <?php if ($d['libelle']): ?><div style="font-size:9pt;color:var(--text-muted)"><?= e($d['libelle']) ?></div><?php endif; ?>
            </td>
            <td>
              <?php $bc = ['nouveau'=>'badge-warning','traite'=>'badge-success','rejete'=>'badge-danger'][$d['statut']]; ?>
              <span class="badge <?= $bc ?>"><?= ucfirst($d['statut']) ?></span>
            </td>
            <td style="white-space:nowrap">
              <?php if ($d['statut'] === 'nouveau'): ?>
              <form method="post" action="<?= APP_URL ?>/dossier/portail/depot" style="display:inline">
                <?= csrfField() ?>
                <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
                <input type="hidden" name="depot_id" value="<?= $d['id'] ?>">
                <button type="submit" name="statut" value="traite" class="btn btn-outline btn-sm">✓ Traité</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Formulaire de création d'accès -->
  <div class="card" style="padding:24px">
    <h3 style="font-size:13pt;font-weight:700;color:var(--navy);margin-bottom:6px">Nouvel accès client</h3>
    <p style="font-size:10pt;color:var(--text-muted);margin-bottom:18px">Le client pourra consulter son dossier en lecture seule.</p>
    <form method="post" action="<?= APP_URL ?>/dossier/portail/creer">
      <?= csrfField() ?>
      <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
      <div class="form-field" style="margin-bottom:14px">
        <label>Nom du contact *</label>
        <input type="text" name="nom" placeholder="Ex: Aïssatou Diop" required>
      </div>
      <div class="form-field" style="margin-bottom:14px">
        <label>Email *</label>
        <input type="email" name="email" placeholder="client@entreprise.sn" required>
      </div>
      <div class="form-field" style="margin-bottom:14px">
        <label>Téléphone</label>
        <input type="tel" name="telephone" placeholder="77 000 00 00">
      </div>
      <div class="form-field" style="margin-bottom:16px">
        <label>Mot de passe *</label>
        <input type="password" name="password" placeholder="Min. 8 caractères" required>
      </div>
      <div style="border-top:1px solid var(--border);padding-top:14px;margin-bottom:16px">
        <div style="font-size:10pt;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px">Ce que le client peut voir</div>
        <label style="display:flex;align-items:center;gap:9px;margin-bottom:9px;cursor:pointer"><input type="checkbox" name="voir_etats" checked style="accent-color:var(--green)"> <span>États financiers (bilan, résultat)</span></label>
        <label style="display:flex;align-items:center;gap:9px;margin-bottom:9px;cursor:pointer"><input type="checkbox" name="voir_honoraires" checked style="accent-color:var(--green)"> <span>Factures d'honoraires</span></label>
        <label style="display:flex;align-items:center;gap:9px;cursor:pointer"><input type="checkbox" name="permet_depot" checked style="accent-color:var(--green)"> <span>Dépôt de pièces</span></label>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Créer l'accès</button>
    </form>
  </div>

</div>

<style>
@media(max-width:980px){ .page-header + div, div[style*="grid-template-columns:1fr 380px"]{ grid-template-columns:1fr !important; } }
</style>
