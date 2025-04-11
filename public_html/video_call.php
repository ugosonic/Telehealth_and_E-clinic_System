<?php
/**
 * video_script.php
 * 
 * A single-page Telehealth example with:
 * - Live chat (AJAX, no reload)
 * - 8x8/Jitsi integration
 * - Optional consultation form for staff
 */

// 1) Start Session + Timezone
session_start();
date_default_timezone_set('Europe/London');

// 2) Include DB/Config (must define $conn)
require_once 'init.php';    // <-- Make sure this sets up $conn
require_once 'config.php';  // <-- If additional config is needed

// 3) AJAX: SAVE, FETCH, MARK READ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 3.1) SAVE NEW CHAT MESSAGE
    if (isset($_POST['action']) && $_POST['action'] === 'save_message') {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_POST['meeting_id'], $_POST['username'], $_POST['message'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
            exit;
        }

        $meeting_id = trim($_POST['meeting_id']);
        $username   = trim($_POST['username']);
        $message    = trim($_POST['message']);
        $status     = 'sent';

        $stmt = $conn->prepare('INSERT INTO session_messages (meeting_id, username, message, status, created_at) 
                                VALUES (?, ?, ?, ?, NOW())');
        $stmt->bind_param('ssss', $meeting_id, $username, $message, $status);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save message']);
        }
        exit;
    }

    // 3.2) MARK MESSAGES AS READ
    if (isset($_POST['action']) && $_POST['action'] === 'mark_read') {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_POST['meeting_id'], $_POST['current_user'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
            exit;
        }

        $meeting_id   = trim($_POST['meeting_id']);
        $current_user = trim($_POST['current_user']);

        $stmt = $conn->prepare('UPDATE session_messages 
            SET status = "read" 
            WHERE meeting_id = ? 
              AND username <> ? 
              AND status <> "read" ');
        $stmt->bind_param('ss', $meeting_id, $current_user);
        $ok = $stmt->execute();
        $stmt->close();

        echo json_encode(['status' => $ok ? 'success' : 'error']);
        exit;
    }
}

