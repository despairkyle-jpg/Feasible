<?php
session_start();

header('Content-Type: application/json');

require_once 'db.php';

function respond($success, $message) {
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}

// Verify login
if (!isset($_SESSION['user_id'])) {
    respond(false, 'Please log in again.');
}

$ownerId = (int) $_SESSION['user_id'];

// Read JSON sent by saveRoom()
$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    respond(false, 'Invalid request data.');
}

$roomId = filter_var($data['room_id'] ?? null, FILTER_VALIDATE_INT);
$roomNumber = trim($data['room_number'] ?? '');
$price = $data['price'] ?? null;
$availability = $data['availability'] ?? '';

if (
    !$roomId ||
    $roomNumber === '' ||
    !is_numeric($price) ||
    (float)$price < 0 ||
    !in_array($availability, ['available', 'occupied'], true)
) {
    respond(false, 'Please enter valid room details.');
}

try {
    $conn->begin_transaction();

    // Confirm this room belongs to a property owned by this user.
    $stmt = $conn->prepare("
        SELECT r.id, r.property_id
        FROM rooms r
        INNER JOIN properties p ON p.id = r.property_id
        WHERE r.id = ? AND p.owner_id = ?
        FOR UPDATE
    ");

    $stmt->bind_param("ii", $roomId, $ownerId);
    $stmt->execute();
    $roomResult = $stmt->get_result();
    $room = $roomResult->fetch_assoc();
    $stmt->close();

    if (!$room) {
        $conn->rollback();
        respond(false, 'Room not found or you do not have permission to edit it.');
    }

    $propertyId = (int)$room['property_id'];

    // Do not make a room available while it has an active booking.
    if ($availability === 'available') {
        $stmt = $conn->prepare("
            SELECT id
            FROM bookings
            WHERE property_id = ?
              AND room_number = (
                  SELECT room_number FROM rooms WHERE id = ?
              )
              AND status IN ('pending', 'approved', 'completed')
            LIMIT 1
        ");

        $stmt->bind_param("ii", $propertyId, $roomId);
        $stmt->execute();
        $bookingResult = $stmt->get_result();
        $hasActiveBooking = $bookingResult->num_rows > 0;
        $stmt->close();

        if ($hasActiveBooking) {
            $conn->rollback();
            respond(
                false,
                'This room has an active booking. It cannot be marked available.'
            );
        }
    }

    // Save room details.
    $stmt = $conn->prepare("
        UPDATE rooms
        SET room_number = ?, price = ?, availability = ?
        WHERE id = ?
    ");

    $priceValue = (float)$price;
    $stmt->bind_param(
        "sdsi",
        $roomNumber,
        $priceValue,
        $availability,
        $roomId
    );

    $stmt->execute();
    $stmt->close();

    // Recalculate the number of available rooms for this property.
    $stmt = $conn->prepare("
        UPDATE properties
        SET available_rooms = (
            SELECT COUNT(*)
            FROM rooms
            WHERE property_id = ? AND availability = 'available'
        )
        WHERE id = ? AND owner_id = ?
    ");

    $stmt->bind_param("iii", $propertyId, $propertyId, $ownerId);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    respond(true, 'Room details saved successfully.');

} catch (Throwable $e) {
    if ($conn->errno === 0) {
        // The transaction may already have been rolled back.
    } else {
        $conn->rollback();
    }

    error_log('Update room error: ' . $e->getMessage());
    respond(false, 'A database error occurred while updating the room.');
}
?>