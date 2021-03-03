var express = require('express');
var router = express.Router();
const uuid = require('uuid-v4');
const formidable = require('formidable')
const { firebaseDB, storage, bucket, mysqlDB } = require("./database");
const cron = require('node-cron');
const moment = require('moment');

/* GET home page. */
// router.get('/home', function(req, res, next) {

//     console.log(socket);

//     getIOInstance.sockets.emit('getNotification', 'res');

//     var date = moment().add(30, 'm').toDate();

//     mysqlDB.query("SELECT * FROM users", (err, users) => {
//         if (err) {
//             console.log("error: ", err);
//             throw err;
//             return;
//         }
//         // console.log("customers: ", res);
//         res.send({
//             status: 200,
//             code: 200,
//             message: 'home.',
//             date: date,
//             // result: users,
//         });
//     });
// });

// router.get('/chat/:chatId', function(req, res) {
//     firebaseDB.ref("/chats/" + req.params.chatId).once("value", function(snapshot) {
//         let messages = [];
//         // console.log(t.val());
//         /* snapshot.forEach(function(childSnapshot) {
//             var key = childSnapshot.key;
//             var childData = childSnapshot.val();
//             messages.push({
//                 userId: childData.userId,
//                 text: childData.text,
//                 date: childData.date,
//             })
//         }); */

//         res.send({
//             status: 200,
//             code: 200,
//             message: 'History get.',
//             data: snapshot.val()
//         });
//     })
// })

// router.post('/uploadFile', function(req, res) {
//     form = new formidable.IncomingForm();
//     console.log('file called');
//     form.parse(req, function(err, fields, files) {
//         let imageObject = files.image;
//         let extension = imageObject.name.split('.').pop();
//         let random = Math.random().toString(36).substring(2, 1000);
//         var fileName = random + '.' + extension;
//         var filePath = 'chat/' + fields.chatId + '/' + fileName;
//         // res.status(200).json(files);

//         const metadata = {
//             metadata: {
//                 // This line is very important. It's to create a download token.
//                 firebaseStorageDownloadTokens: uuid()
//             },
//             contentType: imageObject.type,
//             cacheControl: 'public, max-age=31536000',
//         };

//         // Uploads a local file to the bucket
//         bucket.upload(imageObject.path, {
//             destination: filePath,
//             // Support for HTTP requests made with `Accept-Encoding: gzip`
//             gzip: true,
//             metadata: metadata,
//         }, function(err, newFile, apiResponse) {
//             /* console.log(apiResponse);
//             console.log(apiResponse.mediaLink); */
//             const downloadUrl = createPersistentDownloadUrl(filePath, apiResponse.metadata.firebaseStorageDownloadTokens)
//             const data = {
//                 senderId: fields.senderId,
//                 receiverId: fields.receiverId,
//                 messageText: '',
//                 fileUrl: downloadUrl ? downloadUrl : '',
//                 storageUri: apiResponse.mediaLink ? apiResponse.mediaLink : '',
//                 timestamp: fields.timestamp
//             }
//             firebaseDB.ref("/chats/" + fields.chatId).push(data)
//             io.sockets.emit('receiveMessage', data)
//             res.send({
//                 status: 200,
//                 code: 200,
//                 message: 'Image sended.',
//                 data: data
//             });
//         });
//     });
// })

// /* All Crons */
// cron.schedule('*/10 * * * * *', function() {
//     console.log('running a task every minute');
//     var date = moment().add(15, 'm').format('YYYY-MM-DD H:mm:00');
//     c(date);
//     /* "SELECT * FROM appointments where status IN('create','attempt') AND start_time = '" + date + "'" */
//     mysqlDB.query("SELECT * FROM appointments where status IN('create','attempt')", (err, appointments) => {
//         if (err) {
//             console.log("error: ", err);
//             throw err;
//             return;
//         }
//         appointments.forEach(function(row) {
//             // console.log(row);
//             // io.sockets.emit('getNotification', row);
//         });

//         // c(appointments);
//     });

// });

// module.exports = router;

module.exports = function(getIOInstance) {

    router.get('/home', function(req, res, next) {

        var date = moment().add(30, 'm').toDate();

        mysqlDB.query("SELECT * FROM users", (err, users) => {
            if (err) {
                console.log("error: ", err);
                throw err;
                return;
            }
            // console.log("customers: ", res);
            res.send({
                status: 200,
                code: 200,
                message: 'home.',
                date: date,
                // result: users,
            });
        });
    });

    router.get('/chat/:chatId', function(req, res) {
        firebaseDB.ref("/chats/" + req.params.chatId).once("value", function(snapshot) {
            let messages = [];
            // console.log(t.val());
            /* snapshot.forEach(function(childSnapshot) {
                var key = childSnapshot.key;
                var childData = childSnapshot.val();
                messages.push({
                    userId: childData.userId,
                    text: childData.text,
                    date: childData.date,
                })
            }); */

            res.send({
                status: 200,
                code: 200,
                message: 'History get.',
                data: snapshot.val()
            });
        })
    })

    /* All Crons */
    cron.schedule('* * * * *', function() {
        console.log('running a task every minute');
        var before_5 = moment().add(5, 'm').format('YYYY-MM-DD H:mm:00');
        var before_15 = moment().add(15, 'm').format('YYYY-MM-DD H:mm:00');

        /* "SELECT * FROM appointments where status IN('create','attempt') AND start_time = '" + date + "'" */
        /* "SELECT app.*,u.name as doctor_name FROM appointments as app left join users as u on u.id = app.doctor_id where app.status IN('create','attempt') */
        mysqlDB.query("SELECT app.*,u.name as doctor_name FROM appointments as app left join users as u on u.id = app.doctor_id where app.status IN('create','attempt') AND app.start_time = '" + before_5 + "'", (err, appointments) => {
            if (err) {
                console.log("error: ", err);
                throw err;
                return;
            }
            appointments.forEach(function(row) {
                // console.log(row);
                getIOInstance().sockets.emit('notifyVideoConsult', row);
            });

            // c(appointments);
        });

        mysqlDB.query("SELECT app.*,u.name as doctor_name FROM appointments as app left join users as u on u.id = app.doctor_id where app.status IN('create','attempt') AND app.start_time = '" + before_15 + "'", (err, appointments) => {
            if (err) {
                console.log("error: ", err);
                throw err;
                return;
            }
            appointments.forEach(function(row) {
                // console.log(row);
                getIOInstance().sockets.emit('notifyVideoConsult', row);
            });
        });

    });

    return router;
}