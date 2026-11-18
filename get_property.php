<?php
session_start();

header("Content-Type: application/json");

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

$owner_id = (int)$_SESSION['user_id'];
$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("
    SELECT *
    FROM properties
    WHERE id = ?
    AND owner_id = ?
");

$stmt->bind_param("ii", $property_id, $owner_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Property not found."
    ]);
    exit;
}

$property = $result->fetch_assoc();

echo json_encode([
    "success" => true,
    "property" => $property
]);

$stmt->close();
$conn->close();
?>
