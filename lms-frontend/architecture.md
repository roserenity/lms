# System Architecture

## Overview

The LMS is composed of two projects:

| Project | Stack | Path |
|---|---|---|
| **Frontend** | Vue 2 + Vuex + Vue Router | `lms-frontend/` |
| **Backend** | Laravel 11 + stancl/tenancy | `lms-backend/` |

All users share one domain and one API. The frontend is a single-instance SPA. The backend retains the stancl/tenancy infrastructure (domain-based routing, per-tenant databases) but central domain routes (super-admin, tenant CRUD, domain validation) have been removed.

---

## 1. Request Flow

```
Browser
   │
   ▼
Frontend SPA (Vue 2)
   │  Axios → Authorization: Bearer <token>
   ▼
Backend API (Laravel 11)  routes/tenant.php + routes/tenants/*
   ├── /v1/auth/*          →  User auth (Passport JWT)
   ├── /v1/tenants/info    →  Branding + feature config
   ├── /v1/admin/*         →  Admin controllers
   ├── /v1/instructor/*    →  Instructor controllers
   └── /v1/user/*          →  User controllers
```

---

## 2. Frontend

### Boot Sequence

```
Browser loads SPA
        │
        ▼
App.vue mounted
└── document.title = VUE_APP_NAME
        │
        ▼
Router beforeEach guard
├── No JWT in localStorage → redirect /login
└── JWT present → decode role → enforce role guard → render layout
```

### Axios

Single instance in `src/services/axios.js`:
- `baseURL`: `VUE_APP_API`
- Request interceptor: reads JWT from `localStorage.access_payload` and attaches `Authorization: Bearer <token>`

### Authentication & Roles

JWT is stored as `localStorage.access_payload` (`{ token, role }`). Role and active role are AES-encrypted (`crypto-js`, key: `"lms-role"`).

| localStorage key | Content |
|---|---|
| `access_payload` | `{ token, role (encrypted) }` |
| `current` | Active role (AES string) |
| `roles` | Accessible roles (AES JSON array) |

Role hierarchy:
```
ADMIN       →  can switch to INSTRUCTOR or USER view
INSTRUCTOR  →  can switch to USER view
USER        →  single role
```

Route guards in `src/router/middleware.js` block cross-role path access on every navigation.

### Vuex Store

```
store/
├── state.js              user, customization, courses, cart, …
├── mutations.js          signInMutation, authMutation, customizationMutation, …
├── getters.js
├── actions.js
└── modules/
    ├── admin/
    ├── instructor/
    └── user/
```

### Frontend File Map

```
src/
├── App.vue                        Sets document.title; storage change listener
├── main.js
├── services/
│   └── axios.js                   Single Axios instance + JWT interceptor
├── store/
│   ├── state.js
│   ├── mutations.js
│   ├── getters.js
│   ├── actions.js
│   └── modules/
│       ├── admin/
│       ├── instructor/
│       └── user/
├── router/
│   ├── index.js                   Route table + beforeEach guard
│   ├── middleware.js              Role-based access guard
│   ├── middleware-pipeline.js     Chains multiple guards
│   ├── auth.js                    Login / register routes
│   ├── landing.js                 Public landing routes
│   ├── admin.js                   /admin/* routes
│   ├── teacher.js                 /instructor/* routes
│   └── user.js                    /user/* routes
├── views/
│   ├── admin/
│   ├── instructor/
│   └── user/
├── components/
├── layouts/
└── plugins/
```

---

## 3. Backend

### Framework & Key Packages

| Package | Purpose |
|---|---|
| `laravel/framework ^11` | Core framework |
| `laravel/passport 13.x` | OAuth2 JWT issuance and validation |
| `stancl/tenancy ^3.9` | Domain-based tenancy, per-tenant DB isolation |
| `spatie/laravel-permission` | Role and permission management |
| `spatie/laravel-activitylog` | Audit logging |
| `maatwebsite/excel` | Import / export (Excel) |
| `barryvdh/laravel-dompdf` | PDF generation (certificates, reports) |
| `predis/predis` | Redis (cache, queues) |
| `pusher/pusher-php-server` | Real-time events (Laravel Echo) |
| `aws/aws-sdk-php` | S3 file storage |
| `google/apiclient` | Google Drive integration |
| `vimeo/laravel` | Vimeo video hosting |
| `simplesoftwareio/simple-qrcode` | QR code generation |

