<?php
$mois_noms = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$preselect_employe = (int)($_GET['employe_id'] ?? 0);
?>
<div class="page-header">
    <div>
        <div class="page-title">Générer un bulletin de paie</div>
        <div class="page-subtitle"><?= e($entreprise['raison_sociale']) ?></div>
    </div>
    <a href="<?= APP_URL ?>/dossier/rh/bulletins?id=<?= $entreprise['id'] ?>" class="btn btn-outline btn-sm">Retour</a>
</div>

<div style="max-width:680px">
    <form method="POST" action="<?= APP_URL ?>/dossier/rh/bulletin/store" onsubmit="
        var inp = document.getElementById('prime_anciennete');
        document.getElementById('prime_anciennete_override').value = (inp.dataset.auto == '0') ? '1' : '0';
    ">
        <input type="hidden" name="entreprise_id" value="<?= $entreprise['id'] ?>">

        <div class="card" style="margin-bottom:20px">
            <div style="font-family:'Cormorant Garamond',serif;font-size:13px;font-weight:400;color:var(--navy-dark);margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)">
                Sélection employé & période
            </div>
            <div class="form-grid">
                <div class="form-field" style="grid-column:1/-1">
                    <label>Employé *</label>
                    <select name="employe_id" required onchange="updateSalaireInfo(this);chargerAbsences()">
                        <option value="">-- Sélectionner un employé --</option>
                        <?php foreach($employes as $emp): ?>
                        <option value="<?= $emp['id'] ?>"
                                data-base="<?= $emp['salaire_base'] ?>"
                                data-sursalaire="<?= $emp['sursalaire'] ?>"
                                data-transport="<?= $emp['indemnite_transport'] ?>"
                                data-logement="<?= $emp['indemnite_logement'] ?>"
                                data-repr="<?= $emp['indemnite_representation'] ?>"
                                data-date-embauche="<?= e($emp['date_embauche'] ?? '') ?>"
                                <?= $emp['id']==$preselect_employe?'selected':'' ?>>
                            <?= e($emp['nom'].' '.$emp['prenom']) ?> — <?= e($emp['matricule']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field">
                    <label>Mois *</label>
                    <select name="mois" required onchange="chargerAbsences()">
                        <?php for($m=1;$m<=12;$m++): ?>
                        <option value="<?= $m ?>" <?= $m==date('n')?'selected':'' ?>><?= $mois_noms[$m] ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-field">
                    <label>Année *</label>
                    <select name="annee" required onchange="chargerAbsences()">
                        <?php for($y=2027;$y>=2022;$y--): ?>
                        <option value="<?= $y ?>" <?= $y==2026?'selected':'' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Aperçu salaire de base -->
        <div id="salaire-preview" class="card" style="margin-bottom:20px;display:none;background:rgba(30,58,95,0.03)">
            <div style="font-size:13px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px">Rémunération de base</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;font-size:14px">
                <div><span style="color:var(--text-muted)">Salaire base</span><br><strong id="prev-base">—</strong></div>
                <div><span style="color:var(--text-muted)">Sursalaire</span><br><strong id="prev-surs">—</strong></div>
                <div><span style="color:var(--text-muted)">Transport</span><br><strong id="prev-trans">—</strong></div>
                <div><span style="color:var(--text-muted)">Logement</span><br><strong id="prev-log">—</strong></div>
            </div>
        </div>

        <div class="card" style="margin-bottom:24px">
            <div style="font-family:'Cormorant Garamond',serif;font-size:13px;font-weight:400;color:var(--navy-dark);margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)">
                Éléments variables
            </div>
            <div class="form-grid">
                <div class="form-field">
                    <label>Heures supp — jour</label>
                    <input type="number" name="heures_supp" value="0" step="0.5" min="0">
                    <small style="color:var(--text-muted);font-size:14px">+15% (8 premières), puis +40% (Art. L.198)</small>
                </div>
                <div class="form-field">
                    <label>Heures supp — nuit</label>
                    <input type="number" name="heures_supp_nuit" value="0" step="0.5" min="0">
                    <small style="color:var(--text-muted);font-size:14px">+60% (nuit, jour ouvrable)</small>
                </div>
                <div class="form-field">
                    <label>Heures supp — dimanche/férié (jour)</label>
                    <input type="number" name="heures_supp_dim" value="0" step="0.5" min="0">
                    <small style="color:var(--text-muted);font-size:14px">+60%</small>
                </div>
                <div class="form-field">
                    <label>Heures supp — dimanche/férié (nuit)</label>
                    <input type="number" name="heures_supp_dim_nuit" value="0" step="0.5" min="0">
                    <small style="color:var(--text-muted);font-size:14px">+100%</small>
                </div>
                <div class="form-field">
                    <label>Prime exceptionnelle (FCFA)</label>
                    <input type="number" name="prime_saisie" value="0" step="1" min="0">
                </div>
                <div class="form-field">
                    <label>Prime d'ancienneté (FCFA)</label>
                    <input type="number" name="prime_anciennete" id="prime_anciennete" value="" step="1" min="0" placeholder="Auto" data-auto="1" oninput="this.dataset.auto=0">
                    <input type="hidden" name="prime_anciennete_override" id="prime_anciennete_override" value="0">
                    <small style="color:var(--text-muted);font-size:14px" id="anciennete_info">Calculée automatiquement (3%/an dès 2 ans) — modifiable</small>
                </div>
                <div class="form-field">
                    <label>Avantages en nature (FCFA)</label>
                    <input type="number" name="avantages_nature" value="0" step="1" min="0">
                    <small style="color:var(--text-muted);font-size:14px">Logement, véhicule… imposables mais non versés en espèces</small>
                </div>
                <div class="form-field">
                    <label>Acompte / avance (FCFA)</label>
                    <input type="number" name="acompte" value="0" step="1" min="0">
                    <small style="color:var(--text-muted);font-size:14px">Déduit du net à payer</small>
                </div>
                <div class="form-field">
                    <label>Retenues diverses (FCFA)</label>
                    <input type="number" name="retenues_diverses" value="0" step="1" min="0">
                    <small style="color:var(--text-muted);font-size:14px">Saisie-arrêt, opposition, retenue syndicale…</small>
                </div>
            </div>
        </div>

        <!-- Bloc absences détecté automatiquement -->
        <div id="absences-bloc" style="display:none;margin-bottom:20px"></div>

        <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);border-radius:10px;padding:14px 18px;font-size:14px;color:#92400e;margin-bottom:20px">
            <strong>Note :</strong> Les cotisations (IPRES, TRIMF, IR, IPM) sont calculées automatiquement selon le barème OHADA/SENEGAL en vigueur.
        </div>

        <button type="submit" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
            Générer le bulletin
        </button>
    </form>
