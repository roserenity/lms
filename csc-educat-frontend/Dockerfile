# Builds the Vue app and serves the static bundle with Nginx.
# Vue CLI 4 (webpack 4) needs Node 16; newer versions fail on OpenSSL 3.
FROM node:16-bullseye AS build
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --legacy-peer-deps

# VUE_APP_* values are baked into the bundle at build time.
ARG VUE_APP_NAME
ARG VUE_APP_API
ARG VUE_APP_TENANT_API
ARG VUE_APP_DOMAIN
ARG VUE_APP_LANDING_DOMAIN
ARG VUE_APP_ASSET_PATH
ARG VUE_APP_AWS_S3
ARG VUE_APP_TINYMCE_KEY
ENV VUE_APP_NAME=$VUE_APP_NAME \
    VUE_APP_API=$VUE_APP_API \
    VUE_APP_TENANT_API=$VUE_APP_TENANT_API \
    VUE_APP_DOMAIN=$VUE_APP_DOMAIN \
    VUE_APP_LANDING_DOMAIN=$VUE_APP_LANDING_DOMAIN \
    VUE_APP_ASSET_PATH=$VUE_APP_ASSET_PATH \
    VUE_APP_AWS_S3=$VUE_APP_AWS_S3 \
    VUE_APP_TINYMCE_KEY=$VUE_APP_TINYMCE_KEY

COPY . .
RUN npm run build

FROM nginx:1.27-alpine
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist /usr/share/nginx/html
EXPOSE 80
