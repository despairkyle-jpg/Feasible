<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nestify";

$conn = new mysqli($servername, $username, $password, $dbname, 3307);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$fullname = "Admin";
$email = "admin@nestify.com";
$adminPassword = password_hash("Admin123", PASSWORD_DEFAULT);
$userType = "admin";

$sql = "INSERT INTO users (fullname, email, password, user_type)
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssss",
    $fullname,
    $email,
    $adminPassword,
    $userType
);

if ($stmt->execute()) {
    echo "Admin account created successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();

?>