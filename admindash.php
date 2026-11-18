<?php

session_start();

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
   ADMIN ACCESS
========================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: log_in.html");
    exit;
}

if ($_SESSION['user_type'] !== 'admin') {
    header("Location: log_in.html");
    exit;
}

$admin_id = $_SESSION['user_id'];
$admin_name = $_SESSION['fullname'];
$admin_email = $_SESSION['email'];


/* =========================
   DASHBOARD COUNTS
========================= */

$totalUsers = 0;
$totalRenters = 0;
$totalOwners = 0;
$totalProperties = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    $totalUsers = $row['total'];
}

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE user_type = 'renter'
");

if ($result) {
    $row = $result->fetch_assoc();
    $totalRenters = $row['total'];
}

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE user_type = 'owner'
");

if ($result) {
    $row = $result->fetch_assoc();
    $totalOwners = $row['total'];
}


/* =========================
   CHECK IF PROPERTIES TABLE EXISTS
========================= */

$propertyTableExists = false;

$tableCheck = $conn->query("
    SHOW TABLES LIKE 'properties'
");

if ($tableCheck && $tableCheck->num_rows > 0) {
    $propertyTableExists = true;

    $result = $conn->query("
        SELECT COUNT(*) AS total
        FROM properties
    ");

    if ($result) {
        $row = $result->fetch_assoc();
        $totalProperties = $row['total'];
    }
}


/* =========================
   GET USERS
========================= */

$users = [];

$userQuery = $conn->query("
    SELECT id, fullname, email, mobile, user_type, created_at
    FROM users
    ORDER BY id DESC
");

if ($userQuery) {

    while ($row = $userQuery->fetch_assoc()) {
        $users[] = $row;
    }

}


/* =========================
   GET MESSAGES
========================= */

$messages = [];

$messageQuery = $conn->prepare("
    SELECT
        messages.id,
        messages.sender_id,
        messages.receiver_id,
        messages.message,
        messages.created_at,
        users.fullname,
        users.user_type
    FROM messages
    JOIN users
        ON messages.sender_id = users.id
    WHERE messages.receiver_id = ?
       OR messages.sender_id = ?
    ORDER BY messages.created_at DESC
");

if ($messageQuery) {

    $messageQuery->bind_param(
        "ii",
        $admin_id,
        $admin_id
    );

    $messageQuery->execute();

    $messageResult = $messageQuery->get_result();

    while ($row = $messageResult->fetch_assoc()) {
        $messages[] = $row;
    }

    $messageQuery->close();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Nestify Admin</title>


<!-- FONT AWESOME -->

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"

>

<!-- GOOGLE FONT -->

<link
    href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">



<style>

/* =========================
   ROOT
========================= */

:root {

    --navy: #162e4d;
    --gold: #d2aa4a;
    --cream: #f4f2ed;
    --white: #ffffff;

    --text: #263238;
    --muted: #78818b;

    --green: #3d8b61;
    --red: #c94b4b;
    --orange: #d58a36;

    --border: #e5e5e5;

}


/* =========================
   RESET
========================= */

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}


body {

    font-family: "Nunito Sans", sans-serif;

    background: var(--cream);

    color: var(--text);

}


/* =========================
   SIDEBAR
========================= */

.sidebar {

    position: fixed;

    left: 0;
    top: 0;

    width: 250px;
    height: 100vh;

    background: var(--navy);

    color: white;

    padding: 25px 18px;

    z-index: 1000;

}


.logo {

    display: flex;

    align-items: center;

    gap: 10px;

    padding: 10px 15px 30px;

}


.logo i {

    color: var(--gold);

    font-size: 27px;

}


.logo h2 {

    font-size: 24px;

    letter-spacing: .5px;

}


.admin-label {

    margin: 0 15px 20px;

    font-size: 12px;

    color: #cbd3dd;

    text-transform: uppercase;

    letter-spacing: 1px;

}


.nav-item {

    width: 100%;

    border: none;

    background: transparent;

    color: #dfe6ee;

    padding: 13px 15px;

    margin-bottom: 5px;

    border-radius: 10px;

    display: flex;

    align-items: center;

    gap: 13px;

    cursor: pointer;

    font-family: inherit;

    font-size: 14px;

    text-align: left;

    transition: .2s;

}


.nav-item i {

    width: 20px;

}


.nav-item:hover {

    background: rgba(255,255,255,.08);

}


.nav-item.active {

    background: var(--gold);

    color: var(--navy);

    font-weight: 800;

}


/* =========================
   SIDEBAR BOTTOM
========================= */

.sidebar-bottom {

    position: absolute;

    bottom: 25px;

    left: 18px;

    right: 18px;

}


.admin-mini {

    border-top: 1px solid rgba(255,255,255,.12);

    padding: 15px 10px;

    margin-bottom: 10px;

}


.admin-mini strong {

    display: block;

    font-size: 14px;

}


.admin-mini span {

    color: #b9c4cf;

    font-size: 12px;

}


/* =========================
   MAIN
========================= */

.main {

    margin-left: 250px;

    min-height: 100vh;

}


/* =========================
   TOPBAR
========================= */

.topbar {

    height: 75px;

    background: white;

    border-bottom: 1px solid var(--border);

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 30px;

    position: sticky;

    top: 0;

    z-index: 900;

}


.page-title h1 {

    color: var(--navy);

    font-size: 23px;

}


.page-title p {

    color: var(--muted);

    font-size: 13px;

}


.top-admin {

    display: flex;

    align-items: center;

    gap: 10px;

}


.top-avatar {

    width: 40px;
    height: 40px;

    border-radius: 50%;

    background: var(--navy);

    color: white;

    display: flex;

    justify-content: center;

    align-items: center;

}


.top-admin span {

    font-weight: 700;

    font-size: 14px;

}


/* =========================
   CONTENT
========================= */

.content {

    padding: 30px;

}


.screen {

    display: none;

}


.screen.active {

    display: block;

}


/* =========================
   STAT CARDS
========================= */

.stats {

    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 18px;

    margin-bottom: 25px;

}


.stat-card {

    background: white;

    border-radius: 14px;

    padding: 20px;

    border: 1px solid var(--border);

    display: flex;

    align-items: center;

    gap: 15px;

}


.stat-icon {

    width: 48px;
    height: 48px;

    border-radius: 12px;

    background: #edf1f6;

    color: var(--navy);

    display: flex;

    justify-content: center;

    align-items: center;

    font-size: 19px;

}


.stat-card h3 {

    color: var(--navy);

    font-size: 23px;

}


.stat-card p {

    color: var(--muted);

    font-size: 12px;

}


/* =========================
   CARDS
========================= */

.card {

    background: white;

    border: 1px solid var(--border);

    border-radius: 14px;

    padding: 22px;

    margin-bottom: 20px;

}


.card-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 20px;

}


.card-header h2 {

    color: var(--navy);

    font-size: 18px;

}


.card-header p {

    color: var(--muted);

    font-size: 13px;

}


/* =========================
   SEARCH
========================= */

.search-box {

    position: relative;

}


.search-box i {

    position: absolute;

    left: 13px;

    top: 13px;

    color: var(--muted);

}


.search-box input {

    width: 260px;

    height: 40px;

    border: 1px solid var(--border);

    border-radius: 8px;

    padding: 0 12px 0 38px;

    outline: none;

    font-family: inherit;

}


.search-box input:focus {

    border-color: var(--gold);

}


/* =========================
   TABLE
========================= */

.table-wrapper {

    overflow-x: auto;

}


table {

    width: 100%;

    border-collapse: collapse;

}


th {

    text-align: left;

    color: var(--muted);

    font-size: 12px;

    font-weight: 800;

    text-transform: uppercase;

    padding: 13px;

    border-bottom: 1px solid var(--border);

}


td {

    padding: 15px 13px;

    border-bottom: 1px solid #eeeeee;

    font-size: 13px;

}


tr:last-child td {

    border-bottom: none;

}


/* =========================
   BADGES
========================= */

.badge {

    display: inline-block;

    padding: 5px 10px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 800;

}


.badge-admin {

    background: #eee8d4;

    color: #765b13;

}


.badge-owner {

    background: #e7eef7;

    color: var(--navy);

}


.badge-renter {

    background: #e5f2eb;

    color: var(--green);

}


.badge-pending {

    background: #fff1db;

    color: var(--orange);

}


.badge-approved {

    background: #e4f2e9;

    color: var(--green);

}


.badge-suspended {

    background: #f8e3e3;

    color: var(--red);

}


/* =========================
   BUTTONS
========================= */

.btn {

    border: none;

    border-radius: 8px;

    padding: 9px 13px;

    cursor: pointer;

    font-family: inherit;

    font-weight: 700;

    font-size: 12px;

}


.btn-primary {

    background: var(--navy);

    color: white;

}


.btn-gold {

    background: var(--gold);

    color: var(--navy);

}


.btn-danger {

    background: #f5dddd;

    color: var(--red);

}


.btn-success {

    background: #e1f0e7;

    color: var(--green);

}


.btn:hover {

    opacity: .85;

}


.action-buttons {

    display: flex;

    gap: 6px;

    flex-wrap: wrap;

}


/* =========================
   DASHBOARD GRID
========================= */

.dashboard-grid {

    display: grid;

    grid-template-columns: 1.5fr 1fr;

    gap: 20px;

}


/* =========================
   ACTIVITY
========================= */

.activity-item {

    display: flex;

    gap: 12px;

    padding: 12px 0;

    border-bottom: 1px solid var(--border);

}


.activity-item:last-child {

    border-bottom: none;

}


.activity-icon {

    width: 38px;
    height: 38px;

    border-radius: 10px;

    background: #edf1f6;

    color: var(--navy);

    display: flex;

    align-items: center;

    justify-content: center;

}


.activity-item strong {

    display: block;

    font-size: 13px;

}


.activity-item span {

    color: var(--muted);

    font-size: 11px;

}




/* =========================
   REPORTS
========================= */

#reports {
    padding: 20px;
}

#reports .section-header {
    margin-bottom: 25px;
}

#reports .section-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
}


