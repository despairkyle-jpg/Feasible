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

$renterId = (int) $_SESSION['user_id'];

$sql = "
    SELECT
        b.id AS booking_id,
        b.property_id,
        b.room_number,
        b.occupants,
        b.move_in_date,
        b.payment_method,
        b.amount,
        b.status AS booking_status,
        b.payment_status,
        b.created_at,
        p.property_name,
        p.location,
        p.address,
        p.image,
        p.price,
        p.price_unit
    FROM bookings b
    INNER JOIN properties p ON p.id = b.property_id
    WHERE b.renter_id = ?
    ORDER BY b.created_at DESC, b.id DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to prepare rental query.'
    ]);
    exit;
}

$stmt->bind_param("i", $renterId);
$stmt->execute();

$result = $stmt->get_result();
$rentals = [];

while ($row = $result->fetch_assoc()) {
    $rentals[] = $row;
}

echo json_encode([
    'success' => true,
    'rentals' => $rentals
]);

$stmt->close();
$conn->close();
?>