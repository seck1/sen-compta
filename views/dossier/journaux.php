<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Journaux comptables</h1>
        <p class="page-subtitle">Exercice <?= $entreprise['exercice_courant'] ?></p>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
    <?php foreach ($journaux as $j): ?>
    <a href="<?= APP_URL ?>/dossier/ecritures?id=<?= $entreprise['id'] ?>&journal=<?= $j['code'] ?>"
       class="card" style="text-decoration:none;transition:transform 0.2s,box-shadow 0.2s;padding:0;overflow:hidden"
       onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 28px rgba(0,0,0,0.09)'"
       onmouseleave="this.style.transform='';this.style.boxShadow=''">
        <div style="height:4px;background:var(--ent-color)"></div>
        <div style="padding:20px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                <div style="width:44px;height:44px;border-radius:11px;background:var(--ent-color);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:white">
                    <?= e($j['code']) ?>
                </div>
                <span class="badge badge-info"><?= ucfirst(str_replace('_',' ',$j['type'])) ?></span>
            </div>
            <div style="font-size:13px;font-weight:600;color:var(--navy-dark);margin-bottom:14px"><?= e($j['libelle']) ?></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div style="background:var(--bg);border-radius:8px;padding:10px">
                    <div style="font-size:13px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:3px">Écritures</div>
                    <div style="font-size:20px;font-family:'Cormorant Garamond',serif;font-weight:600;color:var(--navy-dark)"><?= $j['nb_ecritures'] ?></div>
                </div>
                <div style="background:var(--bg);border-radius:8px;padding:10px">
                    <div style="font-size:13px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:3px">Total</div>
                    <div style="font-size:14px;font-weight:600;color:var(--danger)"><?= number_format($j['total_debit'],0,',',' ') ?></div>
                </div>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
