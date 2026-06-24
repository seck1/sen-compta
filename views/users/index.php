<?php
$roleColors = [
    'admin'        => ['bg'=>'#1e3a5f', 'text'=>'white',   'label'=>'Administrateur'],
    'superviseur'  => ['bg'=>'#7c3aed', 'text'=>'white',   'label'=>'Superviseur'],
    'collaborateur'=> ['bg'=>'#0891b2', 'text'=>'white',   'label'=>'Collaborateur'],
];
$avatarGradients = [
    ['#1e3a5f','#2a4f7c'],
    ['#7c3aed','#a855f7'],
    ['#0891b2','#22d3ee'],
    ['#c9a96e','#a8843f'],
    ['#059669','#34d399'],
];
?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Collaborateurs</h1>
        <p class="page-subtitle"><?= count($users) ?> membre<?= count($users) > 1 ? 's' : '' ?> de l'équipe</p>
    </div>
    <a href="<?= APP_URL ?>/users/create" class="btn btn-gold">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Nouveau collaborateur
    </a>
</div>

<?php if (empty($users)): ?>
<div class="card" style="text-align:center;padding:60px 20px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width:48px;height:48px;margin:0 auto 16px;color:var(--text-muted)"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
    <h3 style="font-size:16px;color:var(--navy-dark);margin-bottom:8px">Aucun collaborateur</h3>
    <p style="font-size:14px;color:var(--text-muted)">Créez des comptes pour les membres de votre équipe</p>
</div>

<?php else: ?>

<!-- Cards grille -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px">

<?php foreach ($users as $i => $u):
    $grad = $avatarGradients[$i % count($avatarGradients)];
    $rc   = $roleColors[$u['role']] ?? ['bg'=>'#64748b','text'=>'white','label'=>ucfirst($u['role'])];
    $initials = strtoupper(substr($u['prenom'],0,1).substr($u['nom'],0,1));
    $connDate = $u['derniere_connexion'] ? date('d/m/Y à H:i', strtotime($u['derniere_connexion'])) : null;
    $joursConnexion = $u['derniere_connexion'] ? floor((time() - strtotime($u['derniere_connexion'])) / 86400) : null;
?>
<div style="background:white;border-radius:16px;border:1px solid var(--border);overflow:hidden;transition:box-shadow .2s,transform .2s;position:relative"
     onmouseover="this.style.boxShadow='0 8px 32px rgba(30,58,95,0.12)';this.style.transform='translateY(-2px)'"
     onmouseout="this.style.boxShadow='none';this.style.transform='none'">

    <!-- Bandeau haut coloré -->
    <div style="height:6px;background:linear-gradient(90deg,<?= $grad[0] ?>,<?= $grad[1] ?>)"></div>

    <!-- Corps de la card -->
    <div style="padding:20px 22px">

        <!-- Header : avatar + nom + badge rôle -->
        <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:18px">
            <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,<?= $grad[0] ?>,<?= $grad[1] ?>);display:flex;align-items:center;justify-content:center;font-size:17px;font-weight:700;color:white;flex-shrink:0;letter-spacing:.5px">
                <?= $initials ?>
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:15px;font-weight:700;color:var(--navy-dark);margin-bottom:3px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <?= e($u['prenom'] . ' ' . $u['nom']) ?>
                </div>
                <div style="font-size:12.5px;color:var(--text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <?= e($u['email']) ?>
                </div>
            </div>
            <!-- Statut actif/inactif -->
            <div style="display:flex;align-items:center;gap:5px;flex-shrink:0;font-size:12px;font-weight:600;color:<?= $u['actif'] ? '#16a34a' : '#dc2626' ?>">
                <span style="width:7px;height:7px;border-radius:50%;background:currentColor;display:inline-block"></span>
                <?= $u['actif'] ? 'Actif' : 'Inactif' ?>
            </div>
        </div>

        <!-- Badges rôle + dossiers -->
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;flex-wrap:wrap">
            <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:20px;font-size:11.5px;font-weight:700;background:<?= $rc['bg'] ?>;color:<?= $rc['text'] ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                <?= $rc['label'] ?>
            </span>
            <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:20px;font-size:11.5px;font-weight:600;background:rgba(30,58,95,0.07);color:var(--navy)">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:12px;height:12px"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/></svg>
                <?= $u['nb_dossiers'] ?> dossier<?= $u['nb_dossiers'] > 1 ? 's' : '' ?>
            </span>
        </div>

        <!-- Dernière connexion -->
        <div style="display:flex;align-items:center;gap:8px;padding:10px 12px;background:var(--bg);border-radius:10px;margin-bottom:16px">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px;color:var(--text-muted);flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div style="flex:1;min-width:0">
                <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.8px;font-weight:600">Dernière connexion</div>
                <?php if ($connDate): ?>
                <div style="font-size:13px;font-weight:500;color:var(--navy-dark);margin-top:1px">
                    <?= $connDate ?>
                    <?php if ($joursConnexion !== null): ?>
                    <span style="font-size:11px;color:<?= $joursConnexion > 7 ? '#dc2626' : '#16a34a' ?>;font-weight:400;margin-left:5px">
                        (il y a <?= $joursConnexion === 0 ? "aujourd'hui" : $joursConnexion . 'j' ?>)
                    </span>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div style="font-size:13px;color:#dc2626;font-weight:500;margin-top:1px">Jamais connecté</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div style="display:flex;gap:8px">
            <a href="<?= APP_URL ?>/users/edit?id=<?= $u['id'] ?>"
               style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:9px 14px;background:var(--navy);color:white;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;transition:background .2s"
               onmouseover="this.style.background='var(--navy-light)'" onmouseout="this.style.background='var(--navy)'">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                Modifier
            </a>
            <form method="post" action="<?= APP_URL ?>/users/delete" onsubmit="return confirm('Désactiver ce compte ?')" style="display:contents">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button type="submit"
                   style="display:flex;align-items:center;justify-content:center;padding:9px 12px;background:rgba(220,38,38,0.07);color:#dc2626;border-radius:10px;border:none;cursor:pointer;transition:background .2s"
                   onmouseover="this.style.background='rgba(220,38,38,0.15)'" onmouseout="this.style.background='rgba(220,38,38,0.07)'"
                   title="Désactiver">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<?php endif; ?>
