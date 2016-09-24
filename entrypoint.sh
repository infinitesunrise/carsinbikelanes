#!/bin/bash
chown -R www-data:www-data /var/www

source /etc/apache2/envvars

cat <<'EOF' > /var/www/html/admin/config_pointer.php
<?php
include ('/var/www/cibl_config/config/config.php');
$config_folder = '/var/www/cibl_config/config';
$config_location = '/var/www/cibl_config/config/config.php';
?>
EOF

apache2ctl -D FOREGROUND
