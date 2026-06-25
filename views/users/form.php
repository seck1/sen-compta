<?php $editMode = $editMode ?? false; ?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title"><?= $editMode ? 'Modifier le collaborateur' : 'Nouveau collaborateur' ?></h1>
        <p class="page-subtitle"><?= $editMode ? e($user['prenom'] . ' ' . $user['nom']) : 'Créer un compte membre' ?></p>
    </div>
    <a href="<?= APP_URL ?>/users" class="btn btn-outline">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
        Retour
    </a>
</div>

<?php if ($error ?? null): ?>
<div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:10px;padding:14px 18px;color:var(--danger);margin-bottom:20px;font-size:14px">
    <?= e($error) ?>
</div>
<?php endif; ?>

<div style="display:flex;gap:20px;align-items:flex-start">

    <!-- Formulaire principal -->
    <div class="card" style="flex:1;min-width:0">
        <form method="POST" action="<?= APP_URL ?>/users/<?= $editMode ? 'update' : 'store' ?>">
            <?php if ($editMode): ?>
            <input type="hidden" name="id" value="<?= $user['id'] ?>">
            <?php endif; ?>

            <div style="margin-bottom:24px">
                <h3 style="font-size:15px;font-weight:600;color:var(--navy-dark);margin-bottom:4px">Informations personnelles</h3>
            </div>

            <div class="form-grid" style="margin-bottom:24px">
                <div class="form-field">
                    <label>Prénom <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="prenom" required placeholder="Prénom"
                           value="<?= e($user['prenom'] ?? '') ?>">
                </div>
                <div class="form-field">
                    <label>Nom <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="nom" required placeholder="Nom de famille"
                           value="<?= e($user['nom'] ?? '') ?>">
                </div>
                <div class="form-field">
                    <label>Email <span style="color:var(--danger)">*</span></label>
                    <input type="email" name="email" required placeholder="email@cabinet-smc.sn"
                           value="<?= e($user['email'] ?? '') ?>">
                </div>
                <div class="form-field">
                    <label>Téléphone</label>
                    <input type="tel" name="telephone" placeholder="+221 77 000 00 00"
                           value="<?= e($user['telephone'] ?? '') ?>">
                </div>
                <div class="form-field" style="grid-column:1/-1">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
                        <label style="margin:0">Rôle</label>
                        <button type="button" onclick="document.getElementById('modal-roles').style.display='flex'"
                            style="width:20px;height:20px;border-radius:50%;border:1.5px solid var(--text-muted);background:none;color:var(--text-muted);font-size:11px;font-weight:700;cursor:pointer;line-height:1;flex-shrink:0"
                            title="Voir les droits de chaque rôle">?</button>
                    </div>
                        <?php
                        $roles = [
                            'admin' => [
                                'label' => 'Administrateur',
                                'color' => '#1e3a5f',
                                'desc'  => 'Accès total au cabinet. Gère l\'équipe, la facturation, clôture les exercices.',
                                'droits'=> ['✅ Accès total tous dossiers', '✅ Validation et invalidation écritures', '✅ Honoraires / Facturation', '✅ Gestion collaborateurs', '✅ Clôture exercice', '✅ Journal des actions'],
                            ],
                            'superviseur' => [
                                'label' => 'Superviseur',
                                'color' => '#b8923f',
                                'desc'  => 'Contrôle et valide le travail des collaborateurs. Voit tous les dossiers.',
                                'droits'=> ['✅ Saisie et validation écritures', '✅ Tous les dossiers (sans assignation)', '✅ Planning toute l\'équipe', '✅ Export comptable', '❌ Invalider une écriture validée', '❌ Honoraires / Facturation'],
                            ],
                            'collaborateur' => [
                                'label' => 'Collaborateur',
                                'color' => '#0891b2',
                                'desc'  => 'Saisit des écritures en brouillon sur ses dossiers assignés. Ne peut pas valider.',
                                'droits'=> ['✅ Saisie écritures (brouillon)', '✅ Ses dossiers assignés uniquement', '✅ Ses missions planning', '❌ Validation écritures', '❌ Tous les dossiers', '❌ Honoraires / Facturation'],
                            ],
                        ];
                        $roleActuel = $user['role'] ?? 'collaborateur';
                        $isEditingAdmin = $editMode && $roleActuel === 'admin';
                        // Grille 3 colonnes si admin, 2 sinon
                        ?>
                    <div style="display:grid;grid-template-columns:<?= $isEditingAdmin ? '420px' : '1fr 1fr' ?>;gap:12px;margin-top:8px">
                        <?php foreach ($roles as $val => $r):
                            $isCurrentRole = $roleActuel === $val;
                            // Si on modifie un admin : afficher seulement la carte admin en lecture seule
                            if ($isEditingAdmin && $val !== 'admin') continue;
                            // Si on crée/modifie un non-admin : masquer la carte admin
                            if (!$isEditingAdmin && $val === 'admin') continue;
                        ?>
                        <label style="<?= $isEditingAdmin ? 'cursor:default;max-width:420px;' : 'cursor:pointer;' ?>border:2px solid <?= $isCurrentRole ? $r['color'] : 'var(--border)' ?>;border-radius:12px;padding:14px;display:block;transition:border-color .15s<?= !$isEditingAdmin ? ';' : '' ?>"
                            <?php if (!$isEditingAdmin): ?>
                            onclick="this.querySelector('input').click();document.querySelectorAll('.role-card').forEach(c=>c.style.borderColor='var(--border)');this.style.borderColor='<?= $r['color'] ?>';"
                            <?php endif; ?>
                            class="role-card">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                                <input type="radio" name="role" value="<?= $val ?>" <?= $isCurrentRole?'checked':'' ?> <?= $isEditingAdmin?'disabled':'' ?> style="accent-color:<?= $r['color'] ?>;width:16px;height:16px">
                                <?php if ($isEditingAdmin): ?>
                                <input type="hidden" name="role" value="admin">
                                <?php endif; ?>
                                <span style="font-weight:700;font-size:14px;color:<?= $r['color'] ?>"><?= $r['label'] ?></span>
                                <?php if ($isEditingAdmin): ?>
                                <span style="font-size:11px;background:rgba(30,58,95,0.08);color:var(--navy);padding:2px 8px;border-radius:20px;margin-left:auto">Rôle fixe — non modifiable</span>
                                <?php endif; ?>
                            </div>
                            <p style="font-size:12px;color:var(--text-muted);margin:0 0 10px;line-height:1.5"><?= $r['desc'] ?></p>
                            <ul style="margin:0;padding:0;list-style:none;font-size:11.5px;display:flex;flex-direction:column;gap:3px">
                                <?php foreach ($r['droits'] as $d): ?>
                                <li style="color:<?= str_starts_with($d,'✅') ? '#1f6e4e' : '#9ca3af' ?>"><?= $d ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-field">
                    <label><?= $editMode ? 'Nouveau mot de passe (laisser vide = inchangé)' : 'Mot de passe *' ?></label>
                    <input type="password" name="password" placeholder="••••••••" <?= !$editMode ? 'required' : '' ?> autocomplete="new-password">
                </div>
                <?php if ($editMode): ?>
                <div class="form-field" style="display:flex;align-items:center;gap:10px;padding-top:28px">
                    <input type="checkbox" name="actif" id="actif" <?= ($user['actif'] ?? 1) ? 'checked' : '' ?> style="width:18px;height:18px;accent-color:var(--navy)">
                    <label for="actif" style="font-size:14px;cursor:pointer">Compte actif</label>
                </div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-gold">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    <?= $editMode ? 'Enregistrer' : 'Créer le compte' ?>
                </button>
                <a href="<?= APP_URL ?>/users" class="btn btn-outline">Annuler</a>
            </div>
        </form>
    </div>

