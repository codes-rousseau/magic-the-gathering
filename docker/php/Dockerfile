# See https://github.com/docker-library/php/blob/master/7.1/fpm/Dockerfile
FROM composer:latest AS composer
FROM php:7.4-fpm

# Variables d'environnement
ARG TIMEZONE
ARG LANGUAGE
ARG USER_ID
ARG GROUP_ID
ARG USER_NAME
ARG GROUP_NAME
ARG SYS_PATH

MAINTAINER Jean-Mathias Gourdet <jean-mathias@gourdet.net>

# Installation des librairies et dépendances
RUN apt-get update && apt-get install -y \
    apt-utils \
    openssl \
    git \
    unzip \
    wget \
    libjpeg-dev \
    libpng-dev \
    libmcrypt-dev \
    libxml2-dev \
    libcurl3-dev \
    libc-client-dev \
    libkrb5-dev \
    libicu-dev \
    ; \
    rm -rf /var/lib/apt/lists/*;

# Mise à l'Heure
RUN ln -snf /usr/share/zoneinfo/$TIMEZONE /etc/localtime && echo $TIMEZONE > /etc/timezone
RUN printf '[PHP]\ndate.timezone = "%s"\n', $TIMEZONE > /usr/local/etc/php/conf.d/tzone.ini
RUN "date"

# Installation des extentions PHP
#RUN docker-php-ext-install curl;
#RUN docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr && docker-php-ext-install gd;
#RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl && docker-php-ext-install imap;
#RUN docker-php-ext-install json;
#RUN docker-php-ext-install mbstring;
RUN pecl install mcrypt-1.0.3;
RUN docker-php-ext-enable mcrypt;
RUN docker-php-ext-install mysqli;
RUN docker-php-ext-install opcache;
#RUN docker-php-ext-install pdo;
RUN docker-php-ext-install pdo_mysql;
#RUN docker-php-ext-install xml;
#RUN docker-php-ext-install zip;
RUN docker-php-ext-configure intl && docker-php-ext-install intl;

# Ajout de symfony
RUN wget https://get.symfony.com/cli/installer -O - | bash && \
    mv $HOME/.symfony/bin/symfony /usr/local/bin/symfony

# Création de l'utilisateur
RUN groupadd -f -g ${GROUP_ID} ${GROUP_NAME}
RUN useradd -u ${USER_ID} -g ${GROUP_ID} ${USER_NAME}

USER ${USER_NAME}:${GROUP_NAME}
WORKDIR ${SYS_PATH}

# Ajout de composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

CMD ["php-fpm"]
EXPOSE 9000
