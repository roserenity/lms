module.exports = {
    apps : [{
      name   : "lms-frontend",
      script : "npm",
      watch: true,
      autorestart: true,
      env_production: {
        PORT: 3000,
        NODE_ENV: "production"
      }
    }]
  }