<?php
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];

  
    // Check if email or username already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $checkStmt->bind_param("ss", $email, $username);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $error_message = "Error: Email or Username already exists!";
    } else {
        // If no duplicate found, insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $email);

        if ($stmt->execute()) {
            // Redirect to login page on success
            header("Location: login.php");
            exit;
        } else {
            $error_message = "Error: Registration Unsuccessful.";
        }

        $stmt->close();
    }

    $checkStmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            background-image: url('https://as2.ftcdn.net/jpg/02/53/31/97/1000_F_253319787_7nXEuEN2OOCIPqXqPtv8TV6ATEgT4nE1.jpg');
            background-size: cover;
            background-position: center;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.7);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #555;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .success-message {
            color: green;
            font-weight: bold;
            margin-top: 20px;
        }

        .error-message {
            color: red;
            font-weight: bold;
            margin-top: 20px;
        }

        a {
            color: blue;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Register</h2>

    <form method="POST" action="">
        <label>Email:
            <input type="email" name="email" placeholder="Enter Your Email" required>
        </label>

        <label>Username:
            <input type="text" name="username" placeholder="Enter Your Username" required>
        </label>

        <label>Password:
            <input type="password" name="password" placeholder="Enter Your Password" required>
        </label>

        <button type="submit">Register</button>
    </form>

    <!-- Display error message if registration fails -->
    <?php if (isset($error_message)) { ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php } ?>

    <p>Already registered? 
        <a href="login.php"><button type="button">Login</button></a>
    </p>
</div>

</body>
</html>
