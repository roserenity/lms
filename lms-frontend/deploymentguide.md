# Deployment Guide

Two projects must be deployed together:

| Project | Runtime | Default port |
|---|---|---|
| `lms-frontend` | Node / PM2 | 3000 |
| `lms-backend` | PHP-FPM / Nginx | 80 / 443 |
| Socket.io server | Node / PM2 | 6002 |
| Queue worker | PHP / PM2 | — |

---

## Prerequisites

| Requirement | Version |
|---|---|
| Node.js | 14.x or higher |
| npm | 6.x or higher |
| PHP | 8.2 or higher |
| Composer | 2.x |
| MySQL / MariaDB | 8.0+ |
| Redis | 6.x or higher |
| PM2 | latest (`npm install -g pm2`) |
| Nginx | latest |

---

## Part 1 — Backend (`lms-backend`)

### 1.1 Clone and Install

```bash
git clone <repository-url>
cd lms-backend
composer install --optimize-autoloader --no-dev
npm install
```

### 1.2 Configure Environment

```bash
cp .env.example .env
```

Minimum required values:

```env
APP_NAME=
APP_ENV=prod
APP_KEY=                        # generated in next step
APP_URL=https://api.lms.example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=

PASSPORT_PRIVATE_KEY=
PASSPORT_PUBLIC_KEY=

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=

SSL_DIR_PRIVKEY=                # path to privkey.pem (used by server.js)
SSL_DIR_FULLCHAIN=              # path to fullchain.pem (used by server.js)
```

### 1.3 Bootstrap Laravel

```bash
php artisan key:generate
php artisan migrate
php artisan passport:install
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 1.4 Configure PHP-FPM + Nginx

PHP-FPM should be running (e.g. `php8.2-fpm`). Add an Nginx server block:

```nginx
server {
    listen 443 ssl http2;
    server_name api.lms.example.com;

    ssl_certificate     /etc/letsencrypt/live/api.lms.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.lms.example.com/privkey.pem;

    root /var/www/lms-backend/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

server {
    listen 80;
    server_name api.lms.example.com;
    return 301 https://$host$request_uri;
}
```

### 1.5 Start Queue Worker (PM2)

```bash
cd lms-backend
pm2 start ecosystem.config.js
pm2 save
```

This runs `php artisan queue:work --tries=3` and auto-restarts on crash.

### 1.6 Start Socket.io Server (PM2)

`server.js` is the real-time Socket.io server. Start it under PM2:

```bash
pm2 start server.js --name lms-socket
pm2 save
```

The server:
- Listens on port **6002**
- Connects to Redis on `127.0.0.1:6379`
- Subscribes to `private-chat-*` channels and relays messages to connected clients
- In production (`APP_ENV=prod`) reads SSL certs from `SSL_DIR_PRIVKEY` / `SSL_DIR_FULLCHAIN`

Ensure port 6002 is open in the firewall:

```bash
ufw allow 6002
```

---

## Part 2 — Frontend (`lms-frontend`)

### 2.1 Clone and Install

```bash
git clone <repository-url>
cd lms-frontend
npm install
```

### 2.2 Configure Environment

```bash
cp .env.example .env
```

Minimum required values:

```env
VUE_APP_NAME=Aster Academy e-Learning
VUE_APP_API=https://api.lms.example.com/v1
VUE_APP_ENV=PROD
VUE_APP_ASSET_PATH=
VUE_APP_AWS_S3=
VUE_APP_TINYMCE_KEY=
VUE_APP_ZOOM_SDK_KEY=
VUE_APP_ZOOM_SDK_SECRET=
```

> Environment variables are baked into the bundle at build time. Any change requires a full rebuild.

### 2.3 Build

```bash
npm run build
```

Output goes to `dist/`. The build minifies JS/CSS, hashes filenames, and skips source maps.

### 2.4 Serve with PM2

```bash
pm2 start pm2.config.js --env production
pm2 save
```

Serves the SPA on port **3000**.

### 2.5 Configure Nginx

```nginx
server {
    listen 443 ssl http2;
    server_name lms.example.com;

    ssl_certificate     /etc/letsencrypt/live/lms.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/lms.example.com/privkey.pem;

    root /var/www/lms-frontend/dist;
    index index.html;

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff2?|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # Proxy Socket.io WebSocket connections
    location /socket.io/ {
        proxy_pass https://127.0.0.1:6002;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
    }

    # All other routes fall back to index.html (Vue Router history mode)
    location / {
        try_files $uri $uri/ /index.html;
    }
}

server {
    listen 80;
    server_name lms.example.com;
    return 301 https://$host$request_uri;
}
```

---

## Part 3 — Deploying Updates

### Frontend update

```bash
cd lms-frontend
git pull origin main
npm install
npm run build
pm2 restart lms-frontend
```

### Backend update

```bash
cd lms-backend
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
pm2 restart laravel-queue-worker
```

> Run `php artisan migrate --force` only after reviewing the migration diff — it runs without confirmation prompts.

### Socket.io server update

```bash
cd lms-backend
git pull origin main
npm install
pm2 restart lms-socket
```

---

## Part 4 — Rollback

```bash
# Frontend
cd lms-frontend
git checkout <previous-tag>
npm run build
pm2 restart lms-frontend

# Backend
cd lms-backend
git checkout <previous-tag>
composer install --optimize-autoloader --no-dev
php artisan migrate:rollback   # only if the update added migrations
php artisan config:cache
pm2 restart laravel-queue-worker
```

Keep a copy of `dist/` before each frontend deploy (`cp -r dist dist.bak`) to skip the rebuild on rollback.

---

## Checklist

### Backend
- [ ] `composer install` completed without errors
- [ ] `.env` filled in — `APP_KEY`, DB, Redis, Passport, mail, AWS, SSL paths
- [ ] `php artisan migrate` ran successfully
- [ ] `php artisan passport:install` ran
- [ ] `storage/` is writable and `storage:link` was run
- [ ] Laravel config/route/view caches are warm
- [ ] Nginx PHP-FPM block is active and returning `200` on `GET /health`
- [ ] Queue worker is running (`pm2 status` shows `laravel-queue-worker`)
- [ ] Socket.io server is running (`pm2 status` shows `lms-socket`)
- [ ] Port 6002 is open in the firewall

### Frontend
- [ ] `.env` filled in — `VUE_APP_API` points to the live backend
- [ ] `npm run build` completed without errors
- [ ] `dist/` deployed to the correct web root
- [ ] Nginx history-mode fallback is active (`try_files ... /index.html`)
- [ ] SSL certificate covers `lms.example.com`
- [ ] DNS A record for `lms.example.com` points to the server
- [ ] Frontend PM2 process is running (`pm2 status` shows `lms-frontend`)
- [ ] `pm2 save` and `pm2 startup` were run on both servers

### Smoke test
- [ ] `https://lms.example.com` loads the SPA
- [ ] Login succeeds and JWT is stored
- [ ] An API call (`GET /v1/user`) returns the authenticated user
- [ ] Real-time (Socket.io) connects without errors in the browser console
