<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: log_in.html");
    exit;
}

if ($_SESSION['user_type'] !== 'owner') {
    die("Access denied.");
}


/* =========================
   DATABASE CONNECTION
========================= */

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


/* =========================
   GET LOGGED-IN OWNER
========================= */

$owner_id = $_SESSION['user_id'];


/* =========================
   GET FORM DATA
========================= */

$property_code = trim($_POST['property_code'] ?? '');
$property_name = trim($_POST['property_name'] ?? '');
$location = trim($_POST['location'] ?? '');
$address = trim($_POST['address'] ?? '');

$price = (float)($_POST['price'] ?? 0);

$price_max = $_POST['price_max'] ?? null;

if ($price_max === '') {
    $price_max = null;
} else {
    $price_max = (float)$price_max;
}

$price_unit = $_POST['price_unit'] ?? 'month';

$property_type = trim($_POST['property_type'] ?? '');

$image = trim($_POST['image'] ?? '');

$available_rooms = (int)($_POST['available_rooms'] ?? 0);


/* =========================
   VALIDATION
========================= */

if (
    $property_code === '' ||
    $property_name === '' ||
    $location === '' ||
    $address === '' ||
    $price <= 0 ||
    $property_type === '' ||
    $image === ''
) {
    die("Please complete all required fields.");
}


/* =========================
   CHECK PROPERTY CODE
========================= */

$sql = "SELECT id
        FROM properties
        WHERE property_code = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param(
    "s",
    $property_code
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $stmt->close();
    $conn->close();

    die("Property code already exists. Please use another code.");
}

$stmt->close();


/* =========================
   INSERT PROPERTY
========================= */

$sql = "INSERT INTO properties
(
    property_code,
    owner_id,
    property_name,
    location,
    address,
    price,
    price_max,
    price_unit,
    property_type,
    image,
    available_rooms,
    rating,
    review_count,
    verified,
    status
)
VALUES
(
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0, 'pending'
)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}


/*
   s = property_code
   i = owner_id
   s = property_name
   s = location
   s = address
   d = price
   d = price_max
   s = price_unit
   s = property_type
   s = image
   i = available_rooms
*/

$stmt->bind_param(
    "sisssddsssi",
    $property_code,
    $owner_id,
    $property_name,
    $location,
    $address,
    $price,
    $price_max,
    $price_unit,
    $property_type,
    $image,
    $available_rooms
);


/* =========================
   SAVE
========================= */

if ($stmt->execute()) {

    $stmt->close();
    $conn->close();

    header("Location: owner.php?added=success");
    exit;

} else {

    echo "Error adding property: " . $stmt->error;

}


$stmt->close();
$conn->close();

?>