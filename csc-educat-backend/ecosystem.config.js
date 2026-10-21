module.exports = {
  apps: [
    {
      name: "laravel-queue-worker",
      script: "php",
      args: "artisan queue:work --tries=3",
      autorestart: true,
      watch: false,
      max_memory_restart: "500M",
      env: {
        NODE_ENV: "production",
      },
    },
  ],
};