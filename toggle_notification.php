<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    echo "error";
    exit;
}

$email = $_SESSION['email'];

// Fetch current setting
$sql = "SELECT notifications_enabled FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$current_status = $user_data['notifications_enabled'];
$stmt->close();

// Toggle the status
$new_status = $current_status ? 0 : 1;
$update_sql = "UPDATE users SET notifications_enabled = ? WHERE email = ?";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param("is", $new_status, $email);
if ($stmt->execute()) {
    echo $new_status; // Return the new status
} else {
    echo "error";
}
$stmt->close();
?>
