#!/bin/sh
# Prepares the container for local use, then runs the given command.
# State that must survive a rebuild (app key, Passport keys)
# lives under storage/, which docker-compose mounts as a named volume.
set -e

STATE_DIR=storage/docker
mkdir -p "$STATE_DIR" storage/logs storage/app/public bootstrap/cache \
    storage/framework/cache/data storage/framework/sessions storage/framework/views

if [ ! -f "$STATE_DIR/app.key" ]; then
    php -r 'echo "base64:".base64_encode(random_bytes(32));' > "$STATE_DIR/app.key"
fi
export APP_KEY="$(cat "$STATE_DIR/app.key")"

# Only the API container sets RUN_SETUP; the queue worker waits for it to be healthy.
if [ "$RUN_SETUP" = "1" ]; then
    php artisan package:discover --ansi

    # Standalone mode uses the tenant schema plus the generic queue tables.
    php artisan migrate --force \
        --path=database/migrations/tenant \
        --path=database/migrations/2019_08_19_000000_create_failed_jobs_table.php \
        --path=database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php \
        --path=database/migrations/2023_01_12_132519_create_jobs_table.php

    if [ ! -f storage/oauth-private.key ]; then
        php artisan passport:keys --force
    fi

    # Seed on first boot, judged by the database itself so a reset DB volume is re-seeded.
    if [ "$(php artisan tinker --execute='echo \App\Models\User::count();')" = "0" ]; then
        php artisan passport:client --personal --name="${TENANT_ID:-local}" --no-interaction
        php artisan db:seed --class=TenantDatabaseSeeder --force
    fi

    php artisan storage:link --force
fi

exec "$@"