<!-- Modal comparaison des 3 rôles (position:fixed, hors flux) -->
<div id="modal-roles" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:2000;align-items:center;justify-content:center;padding:20px" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:white;border-radius:18px;padding:32px;max-width:860px;width:100%;box-shadow:0 24px 80px rgba(0,0,0,0.25);max-height:90vh;overflow-y:auto">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
            <h2 style="margin:0;font-size:20px;color:var(--navy-dark)">Comparaison des rôles</h2>
            <button type="button" onclick="document.getElementById('modal-roles').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--text-muted);line-height:1">×</button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
            <?php
            $allRoles = [
                'admin' => [
                    'label' => 'Administrateur',
                    'color' => '#1e3a5f',
                    'desc'  => 'Gérant ou associé du cabinet. Accès total.',
                    'droits'=> [
                        '✅ Tous les dossiers entreprises',
                        '✅ Saisie des écritures',
                        '✅ Validation des écritures',
                        '✅ Invalider une écriture validée',
                        '✅ Clôture d\'exercice',
                        '✅ Gestion des collaborateurs',
                        '✅ Honoraires & Facturation',
                        '✅ Suivi des paiements',
                        '✅ Journal des actions (audit)',
                        '✅ Planning toute l\'équipe',
                        '✅ Import CSV, Export comptable',
                        '✅ Rapprochement bancaire',
                    ],
                ],
                'superviseur' => [
                    'label' => 'Superviseur',
                    'color' => '#b8923f',
                    'desc'  => 'Expert-comptable associé ou chef de mission.',
                    'droits'=> [
                        '✅ Tous les dossiers entreprises',
                        '✅ Saisie des écritures',
                        '✅ Validation des écritures',
                        '❌ Invalider une écriture validée',
                        '❌ Clôture d\'exercice',
                        '❌ Gestion des collaborateurs',
                        '❌ Honoraires & Facturation',
                        '❌ Suivi des paiements',
                        '❌ Journal des actions (audit)',
                        '✅ Planning toute l\'équipe',
                        '✅ Import CSV, Export comptable',
                        '✅ Rapprochement bancaire',
                    ],
                ],
                'collaborateur' => [
                    'label' => 'Collaborateur',
                    'color' => '#0891b2',
                    'desc'  => 'Salarié du cabinet en charge d\'un portefeuille de dossiers.',
                    'droits'=> [
                        '❌ Tous les dossiers (assignation requise)',
                        '✅ Saisie des écritures (brouillon)',
                        '❌ Validation des écritures',
                        '❌ Invalider une écriture validée',
                        '❌ Clôture d\'exercice',
                        '❌ Gestion des collaborateurs',
                        '❌ Honoraires & Facturation',
                        '❌ Suivi des paiements',
                        '❌ Journal des actions (audit)',
                        '✅ Ses missions planning uniquement',
                        '✅ Export comptable',
                        '✅ Rapprochement bancaire',
                    ],
                ],
            ];
            foreach ($allRoles as $rval => $r):
            ?>
            <div style="border:2px solid <?= $r['color'] ?>;border-radius:12px;padding:18px">
                <div style="display:inline-block;padding:3px 12px;background:<?= $r['color'] ?>;color:white;border-radius:20px;font-size:12px;font-weight:700;margin-bottom:10px"><?= $r['label'] ?></div>
                <p style="font-size:12px;color:var(--text-muted);margin:0 0 14px;line-height:1.5"><?= $r['desc'] ?></p>
                <ul style="margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:5px">
                    <?php foreach ($r['droits'] as $d): ?>
                    <li style="font-size:12px;color:<?= str_starts_with($d,'✅') ? '#18583f' : '#9ca3af' ?>"><?= $d ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="margin-top:20px;padding:14px;background:rgba(201,169,110,0.08);border:1px solid rgba(201,169,110,0.3);border-radius:10px;font-size:12.5px;color:var(--text-muted)">
            <strong style="color:var(--navy-dark)">Note :</strong> Le rôle Admin ne peut être attribué que directement en base de données. Un admin ne peut pas être rétrogradé depuis l'interface.
        </div>
    </div>
