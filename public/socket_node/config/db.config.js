const mysql = require('mysql2');
const db = mysql.createConnection({
    host: '127.0.0.1',   
    user: 'aadyaUsrNew',         
    password: 'MyA@F%^232gsa',         
    database: 'aadyaDBNew',   
    multipleStatements: true,
});

db.connect((err) => {
    if (err) {
        console.error('Error connecting to the database:', err.message);
        return;
    }
    console.log('Connected to the MySQL database.');
});

module.exports = db;
