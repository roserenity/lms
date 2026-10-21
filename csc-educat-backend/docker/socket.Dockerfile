# Socket.io server (server.js) for chat and notifications.
FROM node:18-alpine
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY server.js ./
EXPOSE 6002
CMD ["node", "server.js"]
