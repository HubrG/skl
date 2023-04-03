FROM php:8.2-apache

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

RUN cd /var/www && \
    rm -rf node_modules && \
    npm install --force && \
    npm run build

EXPOSE 80

CMD ["apache2-foreground"]
