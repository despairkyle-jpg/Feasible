<?php

session_start();

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

$email = trim($_POST['email'] ?? '');
$userPassword = $_POST['password'] ?? '';

if ($email === '' || $userPassword === '') {
    die("Please enter your email and password.");
}

$sql = "SELECT * FROM users WHERE email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $user = $result->fetch_assoc();

    // Check password
    if (password_verify($userPassword, $user['password'])) {

        // Create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_type'] = $user['user_type'];

        // Redirect based on account type
       // Redirect based on account type
if ($user['user_type'] === 'admin') {

    header("Location: admindash.php");
    exit;

} elseif ($user['user_type'] === 'owner') {

    header("Location: owner.php");
    exit;

} elseif ($user['user_type'] === 'renter') {

    header("Location: renter.php");
    exit;

} else {

    echo "Invalid account type.";
}
    } else {

        echo "Incorrect password.";
    }

} else {

    echo "Account not found.";
}

$stmt->close();
$conn->close();

?>