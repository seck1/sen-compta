<?php
$activePage = 'mon-compte';
$pageTitle  = 'Mon espace cabinet';
ob_start();
?>
<style>
.mc-wrap { padding:30px; max-width:900px; }
.mc-header h1 { font-size:1.4rem; font-weight:700; color:#1e3a5f; margin:0 0 24px; }
.mc-cards { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:30px; }
.mc-card { background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 4px rgba(0,0,0,.06); }
.mc-card h3 { font-size:.85rem; text-transform:uppercase; color:#6b7280; font-weight:600; margin:0 0 12px; }
.mc-stat { font-size:2rem; font-weight:800; color:#1e3a5f; }
.mc-stat small { font-size:.9rem; color:#9ca3af; font-weight:400; }
.plan-badge { display:inline-block; background:#1e3a5f; color:#d4af37; padding:4px 14px; border-radius:20px; font-weight:700; font-size:.85rem; }
.progress-bar { height:8px; background:#f1f3f7; border-radius:4px; margin:8px 0; overflow:hidden; }
.progress-fill { height:100%; background:linear-gradient(90deg,#1e3a5f,#d4af37); border-radius:4px; transition:.3s; }
.section-title { font-size:1rem; font-weight:700; color:#1e3a5f; margin:24px 0 12px; border-bottom:2px solid #f1f3f7; padding-bottom:8px; }
.upgrade-form { background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 4px rgba(0,0,0,.06); }
.plan-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; margin-bottom:16px; }
.plan-card { border:2px solid #e5e7eb; border-radius:10px; padding:16px; cursor:pointer; transition:.2s; }
.plan-card:hover, .plan-card.selected { border-color:#1e3a5f; background:#f0f4ff; }
.plan-card h4 { margin:0 0 4px; font-size:.95rem; color:#1e3a5f; }
.plan-card .prix { font-size:1.2rem; font-weight:800; color:#d4af37; }
.plan-card input[type=radio] { display:none; }
textarea.form-ctrl { width:100%; border:1px solid #dde3ec; border-radius:8px; padding:10px; font-size:.9rem; resize:vertical; }
.btn-submit { background:#1e3a5f; color:#fff; border:none; padding:10px 24px; border-radius:8px; font-weight:600; cursor:pointer; font-size:.9rem; }
.info-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f1f3f7; font-size:.88rem; }
.info-row:last-child { border-bottom:none; }
.info-label { color:#6b7280; }
.info-val { font-weight:600; color:#1e3a5f; }
.statut-actif { color:#166534; }
.statut-essai { color:#854d0e; }
.statut-suspendu { color:#991b1b; }
</style>

<div class="mc-wrap">
    <div class="mc-header"><h1>Mon espace cabinet — <?= htmlspecialchars($cabinet['nom'] ?? '') ?></h1></div>

    <div class="mc-cards">
        <!-- Abonnement -->
        <div class="mc-card">
            <h3>Abonnement actuel</h3>
            <div style="margin-bottom:12px"><span class="plan-badge"><?= htmlspecialchars($cabinet['plan_nom']) ?></span></div>
            <div class="info-row"><span class="info-label">Statut</span><span class="info-val statut-<?= $cabinet['statut'] ?>"><?= ['actif'=>'Actif','essai'=>'Période d\'essai','suspendu'=>'Suspendu'][$cabinet['statut']] ?? $cabinet['statut'] ?></span></div>
            <div class="info-row"><span class="info-label">Prix</span><span class="info-val"><?= number_format($cabinet['prix_mois'],0,',',' ') ?> FCFA/mois</span></div>
            <?php if ($cabinet['essai_fin']): ?>
            <div class="info-row"><span class="info-label">Essai jusqu'au</span><span class="info-val"><?= date('d/m/Y', strtotime($cabinet['essai_fin'])) ?></span></div>
            <?php endif; ?>
        </div>

        <!-- Utilisation -->
        <div class="mc-card">
            <h3>Utilisation</h3>
            <div style="margin-bottom:16px">
                <div style="display:flex;justify-content:space-between;font-size:.85rem;color:#6b7280"><span>Entreprises</span><span><?= $nb_entreprises ?> / <?= $cabinet['max_entreprises'] ?></span></div>
                <div class="progress-bar"><div class="progress-fill" style="width:<?= $cabinet['max_entreprises'] > 0 ? min(100, round($nb_entreprises/$cabinet['max_entreprises']*100)) : 0 ?>%"></div></div>
            </div>
            <div>
                <div style="display:flex;justify-content:space-between;font-size:.85rem;color:#6b7280"><span>Collaborateurs</span><span><?= $nb_users ?> / <?= $cabinet['max_users'] ?></span></div>
                <div class="progress-bar"><div class="progress-fill" style="width:<?= $cabinet['max_users'] > 0 ? min(100, round($nb_users/$cabinet['max_users']*100)) : 0 ?>%"></div></div>
            </div>
        </div>
    </div>

    <!-- Infos cabinet -->
    <div class="section-title">Informations du cabinet</div>
    <div class="mc-card" style="margin-bottom:24px">
        <div class="info-row"><span class="info-label">Nom</span><span class="info-val"><?= htmlspecialchars($cabinet['nom']) ?></span></div>
        <div class="info-row"><span class="info-label">Responsable</span><span class="info-val"><?= htmlspecialchars($cabinet['responsable_nom'] ?? '') ?></span></div>
        <div class="info-row"><span class="info-label">Email</span><span class="info-val"><?= htmlspecialchars($cabinet['email']) ?></span></div>
        <div class="info-row"><span class="info-label">Téléphone</span><span class="info-val"><?= htmlspecialchars($cabinet['telephone'] ?? '—') ?></span></div>
        <div class="info-row"><span class="info-label">Inscription</span><span class="info-val"><?= date('d/m/Y', strtotime($cabinet['created_at'])) ?></span></div>
    </div>

    <!-- Upgrade -->
    <div class="section-title">Changer de formule</div>
    <div class="upgrade-form">
        <form method="POST" action="<?= APP_URL ?>/mon-compte/upgrade">
            <?= csrfField() ?>
            <div class="plan-grid">
                <?php foreach ($plans as $pl): ?>
                    <?php if ($pl['code'] === 'enterprise') continue; ?>
                    <label class="plan-card <?= $cabinet['plan_id']==$pl['id']?'selected':'' ?>">
                        <input type="radio" name="plan_id" value="<?= $pl['id'] ?>" <?= $cabinet['plan_id']==$pl['id']?'checked':'' ?>>
                        <h4><?= htmlspecialchars($pl['nom']) ?></h4>
                        <div class="prix"><?= number_format($pl['prix_mois'],0,',',' ') ?> <small style="font-size:.7rem;color:#6b7280">FCFA/mois</small></div>
                        <div style="font-size:.75rem;color:#6b7280;margin-top:4px"><?= $pl['max_entreprises'] ?> entreprises · <?= $pl['max_users'] ?> users</div>
                    </label>
                <?php endforeach; ?>
            </div>
            <div style="margin-bottom:12px">
                <label style="font-size:.85rem;color:#6b7280;display:block;margin-bottom:6px">Message (optionnel)</label>
                <textarea name="message" class="form-ctrl" rows="3" placeholder="Décrivez vos besoins..."></textarea>
            </div>
            <button type="submit" class="btn-submit">Envoyer la demande</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require APP_ROOT . '/views/layouts/main.php';
