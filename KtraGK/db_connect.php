<?php
// db_connect.php
$servername = "localhost"; 
$username = "root";       
$password = "";           
$dbname = "test1";

$conn = new mysqli($servername, $username, $password, $dbname);

$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>