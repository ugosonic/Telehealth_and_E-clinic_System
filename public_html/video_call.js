let ws;
let peerConnection;
const isPatientRole = <?php echo json_encode($isPatient); ?>;
const callContainer = document.getElementById('call-container');
const chatContainer = document.getElementById('chat-container');
const localVideo = document.createElement('video');
const remoteVideo = document.createElement('video');

// Initialize WebSocket connection
function initWebSocket() {
    ws = new WebSocket('ws://localhost:8080');
    ws.onopen = () => {
        ws.send(JSON.stringify({
            type: 'join',
            meeting_id: "<?php echo htmlspecialchars($meeting_id); ?>",
            username: "<?php echo htmlspecialchars($username); ?>",
            usergroup: "<?php echo htmlspecialchars($usergroup); ?>"
        }));
    };
    ws.onmessage = (event) => {
        const data = JSON.parse(event.data);
        console.log('WebSocket message received:', data); // Debugging
        handleSignalingData(data);
    };
    ws.onerror = (error) => {
        console.error('WebSocket Error: ', error);
    };
    ws.onclose = () => {
        console.log('WebSocket connection closed');
    };
}

// Handle signaling data for chat and calls
function handleSignalingData(data) {
    switch (data.type) {
        case 'user-joined':
            displayChatMessage(data.username, "joined the chat.");
            showNotification(`${data.username} has joined the chat.`);
            break;
        case 'chat':
            displayChatMessage(data.username, data.message, data.status);
            break;
        case 'call-request':
            showCallRequest(data.callType);
            break;
        case 'offer':
            if (peerConnection) {
                peerConnection.setRemoteDescription(new RTCSessionDescription(data.offer)).then(() => {
                    peerConnection.createAnswer().then(answer => {
                        peerConnection.setLocalDescription(answer);
                        ws.send(JSON.stringify({ type: 'answer', answer: answer, meeting_id: "<?php echo htmlspecialchars($meeting_id); ?>" }));
                    });
                }).catch(console.error);
            }
            break;
        case 'answer':
            if (peerConnection) {
                peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer)).catch(console.error);
            }
            break;
        case 'candidate':
            if (peerConnection) {
                peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate)).catch(console.error);
            }
            break;
    }
}

// Function to initiate a call
function initiateCall(type) {
    if (type === 'video') {
        startVideoCall();
    } else if (type === 'audio') {
        startAudioCall();
    }
    ws.send(JSON.stringify({
        type: 'call-request',
        callType: type,
        meeting_id: "<?php echo htmlspecialchars($meeting_id); ?>",
        username: "<?php echo htmlspecialchars($username); ?>"
    }));
}

// Start video call
function startVideoCall() {
    switchToCallInterface('Video Call');
    setupVideoElements();

    navigator.mediaDevices.getUserMedia({ video: true, audio: true })
        .then(stream => {
            localVideo.srcObject = stream;
            localVideo.play();
            setupPeerConnection(stream);
        }).catch(error => console.error('Error accessing media devices.', error));
}

// Start audio call
function startAudioCall() {
    switchToCallInterface('Audio Call');
    setupAudioOnlyElements();

    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(stream => {
            setupPeerConnection(stream);
        }).catch(error => console.error('Error accessing audio devices.', error));
}

// Setup peer connection and handle media streams
function setupPeerConnection(stream) {
    peerConnection = new RTCPeerConnection();
    stream.getTracks().forEach(track => peerConnection.addTrack(track, stream));

    peerConnection.ontrack = (event) => {
        remoteVideo.srcObject = event.streams[0];
        remoteVideo.play();
    };

    peerConnection.onicecandidate = (event) => {
        if (event.candidate) {
            ws.send(JSON.stringify({ type: 'candidate', candidate: event.candidate, meeting_id: "<?php echo htmlspecialchars($meeting_id); ?>" }));
        }
    };

    peerConnection.createOffer().then(offer => {
        peerConnection.setLocalDescription(offer);
        ws.send(JSON.stringify({ type: 'offer', offer: offer, meeting_id: "<?php echo htmlspecialchars($meeting_id); ?>" }));
    }).catch(console.error);
}

// Switch interface to call mode
function switchToCallInterface(callType) {
    chatContainer.innerHTML = `
        <div class="chat-header">
            <div>${callType} in Progress</div>
            <div class="icons">
                <button id="endCallIcon" onclick="endCall()">❌ End Call</button>
                <button id="chatIcon" onclick="switchToChat()">💬 Chat</button>
            </div>
        </div>
        <div class="call-body"></div>
    `;
    callContainer.style.display = 'flex';
}

