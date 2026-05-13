module.exports = {
  apps: [
    {
      name: "Aadya Laravel",
      script: "artisan",
      cwd: "/projects/aadya.infosparkles.com",
      interpreter: "php",
      args: "queue:work --sleep=3 --tries=3 --timeout=300",
      autorestart: true,
      watch: true,
      max_memory_restart: "500M",
      error_file: "/projects/aadya.infosparkles.com/storage/logs/pm2-error.log",
      out_file: "/projects/aadya.infosparkles.com/storage/logs/pm2-out.log",
    },
  ],
};
