# Aster Academy LMS — Backend

Laravel 11 API for the Aster Academy learning management system, plus the Node.js Socket.io server used for chat and notifications. See the [project README](../README.md) for the overall architecture.

## Stack

- Laravel 11 on PHP 8.2+, MySQL 8 (write host plus read replicas), Redis
- Laravel Passport for OAuth2, spatie/laravel-permission for roles
- stancl/tenancy for multi-tenant mode
- S3 for file storage; Zoom, Vimeo and SCORM Cloud integrations
- Socket.io server (`server.js`, port 6002) fed by Redis pub/sub

## Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed            # needs SUPER_ADMIN_EMAIL and SUPER_ADMIN_PASSWORD in .env
php artisan passport:install --force
php artisan optimize
```

Redis needs the PHP extension (`sudo apt install php-redis` on Debian/Ubuntu).

## Running

```bash
php artisan serve
php artisan queue:work --tries=3
npm install && node server.js
```

In production the queue worker runs under PM2 (`ecosystem.config.js`) and the app deploys to AWS Elastic Beanstalk (`.ebextensions/`, `.platform/`).

## Documentation

- [STANDALONE_SETUP.md](STANDALONE_SETUP.md): running for a single organization without the tenancy layer
- [database_schema.md](database_schema.md): tables and relationships
- [ROLE_PERMISSIONS.md](ROLE_PERMISSIONS.md): what each role can do
- [api.yaml](api.yaml) and [tenant-api.yaml](tenant-api.yaml): OpenAPI specs
