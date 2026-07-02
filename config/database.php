<?php
// Database Configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'foodcourt_cafe';

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");

// Define constants for database
define('DB_HOST', $host);
define('DB_USER', $username);
define('DB_PASS', $password);
define('DB_NAME', $database);
define('BASE_URL', 'http://localhost/foodcourt-cafe/');

// Session configuration
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>