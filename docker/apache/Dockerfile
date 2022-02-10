FROM httpd:2.4
MAINTAINER Thomas DUBUFFET <thomas.dubuffet@gmail.com>

# Installation du module "rewrite"
RUN sed -i \
        -e 's/^#\(LoadModule .*mod_rewrite.so\)/\1/' \
        -e 's/^#\(LoadModule .*mod_proxy.so\)/\1/' \
        -e 's/^#\(LoadModule .*mod_proxy_fcgi.so\)/\1/' \
        /usr/local/apache2/conf/httpd.conf

# Configuration d'Apache
RUN sed -i \
        -e 's/^#ServerName www.example.com:80/ServerName ${APP_DOMAIN}/' \
        -e 's/^Listen 80/Listen ${PORT_APACHE}/' \
        /usr/local/apache2/conf/httpd.conf

# Copie de la déclaration du vhost à la fin du fichier de configuration Apache
RUN touch /usr/local/apache2/conf/virtualhost.conf
RUN echo 'Include conf/virtualhost.conf' >> /usr/local/apache2/conf/httpd.conf

# Création du répertoire puis ajout des droits "www-data"
RUN mkdir -p /var/www/symfony && \
    usermod --non-unique --uid 1000 www-data && \
    groupmod --non-unique --gid 1000 www-data && \
    chown -Rf www-data:www-data /var/www

# Espace de travail
WORKDIR /var/www/symfony