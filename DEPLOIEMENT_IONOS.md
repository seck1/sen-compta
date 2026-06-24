# Déploiement SenCompta sur VPS IONOS (Docker + HTTPS)

Guide complet pour héberger SenCompta sur ton **VPS IONOS Linux M** avec le domaine **sen-compta.com**.

Pile déployée : **Caddy** (reverse proxy + HTTPS auto) → **App PHP 8.2/Apache** → **MySQL 8**, le tout en conteneurs Docker.

---

## Pré-requis (tu les as déjà)
- ✅ VPS IONOS Linux M (contrat 110827293)
- ✅ Domaine sen-compta.com
- L'IP publique de ton VPS (visible dans le panel IONOS → Serveurs & Cloud → ton VPS)

---

## Étape 1 — Pointer le domaine vers le VPS (DNS)

Dans le panel IONOS : **Domaines & SSL → sen-compta.com → DNS**, crée/modifie :

| Type  | Hôte | Valeur                  |
|-------|------|-------------------------|
| A     | `@`  | `IP_PUBLIQUE_DU_VPS`    |
| A     | `www`| `IP_PUBLIQUE_DU_VPS`    |

> La propagation DNS peut prendre de quelques minutes à quelques heures.
> Vérifie avec : `dig sen-compta.com +short` (doit renvoyer l'IP du VPS).

---

## Étape 2 — Se connecter au VPS en SSH

```bash
ssh root@IP_PUBLIQUE_DU_VPS
```
(Mot de passe / clé fournis dans le panel IONOS.)

---

## Étape 3 — Installer Docker sur le VPS

```bash
# Mise à jour
apt update && apt upgrade -y

# Docker + Docker Compose
curl -fsSL https://get.docker.com | sh
apt install -y docker-compose-plugin git

# Vérifier
docker --version && docker compose version
```

---

## Étape 4 — Récupérer le code

```bash
cd /opt
git clone https://github.com/seck1/cabinet-smc.git sencompta
cd sencompta
```

> Si le repo est privé, utilise un token GitHub ou configure une clé SSH de déploiement.

---

## Étape 5 — Configurer le .env de production

```bash
cp .env.production.example .env
nano .env
```

Remplis **obligatoirement** :
- `DB_PASS` et `DB_ROOT_PASS` → mots de passe forts (génère : `openssl rand -hex 16`)
- `BACKUP_TOKEN` → `openssl rand -hex 32`
- `CABINET_NINEA`, `CABINET_RCCM`, etc. → infos réelles du cabinet (sinon factures non conformes DGID)
- `APP_URL=https://sen-compta.com` (déjà pré-rempli)

Enregistre (Ctrl+O, Entrée, Ctrl+X).

---

## Étape 6 — Vérifier le Caddyfile

Le domaine est déjà configuré (`sen-compta.com, www.sen-compta.com`).
Caddy obtiendra le certificat SSL Let's Encrypt automatiquement au démarrage.
Rien à faire, sauf si tu changes de domaine.

---

## Étape 7 — Lancer l'application

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

- Le premier build prend quelques minutes (installe PHP, Composer, dépendances).
- MySQL s'initialise automatiquement avec `database/schema.sql`.
- Caddy obtient le certificat HTTPS (le domaine doit déjà pointer vers le VPS — étape 1).

Vérifier que tout tourne :
```bash
docker compose -f docker-compose.prod.yml ps
docker compose -f docker-compose.prod.yml logs -f   # Ctrl+C pour quitter
```

---

## Étape 8 — Tester

Ouvre **https://sen-compta.com** → la page de login SenCompta doit s'afficher en HTTPS. ✅

---

## Ouvrir le pare-feu (si besoin)

Si IONOS a un pare-feu actif, autorise les ports **80** et **443** (et **22** pour SSH)
dans le panel IONOS → ton VPS → Pare-feu / Firewall Policies.

---

## Commandes utiles (maintenance)

```bash
cd /opt/sencompta

# Mettre à jour le code et redéployer
git pull origin main
docker compose -f docker-compose.prod.yml up -d --build

# Voir les logs
docker compose -f docker-compose.prod.yml logs -f app

# Redémarrer
docker compose -f docker-compose.prod.yml restart

# Arrêter
docker compose -f docker-compose.prod.yml down

# Backup manuel de la base
docker compose -f docker-compose.prod.yml exec db \
  mysqldump -u root -p"$DB_ROOT_PASS" cabinet_smc > backup_$(date +%F).sql

# Importer une base existante (depuis ton ancienne install)
docker compose -f docker-compose.prod.yml exec -T db \
  mysql -u root -p"$DB_ROOT_PASS" cabinet_smc < mon_backup.sql
```

---

## Migrer tes données actuelles (optionnel)

Si tu veux transférer la base de ta machine locale vers le VPS :

```bash
# 1) Sur ta machine locale (XAMPP) — exporter
/Applications/XAMPP/xamppfiles/bin/mysqldump -u root cabinet_smc > sencompta_dump.sql

# 2) Copier vers le VPS
scp sencompta_dump.sql root@IP_PUBLIQUE_DU_VPS:/opt/sencompta/

# 3) Sur le VPS — importer
cd /opt/sencompta
docker compose -f docker-compose.prod.yml exec -T db \
  mysql -u root -p"$DB_ROOT_PASS" cabinet_smc < sencompta_dump.sql
```

---

## Sécurité — déjà en place
- HTTPS automatique (Caddy + Let's Encrypt) + HSTS
- MySQL non exposé à l'extérieur (réseau Docker interne uniquement)
- Uploads de logos validés (allowlist MIME) + exécution PHP désactivée dans public/logos/
- Redémarrage auto des conteneurs (`restart: unless-stopped`)

**À faire en plus côté VPS :** changer le mot de passe root SSH ou passer en clés SSH,
désactiver le login root par mot de passe, garder le système à jour (`apt upgrade`).
