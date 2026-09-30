<?php

session_start();

/* Only logged-in renters can send messages */
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'renter') {
    header("Location: log_in.html");
    exit;
}


/* Database connection */

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
    die("Database connection failed: " . $conn->connect_error);
}


/* Get renter information */

$sender_id = $_SESSION['user_id'];

$message = trim($_POST['message'] ?? '');


if ($message === '') {
    die("Message cannot be empty.");
}


/* Find the admin account */

$adminQuery = $conn->prepare("
    SELECT id
    FROM users
    WHERE user_type = 'admin'
    LIMIT 1
");

$adminQuery->execute();

$adminResult = $adminQuery->get_result();

if ($adminResult->num_rows !== 1) {
    die("Admin account not found.");
}

$admin = $adminResult->fetch_assoc();

$receiver_id = $admin['id'];

$adminQuery->close();


/* Insert message */

$stmt = $conn->prepare("
    INSERT INTO messages
    (sender_id, receiver_id, message)
    VALUES (?, ?, ?)
");

$stmt->bind_param(
    "iis",
    $sender_id,
    $receiver_id,
    $message
);

if ($stmt->execute()) {

    header("Location: renter.php");

    exit;

} else {

    echo "Failed to send message.";

}


$stmt->close();
$conn->close();

?>