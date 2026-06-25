<?php
$fmt = fn($n) => number_format((float)$n, 0, ',', ' ');
// Bilan = classes 1-5 ; Resultat = classes 6-7
$classesAttendues = $type === 'resultat' ? ['6','7'] : ['1','2','3','4','5'];
$rows = array_filter($lignes, fn($l) => in_array((string)$l['classe'], $classesAttendues, true));
$titre = $type === 'resultat' ? 'Compte de résultat' : 'Bilan';
$totDebit = $totCredit = 0;
foreach ($rows as $r) { $totDebit += (float)$r['debit']; $totCredit += (float)$r['credit']; }
?>

<div style="display:flex;align-items:center;gap:14px;margin-bottom:6px">
  <a href="<?= APP_URL ?>/portail" style="text-decoration:none;color:var(--muted);font-size:10pt;font-weight:600">← Retour</a>
</div>
<h1 style="font-size:18pt;font-weight:800;letter-spacing:-.4px;margin-bottom:4px"><?= $titre ?></h1>
<p style="font-size:10.5pt;color:var(--muted);margin-bottom:24px"><?= e($entreprise['raison_sociale']) ?> · Exercice <?= $exercice ?></p>

<div class="pc-card">
  <?php if (empty($rows)): ?>
    <p style="color:var(--muted);font-size:10.5pt;padding:10px 0">Aucune donnée disponible pour cet exercice. Les états seront visibles une fois la comptabilité saisie et validée par votre cabinet.</p>
  <?php else: ?>
  <div style="overflow-x:auto">
  <table>
    <thead><tr><th>Compte</th><th>Intitulé</th><th style="text-align:right">Débit</th><th style="text-align:right">Crédit</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td style="font-family:monospace;font-weight:600"><?= e($r['numero']) ?></td>
        <td><?= e($r['intitule']) ?></td>
        <td style="text-align:right;color:#c0392b"><?= (float)$r['debit'] ? $fmt($r['debit']) : '—' ?></td>
        <td style="text-align:right;color:#1f6e4e"><?= (float)$r['credit'] ? $fmt($r['credit']) : '—' ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr style="border-top:2px solid var(--line);font-weight:800">
        <td colspan="2" style="padding:12px;font-size:10.5pt">TOTAL</td>
        <td style="text-align:right;padding:12px"><?= $fmt($totDebit) ?></td>
        <td style="text-align:right;padding:12px"><?= $fmt($totCredit) ?></td>
      </tr>
    </tfoot>
  </table>
  </div>
  <p style="font-size:9pt;color:var(--muted);margin-top:14px">Document à titre informatif. Seuls les états officiels validés par votre cabinet font foi.</p>
  <?php endif; ?>
</div>
