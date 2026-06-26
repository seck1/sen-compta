<?php
/**
 * Vue super-admin : Utilisateurs en ligne
 * Supervision temps réel des sessions actives sur la plateforme SenCompta.
 *
 * Variables fournies par le contrôleur :
 *   $users    array  liste des sessions (voir spec)
 *   $stats    array  compteurs et pourcentages
 *   $periode  string '24h'|'7j'|'30j'|'tout'
 *   $search   string recherche en cours
 */

// Redéfinis (idempotent) — le contrôleur les a déjà posés, mais on sécurise.
$activePage = 'superadmin-online';
$pageTitle  = 'Utilisateurs en ligne';

// Sécurisation des variables (au cas où elles seraient absentes en dev)
$users   = $users   ?? [];
$stats   = $stats   ?? ['online'=>0,'idle'=>0,'offline'=>0,'total'=>0,'pct_online'=>0,'pct_idle'=>0,'pct_offline'=>0];
$periode = $periode ?? '24h';
$search  = $search  ?? ($_GET['q'] ?? '');

// Libellés des statuts
$statutLabels = ['online'=>'En ligne', 'idle'=>'Inactif', 'offline'=>'Hors ligne'];

// Palette d'avatars déterministe (5 teintes sobres)
$avatarPalette = ['#1e3a5f', '#1f6e4e', '#b8923f', '#475569', '#18583f'];

ob_start();
?>
<!-- Fraunces pour les gros chiffres / titres (acceptable selon spec) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet">

<style>
/* ===== Charte SenCompta scoped ===== */
.uo {
  --uo-green:   #1f6e4e;
  --uo-green-d: #18583f;
  --uo-navy:    #1e3a5f;
  --uo-gold:    #b8923f;
  --uo-red:     #c0392b;
  --uo-amber:   #d97706;
  --uo-slate:   #64748b;
  --uo-bg-card: #ffffff;
  --uo-border:  #e3e7e6;
  --uo-muted:   #6b7672;
  --uo-shadow:  0 1px 3px rgba(30,58,95,.06), 0 8px 24px rgba(30,58,95,.05);
  color: #18241f;
}
.uo .uo-serif { font-family: 'Fraunces', Georgia, serif; }