// 3.3) FETCH MESSAGES
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'fetch_messages') {
    header('Content-Type: application/json; charset=utf-8');
    if (!isset($_GET['meeting_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing meeting_id']);
        exit;
    }

    $meeting_id = trim($_GET['meeting_id']);
    $chatHistory = [];

    $stmt = $conn->prepare('SELECT username, message, created_at, status
                            FROM session_messages
                            WHERE meeting_id = ?
                            ORDER BY created_at ASC');
    $stmt->bind_param('s', $meeting_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $timestamp = strtotime($row['created_at']);
        $timeAgo   = timeAgo($timestamp);

        $chatHistory[] = [
            'username' => $row['username'],
            'message'  => $row['message'],
            'created'  => $row['created_at'],
            'status'   => $row['status'],
            'timeAgo'  => $timeAgo
        ];
    }
    $stmt->close();

    echo json_encode(['status' => 'success', 'messages' => $chatHistory]);
    exit;
}

// 4) MAIN PAGE LOGIC
// 4.1) Must have a meeting_id in the URL: ?meeting_id=abc123
if (!isset($_GET['meeting_id'])) {
    die('Meeting ID is required in the URL, e.g. ?meeting_id=abc123');
}
$meeting_id = trim($_GET['meeting_id']);

// 4.2) Must be logged in
if (!isset($_SESSION['username'])) {
    die('You must be logged in to access this page.');
}
$currentUser = $_SESSION['username'];

// 4.3) Determine if user is staff or patient + get their display name
$isPatient   = false;
$usergroup   = '';
$email       = '';
$displayName = ''; // e.g. "John Doe"

//  -- Attempt to find user in 'users' table (staff) --
$stmt = $conn->prepare('SELECT usergroup, email, first_name, surname 
                        FROM users 
                        WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $currentUser);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    // Found staff
    $row         = $result->fetch_assoc();
    $usergroup   = $row['usergroup'];
    $email       = $row['email'];
    $displayName = trim($row['first_name'].' '.$row['surname']);
    $stmt->close();
} else {
    //  -- Otherwise, check 'patient_db' table --
    $stmt->close(); 
    $stmt2 = $conn->prepare('SELECT username, email, first_name, surname 
                             FROM patient_db
                             WHERE username = ? LIMIT 1');
    $stmt2->bind_param('s', $currentUser);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    if ($res2 && $res2->num_rows > 0) {
        $row         = $res2->fetch_assoc();
        $isPatient   = true;
        $usergroup   = 'Patient';
        $email       = $row['email'];
        $displayName = trim($row['first_name'].' '.$row['surname']);
    } else {
        echo "<p>User not found in staff or patient_db. Please <a href='login.php'>login again</a>.</p>";
        exit;
    }
    $stmt2->close();
}

// 4.4) Fetch existing chat history
$chatHistory = [];
$stmt = $conn->prepare('SELECT username, message, created_at, status 
                        FROM session_messages
                        WHERE meeting_id = ?
                        ORDER BY created_at ASC');
$stmt->bind_param('s', $meeting_id);
$stmt->execute();
$res3 = $stmt->get_result();
while ($r = $res3->fetch_assoc()) {
    $chatHistory[] = $r;
}
$stmt->close();

// 4.5) Distinct chat users
$usersInChat = [];
$stmt = $conn->prepare('SELECT DISTINCT username FROM session_messages WHERE meeting_id = ?');
$stmt->bind_param('s', $meeting_id);
$stmt->execute();
$res4 = $stmt->get_result();
while ($r = $res4->fetch_assoc()) {
    $usersInChat[] = $r['username'];
}
$stmt->close();

// 4.6) If staff, fetch the patient ID from meetings
$patient_id = null;
if (!$isPatient) {
    $stmt = $conn->prepare('SELECT patient_id 
                            FROM meetings
                            WHERE meeting_id = ?
                            LIMIT 1');
    $stmt->bind_param('s', $meeting_id);
    $stmt->execute();
    $res5 = $stmt->get_result();
    if ($res5 && $res5->num_rows > 0) {
        $rowM      = $res5->fetch_assoc();
        $patient_id= $rowM['patient_id'];
    }
    $stmt->close();
}

// 4.7) Helper: timeAgo
function timeAgo($timestamp) {
    $diff = time() - $timestamp;
    if ($diff < 0) {
        $diff = 0;
    }
    if ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        return round($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return round($diff / 3600) . ' hours ago';
    } else {
        return round($diff / 86400) . ' days ago';
    }
}

// 4.8) The 8x8 / Jitsi “roomName” for the call
//     If you have a specific room name from JaaS, set it here:
$jaasRoomName = "vpaas-magic-cookie-fe26adb8811a4200b2e9433174f15eaf/Telehealth System";
         
// Example: "vpaas-magic-cookie-fe26ad.../MyPrivateRoom123"

// [Optional] If you have a JWT token for advanced features (recording, etc.), define it:
$myJwtToken = "eyJraWQiOiJ2cGFhcy1tYWdpYy1jb29raWUtZmUyNmFkYjg4MTFhNDIwMGIyZTk0MzMxNzRmMTVlYWYvNGE5MGI3LVNBTVBMRV9BUFAiLCJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJqaXRzaSIsImlzcyI6ImNoYXQiLCJpYXQiOjE3MzczNjQ4OTksImV4cCI6MTczNzM3MjA5OSwibmJmIjoxNzM3MzY0ODk0LCJzdWIiOiJ2cGFhcy1tYWdpYy1jb29raWUtZmUyNmFkYjg4MTFhNDIwMGIyZTk0MzMxNzRmMTVlYWYiLCJjb250ZXh0Ijp7ImZlYXR1cmVzIjp7ImxpdmVzdHJlYW1pbmciOnRydWUsIm91dGJvdW5kLWNhbGwiOnRydWUsInNpcC1vdXRib3VuZC1jYWxsIjpmYWxzZSwidHJhbnNjcmlwdGlvbiI6dHJ1ZSwicmVjb3JkaW5nIjp0cnVlfSwidXNlciI6eyJoaWRkZW4tZnJvbS1yZWNvcmRlciI6ZmFsc2UsIm1vZGVyYXRvciI6dHJ1ZSwibmFtZSI6InVnb3NvbmljIiwiaWQiOiJnb29nbGUtb2F1dGgyfDExMDkyNDg5NjA1MDUxMTU2ODIyNSIsImF2YXRhciI6IiIsImVtYWlsIjoidWdvc29uaWNAZ21haWwuY29tIn19LCJyb29tIjoiKiJ9.NKEDPzJczwVBETPRnypWXzK_l6meNYNq9Maf-yvaChH9ZlID1W7_8xo_M6x3pWbChoOozDvOMZVV8H0qmLK8yd-_F8VYwbMwgAx-92u2davSFSf7lgcVRd-dZnv4pAJj3jSkfS1FjzokUJzVCC2JaiBMz0Ru8eRnqx3oVzWyCBA7hIq6j5Zp_67sHilVz8VwXAFkaZstB2w_lyJASrxYAOE4iUOkuAS7f4xxfV9VmJTsV_rvxaNkxNQT9t2tHCSlqH9Cd5wduiK7HZjwl-KzhaWiIVlaepaqXDrUxMchAE7ToYGhUqiCdBcvMDqKl_8nJEXbIzD4MdlBU3dA-Hmt-Q"; // e.g. "eyJraWQiOiJ2cGFhcy1tYWdpYy1jb29r..."

// -------------------------------------------
// 5) Start Output: the HTML page
// -------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Telehealth System Example</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- 5.1) jQuery (for $.ajax calls) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJ+Y3+Dw2EnO3K+2LeggpJh7dSNfP3+DkM2ik="
            crossorigin="anonymous"></script>

    <!-- 5.2) 8x8 / Jitsi External API (JaaS) -->
     <script src='https://8x8.vc/vpaas-magic-cookie-fe26adb8811a4200b2e9433174f15eaf/external_api.js'></script>
        

    <!-- 5.3) Bootstrap (optional) + minimal styling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
    <style>
    /* 1) Base page styling */
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  background-color: #f4f4f4;
}

