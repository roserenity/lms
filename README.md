# Aster Academy LMS

A learning management system for delivering courses, assessments and certificates to large organizations. It was built multi-tenant (one database per organization) and can also be deployed for a single organization.

| Sub-project | Path | Stack |
|---|---|---|
| Backend API | [`csc-educat-backend/`](csc-educat-backend/) | Laravel 11, PHP 8.2+, MySQL, Redis |
| Frontend SPA | [`csc-educat-frontend/`](csc-educat-frontend/) | Vue 2.7, Vuetify 2, Vuex 3 |
| Real-time server | [`csc-educat-backend/server.js`](csc-educat-backend/server.js) | Node.js, Socket.io, Redis pub/sub |

## My role

<!-- TODO: replace with 3–5 bullets on what you personally built. This was a team project, so be specific:
     which modules, which integrations, which decisions were yours. -->

## Screenshots

<!-- TODO: add 3–4 screenshots (e.g. admin dashboard, course builder, assessment, certificate) under docs/screenshots/ -->

## What it does

- **Courses**: modules, topics and prerequisites, with video, document and SCORM 1.2 / 2004 materials.
- **Assessments**: question banks, rubrics, scoring and evaluations.
- **Certificates**: customizable templates with signatories and QR-code verification.
- **Communication**: real-time private and group chat, announcements, forums and support tickets.
- **Commerce**: cart, invoices and proof-of-payment uploads.
- **Live sessions**: Zoom meetings scheduled and joined from inside a course.
- **Roles**: admin, instructor and student, each with its own panel, plus a super admin in multi-tenant mode.

The full feature list by role is in [TENANT_FEATURES.md](TENANT_FEATURES.md).

## Architecture

```
Vue SPA ──► Laravel API ──► MySQL (write + read replicas)
   │             │
   │             ├──► Redis (cache, queues, pub/sub)
   │             └──► S3, Vimeo, Zoom, SCORM Cloud, mail
   │
   └──► Socket.io server ◄── Redis pub/sub (chat, notifications)
```

- **Multi-tenant mode** uses [stancl/tenancy](https://tenancyforlaravel.com/): a central database holds tenants and domains, and each tenant gets its own database, resolved by subdomain.
- **Standalone mode** serves one organization from a single database, with tenant identity read from `.env`. This tree is configured for standalone mode; see the `STANDALONE_SETUP.md` guide in each sub-project.
- **Authentication** is Laravel Passport (OAuth2), with role-based access through spatie/laravel-permission.
- **Deployment** targets AWS Elastic Beanstalk with Nginx (`.ebextensions/`, `.platform/`), with PM2 running the queue worker and the frontend.

More detail: [PROJECT_OVERVIEW.md](PROJECT_OVERVIEW.md), [database schema](csc-educat-backend/database_schema.md), [role permissions](csc-educat-backend/ROLE_PERMISSIONS.md), [OpenAPI spec](csc-educat-backend/api.yaml), [frontend architecture](csc-educat-frontend/architecture.md).

## Running with Docker

Requirements: Docker with Compose v2. The first build takes several minutes.

```bash
docker compose up --build
```

On first boot the API container runs the migrations and seeders (users, a sample course with modules and topics, a certificate template, training credits). Once `api` reports healthy:

| Service | URL | Login |
|---|---|---|
| App | http://localhost:8090 | `admin@mail.com`, `instructor@mail.com` or `user@mail.com`, password `password` |
| API | http://localhost:8000 | |
| Mail catcher | http://localhost:8026 | every outgoing email lands here |
| File storage console | http://localhost:9001 | `lmsadmin` / `lmsadmin123` |

To start over with an empty, freshly seeded database: `docker compose down -v && docker compose up --build`.

Integrations that need third-party accounts (Zoom, Vimeo, SCORM Cloud, Google Drive) are not configured in this setup; the rest of the app works without them. Group activity files (shared Google Docs, Sheets and Slides) need a Google service account key at `csc-educat-backend/storage/key/google-service-account.json`; without it, generating a file returns a "not configured" message.

## Running locally without Docker

Requirements: PHP 8.2+, Composer, MySQL 8, Redis, Node.js.

```bash
# Backend
cd csc-educat-backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan passport:install --force
php artisan serve
php artisan queue:work --tries=3   # separate terminal
npm install && node server.js      # separate terminal, Socket.io on port 6002

# Frontend
cd csc-educat-frontend
cp .env.example .env               # set VUE_APP_API to the backend URL
npm install --legacy-peer-deps
npm run serve
```

Set `SUPER_ADMIN_EMAIL` and `SUPER_ADMIN_PASSWORD` in the backend `.env` before seeding. The tenant user seeder creates `admin@mail.com`, `instructor@mail.com` and `user@mail.com`, all with the password `password`.

## Known limitations

- The Zoom Meeting SDK signature is generated in the browser (`csc-educat-frontend/src/services/jsrsasign.js`), which exposes the SDK secret to clients. It should move to a backend endpoint.
- There is no automated test suite.
- The frontend is on Vue 2, which reached end of life in December 2023.
