<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    echo json_encode([]);
    exit;
}

$email = $_SESSION['email'];

// Fetch user ID
$sql = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$user_id = $user_data['id'];
$stmt->close();

// Fetch only "sent" notifications and mark them as "read"
$sql = "SELECT id, message FROM notifications WHERE user_id = ? AND status = 'sent' ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Mark fetched notifications as "read"
if (!empty($notifications)) {
    $update_sql = "UPDATE notifications SET status = 'read' WHERE user_id = ? AND status = 'sent'";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $user_id);
    $update_stmt->execute();
    $update_stmt->close();
}

echo json_encode($notifications);
?>
