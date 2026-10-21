const express = require("express");
const Redis = require("ioredis");
const http = require("http");
const https = require("https");
const fs = require("fs");
const Server = require("socket.io");
const url = require("url");
const WebSocket = require("ws");
const cors = require('cors');
require("dotenv").config();

const app = express();
app.use(cors());

// Set up HTTP or HTTPS server based on environment
let httpd;

if (process.env.APP_ENV === "prod") {
  
  httpd = https.createServer(
    {
      key: fs.readFileSync(`${process.env.SSL_DIR_PRIVKEY}`, "utf8"),
      cert: fs.readFileSync(`${process.env.SSL_DIR_FULLCHAIN}`, "utf8"),
    },
    app
  );
} else {
  httpd = http.createServer(app);
}

const redis = new Redis({
  host: process.env.REDIS_HOST || "127.0.0.1",
  port: 6379,
});

redis.on('ready', () => {
  console.log('Redis client connected successfully');
});

redis.on('error', (error) => {
  console.error('Redis error:', error);
});

redis.psubscribe(`private-chat-*`, (err, count) => {
  if (err) {
    console.error("Failed to subscribe:", err.message);
  } else {
    console.log(`Subscribed successfully to ${count} channels.`);
  }
});

redis.on('pmessage', (pattern, channel, message) => {
  console.log(`Message on channel ${channel}:`, message);
  try {
    const msg = JSON.parse(message);
    io.emit(`private-${msg.data.data.enrollee_id}`, msg);
  } catch (e) {
    console.error('Error parsing message:', e);
  }
});

const io = new Server(httpd, {
 cors: {
    origin: "*",
  },
});
// const wss = new WebSocket.Server({
//   server: httpd,
//   clientTracking: true,
// });

io.on("error", function error(e) {
  console.log(e);
});

const connectedClients = {};

io.on("connection", function connection(socket) {

  connectedClients[socket.id] = {
      id: socket.id,
  };

  console.log(connectedClients)

  socket.on("message", function incoming(message) {
    console.log(`Received from ${id}: ${message}`);
  });

  socket.on("disconnect", () => {
    console.log(`Client disconnected: ${socket.id}`);
    delete connectedClients[socket.id];
    console.log("Remaining clients:", connectedClients);
  });

  socket.on("registerConnection", async (data) => {
    const { connectionName } = data;
    console.log(`Received connection name: ${connectionName} for socket ID: ${socket.id}`);

    console.log('Connected clients:', connectedClients);

  });
});

app.get("/", (req, res) => {
  res.send({
    message: "Server is running",
    status: 200,
  });
});

httpd.listen(6002, () => {
  console.log("Server running on port 6002");
});
