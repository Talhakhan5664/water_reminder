<?php


// Database connection settings
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'water_reminder';

// Create a new MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>