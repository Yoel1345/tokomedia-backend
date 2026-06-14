FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite
COPY . /var/www/html/
RUN mkdir -p /var/www/html/uploads/products && chmod -R 777 /var/www/html/uploads
COPY start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/start.sh
ENV PORT=8080
CMD ["start.sh"]