</div>
<?php if ($editMode): ?><div style="width:360px;flex-shrink:0;display:flex;flex-direction:column;gap:20px">

    <!-- Assignation dossiers -->
    <div class="card" style="margin:0">
        <div style="margin-bottom:20px">
            <h3 style="font-size:15px;font-weight:600;color:var(--navy-dark);margin-bottom:4px">Dossiers assignés</h3>
            <p style="font-size:13px;color:var(--text-muted)">Accès aux entreprises clientes</p>
        </div>

        <!-- Dossiers actuels -->
        <?php if (!empty($assignments)): ?>
        <div style="margin-bottom:20px;display:flex;flex-direction:column;gap:8px">
            <?php foreach ($assignments as $a): ?>
            <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--bg);border-radius:10px">
                <div style="width:32px;height:32px;border-radius:8px;background:<?= e($a['couleur']) ?>;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:white;flex-shrink:0">
                    <?= strtoupper(substr($a['raison_sociale'],0,2)) ?>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:13px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($a['raison_sociale']) ?></div>
                    <div style="font-size:11px;color:var(--text-muted)"><?= ucfirst($a['role']) ?></div>
                </div>
                <a href="<?= APP_URL ?>/users/assign?remove=<?= $a['id'] ?>&user_id=<?= $user['id'] ?>"
                   style="color:var(--danger);font-size:18px;text-decoration:none;line-height:1;flex-shrink:0"
                   title="Retirer">&times;</a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px">Aucun dossier assigné</p>
        <?php endif; ?>

        <!-- Ajouter un dossier -->
        <form method="POST" action="<?= APP_URL ?>/users/assign">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            <div class="form-field" style="margin-bottom:12px">
                <label>Assigner un dossier</label>
                <select name="entreprise_id">
                    <option value="">— Choisir une entreprise —</option>
                    <?php foreach ($entreprises as $ent): ?>
                    <option value="<?= $ent['id'] ?>"><?= e($ent['raison_sociale']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field" style="margin-bottom:14px">
                <label>Niveau d'accès</label>
                <select name="role">
                    <option value="lecteur">Lecture seule</option>
                    <option value="saisie" selected>Saisie</option>
                    <option value="superviseur">Superviseur</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Assigner le dossier
            </button>
        </form>
    </div>

    <!-- Historique d'activité -->
    <div class="card" style="margin:0">

        <!-- Header + dernière connexion -->
        <div style="margin-bottom:14px">
            <h3 style="font-size:14px;font-weight:600;color:var(--navy-dark);margin-bottom:3px">Activité du collaborateur</h3>
            <p style="font-size:12px;color:var(--text-muted)">
                Dernière connexion :
                <strong style="color:<?= $user['derniere_connexion'] ? 'var(--navy-dark)' : '#ef4444' ?>">
                    <?= $user['derniere_connexion'] ? date('d/m/Y à H:i', strtotime($user['derniere_connexion'])) : 'Jamais connecté' ?>
                </strong>
            </p>
        </div>

        <!-- KPIs -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:16px">
            <div style="background:rgba(30,58,95,0.06);border-radius:8px;padding:10px;text-align:center">
                <div style="font-size:20px;font-weight:700;color:var(--navy-dark)"><?= $stats['nb_ecritures'] ?></div>
                <div style="font-size:10.5px;color:var(--text-muted);margin-top:2px">Écritures saisies</div>
            </div>
            <div style="background:rgba(201,169,110,0.08);border-radius:8px;padding:10px;text-align:center">
                <div style="font-size:20px;font-weight:700;color:var(--gold-dark)"><?= $stats['nb_missions'] ?></div>
                <div style="font-size:10.5px;color:var(--text-muted);margin-top:2px">Missions</div>
            </div>
            <div style="background:rgba(99,102,241,0.07);border-radius:8px;padding:10px;text-align:center">
                <div style="font-size:20px;font-weight:700;color:#6366f1"><?= $stats['nb_actions'] ?></div>
                <div style="font-size:10.5px;color:var(--text-muted);margin-top:2px">Actions totales</div>
            </div>
        </div>

        <!-- Timeline -->
        <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px">10 dernières actions</div>
        <?php if (empty($activites)): ?>
        <div style="text-align:center;padding:24px;color:var(--text-muted);font-size:13px">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:32px;height:32px;margin:0 auto 8px;display:block;opacity:.4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Aucune action enregistrée
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:0">
            <?php
            $actionMap = [
                'ECRITURE_VALIDER'        => ['dot'=>'#1f6e4e', 'label'=>'Écriture validée'],
                'ECRITURE_INVALIDER'      => ['dot'=>'#ef4444', 'label'=>'Écriture invalidée'],
                'ECRITURE_VALIDER_TOUT'   => ['dot'=>'#1f6e4e', 'label'=>'Toutes écritures validées'],
                'ECRITURE_CREER'          => ['dot'=>'#1f6e4e', 'label'=>'Écriture créée'],
                'ECRITURE_MODIFIER'       => ['dot'=>'#f59e0b', 'label'=>'Écriture modifiée'],
                'ECRITURE_SUPPRIMER'      => ['dot'=>'#ef4444', 'label'=>'Écriture supprimée'],
                'LOGIN'                   => ['dot'=>'#6366f1', 'label'=>'Connexion'],
            ];
            foreach ($activites as $i => $act):
                $ac = $actionMap[$act['action']] ?? ['dot'=>'#94a3b8', 'label'=>str_replace('_',' ', $act['action'])];
            ?>
            <div style="display:flex;gap:10px;align-items:flex-start;padding:7px 0;<?= $i < count($activites)-1 ? 'border-bottom:1px solid rgba(0,0,0,0.05)' : '' ?>">
                <div style="width:8px;height:8px;border-radius:50%;background:<?= $ac['dot'] ?>;flex-shrink:0;margin-top:5px;box-shadow:0 0 5px <?= $ac['dot'] ?>88"></div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:12px;font-weight:500;color:var(--navy-dark)"><?= $ac['label'] ?></div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:1px;display:flex;gap:5px;flex-wrap:wrap">
                        <?php if ($act['raison_sociale']): ?>
                        <span style="background:rgba(30,58,95,0.07);padding:1px 7px;border-radius:20px"><?= e($act['raison_sociale']) ?></span>
                        <?php endif; ?>
                        <?php if ($act['details']): ?>
                        <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:150px"><?= e($act['details']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="font-size:11px;color:var(--text-muted);white-space:nowrap;flex-shrink:0">
                    <?= date('d/m H:i', strtotime($act['created_at'])) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Lien journal complet -->
        <a href="<?= APP_URL ?>/audit-log?user_id=<?= $user['id'] ?>" style="display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;margin-top:16px;background:rgba(30,58,95,0.05);border:1px solid rgba(30,58,95,0.1);border-radius:10px;font-size:13px;font-weight:500;color:var(--navy);text-decoration:none;transition:background .2s" onmouseover="this.style.background='rgba(30,58,95,0.1)'" onmouseout="this.style.background='rgba(30,58,95,0.05)'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            Voir le journal complet
        </a>
    </div>

    </div><?php endif; ?>
</div><!-- /grid -->
