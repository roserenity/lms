# Standalone Setup Guide

This guide explains how to run this LMS as a **single-tenant application** — without the Stancl multi-tenancy layer. Follow every section in order before going live.

---

## What the Multi-Tenancy Layer Does (and What You're Removing)

The current codebase uses [stancl/tenancy](https://tenancyforlaravel.com/) to serve many organizations from one installation:

| Tenancy Concern | How It Works Now | How It Works Standalone |
|---|---|---|
| Database isolation | One MySQL DB per tenant (`tenant_{uuid}`) | Single database |
| Domain routing | `{subdomain}.central.domain` resolves tenant | Your own domain only |
| Request bootstrapping | `InitializeTenancyByDomain` middleware | Not needed |
| Active status gate | `TenantActivated` middleware checks `tenant()->status` | Not needed |
| Tenant identity | `tenancy()->tenant->id / domain_name / api_endpoint` | `.env` values |
| Super admin panel | Central DB with separate super_admins table | Not applicable |

---

## Prerequisites

- PHP 8.3+
- MySQL 8.0+
- Redis
- Composer
- Node.js 18+ (for the Socket.io server)
- AWS S3 bucket (or use local storage for development)
- Laravel Passport keys

---

## Step 1 — Database

You only need the **tenant migrations** (`database/migrations/tenant/` — 134 files).

### 1a. Copy tenant migrations to the standard path

```bash
cp database/migrations/tenant/* database/migrations/
```

Do **not** run the 9 central migrations. They create tables for the multi-tenant
registry (`tenants`, `domains`, `super_admins`, `tenant_infos`) that you won't need.

The central migrations you can safely ignore:
- `2019_09_15_000010_create_tenants_table.php`
- `2019_09_15_000020_create_domains_table.php`
- `2022_02_16_052934_create_super_admins_table.php`
- `2022_02_16_203415_create_tenant_infos_table.php`
- `2022_12_01_120122_create_images_table.php`

Keep these central ones (they are generic infrastructure):
- `2014_10_12_100000_create_password_resets_table.php`
- `2019_08_19_000000_create_failed_jobs_table.php`
- `2019_12_14_000001_create_personal_access_tokens_table.php`
- `2023_01_12_132519_create_jobs_table.php`

### 1b. Run migrations

```bash
php artisan migrate
```

### 1c. Seed the database

```bash
php artisan db:seed
```

---

## Step 2 — Environment Variables

Copy `STANDALONE_SETUP/.env.standalone` to `.env` and fill in your values:

```bash
cp STANDALONE_SETUP/.env.standalone .env
```

Key differences from the multi-tenant `.env`:
- `APP_URL` is your own domain (e.g. `https://lms.mycompany.com`)
- `APP_DOMAIN` replaces the old `APP_CENTRAL` — just your domain without protocol
- `TENANT_ID` is a short identifier for your organization (used for Passport token names, S3 paths, and certificate QR codes). Use something like `myorg`.
- `TENANT_NAME` is your organization's display name.
- Only one `DB_CONNECTION` / `DB_HOST` needed — no central/read replica setup required unless you want it.

---

## Step 3 — Register the Standalone Providers

In `config/app.php`, replace:

```php
App\Providers\TenancyServiceProvider::class,
```

with:

```php
App\Providers\StandaloneServiceProvider::class,
```

The `StandaloneServiceProvider` is in `STANDALONE_SETUP/app/Providers/StandaloneServiceProvider.php`.
Copy it to `app/Providers/` before making this change.

---

## Step 4 — Replace the Route Service Provider

In `config/app.php`, replace:

```php
App\Providers\RouteServiceProvider::class,
```

with:

```php
App\Providers\StandaloneRouteServiceProvider::class,
```

The `StandaloneRouteServiceProvider` is in `STANDALONE_SETUP/app/Providers/StandaloneRouteServiceProvider.php`.
Copy it to `app/Providers/` before making this change.

This provider loads the standalone route files instead of the tenancy-wrapped ones.

---

## Step 5 — Copy the Standalone Route Files

```bash
cp STANDALONE_SETUP/routes/standalone_public.php   routes/
cp STANDALONE_SETUP/routes/standalone_admin.php    routes/
cp STANDALONE_SETUP/routes/standalone_instructor.php routes/
cp STANDALONE_SETUP/routes/standalone_user.php     routes/
```

These are identical to the originals except the three tenancy middlewares
(`InitializeTenancyByDomain`, `PreventAccessFromCentralDomains`, `TenantActivated`)
have been removed from every route group.

---

## Step 6 — Copy the Standalone Config Helper

```bash
cp STANDALONE_SETUP/app/Helpers/StandaloneConfig.php app/Helpers/
```

This class provides `StandaloneConfig::tenantId()`, `::domainName()`, and `::apiEndpoint()`
as drop-in replacements for `tenancy()->tenant->id`, `tenancy()->tenant->domain_name`,
and `tenancy()->tenant->api_endpoint`. Their values come from `.env`.

---

## Step 7 — Patch Tenancy References in App Code

The following files call `tenancy()` or `tenant()`. You need to replace those calls
with the `StandaloneConfig` equivalents. The table below shows every occurrence:

| File | Original Call | Replace With |
|---|---|---|
| `app/Http/Controllers/Api/V1/Tenants/AuthController.php` | `tenancy()->tenant->domain_name` | `StandaloneConfig::domainName()` |
| `app/Http/Controllers/Api/V1/Tenants/AuthController.php` | `tenancy()->tenant->id` | `StandaloneConfig::tenantId()` |
| `app/Http/Controllers/Api/V1/Tenants/AuthController.php` | `tenant()->id` (token name) | `StandaloneConfig::tenantId()` |
| `app/Http/Controllers/Api/V1/Tenants/User/CertificateController.php` | `tenancy()->tenant->domain_name` | `StandaloneConfig::domainName()` |
| `app/Http/Controllers/Api/V1/Tenants/User/CertificateController.php` | `tenancy()->tenant->id` | `StandaloneConfig::tenantId()` |
| `app/Http/Controllers/Api/V1/Tenants/Admin/UserManagementController.php` | `tenancy()->tenant->domain_name` | `StandaloneConfig::domainName()` |
| `app/Http/Controllers/Api/V1/Tenants/Admin/UserManagementController.php` | `tenancy()->tenant->id` | `StandaloneConfig::tenantId()` |
| `app/Http/Controllers/Api/V1/Central/TenantController.php` | `tenancy()->tenant->id` | `StandaloneConfig::tenantId()` |
| `app/Services/Tenants/CheckoutService.php` | `tenancy()->tenant->api_endpoint` | `StandaloneConfig::apiEndpoint()` |
| `app/Services/Tenants/ScormCloudService.php` | `tenancy()->tenant->api_endpoint` (×3) | `StandaloneConfig::apiEndpoint()` |
| `app/Services/Tenants/LearningMaterialService.php` | `tenancy()->tenant->id` | `StandaloneConfig::tenantId()` |
| `app/Observers/UserObserver.php` | `tenancy()->tenant->domain_name` | `StandaloneConfig::domainName()` |
| `app/Helpers/Helper.php` | `tenant()->id` | `StandaloneConfig::tenantId()` |

Add this import at the top of each file you edit:

```php
use App\Helpers\StandaloneConfig;
```

---

## Step 8 — Patch Console Commands

The following Artisan commands use `tenancy()->find($tenantId)` to switch database
context before running a job. In standalone mode, you are already in the only
database — so remove the `tenancy()->find()` call and run the job directly.

Commands to patch (all in `app/Console/Commands/`):

- `DispatchSetModuleContent.php`
- `DispatchSetAssessmentQuestionSortNo.php`
- `DispatchProfilePageDataNotification.php`
- `DispatchLearnerSetTransactionData.php`
- `DispatchLearnerSetProgress.php`
- `DispatchLearnerSetEvaluationProgress.php`
- `DispatchLearnerSetAssessmentStatus.php`
- `DispatchLearnerCourseCompletionActivityLog.php`
- `DispatchLearnerCertificateActivityLog.php`
- `DispatchLearnerAssessmentDeadlineNotification.php`
- `DispatchFixAssessmentModuleId.php`
- `DispatchCourseTrainingCredit.php`
- `DispatchAssessmentSetTotalPoints.php`
- `DispatchAdjustLearnerAssessmentScoring.php`

In each one, find the pattern:

```php
$tenant = tenancy()->find($tenantId);
$tenant->run(function () {
    // ... dispatch job
});
```

Replace with:

```php
// dispatch job directly — no tenant context switching needed
```

---

## Step 9 — Laravel Passport Setup

```bash
php artisan passport:install
php artisan passport:client --personal
```

The personal access client name can be anything (e.g. your organization's name).
Update `PASSPORT_PERSONAL_ACCESS_CLIENT_ID` and `PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET` in `.env`.

---

## Step 10 — Authentication: Admin Account

There is no "super admin" seeder in this codebase — in the multi-tenant setup,
the initial admin account was created automatically when a tenant was activated.

Create your first admin user manually:

```bash
php artisan tinker
```

```php
$user = App\Models\User::create([
    'first_name'  => 'Admin',
    'last_name'   => 'User',
    'email'       => 'admin@yourorg.com',
    'password'    => bcrypt('your-secure-password'),
    'email_verified_at' => now(),
]);

// Assign admin role (requires spatie/laravel-permission to be set up)
$user->assignRole('ADMIN');
```

---

## Step 11 — Queue Worker

```bash
php artisan queue:work redis --queue=default --tries=3
```

Or with PM2 (see `ecosystem.config.js`):

```bash
pm2 start ecosystem.config.js
```

---

## Step 12 — Socket.io Server (Real-Time Chat & Notifications)

```bash
npm install        # in the backend root
node server.js
```

Or with PM2:

```bash
pm2 start server.js --name "socket-server"
```

Update `.env` with the correct `PUSHER_APP_*` values matching `server.js` configuration.

---

## Step 13 — Frontend

```bash
cd ../lms-frontend
cp .env.example .env
# Set VUE_APP_API to your backend URL, e.g. https://api.mycompany.com/v1
npm install
npm run build
pm2 start pm2.config.js
```

---

## What You Do NOT Need

The following are multi-tenant only and can be ignored entirely:

| Item | Location | Reason |
|---|---|---|
| `TenancyServiceProvider` | `app/Providers/` | Registers tenant route maps and DB bootstrappers |
| `TenantActivated` middleware | `app/Http/Middleware/` | Checks central DB tenant status |
| Central migrations | `database/migrations/` (9 files) | Central registry tables |
| `Central\Tenant` model | `app/Models/Central/` | Stancl tenant model |
| `Central\TenantInfo` model | `app/Models/Central/` | Contact metadata |
| `Central\SuperAdmin` model | `app/Models/Central/` | Platform operator login |
| `TenantController` CRUD methods | `app/Http/Controllers/Api/V1/Central/` | Super admin manages tenants |
| `AuthController` (Central) | `app/Http/Controllers/Api/V1/Central/` | Super admin login/logout |
| `stancl/tenancy` package | `composer.json` | Can remove to reduce dependencies |

> You do not need to delete any of these. They will simply be unused code.
> Remove only if you want a cleaner install.

---

## Nginx Configuration

```nginx
server {
    listen 80;
    server_name lms.mycompany.com;
    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## Summary of Changes

| # | Change | Files Affected |
|---|---|---|
| 1 | Use tenant migrations only | `database/migrations/` |
| 2 | Set `.env` values for domain/tenant identity | `.env` |
| 3 | Register `StandaloneServiceProvider` | `config/app.php` |
| 4 | Register `StandaloneRouteServiceProvider` | `config/app.php` |
| 5 | Use standalone route files (no tenancy middleware) | `routes/` |
| 6 | Add `StandaloneConfig` helper | `app/Helpers/` |
| 7 | Replace `tenancy()` calls with `StandaloneConfig` | 8 files |
| 8 | Strip `tenancy()->find()` from Artisan commands | 14 commands |
| 9 | Run `passport:install` for single-app OAuth | — |
| 10 | Create first admin user via tinker | — |
