#!/bin/bash
touch /var/app/current/storage/logs/laravel.log

chmod -R 777 /var/app/current/storage
chmod -R 777 /var/app/current/bootstrap/cache
chmod -R 660 /var/app/current/storage/oauth-private.key
chmod -R 660 /var/app/current/storage/oauth-public.key

chown -R webapp:webapp /var/app/current/storage
chown -R webapp:webapp /var/app/current/bootstrap/cache
chown -R webapp:webapp /var/app/current/storage/framework/cache
# chgrp -R webapp /var/app/current/storage /var/app/current/bootstrap/cache
# chmod -R ug+rwx /var/app/current/storage /var/app/current/bootstrap/cache
