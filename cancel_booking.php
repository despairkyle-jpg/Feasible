<?php
session_start();
header('Content-Type: application/json');

require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Please log in first.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

$renterId = (int) $_SESSION['user_id'];
$bookingId = (int) ($_POST['booking_id'] ?? 0);

if ($bookingId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid booking ID.'
    ]);
    exit;
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("
        SELECT property_id, room_number
        FROM bookings
        WHERE id = ? AND renter_id = ? AND status = 'pending'
        FOR UPDATE
    ");
    $stmt->bind_param("ii", $bookingId, $renterId);
    $stmt->execute();

    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$booking) {
        throw new Exception(
            'Booking not found or it can no longer be cancelled.'
        );
    }

    $propertyId = (int) $booking['property_id'];
    $roomNumber = $booking['room_number'];

    $stmt = $conn->prepare("
        UPDATE bookings
        SET status = 'cancelled'
        WHERE id = ? AND renter_id = ? AND status = 'pending'
    ");
    $stmt->bind_param("ii", $bookingId, $renterId);
    $stmt->execute();

    if ($stmt->affected_rows !== 1) {
        $stmt->close();
        throw new Exception('The booking could not be cancelled.');
    }

    $stmt->close();

    // Release the room only if no other active booking uses it.
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS active_count
        FROM bookings
        WHERE property_id = ?
          AND room_number = ?
          AND status IN ('pending', 'approved', 'completed')
    ");
    $stmt->bind_param("is", $propertyId, $roomNumber);
    $stmt->execute();

    $activeCount = (int) $stmt->get_result()
        ->fetch_assoc()['active_count'];

    $stmt->close();

    if ($activeCount === 0) {
        $stmt = $conn->prepare("
            UPDATE rooms
            SET availability = 'available'
            WHERE property_id = ?
              AND room_number = ?
              AND availability = 'occupied'
        ");
        $stmt->bind_param("is", $propertyId, $roomNumber);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS available_count
        FROM rooms
        WHERE property_id = ?
          AND availability = 'available'
    ");
    $stmt->bind_param("i", $propertyId);
    $stmt->execute();

    $availableRooms = (int) $stmt->get_result()
        ->fetch_assoc()['available_count'];

    $stmt->close();

    $stmt = $conn->prepare("
        UPDATE properties
        SET available_rooms = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ii", $availableRooms, $propertyId);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Booking cancelled successfully.'
    ]);

} catch (Exception $e) {
    $conn->rollback();

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>