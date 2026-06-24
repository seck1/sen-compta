<div class="page-header">
    <div>
        <div class="page-title" style="font-family:'Cormorant Garamond',serif;font-size:28px">Nouvelle facture</div>
    </div>
    <a href="<?= APP_URL ?>/honoraires" class="btn btn-outline btn-sm">Retour</a>
</div>

<div style="max-width:900px">
    <form method="POST" action="<?= APP_URL ?>/honoraires/store">

        <div class="card" style="margin-bottom:20px">
            <div style="font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:400;color:var(--navy-dark);margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)">
                Informations générales
            </div>
            <div class="form-grid">
                <div class="form-field">
                    <label>Client *</label>
                    <select name="entreprise_id" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach($entreprises as $ent): ?>
                        <option value="<?= $ent['id'] ?>"><?= e($ent['raison_sociale']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field">
                    <label>N° Facture</label>
                    <input type="text" name="numero_facture" value="<?= e($numero_auto) ?>" required>
                </div>
                <div class="form-field">
                    <label>Date facture *</label>
                    <input type="date" name="date_facture" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-field">
                    <label>Date d'échéance</label>
                    <input type="date" name="date_echeance">
                </div>
                <div class="form-field" style="grid-column:1/-1">
                    <label>Description</label>
                    <textarea name="description" rows="2" placeholder="Objet de la facture..."></textarea>
                </div>
            </div>
        </div>

        <!-- Lignes -->
        <div class="card" style="margin-bottom:20px">
            <div style="font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:400;color:var(--navy-dark);margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)">
                Prestations
            </div>

            <table id="lignes-table" style="width:100%;border-collapse:collapse;margin-bottom:12px">
                <thead>
                    <tr style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">
                        <th style="padding:8px 12px;text-align:left;font-weight:600">Libellé</th>
                        <th style="padding:8px 12px;text-align:right;font-weight:600;width:100px">Qté</th>
                        <th style="padding:8px 12px;text-align:right;font-weight:600;width:160px">Prix unitaire HT</th>
                        <th style="padding:8px 12px;text-align:right;font-weight:600;width:160px">Montant HT</th>
                        <th style="padding:8px;width:40px"></th>
                    </tr>
                </thead>
                <tbody id="lignes-body">
                    <tr class="ligne-row">
                        <td style="padding:6px 12px"><input type="text" name="ligne_libelle[]" class="ligne-libelle" placeholder="Description de la prestation" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:13px"></td>
                        <td style="padding:6px 12px"><input type="number" name="ligne_qte[]" value="1" class="ligne-qte" step="0.5" min="0" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:13px;text-align:right"></td>
                        <td style="padding:6px 12px"><input type="number" name="ligne_pu[]" value="0" class="ligne-pu" step="1" min="0" style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:13px;text-align:right"></td>
                        <td style="padding:6px 12px;text-align:right;font-family:monospace;font-weight:600" class="ligne-total">0</td>
                        <td style="padding:6px 8px;text-align:center"><button type="button" onclick="removeLigne(this)" style="background:none;border:none;color:var(--danger);cursor:pointer;font-size:18px;line-height:1">×</button></td>
                    </tr>
                </tbody>
            </table>

            <button type="button" onclick="addLigne()" class="btn btn-outline btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Ajouter une ligne
            </button>
        </div>

        <!-- Totaux -->
        <div class="card" style="margin-bottom:24px">
            <div style="display:flex;justify-content:flex-end">
                <div style="min-width:320px">
                    <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);font-size:13.5px">
                        <span>Montant HT</span>
                        <span id="total-ht" style="font-family:monospace;font-weight:600">0 FCFA</span>
                        <input type="hidden" name="montant_ht" id="montant-ht-input" value="0">
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border);font-size:13.5px">
                        <span>TVA</span>
                        <div style="display:flex;align-items:center;gap:6px">
                            <select name="taux_tva" id="taux-tva" onchange="recalc()" style="padding:4px 8px;border:1px solid var(--border);border-radius:6px;font-size:12px">
                                <option value="18">18 %</option>
                                <option value="0">Exonéré</option>
                            </select>
                            <span id="total-tva" style="font-family:monospace">0 FCFA</span>
                        </div>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:12px 0;font-size:16px;font-weight:700">
                        <span>Total TTC</span>
                        <span id="total-ttc" style="font-family:monospace;color:var(--navy-dark)">0 FCFA</span>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Enregistrer la facture
        </button>
    </form>
</div>

<script>
function fmt(n) { return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA'; }

function recalc() {
    let ht = 0;
    document.querySelectorAll('.ligne-row').forEach(row => {
        const qte = parseFloat(row.querySelector('.ligne-qte').value) || 0;
        const pu  = parseFloat(row.querySelector('.ligne-pu').value)  || 0;
        const total = qte * pu;
        row.querySelector('.ligne-total').textContent = fmt(total);
        ht += total;
    });
    const taux = parseFloat(document.getElementById('taux-tva').value) || 0;
    const tva  = ht * taux / 100;
    const ttc  = ht + tva;
    document.getElementById('total-ht').textContent  = fmt(ht);
    document.getElementById('total-tva').textContent = fmt(tva);
    document.getElementById('total-ttc').textContent = fmt(ttc);
    document.getElementById('montant-ht-input').value = Math.round(ht);
}

function addLigne() {
    const tbody = document.getElementById('lignes-body');
    const newRow = tbody.rows[0].cloneNode(true);
    newRow.querySelectorAll('input').forEach(i => { if(i.type !== 'button') i.value = i.type === 'number' ? (i.classList.contains('ligne-qte') ? 1 : 0) : ''; });
    newRow.querySelector('.ligne-total').textContent = '0';
    tbody.appendChild(newRow);
    bindLigne(newRow);
}

function removeLigne(btn) {
    const body = document.getElementById('lignes-body');
    if (body.rows.length <= 1) return;
    btn.closest('tr').remove();
    recalc();
}

function bindLigne(row) {
    row.querySelectorAll('input.ligne-qte, input.ligne-pu').forEach(i => i.addEventListener('input', recalc));
}

document.querySelectorAll('.ligne-row').forEach(bindLigne);
</script>
