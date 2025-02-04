<?php
// Start the session
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect to the Log In Page 
header("Location: login.php");
exit;
?>
