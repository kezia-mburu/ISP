<?php
// --- Database Connection Configuration ---
// IMPORTANT: Replace these with your actual database credentials.
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wifi_mtaani";

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    // If connection fails, stop the script and show an error message.
    die("Connection failed: " . $conn->connect_error);
}
?>