# Standalone Frontend Setup Guide

This guide explains how to deploy the Aster Academy **frontend** for a single tenant
without the multi-tenancy layer. All code changes have already been applied on the
`feature/standalone-setup` branch.

Reference copies of the changed files live in `STANDALONE_SETUP/` so you can review
the diff at a glance.

---

## What Changed

| File | Change |
|---|---|
| `src/router/domain-middleware.js` | No-op — skips the `GET /tenants/domain` API call that validates registered tenant domains |
| `src/router/index.js` | Removed `SuperRoutes` import and the `VUE_APP_MAIN_DOMAIN` conditional that loaded super-admin routes |

### Why these two files?

**`domain-middleware.js`** — In the multi-tenant version every page navigation fires a
`GET /v1/tenants/domain` request to the central backend to confirm the visiting URL is
a registered tenant. In standalone mode there is no central backend and only one domain,
so the check is unnecessary and would throw a 404.

**`router/index.js`** — Super-admin routes (`/super-admin/*`) are only useful in the
central domain where tenants are managed. A standalone deployment is already a single
tenant, so these routes (and the `VUE_APP_MAIN_DOMAIN` comparison) are removed.

---

## Step-by-Step Setup

### 1. Clone & install

```bash
git clone <repo-url> lms-frontend
cd lms-frontend
git checkout feature/standalone-setup
npm install
```

### 2. Configure environment

Copy the template and fill in your values:

```bash
cp STANDALONE_SETUP/.env.standalone .env
```

Minimum required variables:

| Variable | Example | Notes |
|---|---|---|
| `VUE_APP_NAME` | `My LMS` | Browser tab title |
| `VUE_APP_API` | `https://lms.mycompany.com/v1` | Must include `/v1` |
| `VUE_APP_TENANT_API` | `https://lms.mycompany.com` | Same host, no `/v1` |
| `VUE_APP_ENV` | `PROD` | Switches socket.io to API host; anything else uses `localhost:6002` |
| `VUE_APP_ASSET_PATH` | `https://your-bucket.s3.amazonaws.com` | CDN prefix for uploaded files |

Optional variables (leave blank to disable the feature):

| Variable | Feature |
|---|---|
| `VUE_APP_AWS_S3` | SCORM package hosting |
| `VUE_APP_TINYMCE_KEY` | Rich-text editor in course builder |
| `VUE_APP_ZOOM_SDK_KEY` / `VUE_APP_ZOOM_SDK_SECRET` | Live Zoom meetings |
| `VUE_APP_GA_TRACKING_ID` | Google Analytics |
| `VUE_APP_MAIN_DOMAIN` | Leave **blank** — no super-admin panel in standalone |

### 3. Build for production

```bash
npm run build
```

The compiled output is in `dist/`. Serve it with Nginx or any static file server.

### 4. Nginx example

```nginx
server {
    listen 443 ssl;
    server_name lms.mycompany.com;

    root /var/www/lms-frontend/dist;
    index index.html;

    # SPA fallback — all paths serve index.html
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Proxy API requests to Laravel backend
    location /v1/ {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }

    # Proxy socket.io to Node.js socket server
    location /socket.io/ {
        proxy_pass http://127.0.0.1:6002;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }

    ssl_certificate     /etc/ssl/certs/lms.mycompany.com.crt;
    ssl_certificate_key /etc/ssl/private/lms.mycompany.com.key;
}
```

### 5. Local development

```bash
npm run serve
```

The dev server reads `.env` automatically. Hot-reload works out of the box.

---

## Socket.io / Real-time

The socket server is a separate Node.js process. See the backend `STANDALONE_SETUP.md`
for instructions on starting it. The frontend connects to it automatically using the
host derived from `VUE_APP_API`:

```
VUE_APP_API=https://lms.mycompany.com/v1
  → socket host: https://lms.mycompany.com:6002  (when VUE_APP_ENV=PROD)
  → socket host: http://localhost:6002             (all other ENV values)
```

---

## What is NOT changed

- All store modules (admin, instructor, user) — unchanged
- All Vue components and views — unchanged
- All API calls — unchanged (they all go to `VUE_APP_API`)
- Authentication flow — unchanged
- Role-based access guards — unchanged (ADMIN / INSTRUCTOR / USER roles still enforced)
- The super-admin store module (`src/store/modules/super/`) — still in the codebase but
  never reachable since super-admin routes are not registered
