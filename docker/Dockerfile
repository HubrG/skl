FROM php:8.2-apache

ENV DEBIAN_FRONTEND noninteractive

RUN sed -i 's/-msign-return-address=all//' /etc/apache2/conf-enabled/security.conf && \
    sed -i 's/-msign-return-address=all//' /usr/local/bin/docker-php-ext-configure && \
    sed -i 's/-msign-return-address=all//' /usr/local/bin/docker-php-ext-install && \
    sed -i 's/-msign-return-address=all//' /usr/local/bin/docker-php-ext-enable

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_mysql gd exif intl zip

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
    cp composer.phar /usr/local/bin/composer

RUN apt update && apt install -yqq nodejs npm

COPY . /var/www/

COPY ./docker/apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www

RUN composer install 

RUN apt update && apt install -yqq curl && \
    curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash && \
    export NVM_DIR="$HOME/.nvm" && \
    [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh" && \
    nvm install 18.15.0 && \
    node -v && \
    cd /var/www && \
    rm -rf node_modules && \
    npm install --force && \
    npm run build

EXPOSE 80

CMD ["apache2-foreground"]
