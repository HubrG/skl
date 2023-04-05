# Utilisez une image de base avec PHP et Nginx
FROM php:8.2-fpm

# Installation des dépendances nécessaires
RUN apt-get update && apt-get install -y \
    curl \
    git \
    libicu-dev \
    libzip-dev \
    unzip \
    libpng-dev \
    nginx \
    gettext-base \
    && docker-php-ext-install -j$(nproc) intl pdo_mysql zip gd \
    && pecl install apcu \
    && docker-php-ext-enable apcu

# Installation de Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Installation de Node.js 18
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y nodejs

# Configuration de l'application
WORKDIR /app
COPY . /app

RUN composer install 

RUN rm -rf node_modules/ && npm ci

RUN npm run build

# Copiez la configuration Nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf.template

ENV PORT 8080
ENV HOST 0.0.0.0

EXPOSE ${PORT:-8080}

# Définition de la commande pour démarrer l'application
CMD ["sh", "-c", "envsubst '$PORT' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf && php-fpm & nginx -g 'daemon off;'"]