/* REPORT STATISTICS GRID */

#reports .stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}


/* STAT CARD */

#reports .stat-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 22px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    border: 1px solid #e5e5e5;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

#reports .stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
}


/* CARD TITLE */

#reports .stat-card h3 {
    margin: 0 0 10px;
    font-size: 15px;
    font-weight: 600;
    color: #666;
}


/* CARD VALUE */

#reports .stat-card strong {
    display: block;
    font-size: 28px;
    font-weight: 700;
    color: #162e4d;
}


/* RESPONSIVE */

@media (max-width: 900px) {

    #reports .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

}

@media (max-width: 600px) {

    #reports .stats-grid {
        grid-template-columns: 1fr;
    }

    #reports {
        padding: 15px;
    }

}


/* =========================
   MESSAGE
========================= */

.message-item {

    padding: 15px;

    border: 1px solid var(--border);

    border-radius: 10px;

    margin-bottom: 10px;

}


.message-top {

    display: flex;

    justify-content: space-between;

    margin-bottom: 7px;

}


.message-name {

    font-weight: 800;

    color: var(--navy);

}


.message-time {

    font-size: 11px;

    color: var(--muted);

}


.message-text {

    font-size: 13px;

    color: #4e5963;

}


.reply-form {

    display: flex;

    gap: 8px;

    margin-top: 12px;

}


