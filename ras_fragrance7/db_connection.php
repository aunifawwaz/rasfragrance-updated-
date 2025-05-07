<?php
$servername = "localhost"; // Server name (usually 'localhost' for local servers)
$username = "root"; // Default username for phpMyAdmin
$password = ""; // Default password for phpMyAdmin (usually empty)
$database = "ras_fragrance"; // Your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>
