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

cp /etc/php5/apache2/php.ini.template /etc/php5/apache2/php.ini

PARAMETERS=$(printenv | awk -F "=" '{print $1}' |grep CIBL_.*)
for name in $PARAMETERS; do
   eval value=\$$name
   sed -i "s|\${${name}}|${value}|g" /etc/php5/apache2/php.ini
done

apache2ctl -D FOREGROUND
