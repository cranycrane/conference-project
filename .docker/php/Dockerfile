FROM php:8.1-apache

ENV APACHE_DOCUMENT_ROOT /var/www/html/www


RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN a2enmod rewrite

RUN apt-get update
RUN apt-get -y install libicu-dev lsb-release ca-certificates apt-transport-https wget git zip unzip zlib1g-dev libpng-dev


RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-install gd
RUN docker-php-ext-enable mysqli pdo pdo_mysql gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer self-update
ENV COMPOSER_ALLOW_SUPERUSER=1

CMD sh -c "\
	composer install && composer dump-autoload && \
	bin/console orm:schema-tool:update --force && \
	NETTE_DEBUG=1 php bin/console doctrine:fixtures:load --no-interaction && \
    apache2-foreground"