/* 2) Main container: three columns side-by-side, filling viewport height */
.container-flex {
  display: flex;
  flex-wrap: wrap;
  width: 100%;
  height: 100vh; /* Use full browser height */
  box-sizing: border-box; /* Helps with consistent sizing */
}

/* 3) Jitsi/video container */
#jaas-container {
  flex: 1 1 auto;
  min-width: 320px;
  height: 100%;
  background: #000;

  /* Make sure the Jitsi iframe can fill 100% */
  display: flex;          /* So child iframe can flex */
  flex-direction: column;
}

/* Force the Jitsi iframe to occupy full space */
#jaas-container iframe {
  flex: 1;
  width: 100% !important;
  height: 100% !important;
  border: 0;
}

/* 4) Chat container */
.chat-container {
  flex: 0 1 350px; 
  height: 100%;
  display: flex;
  flex-direction: column;
  border-left: 1px solid #ccc;
  background-color: #fff;
}

/* 5) Consultation container */
.consultation-form-container-embedded {
  flex: 0 1 350px;
  min-width: 400px;
  height: 100%;
  overflow-y: auto;  /* Scroll if content is tall */
  border-left: 1px solid #ccc;
  background: #fafafa;
}

/* 6) Responsive: stack them vertically on narrow screens */
@media (max-width: 1000px) {
  .container-flex {
    display: grid;
    grid-template-columns: 1fr;  /* single column */
    grid-auto-rows: auto;
    height: auto;                /* let each child be 400px tall */
  }
  #jaas-container,
  .chat-container,
  .consultation-form-container-embedded {
    width: 100% !important;
    height: 400px; /* or adjust as you like for mobile */
  }
}

/* 7) Chat layout details */
.chat-header {
  padding: 1rem;
  background: #007bff;
  color: #fff;
  font-weight: bold;
  display: flex;
  justify-content: space-between;
}
.chat-users {
  font-size: 0.8rem;
  margin-left: 1rem;
}
.chat-body {
  flex: 1;
  overflow-y: auto; /* scroll chat messages */
  padding: 1rem;
}
.message {
  margin-bottom: 0.75rem;
}
.message.self strong {
  color: #007bff;
}
.message.other strong {
  color: #ff5722;
}
.message .time {
  font-size: 0.8rem;
  color: #999;
  margin-left: 0.5rem;
}
.chat-footer {
  border-top: 1px solid #ccc;
  padding: 0.5rem;
  display: flex;
}
.chat-footer input {
  flex: 1;
  padding: 0.5rem;
  margin-right: 0.5rem;
}
.chat-footer button {
  padding: 0.5rem 1rem;
  cursor: pointer;
}
.date-separator {
  text-align: center;
  color: #888;
  font-size: 0.85rem;
  margin: 1rem 0;
}

    </style>