.reply-form input {

    flex: 1;

    height: 38px;

    border: 1px solid var(--border);

    border-radius: 8px;

    padding: 0 12px;

    font-family: inherit;

    outline: none;

}


/* =========================
   EMPTY
========================= */

.empty {

    text-align: center;

    padding: 40px;

    color: var(--muted);

}


.empty i {

    font-size: 35px;

    margin-bottom: 10px;

}

/* =========================
   ADMIN PROFILE
========================= */

#profile {
    padding: 20px;
}

#profile .profile-box {
    max-width: 500px;
    margin: 20px auto;
    padding: 35px;
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    text-align: center;
}

#profile .profile-avatar {
    width: 85px;
    height: 85px;
    margin: 0 auto 20px;
    border-radius: 50%;
    background: #162e4d;
    display: flex;
    align-items: center;
    justify-content: center;
}

#profile .profile-avatar i {
    font-size: 38px;
    color: #ffffff;
}

#profile .profile-header h2 {
    margin: 10px 0 5px;
    color: #162e4d;
}

#profile .profile-header p {
    margin: 0 0 20px;
    color: #666;
}

#profile .badge-admin {
    display: inline-block;
    padding: 7px 14px;
    border-radius: 20px;
    background: #162e4d;
    color: #ffffff;
    font-size: 13px;
}


