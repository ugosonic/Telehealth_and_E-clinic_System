<?php 

	session_start();

	$_SESSION = array();

	if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-86400, '/');
	}

	// Update the online status
$conn = mysqli_connect('localhost', 'root', '', 'administrator');
$update_status = "UPDATE users SET online_status = 0 WHERE username = '$username'";
mysqli_query($conn, $update_status);

	session_destroy();

	// redirecting the user to the login page
	header('Location: ./login/login.php?action=logout');

 ?>