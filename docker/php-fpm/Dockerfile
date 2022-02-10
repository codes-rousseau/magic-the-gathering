FROM debian:buster
MAINTAINER Thomas DUBUFFET <thomas.dubuffet@gmail.com>

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        lsb-release \
        apt-transport-https \
        ca-certificates \
        wget

# Les paquets PHP version 7.4 ne sont pas disponibles dans les serveurs miroirs officiels de Debian 10 (buster).
# Par conséquent, pour installer PHP il faut ajouter le dépôt de sury ci-dessous.
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" |tee /etc/apt/sources.list.d/php.list

# Installation des différents paquets linux
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        # PHP 7.4
        php7.4-fpm \
        php7.4-opcache \
        php7.4-common \
        php7.4-curl \
        php7.4-gd \
        php7.4-intl \
        php7.4-mbstring \
        php7.4-xml \
        php7.4-zip \
        php7.4-sqlite \
        # Helpers
        zip \
        unzip \
        vim \
        curl \
        telnet \
        supervisor

# Gestion du Timezone
RUN ln -snf /usr/share/zoneinfo/Europe/Paris /etc/localtime && \
    echo 'Europe/Paris' > /etc/timezone && \
    date

# Configuration de PHP 7.4
RUN touch /etc/php/7.4/php.ini && \
    ln -s /etc/php/7.4/php.ini /etc/php/7.4/fpm/conf.d/symfony.ini && \
    ln -s /etc/php/7.4/php.ini /etc/php/7.4/cli/conf.d/symfony.ini

# Pour passer les variables d'environnement :
# https://medium.com/@tomahock/passing-system-environment-variables-to-php-fpm-when-using-nginx-a70045370fad
RUN sed -i \
    -e 's/^listen =.*$/listen = 9000/' \
    -e 's/^;\(clear_env = no\)$/\1/' \
    /etc/php/7.4/fpm/pool.d/www.conf

# Installation de composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Gestion des droits d'utilisateur
RUN mkdir -p /var/www && \
    usermod --non-unique --uid 1000 www-data && \
    groupmod --non-unique --gid 1000 www-data && \
    usermod --append --groups dialout www-data && \
    chown -Rf www-data:www-data /var/www

# Espace de travail
WORKDIR /var/www/symfony

# Lancement de supervisor
RUN mkdir -p /var/run/php && \
    mkdir -p /var/log/supervisor
CMD ["/usr/bin/supervisord"]

# Raccourci pour accéder aux commandes Symfony
RUN echo 'alias sf="php bin/console"' >> /var/www/.bashrc