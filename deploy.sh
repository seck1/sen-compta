#!/bin/bash
# Script de déploiement Cabinet SMC
set -e

echo "=== Déploiement Cabinet SMC ==="

# Vérifications
if [ ! -f ".env" ]; then
    echo "ERREUR : fichier .env manquant. Copier .env.example et remplir les valeurs."
    exit 1
fi

# Charger les variables .env
export $(grep -v '^#' .env | grep -v '^$' | xargs)

# Pull git
git pull origin main

# Migrations DB (exécuter tous les fichiers SQL dans database/migrations/)
echo "Application des migrations..."
for f in database/migrations/*.sql; do
    [ -f "$f" ] || continue
    echo "  → $f"
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$f"
done

# Permissions
chmod -R 755 public/
chmod -R 770 backups/ archives/ log/ 2>/dev/null || true
chown -R www-data:www-data .

# Cache PHP (si opcache)
php -r "opcache_reset();" 2>/dev/null || true

echo "=== Déploiement terminé ==="
