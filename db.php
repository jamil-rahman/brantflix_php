<?php
$servername = "localhost";
$username = "root";
$password = ""; // Default password for root is usually empty in XAMPP
$dbname = "movie_store";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
