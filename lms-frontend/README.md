# Aster Academy LMS — Frontend

Vue 2 single-page app for the Aster Academy learning management system, with separate panels for admins, instructors and students. See the [project README](../README.md) for the overall architecture.

## Stack

- Vue 2.7, Vuetify 2, Vuex 3, Vue Router 3, Axios
- socket.io-client and laravel-echo for chat and notifications
- TinyMCE, FullCalendar, ApexCharts, Plyr, Zoom Meeting SDK

## Setup

```bash
cp .env.example .env
npm install --legacy-peer-deps
```

Set `VUE_APP_API` to the backend URL. Every variable is described in [environment_variables.md](environment_variables.md).

## Running

```bash
npm run serve   # development, with hot reload
npm run build   # production build
```

In production the app runs under PM2 on port 3000 (`pm2.config.js`).

## Documentation

- [architecture.md](architecture.md): routing, store layout and middleware
- [deploymentguide.md](deploymentguide.md): server setup
- [STANDALONE_SETUP.md](STANDALONE_SETUP.md): deploying for a single organization
