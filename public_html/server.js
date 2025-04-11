const express = require('express');
const path = require('path');

const app = express();

// Serve static files (like CSS, JS, etc.)
app.use(express.static(path.join(__dirname, 'public')));

// Route to serve the video_call.php file
app.get('/video_call', (req, res) => {
    res.sendFile(path.join(__dirname, 'video_call.php'));
});

app.listen(8000, () => {
    console.log('HTTP server running on http://localhost:8000');
});
