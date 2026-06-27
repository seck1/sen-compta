<?php
$type_labels = [
    'conge_paye'  => 'Congé payé',
    'maladie'     => 'Maladie',
    'maternite'   => 'Maternité',
    'paternite'   => 'Paternité',
    'sans_solde'  => 'Sans solde',
    'autre'       => 'Autre',
];
$statut_colors = [
    'en_attente' => '#f59e0b',
    'approuve'   => '#1f6e4e',
    'refuse'     => '#dc2626',
    'annule'     => '#6b7280',
];
$statut_labels = [
    'en_attente' => 'En attente',
    'approuve'   => 'Approuvé',
    'refuse'     => 'Refusé',
    'annule'     => 'Annulé',
];
$id = $entreprise['id'];
?>

<div class="page-header">
    <div>
        <div class="page-title">Gestion des congés</div>
        <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?> — <?= $annee ?></div>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
        <select onchange="location.href='?id=<?= $id ?>&annee='+this.value" style="padding:7px 12px;border-radius:8px;border:1px solid var(--border);font-size:14px">
            <?php for($y=2027;$y>=2022;$y--): ?>
            <option value="<?= $y ?>" <?= $y==$annee?'selected':'' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
        <?php if(isSuperviseur()): ?>
        <a href="<?= APP_URL ?>/dossier/rh/conges/parametres?id=<?= $id ?>" class="btn btn-secondary" style="display:flex;align-items:center;gap:6px">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            Paramètres
        </a>
        <?php endif; ?>
        <button onclick="document.getElementById('modaleDemande').style.display='flex'" class="btn btn-primary">
            + Nouvelle demande
        </button>
    </div>
</div>

<?php if(isset($_GET['ok'])): ?>
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#166534;font-size:14px">Opération enregistrée.</div>
<?php endif; ?>

<!-- KPIs -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px">
    <div class="card" style="padding:16px 20px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">Demandes <?= $annee ?></div>
        <div style="font-size:28px;font-weight:700;color:var(--navy-dark)"><?= count($conges) ?></div>
    </div>
    <div class="card" style="padding:16px 20px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">En attente</div>
        <div style="font-size:28px;font-weight:700;color:#f59e0b"><?= $en_attente ?></div>
    </div>
    <div class="card" style="padding:16px 20px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">Approuvés</div>
        <div style="font-size:28px;font-weight:700;color:#1f6e4e"><?= $approuves ?></div>
    </div>
    <div class="card" style="padding:16px 20px">
        <div style="font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:6px">Employés actifs</div>
        <div style="font-size:28px;font-weight:700;color:var(--navy-dark)"><?= count($employes) ?></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px">

