// var express = require('express');
// var app = express();
// var http = require('http').Server(app);
// var io = require('socket.io')(http);
// var path = require('path');
// var admin = require("firebase-admin");

// var serviceAccount = require("./neucrad-b797d-firebase-adminsdk-fektv-860ab67d2a.json");

// admin.initializeApp({
//     credential: admin.credential.cert(serviceAccount),
//     databaseURL: "https://neucrad-b797d.firebaseio.com"
// });

// // As an admin, the app has access to read and write all data, regardless of Security Rules
// var db = admin.database();
// var ref = db.ref("/chats");

// /* db.ref(`/chats/${1}`).push().set({
//     userId: 1,
//     text: "HIEO",
//     date: "time"
// })
//  */

// /* ref.once("value", function(snapshot) {
//     console.log("Yeah this is snapshots.");
//     console.log(snapshot.val());
// }); */

// app.get('/chat/:userId', function(req, res) {
//     res.send(req.params)
// })


// // Register events on socket connection
// io.on('connection', function(socket) {

//     console.log('connected');
//     socket.on('sendChatToServer', function(message) {
//         console.log(message);
//         io.sockets.emit('serverChatToClient', message);
//     });

//     // Listen to notifyTyping event sent by client and emit a notifyTyping to the client
//     socket.on('notifyTyping', function(sender, sender_name, receiver) {
//         io.sockets.emit('notifyTyping', sender, sender_name, receiver);
//     });

//     socket.on('disconnect', function() {
//         console.log('leaved');
//     });

// });

// // Listen application request on port 3000
// http.listen(3000, function() {
//     console.log('listening on *:3000');
// });