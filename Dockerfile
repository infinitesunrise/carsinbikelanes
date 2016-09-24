FROM debian:jessie

MAINTAINER Mathieu Ruellan <mathieu.ruellan@gmail.com>

ENV DEBIAN_FRONTEND noninteractive
ENV HOME /root

RUN apt-get update \
     && apt-get upgrade -yy \
     && apt-get install apache2 libapache2-mod-php5  -yy \
     && apt-get install -yy php5-mysql php5-gd imagemagick \
     && apt-get install -yy \
     && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN ln -sf /dev/stdout /var/log/apache2/access.log && \
    ln -sf /dev/stderr /var/log/apache2/error.log



ADD php.ini /etc/php5/apache2/php.ini.template

RUN rm -rf /var/www/html/*
ADD *.php /var/www/html/
ADD admin /var/www/html/admin
ADD config /var/www/html/config
ADD css /var/www/html/css
ADD scripts /var/www/html/scripts


RUN mv /var/www/html/index.php /var/www/html/config.php
RUN mv /var/www/html/index_actual.php /var/www/html/index.php

RUN chown -R www-data:www-data /var/www/html
RUN chmod a-w /var/www/html/config.php
RUN chmod a-w /var/www/html/index.php

VOLUME /var/www/cibl_config
VOLUME /var/www/html/images
VOLUME /var/www/html/thumbs

ENV CIBL_SMTP_SERVER "localhost"
ENV CIBL_SMTP_PORT "25"
ENV CIBL_NORTH "47.29"
ENV CIBL_SOUTH "47.141"
ENV CIBL_EAST "-1.43"
ENV CIBL_WEST "-1.66"

ADD entrypoint.sh /entrypoint.sh

ENTRYPOINT /entrypoint.sh
EXPOSE 80