### Route Groups

**All routes** — handled by `TenancyServiceProvider::mapRoutes()`:

```
routes/tenant.php  (prefix: /v1, middleware: InitializeTenancyByDomain, PreventAccessFromCentralDomains, TenantActivated)
├── Auth:   POST /register, /login, /logout, /forgot-password, /reset-password
├── User:   GET  /user, /login/history, /activity-log
├── Notify: GET/POST /user/notifications
└── Public: GET  /tenants/info, /tenants/courses, /tenants/advisories, …

routes/tenants/admin.php  (prefix: /v1/admin)
routes/tenants/instructor.php  (prefix: /v1/instructor)
routes/tenants/user.php  (prefix: /v1/user)
```

### Middleware Stack

| Middleware | Applied on | Effect |
|---|---|---|
| `InitializeTenancyByDomain` | Tenant routes | Resolves tenant from request domain; switches DB connection |
| `PreventAccessFromCentralDomains` | Tenant routes | 404 if hit from central domain |
| `TenantActivated` | Tenant routes | 403 if `tenant()->status !== 'active'` |
| `auth:api` | Protected routes | Validates Passport JWT |
| `verified` | `/user` endpoint | Requires email verified |
| `auth.admin` / `auth.instructor` / `auth.user` | Role routes | Role-based access guard |
| `throttle.login:3,1` | POST `/login` | Max 3 login attempts per minute |
| `throttle:300,5` | All API | 300 requests per 5 minutes |

### Database Strategy

stancl/tenancy creates a **separate database per tenant** when a tenant is provisioned:

```
TenantCreated event
└── JobPipeline
    ├── CreateDatabase    ← provisions new DB
    └── MigrateDatabase  ← runs all tenant migrations into new DB
```

On each tenant request, `InitializeTenancyByDomain` switches the active DB connection to the matching tenant database. Central tables (tenants, domains, super admins) remain in the central DB.

### Controller Structure

```
app/Http/Controllers/Api/V1/Tenants/
├── AuthController.php          User auth (register, login, password, profile)
├── NotificationController.php
├── Admin/                      Admin-scoped controllers (dashboard, users, enrollment, …)
├── Teacher/                    Instructor-scoped controllers (courses, assessments, grading, …)
└── User/                       Student-scoped controllers (courses, cart, certificates, …)
```

### Service Layer

```
app/Services/Tenants/
├── Auth/
│   └── PasswordService.php
├── AttachmentService.php
├── CheckoutService.php
├── ContentService.php
├── CourseService.php
├── GoogleDriveService.php
├── ImageService.php
├── LearningMaterialService.php
├── ResendService.php
├── ScormCloudService.php
├── ScormJSService.php
└── UserService.php
```

---

## 4. Environment Variables

### Frontend (`lms-frontend/.env`)

| Variable | Purpose |
|---|---|
| `VUE_APP_API` | Backend API base URL |
| `VUE_APP_NAME` | Browser tab title |
| `VUE_APP_SOCKET_HOST` / `VUE_APP_SOCKET_PORT` | Laravel Echo / Socket.io |
| `VUE_APP_ASSET_PATH` | CDN prefix for uploaded assets |

### Backend (`lms-backend/.env`)

| Variable | Purpose |
|---|---|
| `APP_URL` | Central domain URL |
| `DB_*` | Central database credentials |
| `PASSPORT_*` | OAuth2 keys |
| `REDIS_*` | Redis connection |
| `PUSHER_*` | Pusher / Laravel Echo |
| `AWS_*` | S3 storage |
| `MAIL_*` | Transactional email |
| `VIMEO_*` | Vimeo API |
| `SCORM_CLOUD_*` | SCORM Cloud credentials |
