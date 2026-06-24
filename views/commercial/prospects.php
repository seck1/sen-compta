<?php
$stageLabels = ['nouveau'=>'Nouveau','qualifie'=>'Qualifié','devis_envoye'=>'Devis envoyé','negociation'=>'Négociation','client'=>'Client','perdu'=>'Perdu'];
$stageColors = ['nouveau'=>'#94a3b8','qualifie'=>'#3b82f6','devis_envoye'=>'#f59e0b','negociation'=>'#8b5cf6','client'=>'#22c55e','perdu'=>'#ef4444'];
$stageBg     = ['nouveau'=>'#f1f5f9','qualifie'=>'#dbeafe','devis_envoye'=>'#fef3c7','negociation'=>'#ede9fe','client'=>'#dcfce7','perdu'=>'#fee2e2'];
$stageText   = ['nouveau'=>'#475569','qualifie'=>'#2563eb','devis_envoye'=>'#d97706','negociation'=>'#7c3aed','client'=>'#16a34a','perdu'=>'#dc2626'];
$viewMode = $_GET['view'] ?? 'kanban';

// Grouper par stage pour kanban
$byStage = [];
foreach ($stageLabels as $k => $v) $byStage[$k] = [];
foreach ($prospects as $p) $byStage[$p['pipeline_stage'] ?? 'nouveau'][] = $p;
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600;700&display=swap');

.pros-root { padding:28px 32px;max-width:1600px; }

/* Header */
.page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:12px; }
.page-header h1 { font-family:'Playfair Display',serif;font-size:26px;font-weight:700;color:var(--navy-dark); }
.page-header p { font-size:13px;color:var(--text-muted);margin-top:2px; }
.header-right { display:flex;gap:10px;align-items:center; }

