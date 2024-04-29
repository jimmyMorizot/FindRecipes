FROM dunglas/frankenphp

ENV SERVER_NAME=your-domain-name.example.com
ENV APP_RUNTIME=Runtime\\FrankenPhpSymfony\\Runtime
ENV APP_ENV=prod
ENV FRANKENPHP_CONFIG="worker ./public/index.php"

COPY . /app/

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Installer les dépendances du projet
RUN composer install --no-dev --optimize-autoloader

# Vider le cache
RUN APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear