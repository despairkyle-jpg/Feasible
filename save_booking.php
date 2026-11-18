<?php

session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "You must be logged in."
    ]);
    exit;
}

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "nestify",
    3307
);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);
    exit;
}

$renter_id = (int)$_SESSION['user_id'];

$property_id = (int)($_POST['property_id'] ?? 0);
$room_display = $_POST['room_number'] ?? '';

$room_number = trim(
    preg_replace('/\s*\(.*\)$/', '', $room_display)
);


$occupants = $_POST['occupants'] ?? '';
$move_in_date = $_POST['move_in_date'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';
$amount = (float)($_POST['amount'] ?? 0);


/* =========================
   VALIDATION
========================= */

if (
    $property_id <= 0 ||
    empty($room_number) ||
    empty($move_in_date) ||
    empty($payment_method)
) {
    echo json_encode([
        "success" => false,
        "message" => "Missing booking information."
    ]);
    exit;
}


/* =========================
   START TRANSACTION
========================= */

$conn->begin_transaction();

try {

    /* =========================
       GET PROPERTY
    ========================= */

    $stmt = $conn->prepare("
        SELECT owner_id
        FROM properties
        WHERE id = ?
    ");

    $stmt->bind_param("i", $property_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {

        throw new Exception("Property not found.");
    }

    $property = $result->fetch_assoc();

    $owner_id = (int)$property['owner_id'];

    $stmt->close();


    /* =========================
       OCCUPY SELECTED ROOM
    ========================= */

    $stmt = $conn->prepare("
        UPDATE rooms
        SET availability = 'occupied'
        WHERE property_id = ?
        AND room_number = ?
        AND availability = 'available'
    ");

    $stmt->bind_param(
        "is",
        $property_id,
        $room_number
    );

    $stmt->execute();

    if ($stmt->affected_rows !== 1) {

        throw new Exception(
            "Selected room is no longer available."
        );
    }

    $stmt->close();


    /* =========================
       UPDATE AVAILABLE ROOMS
    ========================= */

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS available_count
        FROM rooms
        WHERE property_id = ?
        AND availability = 'available'
    ");

    $stmt->bind_param("i", $property_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $row = $result->fetch_assoc();

    $available_rooms = (int)$row['available_count'];

    $stmt->close();


    $stmt = $conn->prepare("
        UPDATE properties
        SET available_rooms = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ii",
        $available_rooms,
        $property_id
    );

    $stmt->execute();

    $stmt->close();


    /* =========================
       SAVE BOOKING
    ========================= */

    $stmt = $conn->prepare("
        INSERT INTO bookings
        (
            renter_id,
            owner_id,
            property_id,
            room_number,
            occupants,
            move_in_date,
            payment_method,
            amount,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");

    $stmt->bind_param(
        "iiissssd",
        $renter_id,
        $owner_id,
        $property_id,
        $room_number,
        $occupants,
        $move_in_date,
        $payment_method,
        $amount
    );

    if (!$stmt->execute()) {

        throw new Exception(
            "Failed to save booking."
        );
    }

    $booking_id = $stmt->insert_id;

    $stmt->close();


    /* =========================
       COMMIT
    ========================= */

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Booking submitted successfully.",
        "booking_id" => $booking_id
    ]);

} catch (Exception $e) {

    /* =========================
       ROLLBACK
    ========================= */

    $conn->rollback();

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}

$conn->close();

?>