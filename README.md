# Aster Academy LMS

A learning management system for delivering courses, assessments and certificates to large organizations. It has separate panels for administrators, instructors and learners, and was built multi-tenant (one database per organization) before being adapted for single-organization deployments.

| Part | Path | Stack |
|---|---|---|
| Backend API | [`csc-educat-backend/`](csc-educat-backend/) | Laravel 11, PHP 8.2+, MySQL 8, Redis |
| Frontend SPA | [`csc-educat-frontend/`](csc-educat-frontend/) | Vue 2.7, Vuetify 2, Vuex 3 |
| Real-time server | [`csc-educat-backend/server.js`](csc-educat-backend/server.js) | Node.js, Socket.io, Redis pub/sub |

## Quick start

Requirements: Docker with Compose v2. The first build takes several minutes.

```bash
docker compose up --build
```

On first boot the API container runs the migrations and seeders. Once the `api` service reports healthy, open:

| Service | URL | Login |
|---|---|---|
| App | http://localhost:8090 | see accounts below |
| API | http://localhost:8000 | |
| Mail catcher | http://localhost:8026 | every outgoing email lands here |
| File storage console | http://localhost:9001 | `lmsadmin` / `lmsadmin123` |

Seeded accounts (password `password` for all three):

| Role | Email |
|---|---|
| Admin | `admin@mail.com` |
| Instructor | `instructor@mail.com` |
| Learner | `user@mail.com` |

The seeders also create a sample course with modules and topics, a default certificate template and the training credit types.

To start over with an empty, freshly seeded database:

```bash
docker compose down -v && docker compose up --build
```

All credentials in `docker-compose.yml` are throwaway values for local use.

## What it does

- **Courses**: modules, topics and prerequisites, with video, document and SCORM 1.2 / 2004 materials.
- **Assessments**: question banks, rubrics, scoring, grading and course evaluations.
- **Group activities**: learners split into groups that work on a shared Google Doc, Sheet or Slides file.
- **Certificates**: customizable templates with signatories and QR-code verification, rendered to PDF.
- **Communication**: real-time private and group chat, announcements, forums and support tickets.
- **Commerce**: cart, checkout, invoices and proof-of-payment uploads.
- **Live sessions**: Zoom meetings scheduled and joined from inside a course.
- **Reporting**: Excel exports of enrollments, progress and grades, and bulk user import.

The full feature list by role is in [TENANT_FEATURES.md](TENANT_FEATURES.md).

## My role

I was the main developer on both the API and the frontend from 2022 to 2026, in a small team. My work included:

- **API**: most of the REST endpoints for the admin, instructor and learner panels, with their validation, resources and routes.
- **SCORM**: the SCORM Cloud integration for uploading, launching and tracking SCORM packages.
- **Commerce**: checkout, invoicing and proof-of-payment handling.
- **Certificates and reports**: certificate PDF templates, email templates, the Excel exports behind admin and instructor reports, and bulk user import.
- **Frontend**: the instructor course builder, the learner course and assessment views, the inbox, admin enrollment and dashboard screens, and the Vuex store behind them.
- **Standalone mode**: adapting the multi-tenant codebase to serve a single organization from one database.

## Architecture

```
Vue SPA ──► Laravel API ──► MySQL
   │             │
   │             ├──► Redis (cache, queues, pub/sub)
   │             └──► S3, Vimeo, Zoom, SCORM Cloud, Google Drive, mail
   │
   └──► Socket.io server ◄── Redis pub/sub (chat, notifications)
```

- **Authentication**: Laravel Passport (OAuth2) access tokens, with role-based access through spatie/laravel-permission.
- **Tenancy**: multi-tenant mode uses [stancl/tenancy](https://tenancyforlaravel.com/), with a central database for tenants and domains and one database per tenant, resolved by subdomain. Standalone mode serves one organization from a single database, with its identity read from `.env`. This repository is configured for standalone mode.
- **Background work**: emails, exports and notifications run on a Redis-backed queue worker.
- **Production deployment**: AWS Elastic Beanstalk with Nginx (`.ebextensions/`, `.platform/`), and PM2 running the queue worker.

### Docker services

| Service | Purpose |
|---|---|
| `api` | Laravel API; runs migrations and seeders on first boot |
| `queue` | Laravel queue worker |
| `socket` | Socket.io server for chat and notifications |
| `web` | Built Vue app served by Nginx |
| `mysql`, `redis` | Database and cache/queue |
| `mailpit` | Catches outgoing email |
| `storage`, `storage-init` | S3-compatible file storage (RustFS) and its bucket setup |

### Third-party integrations

Zoom, Vimeo, SCORM Cloud and Google Drive need their own accounts and are not configured in the Docker setup. The rest of the app works without them. Group activity files need a Google service account key at `csc-educat-backend/storage/key/google-service-account.json`; without it, generating a file returns a "not configured" message instead of failing.

## Repository layout

```
.
├── docker-compose.yml          Local stack
├── csc-educat-backend/
│   ├── app/                    Controllers, models, services, jobs, exports
│   ├── database/migrations/    Central and tenant (tenant/) schemas
│   ├── database/seeders/       Demo data
│   ├── routes/                 API routes per role
│   ├── docker/                 Entrypoint and socket server image
│   └── server.js               Socket.io server
└── csc-educat-frontend/
    └── src/
        ├── views/, components/ Admin, instructor and learner screens
        ├── store/              Vuex modules per role
        └── router/             Routes and role guards
```

## Running without Docker

Requirements: PHP 8.2+, Composer, MySQL 8, Redis and Node.js 16.

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

## Known limitations

- The Zoom Meeting SDK signature is generated in the browser (`csc-educat-frontend/src/services/jsrsasign.js`), which exposes the SDK secret to clients. It should move to a backend endpoint.
- There is no automated test suite.
- The frontend is on Vue 2, which reached end of life in December 2023, and on Vue CLI 4, which needs Node 16 to build.

## Further documentation

- [Project overview](PROJECT_OVERVIEW.md)
- [Database schema](csc-educat-backend/database_schema.md)
- [Role permissions](csc-educat-backend/ROLE_PERMISSIONS.md)
- [OpenAPI spec](csc-educat-backend/api.yaml)
- [Frontend architecture](csc-educat-frontend/architecture.md)
- Standalone setup: [backend](csc-educat-backend/STANDALONE_SETUP.md), [frontend](csc-educat-frontend/STANDALONE_SETUP.md)
