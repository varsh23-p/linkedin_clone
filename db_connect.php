<?php
// db_connect.php - update credentials for your environment
$servername = "localhost";
$username = "root";      // XAMPP default
$password = "";          // XAMPP default is empty
$dbname = "linkedin_clone";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