</head>
<body>

<!-- (Optional) Sidebar if you have it -->
<?php include 'sidebar.php'; ?>

<div class="container-flex">
  <!-- 6) Jitsi/8x8 Video Container -->
  <div id="jaas-container"></div>

  <!-- 7) Chat Section -->
  <div class="chat-container">
    <div class="chat-header">
      <div>Chat</div>
      <div class="chat-users">
        <span style="font-size:0.9rem;">Online:</span>
        <?php foreach ($usersInChat as $u): ?>
          <span style="margin-left:5px;"><?= htmlspecialchars($u); ?></span>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="chat-body" id="chat-messages">
      <?php 
      // Display existing chat messages with date separators
      $lastDate = null;
      foreach ($chatHistory as $msg):
          $timestamp = strtotime($msg['created_at']);
          $dateOnly  = date('Y-m-d', $timestamp);

          // If a new date, show a separator
          if ($dateOnly !== $lastDate) {
              $lastDate = $dateOnly;
              // Check if today or yesterday
              $today      = date('Y-m-d');
              $yesterday  = date('Y-m-d', time() - 86400);
              if ($dateOnly === $today) {
                  echo '<div class="date-separator">Today</div>';
              } elseif ($dateOnly === $yesterday) {
                  echo '<div class="date-separator">Yesterday</div>';
              } else {
                  echo '<div class="date-separator">' . date('F j, Y', $timestamp) . '</div>';
              }
          }

          // Time-ago text
          $timeAgoText = timeAgo($timestamp);
          $ownerClass  = ($msg['username'] === $currentUser) ? 'self' : 'other';
      ?>
        <div class="message <?= $ownerClass; ?>">
          <strong><?= htmlspecialchars($msg['username']); ?>:</strong>
          <?= htmlspecialchars($msg['message']); ?>
          <span class="time">
            <?= $timeAgoText; ?> -
            <span class="message-status"><?= htmlspecialchars($msg['status']); ?></span>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="chat-footer">
      <input type="text" id="chat-input" placeholder="Type a message" />
      <button id="send-message">Send</button>
    </div>
  </div>

  <!-- 8) Consultation Form (only staff & if we have a patient_id) -->
  <?php if (!$isPatient && $patient_id): ?>
    <div class="consultation-form-container-embedded">
      <?php
        // Your staff consultation form code
        // Pass $patient_id, etc. to it
        $patient_id_variable = $patient_id;
        include './Video_call/consultation.php'; 
        // Or remove if you don’t have that file
      ?>
    </div>
  <?php elseif (!$isPatient): ?>
    <!-- If staff but no patient found -->
    <div style="padding:1rem;">
      <p>No patient found for this meeting.</p>
    </div>
  <?php else: ?>
    <!-- If isPatient, maybe show some info or nothing -->
    <div style="padding:1rem;">
      <h5>You are in a patient view.</h5>
    </div>
  <?php endif; ?>

</div> <!-- /.container-flex -->


