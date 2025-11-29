# backend/Dockerfile

# Alap image
FROM php:8.3-fpm-alpine

# Telepítjük a Composer és a git számára szükséges csomagokat
RUN apk add --no-cache git

# --- ADD EZT A BLOKKOT IDE ---
# Telepítjük a pdo_mysql kiterjesztést
RUN apk add --no-cache mysql-client
RUN docker-php-ext-install pdo_mysql
# ------------------------------

# Telepítjük a Composer-t globálisan
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# A munkakönyvtár beállítása
WORKDIR /var/www/html
