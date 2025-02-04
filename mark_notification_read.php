<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['email'])) {
        echo "error: not logged in";
        exit;
    }

    $email = $_SESSION['email'];

    // Get user ID
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $user_id = $user_data['id'];
    $stmt->close();

    if (!isset($_POST['notification_id'])) {
        echo "error: no notification id";
        exit;
    }

    $notification_id = (int)$_POST['notification_id'];

    // Update notification status in the database
    $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: failed to update";
    }
    $stmt->close();
}
?>
