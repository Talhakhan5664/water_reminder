<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['email'];

// Fetch user details
$sql = "SELECT id, username FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$user_id = $user_data['id'];
$username = $user_data['username'];
$stmt->close();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['water_goal'])) {
        $water_goal = (int)$_POST['water_goal'];

        $stmt = $conn->prepare("INSERT INTO user_goals (user_id, water_goal, water_consumed, goal_date) 
        VALUES (?, ?, 0, CURDATE()) 
        ON DUPLICATE KEY UPDATE water_goal = ?, water_consumed = 0;");
        $stmt->bind_param("iii", $user_id, $water_goal, $water_goal);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['add_water'])) {
        $additional_water = (int)$_POST['add_water'];
        $time_consumed = date("H:i:s");

        // Update water consumption
        $sql = "UPDATE user_goals SET water_consumed = water_consumed + ? 
                WHERE user_id = ? AND goal_date = CURDATE();";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $additional_water, $user_id);
        $stmt->execute();
        $stmt->close();

        // Insert water log
        $sql = "INSERT INTO water_logs (user_id, water_amount, time_consumed, log_date) 
                VALUES (?, ?, ?, CURDATE());";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $user_id, $additional_water, $time_consumed);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch current goal and water consumption
$sql = "SELECT water_goal, water_consumed FROM user_goals 
        WHERE user_id = ? AND goal_date = CURDATE();";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$goal_data = $result->fetch_assoc();
$stmt->close();

$water_goal = $goal_data['water_goal'] ?? 0;
$water_consumed = $goal_data['water_consumed'] ?? 0;

// Fetch unread notifications
$sql = "SELECT id, message FROM notifications WHERE user_id = ? AND status = 'sent' ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Insert a new water reminder notification every hour
$current_time = date("H:i:s");
$check_sql = "SELECT id FROM notifications WHERE user_id = ? AND message LIKE '%Time to drink water%' AND created_at >= NOW() - INTERVAL 1 HOUR ";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows == 0) {
    $message = "💧 Time to drink water! Stay hydrated! $username !  [$current_time] ";
    $insert_sql = "INSERT INTO notifications (user_id, message, status) VALUES (?, ?, 'sent')";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("is", $user_id, $message);
    $insert_stmt->execute();
    $insert_stmt->close();
}

$check_stmt->close();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Reminder Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">

    <style>
        body {
            background-image: url('https://images.pexels.com/photos/3500006/pexels-photo-3500006.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2');
            background-size: cover;
            background-position: center;
            font-family: Arial, sans-serif;
        }
        .navbar {
            background-color: #0d6efd;
        }
        .navbar-brand, .btn {
            color: #fff !important;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .btn-primary, .btn-success {
            border-radius: 50px;
        }
        h1 {
            color: #0d6efd;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#">Water Reminder</a>

        <div class="dropdown">
            <button class="btn btn-dark position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                🔔 Notifications
                <?php if (count($notifications) > 0) { ?>
                    <span class="badge bg-danger" id="notificationCount"><?php echo count($notifications); ?></span>
                <?php } ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                <?php if (count($notifications) > 0) { ?>
                    <?php foreach ($notifications as $notification) { ?>
                        <li>
                            <a class="dropdown-item notification-item" href="#" data-id="<?php echo $notification['id']; ?>">
                                <?php echo htmlspecialchars($notification['message']); ?>
                            </a>
                        </li>
                    <?php } ?>
                <?php } else { ?>
                    <li><a class="dropdown-item" href="#">No new notifications</a></li>
                <?php } ?>
            </ul>
        </div>

        <div class="ml-auto">
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <h1 class="text-center mb-4">Welcome, <?php echo htmlspecialchars($username); ?>!</h1>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Set Your Daily Water Goal</h5>
            <form method="POST">
                <div class="mb-3">
                    <label for="water_goal" class="form-label">Water Goal (in ml):</label>
                    <input type="number" class="form-control" id="water_goal" name="water_goal" required value="<?php echo $water_goal; ?>">
                </div>
                <button type="submit" class="btn btn-primary">Set Goal</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Today's Water Consumption</h5>
            <p><strong>Goal:</strong> <?php echo $water_goal; ?> ml</p>
            <p><strong>Consumed:</strong> <?php echo $water_consumed; ?> ml</p>
            <p><strong>Required:</strong> <?php echo max(0, $water_goal - $water_consumed); ?> ml</p>

            <form method="POST">
                <div class="mb-3">
                    <label for="add_water" class="form-label">Add Water (in ml):</label>
                    <input type="number" class="form-control" id="add_water" name="add_water" required>
                </div>
                <button type="submit" class="btn btn-success">Add Water</button>
            </form>
        </div>
    </div>
    <div class="progress">
        <div class="progress-bar" role="progressbar" 
            style="width: <?php echo ($water_consumed / max(1, $water_goal)) * 100; ?>%;" 
            aria-valuenow="<?php echo $water_consumed; ?>" 
            aria-valuemin="0" 
            aria-valuemax="<?php echo $water_goal; ?>">
            <?php echo round(($water_consumed / max(1, $water_goal)) * 100); ?>%
        </div>
    </div>
</div>



<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    // Request permission for notifications
    function requestNotificationPermission() {
        if (Notification.permission === "default") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    console.log("Notification permission granted!");
                } else {
                    console.log("Notification permission denied.");
                }
            });
        }
    }

    // Function to show water reminder notification
    function showReminder(message, notificationId) {
        if (Notification.permission === "granted") {
            const notification = new Notification("💧 Water Reminder", {
                body: message,
                icon: "https://img.icons8.com/ios-filled/50/000000/water.png"
            });

            // Add a click event listener to the notification
            notification.onclick = function () {
                window.location.href = `http://localhost/water_reminder/index.php?id=${notificationId}`;
            };

            let audio = new Audio('notification.mp3');
            audio.play();
        }
    }

    // Fetch new notifications and show in navbar
    function fetchNotifications() {
        fetch("fetch_notifications.php")
            .then(response => response.json())
            .then(data => {
                let notificationDropdown = document.querySelector(".dropdown-menu");
                notificationDropdown.innerHTML = "";

                if (data.length > 0) {
                    document.getElementById("notificationCount").innerText = data.length;

                    data.forEach(notification => {
                        let listItem = document.createElement("li");
                        listItem.innerHTML = `<a class="dropdown-item notification-item" href="#" data-id="${notification.id}">${notification.message}</a>`;
                        notificationDropdown.appendChild(listItem);

                        // Show browser notification
                        showReminder(notification.message, notification.id);
                    });
                } else {
                    document.getElementById("notificationCount").innerText = "";
                    notificationDropdown.innerHTML = `<li><a class="dropdown-item" href="#">No new notifications</a></li>`;
                }
            });
    }

    // Mark notification as read when clicked
    document.addEventListener("click", function (event) {
        if (event.target.classList.contains("notification-item")) {
            event.preventDefault();
            let notificationId = event.target.getAttribute("data-id");

            fetch("mark_notification_read.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "notification_id=" + notificationId
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === "success") {
                    event.target.closest("li").remove();
                }
            });
        }
    });

    // Request notification permission on page load
    if (Notification.permission !== "granted") {
        requestNotificationPermission();
    }

    // Fetch notifications every 1 hour seconds
    setInterval(fetchNotifications, 60 * 60 * 1000);
});


</script>

</body>
</html>
