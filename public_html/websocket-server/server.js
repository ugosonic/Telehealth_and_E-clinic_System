const WebSocket = require('ws');
const wss = new WebSocket.Server({ port: 8080 });

const clients = new Set();

wss.on('connection', (ws) => {
    clients.add(ws);

    ws.on('message', (message) => {
        const data = JSON.parse(message);
        if (data.type === 'chat') {
            // Broadcast message to all connected clients
            clients.forEach(client => {
                if (client !== ws && client.readyState === WebSocket.OPEN) {
                    client.send(JSON.stringify(data));
                }
            });

            // Optionally, save message to the database here (send an AJAX request to PHP)
        }
    });

    ws.on('close', () => {
        clients.delete(ws);
    });
});

console.log('WebSocket server is running on ws://localhost:8080');
