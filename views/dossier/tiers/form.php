<?php
// Type pré-sélectionné : depuis l'écriture existante, ou depuis ?type=, ou fournisseur par défaut
$typeDefaut = $tiers['type'] ?? ($_GET['type'] ?? 'fournisseur');
$isEdit     = !empty($tiers);

$svgTruck   = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:28px;height:28px"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>';
$svgUsers   = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:28px;height:28px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>';
$svgSwitch  = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:28px;height:28px"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>';
$typeConfig = [
    'fournisseur' => ['icon'=>$svgTruck,  'label'=>'Fournisseur', 'desc'=>'Achats, charges',       'bg'=>'rgba(245,158,11,.12)', 'col'=>'#d97706'],
    'client'      => ['icon'=>$svgUsers,  'label'=>'Client',      'desc'=>'Ventes, recettes',       'bg'=>'rgba(31,110,78,.12)', 'col'=>'#2563eb'],
    'les_deux'    => ['icon'=>$svgSwitch, 'label'=>'Les deux',    'desc'=>'Fournisseur & client',   'bg'=>'rgba(139,92,246,.12)', 'col'=>'#b8923f'],
];

$tc      = $typeConfig[$typeDefaut] ?? $typeConfig['fournisseur'];
$retour  = APP_URL . '/dossier/tiers?id=' . $entreprise['id'] . '&type=' . $typeDefaut;
$titre   = $isEdit ? 'Modifier ' . $tc['label'] : 'Nouveau ' . $tc['label'];
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= $titre ?></h1>
        <p class="page-subtitle"><?= e($entreprise['raison_sociale']) ?></p>
    </div>
    <a href="<?= $retour ?>" class="btn btn-outline">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Retour
    </a>
</div>

<div class="card" style="max-width:700px">
    <form method="POST" action="<?= APP_URL ?>/dossier/tiers/store">
        <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">
        <input type="hidden" name="tiers_id"      value="<?= $tiers['id'] ?? 0 ?>">

        <?php if ($isEdit): ?>
        <!-- Édition : on peut changer le type -->
        <div style="margin-bottom:24px">
            <label style="display:block;font-size:15px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px">Type de tiers <span style="color:var(--danger)">*</span></label>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">
                <?php foreach ($typeConfig as $val => $cfg): ?>
                <label style="cursor:pointer">
                    <input type="radio" name="type" value="<?= $val ?>" <?= $typeDefaut === $val ? 'checked' : '' ?> style="display:none" class="type-radio">
                    <div class="type-card" data-val="<?= $val ?>" data-col="<?= $cfg['col'] ?>" data-bg="<?= $cfg['bg'] ?>"
                         style="border:2px solid <?= $typeDefaut===$val ? $cfg['col'] : 'var(--border)' ?>;background:<?= $typeDefaut===$val ? $cfg['bg'] : '' ?>;border-radius:12px;padding:16px;text-align:center;transition:all .15s">
                        <div style="display:flex;justify-content:center;margin-bottom:6px"><?= $cfg['icon'] ?></div>
                        <div style="font-size:17px;font-weight:700;color:var(--navy-dark)"><?= $cfg['label'] ?></div>
                        <div style="font-size:15px;color:var(--text-muted);margin-top:2px"><?= $cfg['desc'] ?></div>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <!-- Création : type fixé, affiché en badge, pas de choix -->
        <input type="hidden" name="type" value="<?= $typeDefaut ?>">
        <div style="margin-bottom:20px">
            <div style="display:inline-flex;align-items:center;gap:10px;padding:12px 20px;border-radius:12px;border:2px solid <?= $tc['col'] ?>;background:<?= $tc['bg'] ?>">
                <span style="display:flex;align-items:center"><?= $tc['icon'] ?></span>
                <div>
                    <div style="font-size:18px;font-weight:700;color:var(--navy-dark)"><?= $tc['label'] ?></div>
                    <div style="font-size:15px;color:var(--text-muted)"><?= $tc['desc'] ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Champs -->
        <div class="form-grid" style="margin-bottom:16px">
            <div class="form-field" style="grid-column:1/-1">
                <label>Nom / Raison sociale <span style="color:var(--danger)">*</span></label>
                <input type="text" name="nom" value="<?= e($tiers['nom'] ?? '') ?>"
                       placeholder="<?= $typeDefaut === 'client' ? 'Ex: BuildSen Construction, Dakar Invest…' : 'Ex: ETIM, Total Sénégal…' ?>" required>
            </div>
            <div class="form-field">
                <label>NINEA</label>
                <input type="text" name="ninea" value="<?= e($tiers['ninea'] ?? '') ?>" placeholder="Ex: 12345678901">
            </div>
            <div class="form-field">
                <label>Téléphone</label>
                <input type="text" name="telephone" value="<?= e($tiers['telephone'] ?? '') ?>" placeholder="Ex: +221 77 000 00 00">
            </div>
            <div class="form-field">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($tiers['email'] ?? '') ?>" placeholder="contact@societe.sn">
            </div>
            <div class="form-field">
                <label>Adresse</label>
                <input type="text" name="adresse" value="<?= e($tiers['adresse'] ?? '') ?>" placeholder="Rue, Quartier, Ville">
            </div>
        </div>

        <div style="display:flex;gap:10px;padding-top:12px;border-top:1px solid var(--border)">
            <button type="submit" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le ' . strtolower($tc['label']) ?>
            </button>
            <a href="<?= $retour ?>" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('.type-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.type-card').forEach(card => {
            const active = card.dataset.val === this.value;
            card.style.borderColor = active ? card.dataset.col : 'var(--border)';
            card.style.background  = active ? card.dataset.bg  : '';
        });
    });
});
</script>
