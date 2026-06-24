<?php
$exerciceActuel = $exercice;
$nomFichier = 'EtatFinancier_DGID_' . preg_replace('/[^A-Za-z0-9]/', '_', $entreprise['raison_sociale']) . '_' . $exercice . '.xlsx';
?>

<style>
.dgid-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2a5080 50%, #1e3a5f 100%);
    border-radius: 20px;
    padding: 48px 40px;
    position: relative;
    overflow: hidden;
    margin-bottom: 28px;
    box-shadow: 0 8px 40px rgba(30,58,95,0.25);
}
.dgid-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 260px; height: 260px;
    border-radius: 50%;
    background: rgba(201,169,110,0.08);
}
.dgid-hero::after {
    content: '';
    position: absolute;
    bottom: -80px; left: -40px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
}
.dgid-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(201,169,110,0.2);
    border: 1px solid rgba(201,169,110,0.4);
    border-radius: 30px;
    padding: 6px 16px;
    font-size: 15px; font-weight: 700;
    color: #c9a96e;
    letter-spacing: .8px;
    text-transform: uppercase;
    margin-bottom: 18px;
}
.dgid-title {
    font-size: 28px; font-weight: 800;
    color: white;
    margin-bottom: 10px;
    line-height: 1.2;
}
.dgid-subtitle {
    font-size: 18px;
    color: rgba(255,255,255,0.65);
    margin-bottom: 32px;
}
.dgid-download-btn {
    display: inline-flex; align-items: center; gap: 10px;
    background: linear-gradient(135deg, #c9a96e, #a8843f);
    color: white;
    padding: 14px 28px;
    border-radius: 12px;
    font-size: 18px; font-weight: 700;
    text-decoration: none;
    box-shadow: 0 4px 20px rgba(201,169,110,0.35);
    transition: all .2s;
    position: relative; z-index: 1;
}
.dgid-download-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(201,169,110,0.5);
}
.dgid-exercice-select {
    display: inline-flex; align-items: center; gap: 10px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 10px;
    padding: 10px 16px;
    margin-left: 12px;
    position: relative; z-index: 1;
}
.dgid-exercice-select select {
    background: transparent;
    border: none;
    color: white;
    font-size: 17px; font-weight: 600;
    outline: none;
    cursor: pointer;
}
.dgid-exercice-select select option { background: #1e3a5f; color: white; }
.sheets-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}
.sheet-card {
    background: white;
    border-radius: 14px;
    border: 1px solid var(--border);
    padding: 22px 20px;
    display: flex; align-items: flex-start; gap: 14px;
    transition: box-shadow .2s, transform .2s;
}
.sheet-card:hover { box-shadow: 0 6px 24px rgba(30,58,95,0.10); transform: translateY(-2px); }
.sheet-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 20px;
}
.sheet-card h4 { font-size: 17px; font-weight: 700; color: var(--navy-dark); margin-bottom: 5px; }
.sheet-card p { font-size: 15px; color: var(--text-muted); line-height: 1.5; }
.info-card {
    background: white;
    border-radius: 14px;
    border: 1px solid var(--border);
    padding: 24px;
    margin-bottom: 16px;
}
.info-card h3 { font-size: 18px; font-weight: 700; color: var(--navy-dark); margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.info-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border); }
.info-row:last-child { border-bottom: none; }
.info-label { font-size: 16px; color: var(--text-muted); }
.info-value { font-size: 16px; font-weight: 600; color: var(--navy-dark); }
</style>

<div class="page-header" style="margin-bottom:24px">
    <div>
        <h1 class="page-title">État Financier DGID</h1>
        <p class="page-subtitle">SYSCOHADA · Exercice <?= $exerciceActuel ?></p>
    </div>
</div>

<!-- Hero section -->
<div class="dgid-hero">
    <div class="dgid-badge">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
        DGID · Direction Générale des Impôts et Domaines
    </div>
    <div class="dgid-title">États Financiers SYSCOHADA</div>
    <div class="dgid-subtitle">
        <?= e($entreprise['raison_sociale']) ?> · Exercice <?= $exerciceActuel ?><br>
        Fichier Excel conforme au modèle DGID — prêt à déposer
    </div>
    <div style="display:flex;align-items:center;flex-wrap:wrap;gap:12px;position:relative;z-index:1">
        <a href="<?= APP_URL ?>/dossier/etat-financier-dgid/telecharger?id=<?= $id ?>&exercice=<?= $exerciceActuel ?>"
           class="dgid-download-btn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px;height:20px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Télécharger l'état financier DGID
        </a>
        <?php if (count($exercicesDispos) > 1): ?>
        <form method="get" action="<?= APP_URL ?>/dossier/etat-financier-dgid" style="display:inline-flex;align-items:center">
            <input type="hidden" name="id" value="<?= $id ?>">
            <div class="dgid-exercice-select">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:16px;height:16px;color:rgba(255,255,255,0.6)"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                <select name="exercice" onchange="this.form.submit()">
                    <?php foreach ($exercicesDispos as $ex): ?>
                    <option value="<?= $ex ?>" <?= $ex == $exerciceActuel ? 'selected' : '' ?>>Exercice <?= $ex ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<!-- Contenu des feuilles -->
