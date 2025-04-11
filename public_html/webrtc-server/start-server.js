const { exec } = require('child_process');

// Start the signaling server
exec('node signaling-server.js', (error, stdout, stderr) => {
    if (error) {
        console.error(`Error starting signaling server: ${error.message}`);
        return;
    }
    if (stderr) {
        console.error(`Signaling server error: ${stderr}`);
        return;
    }
    console.log(`Signaling server started successfully:\n${stdout}`);
});

