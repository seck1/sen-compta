FROM php:8.2-apache

# Extensions PHP requises (pdo_mysql pour la base, gd/zip pour PhpSpreadsheet & uploads)
RUN apt-get update && apt-get install -y \
        libzip-dev libpng-dev libjpeg-dev libfreetype6-dev unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd \
    && rm -rf /var/lib/apt/lists/*

# Composer (pour installer vendor/ qui est gitignore)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache : rewrite + headers + .htaccess autorise
RUN a2enmod rewrite headers
RUN sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

# Racine web = /public (le routeur de production : public/index.php)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Code de l'application
COPY . /var/www/html/
WORKDIR /var/www/html

# Dependances PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction || true

RUN chown -R www-data:www-data /var/www/html

# Entrypoint : corrige les permissions des volumes montes (logos/uploads) AU DEMARRAGE.
# Les volumes Docker ecrasent les permissions de l'image ; il faut donc le faire au runtime.
RUN printf '#!/bin/sh\nchown -R www-data:www-data /var/www/html/public/logos /var/www/html/public/uploads 2>/dev/null || true\nchmod -R 775 /var/www/html/public/logos /var/www/html/public/uploads 2>/dev/null || true\nexec "$@"\n' > /usr/local/bin/fix-perms.sh \
    && chmod +x /usr/local/bin/fix-perms.sh

ENTRYPOINT ["/usr/local/bin/fix-perms.sh"]
CMD ["apache2-foreground"]
EXPOSE 80
