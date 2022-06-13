#!/bin/sh
cd /var/www

FILE=/var/www/.env

if [ ! -f $FILE ]; then
  cp /var/www/.env.example /var/www/.env
fi

bash /var/www/docker/php/script/wait-for-it.sh db:3306 -s -t 60 -- echo "Database iniciado"

echo "Copying default XDEBUG ini"
sudo cp /home/xdebug/xdebug-default.ini /usr/local/etc/php/conf.d/xdebug.ini

if [[ $XDEBUG_MODES == *"profile"* ]]; then
    echo "Appending profile ini"
    sudo bash -c "cat /home/xdebug/xdebug-profile.ini >> /usr/local/etc/php/conf.d/xdebug.ini"
fi

if [[ $XDEBUG_MODES == *"debug"* ]]; then
    echo "Appending debug ini"
    sudo bash -c "cat /home/xdebug/xdebug-debug.ini >> /usr/local/etc/php/conf.d/xdebug.ini"

    echo "Setting Client Host to: $XDEBUG_CLIENT_HOST"
    sudo sed -i -e 's/xdebug.client_host = localhost/xdebug.client_host = '"${XDEBUG_CLIENT_HOST}"'/g' /usr/local/etc/php/conf.d/xdebug.ini

    echo "Setting Client Port to: $XDEBUG_CLIENT_PORT"
    sudo sed -i -e 's/xdebug.client_port = 9003/xdebug.client_port = '"${XDEBUG_CLIENT_PORT}"'/g' /usr/local/etc/php/conf.d/xdebug.ini

    echo "Setting IDE Key to: $IDE_KEY"
    sudo sed -i -e 's/xdebug.idekey = docker/xdebug.idekey = '"${IDE_KEY}"'/g' /usr/local/etc/php/conf.d/xdebug.ini
fi

if [[ $XDEBUG_MODES == *"trace"* ]]; then
    echo "Appending trace ini"
    sudo bash -c "cat /home/xdebug/xdebug-trace.ini >> /usr/local/etc/php/conf.d/xdebug.ini"
fi

if [[ "off" == $XDEBUG_MODES || -z $XDEBUG_MODES ]]; then
    echo "Disabling XDEBUG";
    sudo cp /home/xdebug/xdebug-off.ini /usr/local/etc/php/conf.d/xdebug.ini
else
    echo "Setting XDEBUG mode: $XDEBUG_MODES"
    sudo bash -c "echo -e '\n' >> /usr/local/etc/php/conf.d/xdebug.ini"
    sudo bash -c "echo 'xdebug.mode = $XDEBUG_MODES' >> /usr/local/etc/php/conf.d/xdebug.ini"
fi;

composer install
php artisan migrate
php artisan swagger-lume:generate


exec php-fpm
