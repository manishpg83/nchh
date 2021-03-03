require('dotenv').config({ path: './chat_service/.env' });
const express = require('express');
const fs = require('fs');

const options = {
    key: fs.readFileSync('./chat_service/privkey.pem','utf8'),
    cert: fs.readFileSync('./chat_service/cert.pem','utf8'),
    ca: fs.readFileSync('./chat_service/fullchain.pem','utf8')
  };

const app = express();
const cors = require("cors");

const https = require('https').Server(options, app);
const bodyParser = require('body-parser');

const io = require('socket.io')(https,
    {
        cors: {
          origin: "https://www.nchealthhub.com",
          methods: ["GET", "POST"],
          credentials:true,
          transports: ['websocket', 'polling']
        },
        allowEIO3: true
      });
const path = require('path');
const moment = require('moment');
const cron = require('node-cron');
const createError = require('http-errors');

const { firebaseDB, storage, bucket } = require("./database");
const users = {};

app.use(cors());
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());
app.use(bodyParser.raw());

/* all routes and crons */
var getIOInstance = function () {
    return io;
};
app.use('/', require('./routes')(getIOInstance));

io.use(function (socket, next) {
    /* console.log('Query: ', socket.handshake.query);
    const user = socket.handshake.query */
    var id = socket.handshake.query.id;
    if (id) {

        users[socket.id] = {
            id: socket.handshake.query.id,
            name: socket.handshake.query.name,
            email: socket.handshake.query.email,
        };

        // users[socket.id] = socket.handshake.query;
        return next();

    }
    // call next() with an Error if you need to reject the connection.
    next(new Error('Authentication error'));
});

io.on('connection', function (socket) {
    let user = users[socket.id];
    console.log('connected user:' + user.name);

    /* On connect change status online = true */
    firebaseDB.ref("/users/" + user.id).update({
        id: user.id ? user.id : '',
        name: user.name ? user.name : '',
        email: user.email ? user.email : '',
        online: true,
        timestamp: moment().format(),
    });

    // io.sockets.emit('activeUsers', users)

    socket.on('sendMessage', function (res) {
        let getResponse = JSON.parse(res);
        firebaseDB.ref("/chats/" + getResponse.chatId).push(getResponse.message)
        io.sockets.emit('receiveMessage', getResponse.message);
        io.sockets.connected[socket.id].emit('syncMessageCounter', getResponse.message);
    });

    socket.on('sendChatToServer', function (message) {
        console.log(message);
        console.log(db.ref("chats"));
        io.sockets.emit('serverChatToClient', message);
    });

    // Listen to notifyTyping event sent by client and emit a notifyTyping to the client
    socket.on('notifyTyping', function (res) {
        io.sockets.emit('notifyTyping', res);
    });

    socket.on('disconnect', function () {
        console.log('leaved ' + socket.id);
        delete users[socket.id];

        /* On connect change status online = false */
        firebaseDB.ref("/users/" + user.id).update({
            id: user.id ? user.id : '',
            name: user.name ? user.name : '',
            email: user.email ? user.email : '',
            online: false,
            timestamp: moment().format()
        });
        // io.sockets.emit('activeUsers', users)
    });
});

https.listen(process.env.PORT || '8000', function () {
    console.log(`server listening on *:${process.env.PORT}`);
});

createPersistentDownloadUrl = (pathToFile, downloadToken) => {
    return `https://firebasestorage.googleapis.com/v0/b/${process.env.storageBucket}/o/${encodeURIComponent(
        pathToFile
    )}?alt=media&token=${downloadToken}`;
};

c = (value, i = 0) => {
    console.log(value);
    if (i) {
        return false;
    }
};