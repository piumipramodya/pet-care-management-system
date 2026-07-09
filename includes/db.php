<?php
// Database connection
$host = 'localhost';
$user = 'root';
$pass = '123456';
$dbname = 'pet_management';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>