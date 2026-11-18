<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    exit("Unauthorized");
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nestify";
$port = 3307;

$conn = new mysqli(
    $servername,
    $username,
    $password,
    $dbname,
    $port
);

if ($conn->connect_error) {
    http_response_code(500);
    exit("Database connection failed");
}

if (!isset($_POST['property_id'], $_POST['status'])) {
    http_response_code(400);
    exit("Missing data");
}

$property_id = (int) $_POST['property_id'];
$status = $_POST['status'];

if (!in_array($status, ['approved', 'suspended'], true)) {
    http_response_code(400);
    exit("Invalid status");
}

$stmt = $conn->prepare("
    UPDATE properties
    SET status = ?
    WHERE id = ?
");

$stmt->bind_param("si", $status, $property_id);

if ($stmt->execute()) {
    echo "success";
} else {
    http_response_code(500);
    echo "Update failed";
}

$stmt->close();
$conn->close();

?>