<!-- Colonne gauche : demandes -->
<div>
    <!-- Demandes de congés -->
    <div class="card" style="padding:0;overflow:hidden;margin-bottom:20px">
        <div style="padding:14px 20px;border-bottom:1px solid var(--border);font-weight:700;font-size:14px;display:flex;justify-content:space-between;align-items:center">
            Demandes de congés
            <?php if($en_attente > 0 && isSuperviseur()): ?>
            <span style="background:#fef3c7;color:#92400e;font-size:14px;font-weight:700;padding:3px 10px;border-radius:20px"><?= $en_attente ?> à traiter</span>
            <?php endif; ?>
        </div>
        <?php if(empty($conges)): ?>
        <div style="padding:48px;text-align:center;color:var(--text-muted)">
            <div style="font-size:32px;margin-bottom:12px">🏖️</div>
            <div style="font-weight:600">Aucune demande de congé</div>
            <div style="font-size:14px;margin-top:6px">Cliquez sur "Nouvelle demande" pour en créer une.</div>
        </div>
        <?php else: ?>
        <table style="width:100%;border-collapse:collapse;font-size:14px">
            <thead>
                <tr style="background:var(--bg);border-bottom:2px solid var(--border)">
                    <th style="padding:10px 16px;text-align:left;font-size:14px;background:#f1f5f9;color:#4a554f;text-transform:uppercase">Employé</th>
                    <th style="padding:10px 16px;text-align:left;font-size:14px;background:#f1f5f9;color:#4a554f;text-transform:uppercase">Type</th>
                    <th style="padding:10px 16px;text-align:left;font-size:14px;background:#f1f5f9;color:#4a554f;text-transform:uppercase">Période</th>
                    <th style="padding:10px 16px;text-align:center;font-size:14px;background:#f1f5f9;color:#4a554f;text-transform:uppercase">Jours</th>
                    <th style="padding:10px 16px;text-align:center;font-size:14px;background:#f1f5f9;color:#4a554f;text-transform:uppercase">Statut</th>
                    <th style="padding:10px 16px;text-align:center;font-size:14px;background:#f1f5f9;color:#4a554f;text-transform:uppercase">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($conges as $c): ?>
            <tr style="border-bottom:1px solid var(--border)">
                <td style="padding:11px 16px">
                    <div style="font-weight:600"><?= e($c['nom'].' '.$c['prenom']) ?></div>
                    <div style="font-size:14px;color:var(--text-muted)"><?= e($c['poste'] ?: '—') ?></div>
                </td>
                <td style="padding:11px 16px">
                    <span style="background:#f0f3f8;color:var(--navy-dark);font-size:14px;font-weight:600;padding:2px 8px;border-radius:4px">
                        <?= $type_labels[$c['type_conge']] ?? $c['type_conge'] ?>
                    </span>
                </td>
                <td style="padding:11px 16px;font-size:13px">
                    <?= date('d/m/Y', strtotime($c['date_debut'])) ?> → <?= date('d/m/Y', strtotime($c['date_fin'])) ?>
                </td>
                <td style="padding:11px 16px;text-align:center;font-weight:700;font-family:monospace"><?= $c['nb_jours'] ?>j</td>
                <td style="padding:11px 16px;text-align:center">
                    <span style="background:<?= $statut_colors[$c['statut']] ?>22;color:<?= $statut_colors[$c['statut']] ?>;font-size:14px;font-weight:700;padding:3px 10px;border-radius:20px">
                        <?= $statut_labels[$c['statut']] ?>
                    </span>
                </td>
                <td style="padding:11px 16px;text-align:center">
                    <div style="display:flex;gap:6px;justify-content:center">
                    <?php if($c['statut'] === 'en_attente' && isSuperviseur()): ?>
                        <button onclick="traiterConge(<?= $c['id'] ?>, 'approuve')" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:5px;color:#1f6e4e;font-size:14px;font-weight:700;padding:4px 10px;cursor:pointer">✓ Approuver</button>
                        <button onclick="traiterConge(<?= $c['id'] ?>, 'refuse')" style="background:#fef2f2;border:1px solid #fecaca;border-radius:5px;color:#dc2626;font-size:14px;font-weight:700;padding:4px 10px;cursor:pointer">✗ Refuser</button>
                    <?php elseif($c['statut'] === 'en_attente'): ?>
                        <button onclick="supprimerConge(<?= $c['id'] ?>)" style="background:#fef2f2;border:1px solid #fecaca;border-radius:5px;color:#dc2626;font-size:14px;padding:4px 10px;cursor:pointer">Annuler</button>
                    <?php else: ?>
                        <span style="font-size:14px;color:var(--text-muted)"><?= $c['commentaire_rh'] ? 'Note: '.e(substr($c['commentaire_rh'],0,30)) : '—' ?></span>
                    <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Colonne droite : soldes -->
