<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "nestify";
$port = 3307; // Change to 3306 if that is your MySQL port

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database,
    $port
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>