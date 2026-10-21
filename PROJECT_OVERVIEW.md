# Aster Academy LMS — Project Overview

## Summary

**Aster Academy LMS** is a multi-tenant Learning Management System (LMS). It manages courses, assessments, certifications, and user interactions across isolated tenant environments.

The system is split into two sub-projects under a monorepo:

| Sub-project | Path | Stack |
|---|---|---|
| Backend API | `lms-backend/` | Laravel 11 (PHP 8.3) |
| Frontend SPA | `lms-frontend/` | Vue.js 2 + Vuetify 2 |

---

## Architecture

### Multi-Tenancy

The platform uses [stancl/tenancy](https://tenancyforlaravel.com/) with **one database per tenant**. Each tenant database is named `tenant_<uuid>`. A central database stores shared records (tenants, domains, super-admin users).

```
Central DB ──► tenants, domains, super-admin users
Tenant DBs ──► courses, users, assessments, certificates, messages, etc.
```

### User Roles

| Role | Scope |
|---|---|
| Super Admin | Platform-wide (central) |
| Admin | Tenant-level management |
| Instructor | Course delivery and assessment |
| Student | Course enrollment and learning |

### Real-Time Layer

A standalone **Node.js Socket.io server** (`server.js`, port `6002`) handles:
- Private and group chat messaging (via Redis pub/sub)
- Real-time notifications

The frontend connects via `socket.io-client` + `laravel-echo`.

---

## Tech Stack

### Backend (`lms-backend`)

| Category | Technology |
|---|---|
| Framework | Laravel 11 |
| Language | PHP 8.3 |
| Authentication | Laravel Passport (OAuth2) + Sanctum |
| Multi-tenancy | stancl/tenancy ^3.9 |
| Authorization | spatie/laravel-permission (RBAC) |
| File storage | AWS S3 (league/flysystem-aws-s3-v3) |
| Queue / Cache | Redis (predis/predis) |
| Real-time | Pusher + Node.js Socket.io server |
| PDF generation | barryvdh/laravel-dompdf |
| Excel / CSV | maatwebsite/excel |
| Word documents | phpoffice/phpword |
| Image handling | intervention/image |
| QR codes | simplesoftwareio/simple-qrcode |
| Activity logging | spatie/laravel-activitylog |
| Google APIs | google/apiclient |
| Video | Vimeo (vimeo/laravel), Zoom (custom config) |
| SCORM | SCORM Cloud integration |
| Email | SMTP / Mailgun / SES / Resend |
| JWT | firebase/php-jwt |
| Asset build | Laravel Mix (webpack) |
| Deployment | AWS Elastic Beanstalk + Nginx |
| Process mgr | PM2 (queue worker) |

### Frontend (`lms-frontend`)

| Category | Technology |
|---|---|
| Framework | Vue.js 2.7 |
| UI components | Vuetify 2.6 |
| State | Vuex 3 |
| Routing | Vue Router 3 |
| HTTP | Axios |
| Real-time | socket.io-client 2 + laravel-echo |
| Rich text | TinyMCE, vue2-editor (Quill) |
| Charts | ApexCharts, Chart.js |
| Calendar | FullCalendar |
| Date utils | Moment.js |
| Video | Plyr, vue-vimeo-player, video.js |
| Drag-and-drop | vuedraggable |
| QR scan | html5-qrcode |
| Crypto | jsrsasign, crypto-js |
| Animations | AOS |
| Analytics | vue-gtag (Google Analytics) |
| PWA | register-service-worker |
| Build | Vue CLI |
| Process mgr | PM2 (port 3000) |

---

## Project Structure

```
lms/
├── lms-backend/
│   ├── app/
│   │   ├── Models/          # 90+ Eloquent models
│   │   ├── Http/            # Controllers, Middleware, Requests, Resources
│   │   ├── Services/        # Business logic (Central + Tenant services)
│   │   ├── Jobs/            # Queue jobs
│   │   ├── Mail/            # Mailable classes
│   │   ├── Notifications/   # Notification classes
│   │   ├── Observers/       # 60+ model observers
│   │   ├── Repositories/    # Data access layer
│   │   ├── Exports/         # Excel/CSV export handlers
│   │   ├── Imports/         # Excel/CSV import handlers
│   │   └── Rules/           # Custom validation rules
│   ├── config/              # 27 config files
│   ├── database/
│   │   ├── migrations/          # Central DB migrations
│   │   └── migrations/tenant/   # 134+ tenant DB migrations
│   ├── routes/
│   │   ├── api.php          # Super-admin API routes
│   │   ├── tenant.php       # Tenant router entry
│   │   └── tenants/
│   │       ├── admin.php
│   │       ├── instructor.php
│   │       └── user.php
│   ├── resources/views/     # Blade templates (certificates, emails)
│   ├── server.js            # Node.js Socket.io server (port 6002)
│   ├── ecosystem.config.js  # PM2 — queue worker
│   ├── .ebextensions/       # AWS Elastic Beanstalk configs
│   └── .platform/           # Nginx + post-deploy hooks
│
└── lms-frontend/
    ├── src/
    │   ├── components/      # admin/, teacher/, user/, super/, reusable/
    │   ├── views/           # Page components per role
    │   ├── router/          # Per-role route files + middleware
    │   ├── store/           # Vuex modules per role
    │   ├── layouts/         # Layout shells per role
    │   ├── shared/          # Utility helpers
    │   ├── constants/       # Static constants (nav, table headers, tabs)
    │   ├── plugins/         # vuetify.js, socket.js
    │   ├── services/        # axios.js, jsrsasign.js
    │   └── mixins/          # paginateMixin, searchAndPaginateMixin
    ├── vue.config.js
    └── pm2.config.js        # PM2 — serves frontend on port 3000
```

---

## Features

### Core LMS
- Course, module, and topic management with prerequisites
- Multi-format learning materials (video, document, attachment, SCORM)
- Enrollment and progress tracking per student
- Assessment system: questions, rubrics, scoring, evaluations
- Group activities and discussions
- Forum system
- Announcement and advisory management

### Certification
- Customizable certificate templates with signatory management
- QR code validation on certificates
- Commitment and endorsement letter generation (Word/PDF)

### Communication
- Private and group messaging (real-time via Socket.io)
- Ticket / support system
- Broadcast announcements

### Commerce
- Invoice and cart system
- Proof of payment uploads

### Integrations
| Integration | Purpose |
|---|---|
| AWS S3 | File storage |
| Google Drive / OAuth | Document access, authentication |
| Zoom | Live meeting scheduling |
| Vimeo | Video hosting |
| SCORM Cloud | SCORM 1.2 / 2004 package delivery |
| Mailgun / SES / Resend | Transactional email |
| Pusher | WebSocket broadcasting (complement to Socket.io) |
| Google Analytics | Frontend usage analytics |
| TinyMCE | Rich content authoring |
| Redis | Caching, queues, pub/sub |

---

## API Structure

### Central (Super Admin)
```
POST   /api/super-admin/login
POST   /api/super-admin/logout
GET    /api/super-admin/user
GET    /api/tenants/domain
CRUD   /api/super-admin/tenants
PUT    /api/super-admin/tenants/update/status
PUT    /api/super-admin/tenants/remove/photo/{tenant_id}
```

### Tenant-scoped (resolved by subdomain)
```
/tenants/admin/*        Admin panel
/tenants/instructor/*   Instructor panel
/tenants/user/*         Student panel
```

---

## Database

### Central DB
9 migrations: users, tenants, domains, OAuth tokens, images, password resets, jobs.

### Tenant DB (134+ migrations)
| Domain | Key tables |
|---|---|
| Courses | courses, modules, topics, sub_topics, enrollments, prerequisites |
| Content | learning_materials, videos, documents, attachments, scorm_packages |
| Assessment | assessments, questions, answers, scores, rubrics, evaluations |
| Certification | certificates, certificate_templates, signatories, commitment_letters, endorsement_letters |
| Communication | private_messages, group_chats, announcements, forum_posts, tickets |
| Progress | student_course_progress, student_module_progress, student_certificate_assignments |
| Commerce | invoices, cart_items, proof_of_payments |
| Live sessions | zoom_meetings |
| Auditing | activity_log (via Spatie) |

---

## Environment Variables

### Backend (`.env`)
```
APP_URL          # Central app URL
APP_CENTRAL      # Central domain

DB_HOST / DB_HOST_READ1/2/3   # MySQL + read replicas
REDIS_HOST

MAIL_MAILER      # smtp | mailgun | ses | resend
AWS_*            # S3 credentials

BROADCAST_DRIVER
QUEUE_CONNECTION
CACHE_DRIVER
```

### Frontend (`.env`)
```
VUE_APP_API           # Backend API base URL
VUE_APP_TENANT_API    # Tenant API base URL
VUE_APP_SOCKET_HOST   # Socket.io server host
VUE_APP_SOCKET_PORT   # Default: 6002
VUE_APP_MAIN_DOMAIN   # Main domain (for subdomain resolution)
VUE_APP_AWS_S3        # S3 asset base URL
VUE_APP_TINYMCE_KEY   # TinyMCE API key
VUE_APP_GA_TRACKING_ID
```

---

## Local Setup

### Backend
```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan passport:install --force
php artisan optimize
# Start queue worker
php artisan queue:work --tries=3
# Start Socket.io server
node server.js
```

### Frontend
```bash
cp .env.example .env
npm install
npm run serve       # dev
npm run build       # production
```

---

## Deployment

The backend is deployed on **AWS Elastic Beanstalk** with:
- PHP 8.3 + Nginx (configured via `.ebextensions/` and `.platform/`)
- PM2 managing the Laravel queue worker
- Post-deploy hooks for cache clearing and migration

The frontend is served as a Node.js process via PM2 on port `3000`.

The Socket.io server runs independently on port `6002`.

---

## Key Numbers

| Metric | Count |
|---|---|
| Tenant DB migrations | 134+ |
| Eloquent models | 90+ |
| Model observers | 60+ |
| Config files | 27 |
| Vue route groups | 6 (landing, auth, admin, teacher, user, super) |
| Vuex store modules | 4 (admin, instructor, super, user) |
