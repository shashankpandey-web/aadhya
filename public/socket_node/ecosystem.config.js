module.exports = {
  apps: [
    {
      name: "Addya",
      script: "./app.js",
      watch: true,
      ignore_watch: ["node_modules", "public"],
      instances: 1,       
      exec_mode: "fork", 
    },
  ],
};