<?php

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Not logged in."
    ]);
    exit;
}

if ($_SESSION['user_type'] !== 'owner') {
    echo json_encode([
        "success" => false,
        "message" => "Access denied."
    ]);
    exit;
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
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);
    exit;
}


$owner_id = (int)$_SESSION['user_id'];
$booking_id = (int)($_POST['booking_id'] ?? 0);


if ($booking_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid booking."
    ]);
    exit;
}


/*
    Make sure this booking
    actually belongs to this owner.
*/

$stmt = $conn->prepare("
    UPDATE bookings
    SET status = 'approved'
    WHERE id = ?
      AND owner_id = ?
      AND status = 'pending'
");

$stmt->bind_param(
    "ii",
    $booking_id,
    $owner_id
);


if ($stmt->execute() && $stmt->affected_rows > 0) {

    echo json_encode([
        "success" => true,
        "message" => "Booking approved."
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Booking could not be approved."
    ]);
}

$stmt->close();
$conn->close();