/* =========================
   MOBILE
========================= */

.mobile-menu {

    display: none;

    border: none;

    background: none;

    font-size: 22px;

    color: var(--navy);

}


@media (max-width: 1000px) {

    .stats {

        grid-template-columns:
            repeat(2, 1fr);

    }

    .dashboard-grid {

        grid-template-columns: 1fr;

    }

}


@media (max-width: 700px) {

    .sidebar {

        transform: translateX(-100%);

        transition: .25s;

    }

    .sidebar.open {

        transform: translateX(0);

    }

    .main {

        margin-left: 0;

    }

    .mobile-menu {

        display: block;

    }

    .content {

        padding: 18px;

    }

    .stats {

        grid-template-columns: 1fr 1fr;

        gap: 10px;

    }

    .stat-card {

        padding: 14px;

    }

    .stat-card h3 {

        font-size: 18px;

    }

    .top-admin span {

        display: none;

    }

    .search-box input {

        width: 180px;

    }

}


</style>

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->

<aside class="sidebar" id="sidebar">

    <div class="logo">

        <i class="fa-solid fa-house"></i>

        <h2>Nestify</h2>

    </div>


    <div class="admin-label">

        Administration

    </div>


    <button
        class="nav-item active"
        onclick="showScreen('dashboard', this)"
    >

        <i class="fa-solid fa-chart-line"></i>

        Dashboard

    </button>


    <button
        class="nav-item"
        onclick="showScreen('properties', this)"
    >

        <i class="fa-solid fa-house"></i>

        Properties

    </button>


    <button
        class="nav-item"
        onclick="showScreen('users', this)"
    >

        <i class="fa-solid fa-users"></i>

        Users

    </button>


    <button
        class="nav-item"
        onclick="showScreen('reports', this)"
    >

        <i class="fa-solid fa-flag"></i>

        Reports

    </button>


    <button
        class="nav-item"
        onclick="showScreen('messages', this)"
    >

        <i class="fa-solid fa-message"></i>

        Messages

    </button>


    <button
        class="nav-item"
        onclick="showScreen('profile', this)"
    >

        <i class="fa-solid fa-user"></i>

        Profile

    </button>


    <div class="sidebar-bottom">

        <div class="admin-mini">

            <strong>
                <?= htmlspecialchars($admin_name) ?>
            </strong>

            <span>
                Administrator
            </span>

        </div>


        <button
            class="nav-item"
            onclick="logout()"
        >

            <i class="fa-solid fa-right-from-bracket"></i>

            Logout

        </button>

    </div>

</aside>


<!-- =========================
     MAIN
========================= -->

<main class="main">


<!-- TOPBAR -->

<header class="topbar">

    <div class="page-title">

        <button
            class="mobile-menu"
            onclick="toggleSidebar()"
        >

            <i class="fa-solid fa-bars"></i>

        </button>

        <h1 id="pageTitle">
            Dashboard
        </h1>

        <p>
            Manage Nestify
        </p>

    </div>


    <div class="top-admin">

        <div class="top-avatar">

            <i class="fa-solid fa-user-tie"></i>

        </div>

        <span>
            <?= htmlspecialchars($admin_name) ?>
        </span>

    </div>

</header>


<div class="content">


<!-- =========================
     DASHBOARD
