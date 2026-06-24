# Cron — Alertes fiscales Cabinet SMC

## Fonctionnement

Le script génère automatiquement des alertes pour chaque entreprise active :
- Échéances fiscales dans les 15 prochains jours (IPRES, TVA, IR, IS, CFCE, Patente)
- Échéances en retard (dans les 30 derniers jours)
- Contrats CDD/Stage/Intérim expirant dans les 30 jours
- Déclarations IPRES en attente (entre le 10 et 14 du mois)
- Demandes de congés en attente depuis plus de 3 jours

Les notifications sont créées par utilisateur dans la table `notifications`.
La déduplication interne (12h) empêche les doublons entre deux exécutions.

---

## Token d'authentification

Le token est défini dans `config/app.php` via la constante `BACKUP_TOKEN`.

**Valeur par défaut** (à changer en production) :
```
smc_backup_<hash_partiel_du_chemin_APP_ROOT>
```

**Recommandé en production** : définir la variable d'environnement `CRON_TOKEN` ou `BACKUP_TOKEN` :
```bash
export CRON_TOKEN="$(php -r "echo bin2hex(random_bytes(32));")"
```

Ou l'ajouter dans le fichier `.env` / la configuration du serveur :
```
CRON_TOKEN=votre_token_secret_64_caracteres_hex
```

---

## Option 1 — Exécution en ligne de commande (recommandée)

Le script `public/cron/alertes.php` est réservé à l'exécution CLI.
Il refuse les requêtes HTTP (retourne 403).

### Commande crontab

```bash
crontab -e
```

Ajouter la ligne suivante (exécution tous les jours à 8h00) :

```cron
0 8 * * * php /Applications/XAMPP/xamppfiles/htdocs/cabinet-smc/public/cron/alertes.php --token=VOTRE_TOKEN >> /Applications/XAMPP/xamppfiles/htdocs/cabinet-smc/logs/cron.log 2>&1
```

Remplacer `VOTRE_TOKEN` par la valeur réelle du token.

### Avec variable d'environnement

```cron
0 8 * * * CRON_TOKEN=votre_token_secret php /Applications/XAMPP/xamppfiles/htdocs/cabinet-smc/public/cron/alertes.php >> /Applications/XAMPP/xamppfiles/htdocs/cabinet-smc/logs/cron.log 2>&1
```

### Test manuel

```bash
php /Applications/XAMPP/xamppfiles/htdocs/cabinet-smc/public/cron/alertes.php --token=VOTRE_TOKEN
```

---

## Option 2 — Exécution via URL HTTP

Utiliser la route sécurisée `/cron/alertes` qui appelle `CronController::alertes()`.

### Commande crontab via curl

```cron
# Toutes les nuits à 8h
0 8 * * * curl -s "https://votre-domaine/cron/alertes?token=VOTRE_TOKEN" >> /Applications/XAMPP/xamppfiles/htdocs/cabinet-smc/logs/cron.log 2>&1

# Avec header HTTP (alternative plus discrète que le token en URL)
0 8 * * * curl -s -H "X-Cron-Token: VOTRE_TOKEN" "https://votre-domaine/cron/alertes" >> /Applications/XAMPP/xamppfiles/htdocs/cabinet-smc/logs/cron.log 2>&1
```

### Réponse JSON attendue

```json
{
    "ok": true,
    "nb_alertes_generees": 12,
    "nb_entreprises_traitees": 5,
    "duree_secondes": 0.34,
    "erreurs": [],
    "timestamp": "2026-05-10T08:00:01+00:00"
}
```

---

## Logs

Les logs sont écrits dans :
```
/Applications/XAMPP/xamppfiles/htdocs/cabinet-smc/logs/cron.log
```

Format d'une ligne :
```
[2026-05-10 08:00:01] === Démarrage cron alertes fiscales ===
[2026-05-10 08:00:01]   [Entreprise Alpha] Traitement pour 2 utilisateur(s)...
[2026-05-10 08:00:01]     → user #3 : 2 alerte(s) générée(s)
[2026-05-10 08:00:02] === Terminé en 1.23s — 3 entreprise(s), 5 alerte(s) générée(s) ===
```

---

## Protection contre les exécutions parallèles

Le script CLI utilise un fichier de verrou dans le répertoire temporaire système :
```
/tmp/cabinet-smc-cron-alertes.lock
```

Si un cron est déjà en cours, le second s'arrête immédiatement avec un message `SKIP`.

---

## En production (InfinityFree / hébergement partagé)

Sur InfinityFree, les tâches cron sont configurées depuis le panneau d'administration de l'hébergeur.
Utiliser la commande curl avec l'URL complète du domaine :

```
0 8 * * * curl -s "https://votre-domaine.rf.gd/cron/alertes?token=VOTRE_TOKEN"
```

Récupérer le token avec :
```php
// Dans config/app.php, BACKUP_TOKEN est défini automatiquement.
// Pour le connaître, ajouter temporairement ce code :
var_dump(BACKUP_TOKEN);
```