<div>
    <div class="card" style="padding:0;overflow:hidden;margin-bottom:16px">
        <div style="padding:14px 20px;border-bottom:1px solid var(--border);font-weight:700;font-size:14px">
            Soldes de congés <?= $annee ?>
        </div>
        <?php if(empty($soldes) && empty($employes_sans_solde)): ?>
        <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:14px">Aucun solde défini</div>
        <?php else: ?>
        <div style="padding:12px 16px;display:flex;flex-direction:column;gap:10px">
            <?php foreach($soldes as $s): ?>
            <div style="border:1px solid var(--border);border-radius:8px;padding:12px">
                <div style="font-weight:600;font-size:14px;margin-bottom:8px"><?= e($s['nom'].' '.$s['prenom']) ?></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;text-align:center;margin-bottom:6px">
                    <div style="background:#f0f3f8;border-radius:6px;padding:6px">
                        <div style="font-size:9px;color:var(--text-muted);text-transform:uppercase;margin-bottom:2px">Acquis N</div>
                        <div style="font-weight:700;color:var(--navy-dark)"><?= $s['jours_acquis'] ?>j</div>
                    </div>
                    <div style="background:rgba(184,146,63,0.1);border-radius:6px;padding:6px">
                        <div style="font-size:9px;color:var(--text-muted);text-transform:uppercase;margin-bottom:2px">Report N-1</div>
                        <div style="font-weight:700;color:#4338ca"><?= $s['jours_reportes_n1'] ?>j</div>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;text-align:center">
                    <div style="background:#fef3c7;border-radius:6px;padding:6px">
                        <div style="font-size:9px;color:var(--text-muted);text-transform:uppercase;margin-bottom:2px">Pris</div>
                        <div style="font-weight:700;color:#92400e"><?= $s['jours_pris'] ?>j</div>
                    </div>
                    <div style="background:<?= $s['jours_restants'] > 0 ? '#f0fdf4' : '#fef2f2' ?>;border-radius:6px;padding:6px">
                        <div style="font-size:9px;color:var(--text-muted);text-transform:uppercase;margin-bottom:2px">Solde total</div>
                        <div style="font-weight:700;color:<?= $s['jours_restants'] > 0 ? '#1f6e4e' : '#dc2626' ?>"><?= $s['jours_restants'] ?>j</div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php foreach($employes_sans_solde as $e): ?>
            <div style="border:1px dashed var(--border);border-radius:8px;padding:10px;opacity:.7">
                <div style="font-size:13px;font-weight:600;margin-bottom:4px"><?= e($e['nom'].' '.$e['prenom']) ?></div>
                <div style="font-size:14px;color:var(--text-muted)">Solde non défini</div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if(isSuperviseur()): ?>
    <!-- Formulaire solde -->
    <div class="card" style="padding:16px 20px">
        <div style="font-weight:700;font-size:14px;margin-bottom:14px;color:var(--navy-dark)">Définir un solde</div>
        <form method="post" action="<?= APP_URL ?>/dossier/rh/conges/solde">
            <input type="hidden" name="entreprise_id" value="<?= $id ?>">
            <div style="margin-bottom:10px">
                <label style="font-size:14px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Employé</label>
                <select name="employe_id" required style="width:100%;padding:7px 10px;border:1px solid var(--border);border-radius:6px;font-size:14px">
                    <option value="">Sélectionner...</option>
                    <?php foreach($employes as $emp): ?>
                    <option value="<?= $emp['id'] ?>"><?= e($emp['nom'].' '.$emp['prenom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px">
                <div>
                    <label style="font-size:14px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Année</label>
                    <input type="number" name="annee" value="<?= $annee ?>" min="2020" max="2030" style="width:100%;padding:7px 10px;border:1px solid var(--border);border-radius:6px;font-size:14px">
                </div>
                <div>
                    <label style="font-size:14px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Jours acquis N</label>
                    <input type="number" name="jours_acquis" value="24" min="0" max="365" step="0.5" style="width:100%;padding:7px 10px;border:1px solid var(--border);border-radius:6px;font-size:14px">
                </div>
            </div>
            <div style="margin-bottom:12px">
                <label style="font-size:14px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Report N-1 (jours non pris année précédente)</label>
                <input type="number" name="jours_reportes_n1" value="0" min="0" max="365" step="0.5" style="width:100%;padding:7px 10px;border:1px solid var(--border);border-radius:6px;font-size:14px">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Enregistrer le solde</button>
        </form>
    </div>
    <?php endif; ?>
</div>

</div>

<!-- Modal nouvelle demande -->
<div id="modaleDemande" style="position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;display:none;align-items:center;justify-content:center">
<div style="background:#fff;border-radius:14px;width:500px;max-width:95vw;padding:28px;box-shadow:0 20px 60px rgba(0,0,0,.3);position:relative">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <div style="font-size:14px;font-weight:700;color:#1e3a5f">Nouvelle demande de congé</div>
        <button onclick="document.getElementById('modaleDemande').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:#444;line-height:1">×</button>
    </div>
    <form method="post" action="<?= APP_URL ?>/dossier/rh/conges/store">
        <input type="hidden" name="entreprise_id" value="<?= $id ?>">
        <div style="margin-bottom:14px">
            <label style="font-size:13px;font-weight:700;color:#333;display:block;margin-bottom:5px">Employé *</label>
            <select name="employe_id" required style="width:100%;padding:9px 12px;border:1.5px solid #ccc;border-radius:7px;font-size:14px;color:#1a1a1a;background:#fff">
                <option value="">Sélectionner un employé...</option>
                <?php foreach($employes as $emp): ?>
                <option value="<?= $emp['id'] ?>"><?= e($emp['nom'].' '.$emp['prenom']) ?><?= $emp['poste'] ? ' — '.e($emp['poste']) : '' ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="margin-bottom:14px">
            <label style="font-size:13px;font-weight:700;color:#333;display:block;margin-bottom:5px">Type de congé *</label>
            <select name="type_conge" style="width:100%;padding:9px 12px;border:1.5px solid #ccc;border-radius:7px;font-size:14px;color:#1a1a1a;background:#fff">
                <?php foreach($type_labels as $val => $lab): ?>
                <option value="<?= $val ?>"><?= $lab ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
            <div>
                <label style="font-size:13px;font-weight:700;color:#333;display:block;margin-bottom:5px">Date début *</label>
                <input type="date" name="date_debut" id="dateDebut" required onchange="calculerJours()" style="width:100%;padding:9px 12px;border:1.5px solid #ccc;border-radius:7px;font-size:14px;color:#1a1a1a">
            </div>
            <div>
                <label style="font-size:13px;font-weight:700;color:#333;display:block;margin-bottom:5px">Date fin *</label>
                <input type="date" name="date_fin" id="dateFin" required onchange="calculerJours()" style="width:100%;padding:9px 12px;border:1.5px solid #ccc;border-radius:7px;font-size:14px;color:#1a1a1a">
            </div>
        </div>
        <div id="nbJoursApercu" style="display:none;background:#e8f0fb;border-radius:6px;padding:9px 14px;margin-bottom:14px;font-size:14px;font-weight:700;color:#1e3a5f"></div>
        <div style="margin-bottom:20px">
            <label style="font-size:13px;font-weight:700;color:#333;display:block;margin-bottom:5px">Motif (optionnel)</label>
            <textarea name="motif" rows="2" style="width:100%;padding:9px 12px;border:1.5px solid #ccc;border-radius:7px;font-size:14px;color:#1a1a1a;resize:vertical"></textarea>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px">
            <button type="button" onclick="document.getElementById('modaleDemande').style.display='none'" class="btn btn-secondary">Annuler</button>
            <button type="submit" class="btn btn-primary">Enregistrer la demande</button>
        </div>
    </form>
</div>
</div>

<!-- Modal traitement -->
<div id="modaleTraiter" style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:2000;display:none;align-items:center;justify-content:center">
<div style="background:#fff;border-radius:14px;width:420px;max-width:95vw;padding:24px">
    <div style="font-size:13px;font-weight:700;color:var(--navy-dark);margin-bottom:16px" id="traiterTitre">Traiter la demande</div>
    <div style="margin-bottom:16px">
        <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">Commentaire RH (optionnel)</label>
        <textarea id="traiterCommentaire" rows="3" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:6px;font-size:14px;resize:vertical"></textarea>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:10px">
        <button onclick="document.getElementById('modaleTraiter').style.display='none'" class="btn btn-secondary">Annuler</button>
        <button id="btnConfirmerTraiter" class="btn btn-primary">Confirmer</button>
    </div>
</div>
</div>

<script>
function calculerJours() {
    var d1 = document.getElementById('dateDebut').value;
    var d2 = document.getElementById('dateFin').value;
    if (!d1 || !d2) return;
    var start = new Date(d1), end = new Date(d2);
    if (end < start) return;
    var jours = 0;
    var cur = new Date(start);
    while (cur <= end) {
        if (cur.getDay() !== 0) jours++;
        cur.setDate(cur.getDate() + 1);
    }
    var ap = document.getElementById('nbJoursApercu');
    ap.style.display = 'block';
    ap.textContent = jours + ' jour(s) ouvré(s)';
}

var congeIdEnCours = 0;
var statutEnCours = '';

function traiterConge(congeId, statut) {
    congeIdEnCours = congeId;
    statutEnCours = statut;
    document.getElementById('traiterTitre').textContent = statut === 'approuve' ? 'Approuver la demande' : 'Refuser la demande';
    document.getElementById('traiterCommentaire').value = '';
    document.getElementById('btnConfirmerTraiter').style.background = statut === 'approuve' ? '#1f6e4e' : '#dc2626';
    document.getElementById('modaleTraiter').style.display = 'flex';
}

document.getElementById('btnConfirmerTraiter').onclick = function() {
    var fd = new FormData();
    fd.append('entreprise_id', '<?= $id ?>');
    fd.append('conge_id', congeIdEnCours);
    fd.append('statut', statutEnCours);
    fd.append('commentaire', document.getElementById('traiterCommentaire').value);
    fetch('<?= APP_URL ?>/dossier/rh/conges/traiter', { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(d) { if(d.ok) location.reload(); });
};

function supprimerConge(congeId) {
    if (!confirm('Annuler cette demande ?')) return;
    var fd = new FormData();
    fd.append('entreprise_id', '<?= $id ?>');
    fd.append('conge_id', congeId);
    fetch('<?= APP_URL ?>/dossier/rh/conges/supprimer', { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(d) { if(d.ok) location.reload(); });
}
</script>
