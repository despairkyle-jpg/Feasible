<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nestify";
$port = 3307;

// Connect to database
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

// Get form data
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$userPassword = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Check required fields
if (
    $fullname === '' ||
    $email === '' ||
    $mobile === '' ||
    $userPassword === '' ||
    $confirmPassword === ''
) {
    die("Please complete all fields.");
}

// Check passwords
if ($userPassword !== $confirmPassword) {
    die("Passwords do not match.");
}

// Check if email already exists
$sql = "SELECT id FROM users WHERE email = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die("This email is already registered.");
}

$stmt->close();

// Hash password
$hashedPassword = password_hash(
    $userPassword,
    PASSWORD_DEFAULT
);

// Set account type
$userType = "owner";

// Insert owner account
$sql = "INSERT INTO users
        (fullname, email, mobile, password, user_type)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param(
    "sssss",
    $fullname,
    $email,
    $mobile,
    $hashedPassword,
    $userType
);

// Save account
if ($stmt->execute()) {

    // Owner account created successfully
    header("Location: property_info.html");
    exit;

} else {

    echo "Error creating account: " . $stmt->error;
}

$stmt->close();
$conn->close();

?>