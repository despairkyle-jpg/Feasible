<?php
header('Content-Type: application/json');

require_once 'db.php';

if (!isset($_GET['property_id']) ||
    !is_numeric($_GET['property_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid property ID.'
    ]);
    exit;
}

$propertyId = (int) $_GET['property_id'];

$sql = "SELECT id, room_number, price, availability
        FROM rooms
        WHERE property_id = ?
        AND availability = 'available'
        ORDER BY room_number ASC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database query failed.'
    ]);
    exit;
}

$stmt->bind_param("i", $propertyId);
$stmt->execute();

$result = $stmt->get_result();
$rooms = [];

while ($row = $result->fetch_assoc()) {
    $rooms[] = $row;
}

echo json_encode([
    'success' => true,
    'rooms' => $rooms
]);

$stmt->close();
$conn->close();
?>