/* ---- En-tête ---- */
.uo-head { display:flex; align-items:flex-start; justify-content:space-between; gap:24px; margin-bottom:28px; flex-wrap:wrap; }
.uo-title { font-family:'Fraunces',Georgia,serif; font-size:32px; font-weight:600; color:var(--uo-navy); line-height:1.1; margin:0 0 8px; letter-spacing:-.01em; }
.uo-sub { font-size:13.5px; color:var(--uo-muted); max-width:560px; line-height:1.55; }
.uo-head-actions { display:flex; gap:10px; align-items:center; }
.uo-btn { display:inline-flex; align-items:center; gap:7px; padding:10px 16px; border-radius:10px; font-size:13px; font-weight:600; text-decoration:none; cursor:pointer; border:1px solid var(--uo-border); background:#fff; color:var(--uo-navy); transition:.15s; white-space:nowrap; }
.uo-btn:hover { border-color:#c8cfcd; box-shadow:0 2px 8px rgba(30,58,95,.07); }
.uo-btn-primary { background:var(--uo-navy); color:#fff; border-color:var(--uo-navy); }
.uo-btn-primary:hover { background:#18305a; }
.uo-dot-i { width:7px; height:7px; border-radius:50%; background:currentColor; display:inline-block; }

/* ---- Cartes de stats ---- */
.uo-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:18px; margin-bottom:28px; }
.uo-card { background:var(--uo-bg-card); border:1px solid var(--uo-border); border-radius:16px; padding:22px 22px 20px; box-shadow:var(--uo-shadow); display:flex; flex-direction:column; }
.uo-card-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
.uo-card-label { font-size:12.5px; font-weight:600; color:var(--uo-navy); }
.uo-card-hint { font-size:11px; color:var(--uo-muted); font-weight:500; }
.uo-card-pill { width:9px; height:9px; border-radius:50%; }
.uo-card-num { font-family:'Fraunces',Georgia,serif; font-size:40px; font-weight:600; line-height:1; color:var(--uo-navy); letter-spacing:-.02em; }
.uo-card-cap { font-size:11.5px; color:var(--uo-muted); margin-top:6px; }
.uo-bar { height:6px; border-radius:6px; background:#eef1f0; margin-top:16px; overflow:hidden; }
.uo-bar > span { display:block; height:100%; border-radius:6px; }

/* ---- Barre de filtres ---- */
.uo-filters { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:18px; flex-wrap:wrap; }
.uo-tabs { display:inline-flex; background:#fff; border:1px solid var(--uo-border); border-radius:11px; padding:4px; gap:2px; }
.uo-tab { padding:7px 15px; border-radius:8px; font-size:12.5px; font-weight:600; color:var(--uo-muted); text-decoration:none; transition:.15s; }
.uo-tab:hover { color:var(--uo-navy); }
.uo-tab.is-active { background:var(--uo-navy); color:#fff; }
.uo-searchwrap { flex:1 1 280px; min-width:220px; max-width:420px; position:relative; }
.uo-search { width:100%; padding:10px 14px 10px 38px; border:1px solid var(--uo-border); border-radius:11px; font-family:inherit; font-size:13px; background:#fff; color:#18241f; outline:none; transition:.15s; }
.uo-search:focus { border-color:var(--uo-green); box-shadow:0 0 0 3px rgba(31,110,78,.10); }
.uo-search-ic { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:var(--uo-muted); pointer-events:none; }
.uo-counts { display:flex; gap:7px; flex-wrap:wrap; }
.uo-count { display:inline-flex; align-items:center; gap:6px; font-size:11.5px; font-weight:600; color:var(--uo-navy); background:#fff; border:1px solid var(--uo-border); border-radius:20px; padding:5px 11px; }
.uo-count .d { width:7px; height:7px; border-radius:50%; }

/* ---- Tableau ---- */
.uo-panel { background:#fff; border:1px solid var(--uo-border); border-radius:16px; box-shadow:var(--uo-shadow); overflow:hidden; }
.uo-panel-head { display:flex; align-items:center; justify-content:space-between; padding:18px 22px; border-bottom:1px solid var(--uo-border); }
.uo-panel-title { display:flex; align-items:center; gap:10px; font-size:15px; font-weight:700; color:var(--uo-navy); }
.uo-badge { font-size:11.5px; font-weight:700; color:var(--uo-green); background:var(--uo-green); background:rgba(31,110,78,.10); border-radius:20px; padding:3px 10px; }
.uo-refresh { font-size:11.5px; color:var(--uo-muted); display:flex; align-items:center; gap:7px; }
.uo-refresh .pulse { width:7px; height:7px; border-radius:50%; background:var(--uo-green); display:inline-block; animation:uo-pulse 1.8s infinite; }
@keyframes uo-pulse { 0%{box-shadow:0 0 0 0 rgba(31,110,78,.5);} 70%{box-shadow:0 0 0 6px rgba(31,110,78,0);} 100%{box-shadow:0 0 0 0 rgba(31,110,78,0);} }

.uo-tablewrap { overflow-x:auto; }
.uo-table { width:100%; border-collapse:collapse; min-width:880px; }
.uo-table thead th { text-align:left; font-size:10.5px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; color:var(--uo-muted); padding:12px 18px; border-bottom:1px solid var(--uo-border); white-space:nowrap; }
.uo-table tbody td { padding:14px 18px; border-bottom:1px solid #f0f2f1; vertical-align:middle; font-size:13px; }
.uo-table tbody tr:last-child td { border-bottom:none; }
.uo-table tbody tr { transition:background .12s; }
.uo-table tbody tr:hover { background:#fafbfb; }
.uo-row-online td:first-child { box-shadow:inset 3px 0 0 var(--uo-green); }

.uo-user { display:flex; align-items:center; gap:11px; }
.uo-av { width:38px; height:38px; border-radius:10px; flex:none; display:flex; align-items:center; justify-content:center; color:#fff; font-size:13px; font-weight:700; letter-spacing:.02em; }
.uo-uname { font-weight:700; color:var(--uo-navy); font-size:13.5px; line-height:1.2; }
.uo-uid { font-size:11px; color:var(--uo-muted); margin-top:2px; }

.uo-status { display:inline-flex; align-items:center; gap:7px; font-size:12px; font-weight:600; padding:5px 11px; border-radius:20px; white-space:nowrap; }
.uo-status .d { width:8px; height:8px; border-radius:50%; }
.uo-st-online  { color:var(--uo-green); background:rgba(31,110,78,.10); }
.uo-st-idle    { color:var(--uo-amber); background:rgba(217,119,6,.10); }
.uo-st-offline { color:var(--uo-slate); background:rgba(100,116,139,.12); }

.uo-email { color:#374b43; font-size:12.5px; }
.uo-cabinet { color:#374b43; font-size:12.5px; }

/* Sparkline pure CSS */
.uo-spark { display:flex; align-items:flex-end; gap:2px; height:28px; }
.uo-spark span { width:4px; border-radius:2px; background:var(--uo-green); opacity:.85; min-height:2px; }
.uo-spark-cap { font-size:10.5px; color:var(--uo-muted); margin-top:5px; }

.uo-actions-num { font-family:'Fraunces',Georgia,serif; font-size:22px; font-weight:600; color:var(--uo-navy); line-height:1; }
.uo-actions-cap { font-size:10.5px; color:var(--uo-muted); margin-top:3px; }

.uo-seen { font-weight:700; color:var(--uo-navy); font-size:13px; }
.uo-seen-sub { font-size:11px; color:var(--uo-muted); margin-top:2px; }

.uo-empty { text-align:center; color:var(--uo-muted); font-size:13.5px; padding:48px 20px; }

/* ---- Responsive ---- */
@media (max-width:1100px){ .uo-stats { grid-template-columns:repeat(2,1fr); } }
@media (max-width:640px){
  .uo-stats { grid-template-columns:1fr; }
  .uo-title { font-size:26px; }
  .uo-head { flex-direction:column; }
  .uo-filters { flex-direction:column; align-items:stretch; }
  .uo-searchwrap { max-width:none; }
}
</style>

<?php
// Suffixe pour conserver la recherche dans les liens de période
$qSuffix = $search !== '' ? '&q=' . urlencode($search) : '';
?>
<div class="uo">

  <!-- A. En-tête -->
  <div class="uo-head">
    <div>
      <h1 class="uo-title">Activité utilisateurs</h1>
      <p class="uo-sub">Vue temps réel des sessions actives sur la plateforme. Surveillance de l'engagement, détection des inactifs.</p>
    </div>
    <div class="uo-head-actions">
      <a href="#" onclick="window.location.reload();return false;" class="uo-btn">
        <span class="uo-dot-i" style="color:var(--uo-green);"></span> Actualiser
      </a>
      <a href="<?= APP_URL ?>/superadmin/online?p=<?= e($periode) ?>&export=csv" class="uo-btn uo-btn-primary">Exporter</a>
    </div>
  </div>

  <!-- B. Cartes de stats -->
  <div class="uo-stats">
    <?php
    $cards = [
      ['label'=>'En ligne',   'hint'=>'≤ 5 min',    'num'=>$stats['online'],  'cap'=>'Sessions actives',  'pct'=>$stats['pct_online'],  'color'=>'var(--uo-green)',                                'pill'=>'var(--uo-green)'],
      ['label'=>'Inactifs',   'hint'=>'5–15 min',   'num'=>$stats['idle'],    'cap'=>'En pause',          'pct'=>$stats['pct_idle'],    'color'=>'var(--uo-amber)',                                'pill'=>'var(--uo-amber)'],
      ['label'=>'Hors-ligne', 'hint'=>'> 15 min',   'num'=>$stats['offline'], 'cap'=>'Sessions fermées',  'pct'=>$stats['pct_offline'], 'color'=>'var(--uo-slate)',                                'pill'=>'var(--uo-slate)'],
      ['label'=>'Total',      'hint'=>'période',     'num'=>$stats['total'],   'cap'=>'Utilisateurs uniques','pct'=>100,                 'color'=>'linear-gradient(90deg,var(--uo-navy),var(--uo-gold))','pill'=>'var(--uo-navy)'],
    ];
    foreach ($cards as $c): ?>
    <div class="uo-card">
      <div class="uo-card-top">
        <div>
          <div class="uo-card-label"><?= e($c['label']) ?></div>
          <div class="uo-card-hint"><?= e($c['hint']) ?></div>
        </div>
        <span class="uo-card-pill" style="background:<?= $c['pill'] ?>;"></span>
      </div>
      <div class="uo-card-num"><?= (int)$c['num'] ?></div>
      <div class="uo-card-cap"><?= e($c['cap']) ?></div>
      <div class="uo-bar"><span style="width:<?= max(0,min(100,(int)$c['pct'])) ?>%;background:<?= $c['color'] ?>;"></span></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- C. Barre de filtres -->
  <div class="uo-filters">
    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
      <!-- Onglets période -->
      <div class="uo-tabs">
        <?php
        $tabs = ['24h'=>'24h', '7j'=>'7 jours', '30j'=>'30 jours', 'tout'=>'Tout'];
        foreach ($tabs as $key=>$lbl): ?>
        <a class="uo-tab<?= $periode === $key ? ' is-active' : '' ?>" href="?p=<?= e($key) . $qSuffix ?>"><?= e($lbl) ?></a>
        <?php endforeach; ?>
      </div>

      <!-- Recherche -->
      <form method="GET" action="<?= APP_URL ?>/superadmin/online" class="uo-searchwrap">
        <input type="hidden" name="p" value="<?= e($periode) ?>">
        <svg class="uo-search-ic" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        <input class="uo-search" type="text" name="q" value="<?= e($search) ?>" placeholder="Rechercher un utilisateur, email, cabinet…">
      </form>
    </div>

    <!-- Pastilles de comptage -->
    <div class="uo-counts">
      <span class="uo-count">Tous <?= (int)$stats['total'] ?></span>
      <span class="uo-count"><span class="d" style="background:var(--uo-green);"></span> Online <?= (int)$stats['online'] ?></span>
      <span class="uo-count"><span class="d" style="background:var(--uo-amber);"></span> Idle <?= (int)$stats['idle'] ?></span>
      <span class="uo-count"><span class="d" style="background:var(--uo-slate);"></span> Offline <?= (int)$stats['offline'] ?></span>
    </div>
  </div>

  <!-- D. Tableau des sessions -->
  <div class="uo-panel">
    <div class="uo-panel-head">
      <div class="uo-panel-title">Sessions <span class="uo-badge"><?= (int)$stats['total'] ?></span></div>
      <div class="uo-refresh"><span class="pulse"></span> Mise à jour dans <span id="uo-countdown">30</span>s</div>
    </div>

    <div class="uo-tablewrap">
      <table class="uo-table">
        <thead>
          <tr>
            <th>Utilisateur</th>
            <th>Statut</th>
            <th>Email</th>
            <th>Cabinet / Entreprise</th>
            <th>Activité 14j</th>
            <th>Actions</th>
            <th>Dernière vue</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($users)): ?>
          <tr><td colspan="7" class="uo-empty">Aucune session trouvée.</td></tr>
          <?php else: foreach ($users as $u):
            // --- Avatar : initiales + couleur déterministe ---
            $nom   = trim($u['nom'] ?? '');
            $parts = preg_split('/\s+/', $nom);
            if (count($parts) >= 2) {
              $initials = mb_strtoupper(mb_substr($parts[0],0,1) . mb_substr($parts[count($parts)-1],0,1));
            } else {
              $initials = mb_strtoupper(mb_substr($nom !== '' ? $nom : '?', 0, 2));
            }
            $avColor = $avatarPalette[((int)($u['id'] ?? 0)) % count($avatarPalette)];

            // --- Statut ---
            $st       = $u['statut'] ?? 'offline';
            $stClass  = $st === 'online' ? 'uo-st-online' : ($st === 'idle' ? 'uo-st-idle' : 'uo-st-offline');
            $stColor  = $st === 'online' ? 'var(--uo-green)' : ($st === 'idle' ? 'var(--uo-amber)' : 'var(--uo-slate)');
            $stLabel  = $statutLabels[$st] ?? 'Hors ligne';
            $rowClass = $st === 'online' ? 'uo-row-online' : '';

            // --- Sparkline ---
            $spark    = is_array($u['spark'] ?? null) ? $u['spark'] : [];
            $sparkMax = !empty($spark) ? max($spark) : 0;
            $sparkSum = array_sum($spark);

            // --- Dernière vue ---
            $lastSeen  = $u['last_seen'] ?? null;
            $lastHuman = $u['last_human'] ?? 'jamais';
          ?>
          <tr class="<?= $rowClass ?>">
            <!-- Utilisateur -->
            <td>
              <div class="uo-user">
                <div class="uo-av" style="background:<?= $avColor ?>;"><?= e($initials) ?></div>
                <div>
                  <div class="uo-uname"><?= e($nom !== '' ? $nom : 'Sans nom') ?></div>
                  <div class="uo-uid">#<?= (int)($u['id'] ?? 0) ?> · <?= e($u['role'] ?? '—') ?></div>
                </div>
              </div>
            </td>
            <!-- Statut -->
            <td>
              <span class="uo-status <?= $stClass ?>"><span class="d" style="background:<?= $stColor ?>;"></span><?= e($stLabel) ?></span>
            </td>
            <!-- Email -->
            <td class="uo-email"><?= e($u['email'] ?? '—') ?></td>
            <!-- Cabinet -->
            <td class="uo-cabinet"><?= e($u['cabinet'] ?? '—') ?></td>
            <!-- Sparkline 14j -->
            <td>
              <div class="uo-spark">
                <?php foreach ($spark as $v):
                  $h = $sparkMax > 0 ? max(2, round(($v / $sparkMax) * 28)) : 2;
                ?>
                <span style="height:<?= (int)$h ?>px;"></span>
                <?php endforeach; ?>
              </div>
              <div class="uo-spark-cap"><?= (int)$sparkSum ?> actions / 14j</div>
            </td>
            <!-- Actions -->
            <td>
              <div class="uo-actions-num"><?= number_format((int)($u['nb_actions'] ?? 0), 0, ',', ' ') ?></div>
              <div class="uo-actions-cap">sur la période</div>
            </td>
            <!-- Dernière vue -->
            <td>
              <div class="uo-seen"><?= e($lastHuman) ?></div>
              <?php if (!empty($lastSeen)): ?>
              <div class="uo-seen-sub"><?= e(date('d M, H:i', strtotime($lastSeen))) ?></div>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- E. Auto-refresh + compte à rebours -->
<script>
(function(){
  var n = 30;
  var el = document.getElementById('uo-countdown');
  var t = setInterval(function(){
    n--;
    if (el) el.textContent = n < 0 ? 0 : n;
    if (n <= 0) { clearInterval(t); window.location.reload(); }
  }, 1000);
})();
</script>

<?php
$content = ob_get_clean();
require APP_ROOT . '/views/layouts/main.php';