========================= -->

<section
    id="dashboard"
    class="screen active"

>
    <div class="stats">


        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-users"></i>

            </div>

            <div>

                <h3>
                    <?= $totalUsers ?>
                </h3>

                <p>
                    Total Users
                </p>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-user-tie"></i>

            </div>

            <div>

                <h3>
                    <?= $totalOwners ?>
                </h3>

                <p>
                    Property Owners
                </p>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-user"></i>

            </div>

            <div>

                <h3>
                    <?= $totalRenters ?>
                </h3>

                <p>
                    Renters
                </p>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-house"></i>

            </div>

            <div>

                <h3>
                    <?= $totalProperties ?>
                </h3>

                <p>
                    Properties
                </p>

            </div>

        </div>

    </div>



    <div class="dashboard-grid">


        <div class="card">

            <div class="card-header">

                <div>

                    <h2>
                        Admin Overview
                    </h2>

                    <p>
                        Manage the Nestify platform
                    </p>

                </div>

            </div>


            <div class="activity-item">

                <div class="activity-icon">

                    <i class="fa-solid fa-house"></i>

                </div>

                <div>

                    <strong>
                        Property Management
                    </strong>

                    <span>
                        Review, approve and manage property listings.
                    </span>

                </div>

            </div>


            <div class="activity-item">

                <div class="activity-icon">

                    <i class="fa-solid fa-users"></i>

                </div>

                <div>

                    <strong>
                        User Management
                    </strong>

                    <span>
                        Manage renter and owner accounts.
                    </span>

                </div>

            </div>


            <div class="activity-item">

                <div class="activity-icon">

                    <i class="fa-solid fa-message"></i>

                </div>

                <div>

                    <strong>
                        Messages
                    </strong>

                    <span>
                        Respond to questions and support requests.
                    </span>

                </div>

            </div>


            <div class="activity-item">

                <div class="activity-icon">

                    <i class="fa-solid fa-flag"></i>

                </div>

                <div>

                    <strong>
                        Reports
                    </strong>

                    <span>
                        Review reported properties and users.
                    </span>

                </div>

            </div>

        </div>


        <div class="card">

            <div class="card-header">

                <div>

                    <h2>
                        Account
                    </h2>

                    <p>
                        Current administrator
                    </p>

                </div>

            </div>


            <div class="activity-item">

                <div class="activity-icon">

                    <i class="fa-solid fa-user-tie"></i>

                </div>

                <div>

                    <strong>
                        <?= htmlspecialchars($admin_name) ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars($admin_email) ?>
                    </span>

                </div>

            </div>

        </div>

    </div>

</section>



<!-- =========================
     PROPERTIES
========================= -->

<section
    id="properties"
    class="screen"
