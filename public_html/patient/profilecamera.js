function startCamera() {
    var video = document.getElementById('video');
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: true }).then(function (stream) {
            video.srcObject = stream;
            video.play();
        });
    }
    document.getElementById('cameraOptions').style.display = 'block';
    document.getElementById('profile_pic').style.display = 'none';
}

function takeSnapshot() {
    var canvas = document.getElementById('canvas');
    var video = document.getElementById('video');
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    var dataURL = canvas.toDataURL('image/png');
    document.getElementById('profile_pic_data').value = dataURL;
    document.getElementById('snapshotTaken').style.display = 'block';
    document.getElementById('cameraOptions').style.display = 'none';
}

function cancelSnapshot() {
    var video = document.getElementById('video');
    var stream = video.srcObject;
    if (stream) {
        var tracks = stream.getTracks();
        tracks.forEach(function (track) {
            track.stop();
        });
        video.srcObject = null;
    }
    document.getElementById('cameraOptions').style.display = 'none';
    document.getElementById('profile_pic').style.display = 'none';
    document.getElementById('profile_source').value = "select";
}

function discardSnapshot() {
    var canvas = document.getElementById('canvas');
    var context = canvas.getContext('2d');
    context.clearRect(0, 0, canvas.width, canvas.height);
    document.getElementById('profile_pic_data').value = '';
    document.getElementById('snapshotTaken').style.display = 'none';
    document.getElementById('cameraOptions').style.display = 'block';
}

function toggleProfileInput() {
    var profileSource = document.getElementById('profile_source').value;
    var fileInput = document.getElementById('profile_pic');
    var cameraOptions = document.getElementById('cameraOptions');

    if (profileSource === 'device') {
        fileInput.style.display = 'block';
        cameraOptions.style.display = 'none';
        cancelSnapshot();  // Ensure camera is turned off if switching to device upload
    } else if (profileSource === 'camera') {
        fileInput.style.display = 'none';
        startCamera();
    } else {
        fileInput.style.display = 'none';
        cameraOptions.style.display = 'none';
        cancelSnapshot();  // Reset everything if "Choose an Option" is selected
    }
}
function calculateAge() {
    var dob = new Date(document.getElementById('dob').value);
    var diff_ms = Date.now() - dob.getTime();
    var age_dt = new Date(diff_ms); 
    var year = age_dt.getUTCFullYear();
    var age = Math.abs(year - 1970);
    document.getElementById('age').value = age;
}