<?php
//session_start();
$host = "localhost";
$username = "root";
$pass = "";
$db = "pdftest";
$conn = mysqli_connect($host, $username, $pass, $db);
if (!$conn) {
    die("Database connection error");
} else {
    echo 'connection established';
}