<div class="sheets-grid">
    <div class="sheet-card">
        <div class="sheet-icon" style="background:rgba(30,58,95,0.08)">📋</div>
        <div>
            <h4>Page de garde</h4>
            <p>Informations légales de l'entreprise, exercice, identification fiscale</p>
        </div>
    </div>
    <div class="sheet-card">
        <div class="sheet-icon" style="background:rgba(201,169,110,0.1)">⚖️</div>
        <div>
            <h4>BILAN</h4>
            <p>Actif (Brut / Amort & Dépréc. / Net N / Net N-1) et Passif — refs AD à DZ</p>
        </div>
    </div>
    <div class="sheet-card">
        <div class="sheet-icon" style="background:rgba(8,145,178,0.08)">📊</div>
        <div>
            <h4>COMPTE DE RÉSULTAT</h4>
            <p>Charges et produits d'exploitation, financiers, HAO — refs TA à XI avec totaux intermédiaires</p>
        </div>
    </div>
    <div class="sheet-card">
        <div class="sheet-icon" style="background:rgba(5,150,105,0.08)">💰</div>
        <div>
            <h4>FLUX DE TRÉSORERIE</h4>
            <p>CAFG, flux opérationnels, d'investissement et de financement (TAFIRE)</p>
        </div>
    </div>
</div>

<!-- Infos entreprise + norme -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    <div class="info-card">
        <h3>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:17px;height:17px;color:var(--gold)"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/></svg>
            Dossier
        </h3>
        <div class="info-row"><span class="info-label">Raison sociale</span><span class="info-value"><?= e($entreprise['raison_sociale']) ?></span></div>
        <?php if (!empty($entreprise['forme_juridique'])): ?>
        <div class="info-row"><span class="info-label">Forme juridique</span><span class="info-value"><?= e($entreprise['forme_juridique']) ?></span></div>
        <?php endif; ?>
        <?php if (!empty($entreprise['ninea'])): ?>
        <div class="info-row"><span class="info-label">NINEA</span><span class="info-value"><?= e($entreprise['ninea']) ?></span></div>
        <?php endif; ?>
        <?php if (!empty($entreprise['registre_commerce'])): ?>
        <div class="info-row"><span class="info-label">RCCM</span><span class="info-value"><?= e($entreprise['registre_commerce']) ?></span></div>
        <?php endif; ?>
        <div class="info-row"><span class="info-label">Exercice</span><span class="info-value"><?= $exerciceActuel ?></span></div>
    </div>
    <div class="info-card">
        <h3>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:17px;height:17px;color:#7c3aed"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
            Norme SYSCOHADA
        </h3>
        <div class="info-row"><span class="info-label">Référentiel</span><span class="info-value">SYSCOHADA Révisé 2017</span></div>
        <div class="info-row"><span class="info-label">Format</span><span class="info-value">DGID Sénégal</span></div>
        <div class="info-row"><span class="info-label">Fichier</span><span class="info-value">.xlsx (Excel)</span></div>
        <div class="info-row"><span class="info-label">Feuilles</span><span class="info-value">4 (Garde, Bilan, CR, TAFIRE)</span></div>
        <div class="info-row"><span class="info-label">Calcul automatique</span><span class="info-value" style="color:#16a34a">✓ Depuis les écritures</span></div>
    </div>
</div>

<!-- Note d'avertissement -->
<div style="display:flex;align-items:flex-start;gap:12px;background:rgba(201,169,110,0.08);border:1px solid rgba(201,169,110,0.25);border-radius:12px;padding:16px 20px;margin-top:8px">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;color:var(--gold);flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
    <div style="font-size:13px;color:var(--navy);line-height:1.6">
        <strong style="color:var(--navy-dark)">Important :</strong> Vérifiez que toutes les écritures de l'exercice <?= $exerciceActuel ?> sont saisies et validées avant de générer l'état financier. Les montants sont calculés directement depuis le plan comptable SYSCOHADA.
    </div>
</div>
