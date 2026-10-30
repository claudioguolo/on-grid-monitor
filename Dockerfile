FROM php:8.3-apache
RUN apt-get update && apt-get install -y --no-install-recommends libicu-dev libonig-dev libzip-dev unzip && docker-php-ext-install intl mbstring mysqli pdo_mysql zip && a2enmod rewrite && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader && mkdir -p writable/cache writable/logs writable/session writable/uploads writable/debugbar && chown -R www-data:www-data writable && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
EXPOSE 80
