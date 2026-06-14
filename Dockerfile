FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite
RUN a2dismod mpm_event && a2enmod mpm_prefork
COPY . /var/www/html/
ENV PORT=8080
RUN sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
CMD ["apache2-foreground"]