</div>

<script>
function fmt(n) { return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA'; }
function updateSalaireInfo(sel) {
    const opt = sel.options[sel.selectedIndex];
    const preview = document.getElementById('salaire-preview');
    if (!opt.value) { preview.style.display='none'; return; }
    document.getElementById('prev-base').textContent  = fmt(opt.dataset.base);
    document.getElementById('prev-surs').textContent  = fmt(opt.dataset.sursalaire);
    document.getElementById('prev-trans').textContent = fmt(opt.dataset.transport);
    document.getElementById('prev-log').textContent   = fmt(opt.dataset.logement);
    preview.style.display = 'block';

    // Calcul prime ancienneté automatique
    var dateEmbauche = opt.dataset.dateEmbauche;
    var salaireBase  = parseFloat(opt.dataset.base) || 0;
    if (dateEmbauche && salaireBase) {
        var embauche  = new Date(dateEmbauche);
        var now       = new Date();
        var annees    = Math.floor((now - embauche) / (365.25 * 24 * 3600 * 1000));
        var prime     = 0;
        if (annees >= 2) {
            var taux = Math.min(annees * 0.03, 0.45);
            prime = Math.round(salaireBase * taux);
        }
        var inp  = document.getElementById('prime_anciennete');
        var info = document.getElementById('anciennete_info');
        inp.value = prime;
        if (annees >= 2) {
            info.textContent = annees + ' ans d\'ancienneté → ' + (Math.min(annees*3,45)) + '% = ' + new Intl.NumberFormat('fr-FR').format(prime) + ' F CFA';
        } else if (annees > 0) {
            info.textContent = annees + ' an(s) — prime applicable à partir de 2 ans';
        } else {
            info.textContent = 'Moins d\'un an — pas de prime d\'ancienneté';
        }
    }
}
// Init on load if pre-selected
window.addEventListener('DOMContentLoaded', function() {
    var sel = document.querySelector('select[name="employe_id"]');
    if(sel && sel.value) { updateSalaireInfo(sel); chargerAbsences(); }
});

var typeColors     = { sans_solde:'#fef2f2', maladie:'#fef3c7', conge_paye:'#f0fdf4', maternite:'#ede9fe', paternite:'#ede9fe', autre:'#f0f3f8' };
var typeTextColors = { sans_solde:'#991b1b', maladie:'#92400e', conge_paye:'#166534', maternite:'#4338ca', paternite:'#4338ca', autre:'#1e3a5f' };

function mkEl(tag, styles, textContent) {
    var el = document.createElement(tag);
    if (styles) el.style.cssText = styles;
    if (textContent !== undefined) el.textContent = textContent;
    return el;
}

function chargerAbsences() {
    var empId = document.querySelector('select[name="employe_id"]').value;
    var mois  = document.querySelector('select[name="mois"]').value;
    var annee = document.querySelector('select[name="annee"]').value;
    var bloc  = document.getElementById('absences-bloc');
    if (!empId || !mois || !annee) { bloc.style.display='none'; return; }

    fetch('<?= APP_URL ?>/dossier/rh/conges/api?id=<?= $entreprise['id'] ?>&employe_id='+empId+'&mois='+mois+'&annee='+annee)
        .then(function(r) { return r.json(); })
        .then(function(d) {
            while (bloc.firstChild) bloc.removeChild(bloc.firstChild);
            if (!d.ok || !d.conges.length) { bloc.style.display='none'; return; }
            bloc.style.display = 'block';

            var wrap = mkEl('div','border-radius:10px;overflow:hidden;border:1px solid #e0e0e0');

            // Header
            var header = mkEl('div','background:#1e3a5f;color:#fff;padding:10px 16px;font-size:13px;font-weight:700;display:flex;justify-content:space-between;align-items:center');
            header.appendChild(mkEl('span', null, '🗓 Absences détectées ce mois'));
            header.appendChild(mkEl('span','font-size:14px;opacity:.7','Appliquées automatiquement au bulletin'));
            wrap.appendChild(header);

            // Lignes congés
            d.conges.forEach(function(c) {
                var bg = typeColors[c.type] || '#f0f3f8';
                var tc = typeTextColors[c.type] || '#333';
                var row = mkEl('div','padding:10px 16px;background:'+bg+';border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center');
                var left = mkEl('div');
                var typeSpan = mkEl('span','font-weight:700;color:'+tc+';font-size:14px', c.type_label);
                var dateSpan = mkEl('span','font-size:14px;color:#555;margin-left:8px', c.date_debut+' → '+c.date_fin);
                left.appendChild(typeSpan);
                left.appendChild(dateSpan);
                var right = mkEl('div','font-weight:700;font-size:14px;color:'+tc, c.nb_jours+' jour(s)');
                if (c.type === 'sans_solde') {
                    right.appendChild(mkEl('span','font-size:14px;font-weight:400', ' — déduction : '+c.deduction+' FCFA'));
                }
                row.appendChild(left);
                row.appendChild(right);
                wrap.appendChild(row);
            });

            // Footer déduction
            if (d.deduction_totale && d.deduction_totale !== '0') {
                var footer = mkEl('div','padding:10px 16px;background:#1e3a5f;color:#fff;display:flex;justify-content:space-between;font-weight:700;font-size:14px');
                footer.appendChild(mkEl('span', null, 'Déduction totale sans solde'));
                footer.appendChild(mkEl('span', null, '- '+d.deduction_totale+' FCFA'));
                wrap.appendChild(footer);
            }

            bloc.appendChild(wrap);
        });
}

</script>
