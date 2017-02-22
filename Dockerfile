FROM php:7.0-apache
MAINTAINER Ian Murphy <ian@isogen.net>

#

RUN docker-php-ext-install pdo pdo_mysql

RUN 	mkdir /var/www/sling; \
		mkdir /var/www/ssl/; \
		rm -f /etc/apache2/sites-enabled/*;

COPY 	. /var/www/sling

COPY 	sling.conf /etc/apache2/sites-enabled/

COPY 	server.* /var/www/ssl/

COPY 	php.ini /usr/local/etc/php/

RUN 	a2enmod rewrite; a2enmod ssl


VOLUME 	/var/www/sling/

EXPOSE 80
EXPOSE 443

ENTRYPOINT bash -c "service apache2 start; bash"