>

    <div class="card">

        <div class="card-header">

            <div>

                <h2>
                    Property Management
                </h2>

                <p>
                    Review and manage property listings.
                </p>

            </div>


            <div class="search-box">

                <i class="fa-solid fa-search"></i>

                <input
                    type="text"
                    id="propertySearch"
                    placeholder="Search properties..."
                    onkeyup="searchTable('propertySearch','propertyTable')"
                >

            </div>

        </div>


        <?php if ($propertyTableExists): ?>

        <?php

        $propertyQuery = $conn->query("
            SELECT *
            FROM properties
            ORDER BY 1 DESC
        ");

        ?>


        <div class="table-wrapper">

            <table id="propertyTable">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Property</th>

                        <th>Owner</th>

                        <th>Price</th>

                        <th>Status</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($propertyQuery && $propertyQuery->num_rows > 0): ?>

                    <?php while ($property = $propertyQuery->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($property['id'] ?? '') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $property['name']
                                ?? $property['property_name']
                                ?? 'Property'
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $property['owner_id']
                                ?? '—'
                            ) ?>
                        </td>

                        <td>
                            ₱<?= htmlspecialchars(
                                $property['price']
                                ?? '—'
                            ) ?>
                        </td>

                        <td>

   <?php
$status = strtolower($property['status'] ?? 'pending');

$statusClasses = [
    'approved'  => 'badge-approved',
    'active'    => 'badge-approved',
    'pending'   => 'badge-pending',
    'suspended' => 'badge-suspended',
    'inactive'  => 'badge-suspended'
];

$badgeClass = $statusClasses[$status] ?? 'badge-pending';
?>

<span class="badge <?= $badgeClass ?>">
    <?= htmlspecialchars(ucfirst($status)) ?>
</span>
                        </td>

                        <td>

                            <div class="action-buttons">

                                <button
    class="btn btn-success"
    onclick="approveProperty(<?= (int)$property['id'] ?>, this)"
>
    Approve
</button>

                               <button
    class="btn btn-danger"
    onclick="suspendProperty(<?= (int)$property['id'] ?>, this)"
>
    Suspend
                   </button>

                            </div>

                        </td>

                    </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="6">

                            <div class="empty">

                                <i class="fa-solid fa-house"></i>

                                <p>
                                    No properties found.
                                </p>

                            </div>

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>


        <?php else: ?>

            <div class="empty">

                <i class="fa-solid fa-house"></i>

                <p>
                    The properties table has not been configured yet.
                </p>

            </div>

        <?php endif; ?>

    </div>

</section>



<!-- =========================
     USERS
========================= -->

<section
    id="users"
    class="screen"
>

    <div class="card">

        <div class="card-header">

            <div>

                <h2>
                    User Management
                </h2>

                <p>
                    View Nestify accounts.
                </p>

            </div>


            <div class="search-box">

                <i class="fa-solid fa-search"></i>

                <input
                    type="text"
                    id="userSearch"
                    placeholder="Search users..."
                    onkeyup="searchTable('userSearch','userTable')"
                >

            </div>

        </div>


        <div class="table-wrapper">

            <table id="userTable">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Name</th>

                        <th>Email</th>

                        <th>Mobile</th>

                        <th>Type</th>

                        <th>Created</th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach ($users as $user): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($user['id']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($user['fullname']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($user['email']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $user['mobile'] ?? '—'
                            ) ?>
                        </td>

                        <td>

                            <?php

                            $type = $user['user_type'];

                            $badgeClass =
                                $type === 'admin'
                                ? 'badge-admin'
                                : ($type === 'owner'
                                    ? 'badge-owner'
                                    : 'badge-renter');

                            ?>

                            <span
                                class="badge <?= $badgeClass ?>"
                            >

                                <?= htmlspecialchars(
                                    ucfirst($type)
                                ) ?>

                            </span>

                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $user['created_at']
                            ) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</section>




<!-- =========================
     REPORTS
========================= -->

<section id="reports" class="screen">

    <div class="section-header">
        <h2>Reports</h2>
    </div>

    <div class="stats-grid">

        <?php
        // USERS
        $totalUsers = 0;
        $totalRenters = 0;
        $totalOwners = 0;

        $result = $conn->query("SELECT COUNT(*) AS total FROM users");
        if ($result) {
            $totalUsers = $result->fetch_assoc()['total'];
        }

        $result = $conn->query("
            SELECT COUNT(*) AS total 
            FROM users 
            WHERE user_type = 'renter'
        ");
        if ($result) {
            $totalRenters = $result->fetch_assoc()['total'];
        }

        $result = $conn->query("
            SELECT COUNT(*) AS total 
            FROM users 
            WHERE user_type = 'owner'
        ");
        if ($result) {
            $totalOwners = $result->fetch_assoc()['total'];
        }


        // PROPERTIES
        $totalProperties = 0;

        $result = $conn->query("
            SELECT COUNT(*) AS total 
            FROM properties
        ");

        if ($result) {
            $totalProperties = $result->fetch_assoc()['total'];
        }


        // BOOKINGS
        $totalBookings = 0;
        $pendingBookings = 0;
        $approvedBookings = 0;
        $rejectedBookings = 0;
        $totalRevenue = 0;

        $result = $conn->query("
            SELECT COUNT(*) AS total 
            FROM bookings
        ");

        if ($result) {
            $totalBookings = $result->fetch_assoc()['total'];
        }

        $result = $conn->query("
            SELECT COUNT(*) AS total 
            FROM bookings 
            WHERE status = 'pending'
        ");

        if ($result) {
            $pendingBookings = $result->fetch_assoc()['total'];
        }

        $result = $conn->query("
            SELECT COUNT(*) AS total 
            FROM bookings 
            WHERE status = 'approved'
        ");

        if ($result) {
            $approvedBookings = $result->fetch_assoc()['total'];
        }

        $result = $conn->query("
            SELECT COUNT(*) AS total 
            FROM bookings 
            WHERE status = 'rejected'
        ");

        if ($result) {
            $rejectedBookings = $result->fetch_assoc()['total'];
        }

        $result = $conn->query("
            SELECT COALESCE(SUM(amount), 0) AS total 
            FROM bookings 
            WHERE status = 'approved'
        ");

        if ($result) {
            $totalRevenue = $result->fetch_assoc()['total'];
        }
        ?>

        <div class="stat-card">
            <h3>Total Users</h3>
            <strong><?= (int)$totalUsers ?></strong>
        </div>

        <div class="stat-card">
            <h3>Renters</h3>
            <strong><?= (int)$totalRenters ?></strong>
        </div>

        <div class="stat-card">
            <h3>Owners</h3>
            <strong><?= (int)$totalOwners ?></strong>
        </div>

        <div class="stat-card">
            <h3>Properties</h3>
            <strong><?= (int)$totalProperties ?></strong>
        </div>

        <div class="stat-card">
            <h3>Total Bookings</h3>
            <strong><?= (int)$totalBookings ?></strong>
        </div>

        <div class="stat-card">
            <h3>Pending Bookings</h3>
            <strong><?= (int)$pendingBookings ?></strong>
        </div>

        <div class="stat-card">
            <h3>Approved Bookings</h3>
            <strong><?= (int)$approvedBookings ?></strong>
        </div>

        <div class="stat-card">
            <h3>Rejected Bookings</h3>
            <strong><?= (int)$rejectedBookings ?></strong>
        </div>

        <div class="stat-card">
            <h3>Total Revenue</h3>
            <strong>₱<?= number_format((float)$totalRevenue, 2) ?></strong>
        </div>

    </div>

</section>



<!-- =========================
     MESSAGES
========================= -->
<section id="messages" class="screen">

    <div class="section-header">
        <h2>Messages</h2>
    </div>

    <div class="messages-container">

        <?php
        $adminId = (int)$_SESSION['user_id'];

        $messageQuery = "
            SELECT 
                m.id,
                m.sender_id,
                m.receiver_id,
                m.message,
                u.fullname AS sender_name,
                u.user_type AS sender_type
            FROM messages m
            LEFT JOIN users u
                ON m.sender_id = u.id
            WHERE m.receiver_id = $adminId
               OR m.sender_id = $adminId
            ORDER BY m.id DESC
        ";

        $messageResult = $conn->query($messageQuery);

        if ($messageResult && $messageResult->num_rows > 0):

            while ($msg = $messageResult->fetch_assoc()):

                $isAdminSender = ((int)$msg['sender_id'] === $adminId);
        ?>

            <div class="message-card <?= $isAdminSender ? 'admin-message' : 'user-message' ?>">

                <div class="message-header">

                    <strong>
                        <?= htmlspecialchars(
                            $isAdminSender
                                ? 'You'
                                : ($msg['sender_name'] ?? 'Unknown User')
                        ) ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars(
                            $isAdminSender
                                ? 'Admin'
                                : ucfirst($msg['sender_type'] ?? 'User')
                        ) ?>
                    </span>

                </div>

                <div class="message-body">
                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                </div>

                <?php if (!$isAdminSender): ?>

                    <form
                        action="send_admin_message.php"
                        method="POST"
                        class="reply-form"
                    >

                        <input
                            type="hidden"
                            name="receiver_id"
                            value="<?= (int)$msg['sender_id'] ?>"
                        >

                        <input
                            type="text"
                            name="message"
                            placeholder="Write a reply..."
                            required
                        >

                        <button type="submit" class="btn btn-success">
                            Reply
                        </button>

                    </form>

                <?php endif; ?>

            </div>

        <?php
            endwhile;

        else:
        ?>

            <div class="no-messages">
                No messages found.
            </div>

        <?php endif; ?>

    </div>

</section>

<!-- =========================
     PROFILE
========================= -->

<section id="profile" class="screen">

    <div class="section-header">
        <h2>Profile</h2>
    </div>

    <div class="card profile-box">

        <div class="profile-header">

            <div class="profile-avatar">
                <i class="fa-solid fa-user-tie"></i>
            </div>

            <h2>
                <?= htmlspecialchars($admin_name) ?>
            </h2>

            <p>
                <?= htmlspecialchars($admin_email) ?>
            </p>

            <span class="badge badge-admin">
                Administrator
            </span>

        </div>

    </div>

</section>

</div>

</main>


<script>

/* =========================
   SCREEN NAVIGATION
========================= */

function showScreen(screenId, button) {

    document
        .querySelectorAll(".screen")
        .forEach(screen => {

            screen.classList.remove("active");

        });


    const selectedScreen =
        document.getElementById(screenId);


    if (selectedScreen) {

        selectedScreen.classList.add("active");

    }


    document
        .querySelectorAll(".nav-item")
        .forEach(item => {

            item.classList.remove("active");

        });


    if (button) {

        button.classList.add("active");

    }


    const titles = {

        dashboard: "Dashboard",

        properties: "Properties",

        users: "Users",

        bookings: "Bookings",

        reports: "Reports",

        messages: "Messages",

        profile: "Profile"

    };


    document.getElementById("pageTitle").textContent =
        titles[screenId] || "Dashboard";


    document
        .getElementById("sidebar")
        .classList.remove("open");

}


/* =========================
   MOBILE SIDEBAR
========================= */

function toggleSidebar() {

    document
        .getElementById("sidebar")
        .classList.toggle("open");

}


/* =========================
   SEARCH TABLE
========================= */

function searchTable(inputId, tableId) {

    const input =
        document.getElementById(inputId);

    const filter =
        input.value.toLowerCase();

    const table =
        document.getElementById(tableId);

    const rows =
        table
        .getElementsByTagName("tr");


    for (let i = 1; i < rows.length; i++) {

        const text =
            rows[i].textContent.toLowerCase();


        if (text.includes(filter)) {

            rows[i].style.display = "";

        } else {

            rows[i].style.display = "none";

        }

    }

}


/* =========================
   PROPERTY APPROVE
========================= */
function updatePropertyStatus(propertyId, status, button) {

    const formData = new FormData();

    formData.append("property_id", propertyId);
    formData.append("status", status);

    fetch("update_property_status.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(result => {

        if (result.trim() === "success") {

            const row = button.closest("tr");
            const badge = row.querySelector(".badge");

            if (status === "approved") {

                badge.textContent = "Approved";
                badge.className = "badge badge-approved";

            } else if (status === "suspended") {

                badge.textContent = "Suspended";
                badge.className = "badge badge-suspended";

            }

            button.disabled = true;
            button.style.opacity = ".5";

        } else {

            alert("Failed to update property.");

        }

    })
    .catch(error => {

        console.error(error);

        alert("Something went wrong.");

    });
}


function approveProperty(propertyId, button) {

    if (!confirm("Approve this property?")) {
        return;
    }

    updatePropertyStatus(
        propertyId,
        "approved",
        button
    );
}


function suspendProperty(propertyId, button) {

    if (!confirm("Suspend this property?")) {
        return;
    }

    updatePropertyStatus(
        propertyId,
        "suspended",
        button
    );
}


/* =========================
   LOGOUT
========================= */

function logout() {

    if (
        confirm("Are you sure you want to logout?")
    ) {

        window.location.href =
            "log_in.html";

    }

}

</script>


</body>

</html>


<?php

$conn->close();

?>
