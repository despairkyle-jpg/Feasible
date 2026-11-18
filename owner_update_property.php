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

$owner_id = (int) $_SESSION['user_id'];

$property_id = (int) ($_POST['property_id'] ?? 0);
$property_name = trim($_POST['property_name'] ?? '');
$location = trim($_POST['location'] ?? '');
$price = (float) ($_POST['price'] ?? 0);
$description = trim($_POST['description'] ?? '');
if (
    $property_id <= 0 ||
    $property_name === '' ||
    $location === '' ||
    $price < 0
) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid property information.",
        "debug" => [
            "property_id" => $property_id,
            "property_name" => $property_name,
            "location" => $location,
            "price" => $price,
            "description" => $description
        ]
    ]);
    exit;
}

$stmt = $conn->prepare("
    UPDATE properties
    SET
        property_name = ?,
        location = ?,
        price = ?,
        description = ?
    WHERE id = ?
    AND owner_id = ?
");

$stmt->bind_param(
    "ssdsii",
    $property_name,
    $location,
    $price,
    $description,
    $property_id,
    $owner_id
);

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Property updated successfully."
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Failed to update property."
    ]);
}

$stmt->close();
$conn->close();

?>