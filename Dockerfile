# Utilisation de l'image Distroless PHP8
FROM php:8.2-fpm

ARG TARGETPLATFORM

# Installation des dépendances nécessaires
RUN apt-get update && apt-get install -y \
    curl \
    git \
    libicu-dev \
    libzip-dev \
    unzip \
    libpng-dev \
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

ENV PORT 8080
ENV HOST 0.0.0.0

EXPOSE ${PORT:-8080}

# Définition de la commande pour démarrer l'application
CMD ["php", "bin/console", "run", "--server=0.0.0.0:${PORT:-8080}", "apache-foreground"]
