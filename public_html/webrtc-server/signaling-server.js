const WebSocket = require('ws');
const mysql = require('mysql');

// Create a MySQL database connection
const db = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'administrator'
});

db.connect((err) => {
    if (err) {
        console.error('Database connection failed:', err.stack);
        return;
    }
    console.log('Connected to database.');
});

const wss = new WebSocket.Server({ port: 8080 });

const peers = {}; // Store connected peers

wss.on('connection', (ws) => {
    console.log('New client connected');

    ws.on('message', (message) => {
        const data = JSON.parse(message);
        console.log('Received message:', data);

        switch (data.type) {
            case 'join':
                handleJoin(ws, data);
                break;
            case 'chat':
                handleChat(ws, data);
                break;
            case 'offer':
            case 'answer':
            case 'candidate':
                broadcastToMeeting(data.meeting_id, data, ws);
                break;
            case 'leave':
                handleLeave(ws, data);
                break;
        }
    });

    ws.on('close', () => {
        handleDisconnect(ws);
    });
});

function handleJoin(ws, data) {
    db.query('SELECT first_name, surname FROM ?? WHERE username = ?',
        [data.usergroup === 'Patient' ? 'patient_db' : 'users', data.username],
        (err, results) => {
            if (err) {
                console.error('Error fetching user details:', err.stack);
                return;
            }

            if (results.length > 0) {
                const user = results[0];
                const joinMessage = {
                    type: 'user-joined',
                    username: data.username,
                    meeting_id: data.meeting_id
                };

                peers[data.meeting_id] = peers[data.meeting_id] || [];
                peers[data.meeting_id].push(ws);

                broadcastToMeeting(data.meeting_id, joinMessage, ws);

                console.log(`Client joined meeting: ${data.meeting_id}`);
            }
        });
}

function handleChat(ws, data) {
    // Save the message to the database
    db.query('INSERT INTO session_messages (meeting_id, username, message) VALUES (?, ?, ?)', 
    [data.meeting_id, data.username, data.message], (err) => {
        if (err) {
            console.error('Error saving message:', err.stack);
        }
    });

    // Broadcast the message to all peers in the same meeting
    broadcastToMeeting(data.meeting_id, data, ws);
}

function broadcastToMeeting(meeting_id, message, senderWs) {
    if (peers[meeting_id]) {
        peers[meeting_id].forEach(peer => {
            if (peer !== senderWs) {
                peer.send(JSON.stringify(message));
            }
        });
    }
}

function handleLeave(ws, data) {
    if (peers[data.meeting_id]) {
        peers[data.meeting_id] = peers[data.meeting_id].filter(peer => peer !== ws);
        console.log(`Client left meeting: ${data.meeting_id}`);
    }
}

function handleDisconnect(ws) {
    for (const meeting_id in peers) {
        peers[meeting_id] = peers[meeting_id].filter(peer => peer !== ws);
        if (peers[meeting_id].length === 0) {
            delete peers[meeting_id];
        }
    }
    console.log('Client disconnected');
}

console.log('Signaling server running on ws://localhost:8080');
