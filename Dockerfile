FROM php:8.2-apache
RUN apt-get update && apt-get install -y libcurl4-openssl-dev pkg-config && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo pdo_mysql curl
RUN a2enmod rewrite headers
COPY . /var/www/html/
RUN mkdir -p /var/www/html/uploads/products && chmod -R 777 /var/www/html/uploads
COPY start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/start.sh
ENV PORT=8080
CMD ["start.sh"]