// Setup video elements
function setupVideoElements() {
    localVideo.setAttribute('id', 'localVideo');
    localVideo.setAttribute('autoplay', 'true');
    localVideo.muted = true;
    localVideo.style.position = 'absolute';
    localVideo.style.bottom = '10px';
    localVideo.style.right = '10px';
    localVideo.style.zIndex = '10';
    localVideo.draggable = true;
    chatContainer.querySelector('.call-body').appendChild(localVideo);

    remoteVideo.setAttribute('id', 'remoteVideo');
    remoteVideo.setAttribute('autoplay', 'true');
    remoteVideo.style.width = '100%';
    remoteVideo.style.height = 'calc(100% - 50px)';
    chatContainer.querySelector('.call-body').appendChild(remoteVideo);
}

// Setup audio-only elements
function setupAudioOnlyElements() {
    const audioOnlyNotice = document.createElement('div');
    audioOnlyNotice.textContent = 'Audio Only Call in Progress...';
    audioOnlyNotice.className = 'audio-call-notice';
    chatContainer.querySelector('.call-body').appendChild(audioOnlyNotice);
    createEndCallButton();
}

// Create end call button
function createEndCallButton() {
    const endCallButton = document.createElement('button');
    endCallButton.innerHTML = 'End Call';
    endCallButton.className = 'end-call-button';
    endCallButton.onclick = endCall;
    chatContainer.querySelector('.call-body').appendChild(endCallButton);
}

// End call
function endCall() {
    if (peerConnection) {
        peerConnection.close();
    }
    callContainer.style.display = 'none'; // Hide call container
    switchToChat(); // Show chat
    ws.send(JSON.stringify({
        type: 'end-call',
        meeting_id: "<?php echo htmlspecialchars($meeting_id); ?>",
        username: "<?php echo htmlspecialchars($username); ?>"
    }));
}

// Switch back to chat interface
function switchToChat() {
    chatContainer.innerHTML = `
        <div class="chat-header">
            <div>Chat</div>
            <div class="chat-users">
                <?php foreach ($usersInChat as $user) : ?>
                    <span><?= htmlspecialchars($user); ?></span>
                <?php endforeach; ?>
            </div>
            <div class="icons">
                <button id="videoCallIcon" onclick="initiateCall('video')">🎥</button>
                <button id="audioCallIcon" onclick="initiateCall('audio')">🎤</button>
            </div>
        </div>
        <div class="chat-body" id="chat-messages">
            <?php foreach ($chatHistory as $chat) : ?>
                <div class="message <?= ($chat['username'] === $username) ? 'self' : 'other' ?>">
                    <div class="profile-picture">
                        <img src="path/to/profile.png" alt="User Profile">
                    </div>
                    <div class="message-content">
                        <strong><?= htmlspecialchars($chat['username']); ?>:</strong> <?= htmlspecialchars($chat['message']); ?>
                        <div class="time"><?= timeAgo(strtotime($chat['created_at'])); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="chat-footer">
            <input type="text" id="chat-input" placeholder="Type a message">
            <button id="send-message">Send</button>
        </div>
    `;
}

// Show call request
function showCallRequest(callType) {
    const acceptCall = confirm(`Incoming ${callType} call. Do you want to accept?`);
    if (acceptCall) {
        if (callType === 'video') {
            startVideoCall();
        } else if (callType === 'audio') {
            startAudioCall();
        }
    }
}

// Send a chat message
function sendMessage() {
    const message = document.getElementById('chat-input').value;
    if (message.trim() === '') return;

    $.post('/your_php_endpoint.php', { message: message }, function(response) {
        if (response.success) {
            ws.send(JSON.stringify({
                type: 'chat',
                username: "<?php echo htmlspecialchars($username); ?>",
                message: message,
                status: 'sent'
            }));
            displayChatMessage("<?php echo htmlspecialchars($username); ?>", message, 'sent');
            document.getElementById('chat-input').value = ''; // Clear the input
        } else {
            alert('Error sending message.');
        }
    }, 'json');
}

// Display chat messages in real-time
function displayChatMessage(username, message, status) {
    const chatMessages = document.getElementById('chat-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message ' + (username === "<?php echo htmlspecialchars($username); ?>" ? 'self' : 'other');
    messageDiv.innerHTML = `<strong>${username}:</strong> ${message} <span class="message-status">${status}</span>`;
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight; // Scroll to latest message
}

// Function to mark a message as read in real-time
function markMessageAsRead(messageId) {
    ws.send(JSON.stringify({
        type: 'mark-read',
        messageId: messageId,
        username: "<?php echo htmlspecialchars($username); ?>"
    }));
}

// Call this function when the message is read (e.g., when the user views it)
document.getElementById('chat-messages').addEventListener('scroll', function() {
    // Logic to check if the message is in view
    // Call `markMessageAsRead` with the appropriate messageId when the message comes into view
});

// Show notification
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

// Initialize WebSocket
initWebSocket();

// Send message event listener
document.getElementById('send-message').addEventListener('click', sendMessage);

// Automatically scroll to the latest message
document.getElementById('chat-messages').scrollTop = document.getElementById('chat-messages').scrollHeight;

