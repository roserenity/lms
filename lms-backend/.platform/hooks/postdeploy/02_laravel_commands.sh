#!/bin/bash
# Clear existing queue workers (if any)
# for pid in $(ps -ef | awk '/queue:work/{print $2}'); do kill -9 $pid; done
#su -c "php /var/app/current composer update --ignore-platform-req=php" webapp
su -c "php /var/app/current/artisan config:cache" webapp
su -c "php /var/app/current/artisan optimize:clear" webapp
su -c "php /var/app/current/artisan storage:link" webapp
su -c "php /var/app/current/artisan migrate" webapp
su -c "php /var/app/current/artisan tenants:migrate" webapp
# su -c "nohup php /var/app/current/artisan queue:work > /dev/null 2>&1 &" webapp

cp /opt/elasticbeanstalk/deployment/env /opt/elasticbeanstalk/deployment/laravel_env
chmod 644 /opt/elasticbeanstalk/deployment/laravel_env
rm -f /opt/elasticbeanstalk/deployment/*.bak

# Reload systemd and start the Laravel queue service
systemctl daemon-reload
systemctl enable laravel_queue_worker@{1..3}.service
systemctl restart laravel_queue_worker@{1..3}.service
