<?php
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_warning = $_SESSION['flash_warning'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_warning']);
?>
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Import CSV bancaire</h1>
        <p class="page-subtitle">Importer un relevé bancaire CSV pour créer des écritures en brouillon</p>
    </div>
    <a href="<?= APP_URL ?>/dossier/ecritures?id=<?= $entreprise['id'] ?>" class="btn btn-outline btn-sm">← Retour aux écritures</a>
</div>

<?php if ($flash_success): ?>
<div style="margin-bottom:16px;padding:12px 16px;background:#d1fae5;border:1px solid #6ee7b7;border-radius:8px;color:#065f46;font-size:17px"><?= e($flash_success) ?></div>
<?php endif; ?>
<?php if ($flash_warning): ?>
<div style="margin-bottom:16px;padding:12px 16px;background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;color:#92400e;font-size:17px"><?= e($flash_warning) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

<!-- Formulaire principal -->
<div class="card" style="padding:24px">
    <h3 style="margin:0 0 20px;font-size:18px;color:var(--navy-dark)">Paramètres d'import</h3>
    <form method="POST" action="<?= APP_URL ?>/dossier/import-csv?id=<?= $entreprise['id'] ?>" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $entreprise['id'] ?>">

        <div style="margin-bottom:16px">
            <label style="display:block;font-size:18px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Fichier CSV *</label>
            <input type="file" name="fichier" accept=".csv,.txt" required id="input-fichier"
                style="width:100%;padding:8px;border:2px dashed var(--border);border-radius:8px;font-size:19px;cursor:pointer">
            <div style="font-size:14px;color:var(--text-muted);margin-top:4px">Formats acceptés : CSV, TXT (UTF-8 ou ISO-8859-1)</div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
            <div>
                <label style="display:block;font-size:18px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Journal</label>
                <select name="journal_code" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:19px;font-family:inherit">
                    <?php foreach ($journaux_liste as $j): ?>
                    <option value="<?= e($j['code']) ?>" <?= $j['code']==='BNQ'?'selected':'' ?>><?= e($j['code']) ?> — <?= e($j['libelle']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:18px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Exercice</label>
                <select name="exercice" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:19px;font-family:inherit">
                    <?php foreach ($exercicesDispos as $ex): ?>
                    <option value="<?= $ex ?>" <?= $ex==$entreprise['exercice_courant']?'selected':'' ?>><?= $ex ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
            <div>
                <label style="display:block;font-size:18px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Séparateur</label>
                <select name="separateur" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:19px;font-family:inherit">
                    <option value=";">Point-virgule ( ; )</option>
                    <option value=",">Virgule ( , )</option>
                    <option value="|">Pipe ( | )</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:18px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Première ligne</label>
                <select name="skip_header" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:19px;font-family:inherit">
                    <option value="1">Ignorer (en-tête colonnes)</option>
                    <option value="">Importer (pas d'en-tête)</option>
                </select>
            </div>
        </div>

        <hr style="border:none;border-top:1px solid var(--border);margin:20px 0">
        <h4 style="margin:0 0 14px;font-size:19px;color:var(--navy-dark)">Colonnes <span style="font-weight:400;color:var(--text-muted)">(index à partir de 0)</span></h4>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
            <div>
                <label style="display:block;font-size:18px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Date</label>
                <input type="number" name="col_date" value="0" min="0" max="20" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:19px;font-family:inherit">
            </div>
            <div>
                <label style="display:block;font-size:18px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Libellé</label>
                <input type="number" name="col_libelle" value="1" min="0" max="20" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:19px;font-family:inherit">
            </div>
        </div>

        <div style="margin-bottom:14px">
            <label style="display:block;font-size:18px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px">Format montant</label>
            <div style="display:flex;gap:16px;margin-bottom:10px">
                <label style="font-size:19px;cursor:pointer;display:flex;align-items:center;gap:6px">
                    <input type="radio" name="format_montant" value="debit_credit" checked onchange="toggleMontantCols()"> Débit / Crédit séparés
                </label>
                <label style="font-size:19px;cursor:pointer;display:flex;align-items:center;gap:6px">
                    <input type="radio" name="format_montant" value="montant_unique" onchange="toggleMontantCols()"> Montant unique signé
                </label>
            </div>

            <div id="cols-dc" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label style="display:block;font-size:18px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Col. Débit</label>
                    <input type="number" name="col_debit" value="2" min="0" max="20" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:19px;font-family:inherit">
                </div>
                <div>
                    <label style="display:block;font-size:18px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Col. Crédit</label>
                    <input type="number" name="col_credit" value="3" min="0" max="20" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:19px;font-family:inherit">
                </div>
                <input type="hidden" name="col_montant" value="-1">
            </div>

            <div id="cols-mu" style="display:none">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div>
                        <label style="display:block;font-size:18px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Col. Montant</label>
                        <input type="number" name="col_montant_u" value="2" min="0" max="20" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:19px;font-family:inherit" oninput="syncMontantCol(this)">
                    </div>
                    <div>
                        <label style="display:block;font-size:18px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Sens positif</label>
                        <select name="sens_montant" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:19px;font-family:inherit">
                            <option value="credit_positif">+ = Crédit (entrée)</option>
                            <option value="debit_positif">+ = Débit (sortie)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <hr style="border:none;border-top:1px solid var(--border);margin:20px 0">
        <h4 style="margin:0 0 14px;font-size:19px;color:var(--navy-dark)">Comptes de contrepartie</h4>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px">
            <div>
                <label style="display:block;font-size:18px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Compte trésorerie</label>
                <input type="text" name="compte_contrepartie" value="512100" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:19px;font-family:inherit">
                <div style="font-size:14px;color:var(--text-muted);margin-top:3px">Ex: 512100 Banque</div>
            </div>
            <div>
                <label style="display:block;font-size:18px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Compte tiers contrepartie</label>
                <input type="text" name="compte_tiers" value="401000" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:19px;font-family:inherit">
                <div style="font-size:14px;color:var(--text-muted);margin-top:3px">Ex: 401000 Fournisseurs</div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:12px">
            Importer le fichier CSV
        </button>
    </form>
</div>

<!-- Aide & Aperçu -->
<div style="display:flex;flex-direction:column;gap:16px">

    <div class="card" style="padding:24px">
        <h3 style="margin:0 0 12px;font-size:18px;color:var(--navy-dark)">Aperçu du fichier</h3>
        <pre id="preview-box" style="font-size:14px;font-family:monospace;background:#f8fafc;border:1px solid var(--border);border-radius:8px;padding:12px;min-height:70px;white-space:pre-wrap;word-break:break-all;color:var(--text-muted);margin:0">Sélectionnez un fichier...</pre>
    </div>

    <div class="card" style="padding:24px">
        <h3 style="margin:0 0 14px;font-size:18px;color:var(--navy-dark)">Formats supportés</h3>

        <div style="margin-bottom:14px">
            <div style="font-size:18px;font-weight:600;color:var(--navy-dark);margin-bottom:5px">Débit / Crédit séparés</div>
            <pre style="font-size:14px;background:#f8fafc;border:1px solid var(--border);border-radius:6px;padding:10px;color:var(--text-muted);margin:0;white-space:pre-wrap">Date;Libellé;Débit;Crédit
15/01/2026;Achat fournitures;45000;
20/01/2026;Virement client;;120000</pre>
        </div>

        <div style="margin-bottom:14px">
            <div style="font-size:18px;font-weight:600;color:var(--navy-dark);margin-bottom:5px">Montant unique signé</div>
            <pre style="font-size:14px;background:#f8fafc;border:1px solid var(--border);border-radius:6px;padding:10px;color:var(--text-muted);margin:0;white-space:pre-wrap">Date;Libellé;Montant
15/01/2026;Achat fournitures;-45000
20/01/2026;Virement client;120000</pre>
        </div>

        <div style="background:rgba(201,169,110,0.08);border:1px solid rgba(201,169,110,0.3);border-radius:8px;padding:12px">
            <div style="font-size:18px;font-weight:600;color:var(--gold);margin-bottom:4px">Important</div>
            <div style="font-size:18px;color:var(--text-muted)">Les écritures sont créées en <strong>brouillon</strong>. Vérifiez-les dans la page Écritures avant validation.</div>
        </div>
    </div>

</div>
</div>

<script>
document.getElementById('input-fichier').addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var lines = e.target.result.split(/\r?\n/).slice(0, 10);
        document.getElementById('preview-box').textContent = lines.join('\n') + (lines.length >= 10 ? '\n...' : '');
    };
    reader.readAsText(file, 'ISO-8859-1');
});

function toggleMontantCols() {
    var isUnique = document.querySelector('input[name="format_montant"]:checked').value === 'montant_unique';
    document.getElementById('cols-dc').style.display = isUnique ? 'none' : 'grid';
    document.getElementById('cols-mu').style.display = isUnique ? 'block' : 'none';
    var hidden = document.querySelector('input[name="col_montant"]');
    if (hidden) hidden.value = isUnique ? document.querySelector('input[name="col_montant_u"]').value : '-1';
}

function syncMontantCol(el) {
    var hidden = document.querySelector('input[name="col_montant"]');
    if (hidden) hidden.value = el.value;
}
</script>
