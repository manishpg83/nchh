const admin = require("firebase-admin");
const { Storage } = require('@google-cloud/storage');
serviceAccount = require("../neucrad-b797d-firebase-adminsdk-fektv-860ab67d2a.json");

const mysql = require("mysql");
const dbConfig = require("./db.config");

/* Firebase db connection */
admin.initializeApp({
    credential: admin.credential.cert(serviceAccount),
    databaseURL: process.env.databaseURL || "https://neucrad-b797d.firebaseio.com",
    storageBucket: process.env.storageBucket || "neucrad-b797d.appspot.com"
});

const mysqlDB = mysql.createConnection({
    host: dbConfig.HOST,
    user: dbConfig.USER,
    password: dbConfig.PASSWORD,
    database: dbConfig.DB
});

mysqlDB.connect(error => {
    if (error) throw error;
    console.log("Successfully connected to the database.");
});

/* const storage = new Storage();
const bucket = storage.bucket('neucrad-b797d.appspot.com'); */

var firebaseDB = admin.database();
var storage = admin.storage();
var bucket = admin.storage().bucket();

module.exports = {
        firebaseDB: firebaseDB,
        storage: storage,
        bucket: bucket,
        mysqlDB: mysqlDB
    }
    // module.exports = db, storage, bucket;