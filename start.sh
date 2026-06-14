#!/bin/bash
mkdir -p /var/www/html/uploads/products && chmod -R 777 /var/www/html/uploads
cat > /var/www/html/uploads/products/.htaccess << 'EOF'
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
</IfModule>
EOF
a2dismod mpm_event 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true
sed -i "s/80/${PORT:-80}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
apache2-foreground