/* Toolbar */
.toolbar { display:flex;gap:10px;margin-bottom:20px;align-items:center;flex-wrap:wrap; }
.search-box { position:relative;flex:1;max-width:260px; }
.search-box input { width:100%;padding:8px 14px 8px 36px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;background:#fff;color:var(--text);transition:border-color .2s; }
.search-box input:focus { outline:none;border-color:var(--navy); }
.search-box svg { position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none; }
.view-toggle { display:flex;background:#f1f5f9;border-radius:10px;padding:3px;gap:2px; }
.view-btn { padding:6px 14px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;border:none;background:transparent;color:var(--text-muted);display:flex;align-items:center;gap:5px;transition:all .2s; }
.view-btn.active { background:#fff;color:var(--navy-dark);box-shadow:0 1px 4px rgba(0,0,0,0.1); }

/* Stats rapides */
.stage-tabs { display:flex;gap:6px;flex-wrap:wrap;margin-bottom:20px; }
.stage-tab { display:flex;align-items:center;gap:6px;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;border:1.5px solid var(--border);background:#fff;color:var(--text-muted);transition:all .2s;cursor:pointer; }
.stage-tab:hover { border-color:#94a3b8; }
.stage-tab.active { color:#fff;border-color:transparent; }
.stage-tab .dot { width:8px;height:8px;border-radius:50%;flex-shrink:0; }

/* ═══ KANBAN ═══ */
.kanban-board { display:flex;gap:14px;overflow-x:auto;padding-bottom:16px;min-height:500px; }
.kanban-board::-webkit-scrollbar { height:6px; }
.kanban-board::-webkit-scrollbar-track { background:#f1f5f9;border-radius:3px; }
.kanban-board::-webkit-scrollbar-thumb { background:#cbd5e1;border-radius:3px; }

.kb-col { flex:0 0 240px;display:flex;flex-direction:column;gap:0; }
.kb-header { display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-radius:12px 12px 0 0;margin-bottom:0; }
.kb-header-label { font-size:12px;font-weight:700;letter-spacing:0.3px; }
.kb-count { min-width:22px;height:22px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;background:rgba(255,255,255,0.4);padding:0 6px; }
.kb-total { font-size:10px;opacity:0.75;margin-top:1px; }
.kb-body { flex:1;background:#f8fafc;border-radius:0 0 12px 12px;border:1.5px solid #e8edf3;border-top:none;padding:10px;display:flex;flex-direction:column;gap:8px;min-height:120px; }
.kb-empty { display:flex;align-items:center;justify-content:center;height:60px;color:#cbd5e1;font-size:12px; }

/* Kanban card */
.kb-card {
    background:#fff;border-radius:10px;border:1px solid #e8edf3;
    padding:12px 14px;text-decoration:none;display:block;
    position:relative;overflow:hidden;transition:all .18s;
    cursor:pointer;
}
.kb-card:hover { transform:translateY(-2px);box-shadow:0 6px 16px rgba(30,58,95,0.1);border-color:#c8d4e0; }
.kb-card[draggable="true"] { cursor:grab; }
.kb-card[draggable="true"]:active { cursor:grabbing; }
.kb-card.dragging { opacity:0.4;transform:scale(0.97); }
.kb-body.drag-over { background:#e8f0fe;border-color:#3b82f6;border-style:dashed; }
.kb-body.drag-over .kb-empty { color:#3b82f6; }
.kb-card::before { content:'';position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:3px 0 0 3px; }
.kb-card-name { font-size:13px;font-weight:700;color:var(--navy-dark);margin-bottom:3px;line-height:1.3; }
.kb-card-sub { font-size:11px;color:var(--text-muted);margin-bottom:8px; }
.kb-card-info { display:flex;flex-direction:column;gap:3px; }
.kb-card-row { display:flex;align-items:center;gap:5px;font-size:11px;color:#64748b; }
.kb-card-row svg { width:10px;height:10px;flex-shrink:0;opacity:0.7; }
.kb-card-ca { margin-top:8px;padding-top:8px;border-top:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center; }
.kb-card-ca-val { font-size:12px;font-weight:700;color:var(--navy-dark); }
.kb-card-ca-lbl { font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.3px; }
.kb-avatar { width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0; }

/* ═══ GRILLE ═══ */
.pros-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:14px; }
.pros-card {
    background:#fff;border-radius:14px;border:1px solid var(--border);
    padding:18px;transition:all .2s;text-decoration:none;display:block;
    position:relative;overflow:hidden;
}
.pros-card:hover { transform:translateY(-2px);box-shadow:0 8px 24px rgba(30,58,95,0.1);border-color:#cbd5e1; }
.pros-card::before { content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--card-color,#94a3b8); }
.pros-card-header { display:flex;align-items:flex-start;gap:12px;margin-bottom:12px; }
.pros-avatar { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;color:#fff;flex-shrink:0;background:var(--card-color,#94a3b8); }
.pros-name { font-size:14px;font-weight:700;color:var(--navy-dark);line-height:1.3; }
.pros-meta { font-size:11px;color:var(--text-muted);margin-top:2px; }
.pros-stage { display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600;margin-bottom:10px; }
.pros-info { display:flex;flex-direction:column;gap:4px; }
.pros-info-row { display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text-muted); }
.pros-info-row svg { width:11px;height:11px;flex-shrink:0; }
.pros-ca { margin-top:10px;padding-top:10px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center; }
.pros-ca-label { font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px; }
.pros-ca-value { font-size:13px;font-weight:700;color:var(--navy-dark); }

.empty-state { text-align:center;padding:48px 20px;color:var(--text-muted);background:#f8fafc;border-radius:14px; }
.btn { display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all .2s; }
.btn-gold { background:var(--gold);color:var(--navy-dark); }
.btn-gold:hover { background:var(--gold-dark);color:#fff; }
.btn-outline { background:transparent;color:var(--navy);border:1.5px solid var(--border); }
.btn-outline:hover { border-color:var(--navy);background:#f8fafc; }
</style>

<div class="pros-root">

    <!-- Header -->
    <div class="page-header">
        <div>
            <h1>Prospects & Clients</h1>
            <p><?= array_sum($counts) ?> contact(s) · <?= $counts['client'] ?? 0 ?> client(s) · CA potentiel <?= number_format(array_sum(array_column($prospects, 'ca_potentiel')), 0, ',', ' ') ?> F</p>
        </div>
        <div class="header-right">
            <a href="<?= APP_URL ?>/commercial/prospects/nouveau" class="btn btn-gold">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Nouveau prospect
            </a>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <div class="search-box">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" id="searchInput" placeholder="Rechercher...">
        </div>
        <div class="view-toggle">
            <button class="view-btn <?= $viewMode === 'kanban' ? 'active' : '' ?>" id="btnKanban">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="5" height="18" rx="1"/><rect x="10" y="3" width="5" height="12" rx="1"/><rect x="17" y="3" width="5" height="15" rx="1"/></svg>
                Kanban
            </button>
            <button class="view-btn <?= $viewMode === 'grille' ? 'active' : '' ?>" id="btnGrille">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Grille
            </button>
        </div>
    </div>

    <!-- Stage tabs (filtre rapide) -->
    <div class="stage-tabs">
        <a href="#" class="stage-tab active" id="tabAll" data-stage="">
            Tous <strong><?= array_sum($counts) ?></strong>
        </a>
        <?php foreach ($stageLabels as $key => $label): ?>
        <a href="#" class="stage-tab" data-stage="<?= $key ?>" style="--sc:<?= $stageColors[$key] ?>">
            <span class="dot" style="background:<?= $stageColors[$key] ?>"></span>
            <?= $label ?>
            <strong><?= $counts[$key] ?? 0 ?></strong>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- ═══ VUE KANBAN ═══ -->
    <div id="viewKanban" style="<?= $viewMode === 'grille' ? 'display:none' : '' ?>">
        <?php if (empty($prospects)): ?>
        <div class="empty-state">
            <div style="font-size:40px;margin-bottom:10px">🎯</div>
            <div style="font-size:16px;font-weight:600;color:var(--navy-dark);margin-bottom:6px">Aucun prospect</div>
            <div style="margin-bottom:18px">Commencez par ajouter votre premier prospect</div>
            <a href="<?= APP_URL ?>/commercial/prospects/nouveau" class="btn btn-gold">Nouveau prospect</a>
        </div>
        <?php else: ?>
        <div class="kanban-board" id="kanbanBoard">
            <?php foreach ($stageLabels as $stage => $label):
                $cards = $byStage[$stage] ?? [];
                $totalCA = array_sum(array_column($cards, 'ca_potentiel'));
            ?>
            <div class="kb-col" data-stage="<?= $stage ?>">
                <div class="kb-header" style="background:<?= $stageBg[$stage] ?>;border:1.5px solid <?= $stageColors[$stage] ?>20">
                    <div>
                        <div class="kb-header-label" style="color:<?= $stageText[$stage] ?>"><?= $label ?></div>
                        <?php if ($totalCA > 0): ?>
                        <div class="kb-total" style="color:<?= $stageText[$stage] ?>"><?= number_format($totalCA, 0, ',', ' ') ?> F</div>
                        <?php endif; ?>
                    </div>
                    <div class="kb-count" style="background:<?= $stageColors[$stage] ?>;color:#fff"><?= count($cards) ?></div>
                </div>
                <div class="kb-body">
                    <?php if (empty($cards)): ?>
                    <div class="kb-empty">Vide</div>
                    <?php else: ?>
                    <?php foreach ($cards as $p):
                        $initiales = strtoupper(substr($p['raison_sociale'], 0, 1)) . (strpos($p['raison_sociale'],' ') ? strtoupper(substr(strstr($p['raison_sociale'],' '),1,1)) : '');
                    ?>
                    <a href="<?= APP_URL ?>/commercial/prospect?id=<?= $p['id'] ?>" class="kb-card" draggable="true" data-id="<?= $p['id'] ?>" data-stage="<?= $stage ?>" data-name="<?= htmlspecialchars(strtolower($p['raison_sociale']), ENT_QUOTES) ?>" style="--card-color:<?= $stageColors[$stage] ?>">
                        <div style="position:absolute;left:0;top:0;bottom:0;width:3px;background:<?= $stageColors[$stage] ?>;border-radius:3px 0 0 3px"></div>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                            <div class="kb-avatar" style="background:<?= $stageColors[$stage] ?>"><?= e($initiales) ?></div>
                            <div style="flex:1;min-width:0">
                                <div class="kb-card-name"><?= e($p['raison_sociale']) ?></div>
                                <div class="kb-card-sub"><?= e($p['forme_juridique'] ?? '') ?><?= $p['ville'] ? ' · '.e($p['ville']) : '' ?></div>
                            </div>
                        </div>
                        <div class="kb-card-info">
                            <?php if ($p['contact_nom']): ?>
                            <div class="kb-card-row">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <?= e(trim($p['contact_prenom'].' '.$p['contact_nom'])) ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($p['telephone']): ?>
                            <div class="kb-card-row">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.6 19.79 19.79 0 0 1 1.58 5a2 2 0 0 1 1.98-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 10.4a16 16 0 0 0 6 6l.9-.9a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                <?= e($p['telephone']) ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($p['secteur']): ?>
                            <div class="kb-card-row">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                <?= e($p['secteur']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($p['ca_potentiel'] > 0): ?>
                        <div class="kb-card-ca">
                            <span class="kb-card-ca-lbl">CA potentiel</span>
                            <span class="kb-card-ca-val"><?= number_format($p['ca_potentiel'], 0, ',', ' ') ?> F</span>
                        </div>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ═══ VUE GRILLE ═══ -->
    <div id="viewGrille" style="<?= $viewMode !== 'grille' ? 'display:none' : '' ?>">
        <?php if (empty($prospects)): ?>
        <div class="empty-state">
            <div style="font-size:40px;margin-bottom:10px">🎯</div>
            <div style="font-size:16px;font-weight:600;color:var(--navy-dark);margin-bottom:6px">Aucun prospect</div>
            <a href="<?= APP_URL ?>/commercial/prospects/nouveau" class="btn btn-gold" style="margin-top:12px">Nouveau prospect</a>
        </div>
        <?php else: ?>
        <div class="pros-grid" id="prosGrid">
            <?php foreach ($prospects as $p):
                $color = $stageColors[$p['pipeline_stage']] ?? '#94a3b8';
                $initiales = strtoupper(substr($p['raison_sociale'], 0, 1)) . (strpos($p['raison_sociale'],' ') ? strtoupper(substr(strstr($p['raison_sociale'],' '),1,1)) : '');
            ?>
            <a href="<?= APP_URL ?>/commercial/prospect?id=<?= $p['id'] ?>" class="pros-card" style="--card-color:<?= $color ?>" data-name="<?= htmlspecialchars(strtolower($p['raison_sociale']), ENT_QUOTES) ?>" data-stage="<?= $p['pipeline_stage'] ?>">
                <div class="pros-card-header">
                    <div class="pros-avatar"><?= e($initiales) ?></div>
                    <div>
                        <div class="pros-name"><?= e($p['raison_sociale']) ?></div>
                        <div class="pros-meta"><?= e($p['forme_juridique']) ?> · <?= e($p['ville']) ?></div>
                    </div>
                </div>
                <div>
                    <span class="pros-stage" style="background:<?= $stageBg[$p['pipeline_stage']] ?? '#f1f5f9' ?>;color:<?= $stageText[$p['pipeline_stage']] ?? '#475569' ?>">
                        <?= $stageLabels[$p['pipeline_stage']] ?? $p['pipeline_stage'] ?>
                    </span>
                </div>
                <div class="pros-info">
                    <?php if ($p['contact_nom']): ?>
                    <div class="pros-info-row">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <?= e($p['contact_prenom'] . ' ' . $p['contact_nom']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($p['telephone']): ?>
                    <div class="pros-info-row">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.6 19.79 19.79 0 0 1 1.58 5a2 2 0 0 1 1.98-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 10.4a16 16 0 0 0 6 6l.9-.9a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <?= e($p['telephone']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($p['secteur']): ?>
                    <div class="pros-info-row">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        <?= e($p['secteur']) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ($p['ca_potentiel'] > 0): ?>
                <div class="pros-ca">
                    <span class="pros-ca-label">CA potentiel</span>
                    <span class="pros-ca-value"><?= number_format($p['ca_potentiel'], 0, ',', ' ') ?> F</span>
                </div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
const CSRF_TOKEN = '<?= generateCsrfToken() ?>';
const STAGE_URL  = '<?= APP_URL ?>/commercial/prospect/stage';

// ── Toggle vue ──────────────────────────────────────────
const btnKanban = document.getElementById('btnKanban');
const btnGrille = document.getElementById('btnGrille');
const viewKanban = document.getElementById('viewKanban');
const viewGrille = document.getElementById('viewGrille');

btnKanban.addEventListener('click', function() {
    viewKanban.style.display = '';
    viewGrille.style.display = 'none';
    btnKanban.classList.add('active');
    btnGrille.classList.remove('active');
    localStorage.setItem('prospectView','kanban');
});
btnGrille.addEventListener('click', function() {
    viewGrille.style.display = '';
    viewKanban.style.display = 'none';
    btnGrille.classList.add('active');
    btnKanban.classList.remove('active');
    localStorage.setItem('prospectView','grille');
});
if (localStorage.getItem('prospectView') === 'grille') btnGrille.click();

// ── Filtre par stage ─────────────────────────────────────
document.querySelectorAll('.stage-tab').forEach(function(tab) {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.stage-tab').forEach(function(t) {
            t.classList.remove('active');
            t.style.background = '';
            t.style.color = '';
            t.style.borderColor = '';
        });
        tab.classList.add('active');
        const sc = tab.style.getPropertyValue('--sc');
        if (sc) { tab.style.background = sc; tab.style.color = '#fff'; tab.style.borderColor = sc; }
        const stage = tab.dataset.stage;
        document.querySelectorAll('.kb-col').forEach(function(col) {
            col.style.display = (!stage || col.dataset.stage === stage) ? '' : 'none';
        });
        document.querySelectorAll('.pros-card').forEach(function(card) {
            card.style.display = (!stage || card.dataset.stage === stage) ? '' : 'none';
        });
    });
});

// ── Recherche live ───────────────────────────────────────
document.getElementById('searchInput').addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.kb-card').forEach(function(card) {
        card.style.display = (!q || card.dataset.name.includes(q) || card.textContent.toLowerCase().includes(q)) ? '' : 'none';
    });
    document.querySelectorAll('.pros-card').forEach(function(card) {
        card.style.display = (!q || card.dataset.name.includes(q) || card.textContent.toLowerCase().includes(q)) ? '' : 'none';
    });
});

// ── Drag & Drop Kanban ───────────────────────────────────
let draggedCard = null;

function initDragDrop() {
    // Cards : draggable
    document.querySelectorAll('.kb-card').forEach(function(card) {
        card.addEventListener('dragstart', function(e) {
            draggedCard = card;
            card.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', card.dataset.id);
        });
        card.addEventListener('dragend', function() {
            card.classList.remove('dragging');
            document.querySelectorAll('.kb-body').forEach(function(b) { b.classList.remove('drag-over'); });
            draggedCard = null;
        });
        // Empêcher le clic de naviguer quand on drag
        card.addEventListener('click', function(e) {
            if (card.classList.contains('was-dragged')) {
                e.preventDefault();
                card.classList.remove('was-dragged');
            }
        });
    });

    // Colonnes : drop zones
    document.querySelectorAll('.kb-body').forEach(function(body) {
        body.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            body.classList.add('drag-over');
        });
        body.addEventListener('dragleave', function(e) {
            if (!body.contains(e.relatedTarget)) body.classList.remove('drag-over');
        });
        body.addEventListener('drop', function(e) {
            e.preventDefault();
            body.classList.remove('drag-over');
            if (!draggedCard) return;
            const newStage = body.closest('.kb-col').dataset.stage;
            const oldStage = draggedCard.dataset.stage;
            if (newStage === oldStage) return;

            // Déplacer visuellement
            const emptyDiv = body.querySelector('.kb-empty');
            if (emptyDiv) emptyDiv.remove();
            body.appendChild(draggedCard);
            draggedCard.classList.add('was-dragged');

            // Mettre à jour la couleur de la bande latérale
            const stageColors = <?= json_encode($stageColors) ?>;
            const newColor = stageColors[newStage] || '#94a3b8';
            const bar = draggedCard.querySelector('div[style*="position:absolute"]');
            if (bar) bar.style.background = newColor;
            draggedCard.style.setProperty('--card-color', newColor);
            draggedCard.dataset.stage = newStage;

            // Mettre à jour les compteurs
            updateColCount(oldStage);
            updateColCount(newStage);

            // Ajouter "Vide" si colonne source maintenant vide
            const oldBody = document.querySelector('.kb-col[data-stage="' + oldStage + '"] .kb-body');
            if (oldBody && oldBody.querySelectorAll('.kb-card').length === 0) {
                const emptyEl = document.createElement('div');
                emptyEl.className = 'kb-empty';
                emptyEl.textContent = 'Vide';
                oldBody.appendChild(emptyEl);
            }

            // Appel API
            fetch(STAGE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: parseInt(draggedCard.dataset.id), stage: newStage, csrf_token: CSRF_TOKEN })
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (!data.success) {
                    showToast('Erreur lors du déplacement', 'error');
                }
            }).catch(function() {
                showToast('Erreur réseau', 'error');
            });
        });
    });
}

function updateColCount(stage) {
    const col = document.querySelector('.kb-col[data-stage="' + stage + '"]');
    if (!col) return;
    const count = col.querySelectorAll('.kb-card').length;
    const badge = col.querySelector('.kb-count');
    if (badge) badge.textContent = count;
}

function showToast(msg, type) {
    const t = document.createElement('div');
    t.style.cssText = 'position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:9999;color:#fff;background:' + (type === 'error' ? '#ef4444' : '#22c55e') + ';box-shadow:0 4px 12px rgba(0,0,0,0.15);transition:opacity .3s';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(function() { t.style.opacity = '0'; setTimeout(function() { t.remove(); }, 300); }, 2500);
}

initDragDrop();
</script>
