#!/bin/bash
mkdir -p /var/www/html/uploads/products && chmod -R 777 /var/www/html/uploads
a2dismod mpm_event 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true
sed -i "s/80/${PORT:-80}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
apache2-foreground
