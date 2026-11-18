<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rentals | Nestify</title>
    <link rel="stylesheet" href="my-rentals.css">
</head>
<body>

<main class="rentals-container">
    <a href="javascript:history.back()" class="back-link">
        &larr; Back to Profile
    </a>

    <header class="rentals-header">
        <h1>My Rentals</h1>
        <p>View and manage your rental bookings.</p>
    </header>

    <section class="rental-stats">
        <article class="stat-card">
            <span>Total Bookings</span>
            <strong id="totalBookings">0</strong>
        </article>

        <article class="stat-card">
            <span>Pending</span>
            <strong id="pendingBookings">—</strong>
        </article>

        <article class="stat-card">
            <span>Approved</span>
            <strong id="approvedBookings">—</strong>
        </article>
    </section>

    <section class="rentals-section">
        <h2>Your Bookings</h2>

        <div id="rentalsList" class="rentals-list">
            <p>Loading your rentals...</p>
        </div>
    </section>
</main>

<script src="my-rentals.js"></script>
</body>
</html>