<!-- 9) Scripts for chat + Jitsi initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  /****************************************************
   * A) Initialize the Jitsi/8x8 Meeting
   ****************************************************/
  // If using “vpaas-magic-cookie...” roomName, set that here:
  const domain = "8x8.vc";
  const room   = "<?php echo addslashes($jaasRoomName); ?>"; 
  const displayName = "<?php echo addslashes($displayName); ?>";
  const jwt    = "<?php echo $myJwtToken; ?>"; // If you have a real JWT

  const jitsiOptions = {
    roomName: room,
    parentNode: document.getElementById('jaas-container'),
    userInfo: {
      displayName: displayName
    },
    // uncomment if you have a JWT for advanced features:
    jwt: jwt,
    width: "100%",
    height: "100%"
  };

  // Create the Jitsi external API
  const api = new JitsiMeetExternalAPI(domain, jitsiOptions);

  // Example event listener
  api.addEventListener('participantJoined', (evt) => {
    console.log('Participant joined:', evt);
  });


  /****************************************************
   * B) Chat AJAX
   ****************************************************/
  const meetingID   = "<?php echo htmlspecialchars($meeting_id); ?>";
  const currentUser = "<?php echo htmlspecialchars($currentUser); ?>";
  const chatBody    = document.getElementById('chat-messages');
  const chatInput   = document.getElementById('chat-input');
  const sendBtn     = document.getElementById('send-message');

  // B1) Send new message
  function sendChatMessage() {
    const text = chatInput.value.trim();
    if (!text) return;

    $.ajax({
      url: "<?php echo $_SERVER['PHP_SELF']; ?>?meeting_id=" + encodeURIComponent(meetingID),
      type: "POST",
      data: {
        action: "save_message",
        meeting_id: meetingID,
        username: currentUser,
        message: text
      },
      dataType: "json",
      success: function(resp) {
        if (resp.status === 'success') {
          // Immediately show the new message in chat
          const nowDiv = document.createElement('div');
          nowDiv.className = 'message self';
          nowDiv.innerHTML = `
            <strong>${escapeHtml(currentUser)}:</strong>
            ${escapeHtml(text)}
            <span class="time">Just now - <span class="message-status">sent</span></span>
          `;
          chatBody.appendChild(nowDiv);

          // Scroll down
          chatBody.scrollTop = chatBody.scrollHeight;
          chatInput.value = '';
        } else {
          alert('Error sending chat message: ' + (resp.message || 'Unknown error'));
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error('AJAX Error:', textStatus, errorThrown);
        alert('Could not send message. See console for details.');
      }
    });
  }

  // On button click or Enter key
  sendBtn.addEventListener('click', sendChatMessage);
  chatInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      sendChatMessage();
    }
  });

  // B2) Periodically fetch new messages + mark read
  setInterval(function(){
    fetchMessages();
    markMessagesAsRead();
  }, 3000);

  function fetchMessages() {
    $.ajax({
      url: "<?php echo $_SERVER['PHP_SELF']; ?>",
      type: "GET",
      data: {
        action: "fetch_messages",
        meeting_id: meetingID
      },
      dataType: "json",
      success: function(resp) {
        if (resp.status === 'success') {
          // Clear chat, rebuild
          chatBody.innerHTML = '';
          let lastDate = null;

          resp.messages.forEach(msg => {
            // If date changed, show separator
            const msgTime = new Date(msg.created).getTime();
            const dateOnly= new Date(msgTime).toISOString().split('T')[0];
            if (dateOnly !== lastDate) {
              lastDate = dateOnly;
              const today      = new Date().toISOString().split('T')[0];
              const yesterday  = new Date(Date.now() - 86400000).toISOString().split('T')[0];
              let label = new Date(msgTime).toLocaleDateString('en-GB', {
                year: 'numeric', month: 'long', day: 'numeric'
              });
              if (dateOnly === today) label = 'Today';
              else if (dateOnly === yesterday) label = 'Yesterday';

              const sep = document.createElement('div');
              sep.className = 'date-separator';
              sep.textContent = label;
              chatBody.appendChild(sep);
            }

            // Create message div
            const div = document.createElement('div');
            div.className = 'message ' + (msg.username === currentUser ? 'self' : 'other');
            div.innerHTML = `
              <strong>${escapeHtml(msg.username)}:</strong>
              ${escapeHtml(msg.message)}
              <span class="time">${escapeHtml(msg.timeAgo)} -
                <span class="message-status">${escapeHtml(msg.status)}</span>
              </span>
            `;
            chatBody.appendChild(div);
          });

          // Scroll to bottom
          chatBody.scrollTop = chatBody.scrollHeight;
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error('Fetch messages error:', textStatus, errorThrown);
      }
    });
  }

  function markMessagesAsRead() {
    $.ajax({
      url: "<?php echo $_SERVER['PHP_SELF']; ?>?meeting_id=" + encodeURIComponent(meetingID),
      type: "POST",
      data: {
        action: "mark_read",
        meeting_id: meetingID,
        current_user: currentUser
      },
      dataType: "json",
      success: function(resp) {
        // do nothing or update UI
      },
      error: function() {
        // silently ignore
      }
    });
  }

  // B3) Helper: escape HTML
  function escapeHtml(text) {
    if (!text) return '';
    return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }
});
</script>

</body>
</html>
