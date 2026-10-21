# Environment Variables

Copy `.env.example` to `.env` and fill in each value before running the app.

> Vue CLI only exposes variables prefixed with `VUE_APP_` to the browser bundle. Plain variables like `ENV` are only available to build-time Node scripts.

---

## Application

| Variable | Required | Description |
|---|:---:|---|
| `VUE_APP_NAME` | Yes | Browser tab title (`document.title`). Example: `Aster Academy e-Learning` |
| `ENV` | No | Build-time environment label. Not exposed to the Vue bundle — used only in Node/build scripts. |

---

## Backend API

| Variable | Required | Description |
|---|:---:|---|
| `VUE_APP_API` | Yes | Base URL of the backend API (includes version path). Used for all API calls and as the socket host base. Example: `https://api.lms.example.com/v1` |

---

## Real-time (Socket.io / Laravel Echo)

| Variable | Required | Description |
|---|:---:|---|
| `VUE_APP_ENV` | Yes | Set to `PROD` in production. `src/plugins/socket.js` uses this to switch the Socket.io host: `PROD` → `{VUE_APP_API without /v1}:6002`; any other value → `http://localhost:6002` |
| `VUE_APP_SOCKET_HOST` | No | Declared in `.env.example` but **not read** by `socket.js`. Reserved for future use. |
| `VUE_APP_SOCKET_PORT` | No | Declared in `.env.example` but **not read** by `socket.js`. Port is hardcoded to `6002`. Reserved for future use. |

---

## File Storage

| Variable | Required | Description |
|---|:---:|---|
| `VUE_APP_ASSET_PATH` | Yes | Base URL for uploaded files served from storage or CDN. Prepended to image and file paths throughout the app. Example: `https://assets.lms.example.com/storage` |
| `VUE_APP_AWS_S3` | Yes | AWS S3 bucket URL used specifically for SCORM package uploads (`src/constants/material/Scorm.vue`). Example: `https://s3.ap-southeast-1.amazonaws.com/lms-bucket` |

---

## Third-party Integrations

### TinyMCE

| Variable | Required | Description |
|---|:---:|---|
| `VUE_APP_TINYMCE_KEY` | Yes | API key for the TinyMCE rich-text editor. Used in course descriptions, announcements, evaluations, assessments, and the reusable `Editor.vue` component. Obtain from [tiny.cloud](https://www.tiny.cloud). |

### Zoom

| Variable | Required | Description |
|---|:---:|---|
| `VUE_APP_ZOOM_SDK_KEY` | Yes | Zoom Meeting SDK client key. Used in `src/services/jsrsasign.js` to generate the SDK JWT and in `ZoomMeeting.vue` to initialise the Zoom web SDK. Obtain from the Zoom Marketplace app dashboard. |
| `VUE_APP_ZOOM_SDK_SECRET` | Yes | Zoom Meeting SDK client secret. Used alongside `VUE_APP_ZOOM_SDK_KEY` to sign the HS256 JWT in `src/services/jsrsasign.js`. Keep this value secret and never commit it. |

### Google Analytics

| Variable | Required | Description |
|---|:---:|---|
| `VUE_APP_GA_TRACKING_ID` | No | Google Analytics 4 measurement ID. Read by `App.vue` and `public/index.html`. Analytics is not loaded when this is empty. |

---

## PM2 / Node (server-level, not Vue bundle)

These are set by PM2 at runtime and are **not** available inside the Vue application.

| Variable | Default | Description |
|---|---|---|
| `PORT` | `3000` | Port the Node server listens on (set in `pm2.config.js` under `env_production`). |
| `NODE_ENV` | `production` | Standard Node environment string (set in `pm2.config.js` under `env_production`). |

---

## Full `.env` Template

```env
# Application
VUE_APP_NAME=

# Build environment (Node/build scripts only — not exposed to Vue bundle)
ENV=

# Backend API
VUE_APP_API=

# Real-time
VUE_APP_ENV=
VUE_APP_SOCKET_HOST=
VUE_APP_SOCKET_PORT=

# File storage
VUE_APP_ASSET_PATH=
VUE_APP_AWS_S3=

# TinyMCE
VUE_APP_TINYMCE_KEY=

# Zoom
VUE_APP_ZOOM_SDK_KEY=
VUE_APP_ZOOM_SDK_SECRET=

# Google Analytics
VUE_APP_GA_TRACKING_ID=
```

---

## Notes

- **Never commit `.env`** to version control. It is listed in `.gitignore`.
- `VUE_APP_ZOOM_SDK_SECRET` signs JWTs client-side (`src/services/jsrsasign.js`). For production, consider moving JWT generation to the backend to keep the secret off the browser.
- `VUE_APP_SOCKET_HOST` and `VUE_APP_SOCKET_PORT` are declared in `.env.example` but unused. The socket connection is configured entirely from `VUE_APP_API` and `VUE_APP_ENV`.
- `VUE_APP_GA_TRACKING_ID` has no effect until the hardcoded ID in `App.vue` is replaced with `process.env.VUE_APP_GA_TRACKING_ID`.
