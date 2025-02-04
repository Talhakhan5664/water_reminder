<?php

session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //$username = $_POST['username'];
    $email = $_POST['email'];
    
    $password = $_POST['password'];

  
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit;
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "User not found.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <style>
        /* Full-page background image */
        body {
            margin: 0;
            padding: 0;
            background-image: url('https://as2.ftcdn.net/jpg/02/53/31/97/1000_F_253319787_7nXEuEN2OOCIPqXqPtv8TV6ATEgT4nE1.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            font-family: Arial, sans-serif;
        }

        /* Centered form styling */
        form {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            background: rgba(0, 0, 0, 0.6); /* Semi-transparent dark background */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px); /* Glass effect */
            color: #fff;
            animation: fadeIn 1.5s ease;
        }

        /* Form heading */
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #fff;
            font-size: 1.8rem;
        }

        /* Input fields */
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #ddd;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 1rem;
        }

        input[type="email"]::placeholder,
        input[type="password"]::placeholder {
            color: #ccc;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.3);
            border-color: #007bff;
        }

        /* Submit button */
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        button:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        /* Animation for fade-in effect */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <form method="POST" action="">
        <h2>Login</h2>

        <label>Email:
            <input type="email" name="email" placeholder="Enter Your Email" required>
        </label>

        <label>Password:
            <input type="password" name="password" placeholder="Enter Your Password" required>
        </label>

        <button type="submit">Login</button>
        <div class="already-registered">
        <p>If not registered! First register yourself.   
            <a href="register.php"> <button type="button">Register</button> </a></p>
    
    </div>
    </form>


</body>
</html>
