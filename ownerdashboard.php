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

$owner_id = (int)$_SESSION['user_id'];


$roomsPropertyQuery = "
    SELECT id, property_name, location
    FROM properties
    WHERE owner_id = $owner_id
    ORDER BY id DESC
";

$roomsPropertyResult = $conn->query($roomsPropertyQuery);


/* =========================
   OWNER STATISTICS
========================= */

// My Properties
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM properties
    WHERE owner_id = $owner_id
");

$myProperties = 0;

if ($result) {
    $myProperties = (int)$result->fetch_assoc()['total'];
}


// Total Rooms
$result = $conn->query("
    SELECT COALESCE(SUM(total_rooms), 0) AS total
    FROM properties
    WHERE owner_id = $owner_id
");

$totalRooms = 0;

if ($result) {
    $totalRooms = (int)$result->fetch_assoc()['total'];
}


// Available Rooms
$result = $conn->query("
    SELECT COALESCE(SUM(available_rooms), 0) AS total
    FROM properties
    WHERE owner_id = $owner_id
");

$availableRooms = 0;

if ($result) {
    $availableRooms = (int)$result->fetch_assoc()['total'];
}


// Occupied Rooms
$occupiedRooms = $totalRooms - $availableRooms;

// Approved Properties
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM properties
    WHERE owner_id = $owner_id
    AND status = 'approved'
");

$approvedProperties = 0;

if ($result) {
    $approvedProperties = (int)$result->fetch_assoc()['total'];
}


// Owner's Bookings
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM bookings
    WHERE owner_id = $owner_id
");

$totalBookings = 0;

if ($result) {
    $totalBookings = (int)$result->fetch_assoc()['total'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Owner Dashboard - Nestify</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet" href="owner.css">
</head>

<body>

<!-- =========================
     DASHBOARD
========================= -->

<section id="dashboard" class="screen">

    <header class="owner-header">

        <div class="header-top">

            <div>
                <p class="small-text">Welcome back</p>
                <h1>Owner Dashboard</h1>
            </div>

           <div class="owner-profile">

    <div class="profile-icon">
        <i class="fa-solid fa-user"></i>
    </div>

    <div class="owner-details">

        <strong>
    <?php echo htmlspecialchars($_SESSION['fullname']); ?>
</strong>

        <span>Property Owner</span>

        <button class="logout-btn" onclick="logoutOwner()">
            <i class="fa-solid fa-right-from-bracket"></i>
            Logout
        </button>

    </div>

</div>

        </div>

    </header>


    
        <!-- =========================
             STATISTICS
        ========================= -->

    <main class="content">
        <section class="stats">

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-house"></i>
                </div>

                <div>
                    <span>My Properties</span>
                   <h2><?= $myProperties ?></h2>
                </div>
            </div>


            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-door-open"></i>
                </div>

                <div>
                    <span>Total Rooms</span>
                   <h2><?= $totalRooms ?></h2>
                </div>
            </div>


           <div class="stat-card">
    <div class="stat-icon available">
        <i class="fa-solid fa-circle-check"></i>
    </div>

    <div>
        <span>Available</span>
        <h2><?= $availableRooms ?></h2>
    </div>
</div>


<div class="stat-card">
    <div class="stat-icon occupied">
        <i class="fa-solid fa-user-group"></i>
    </div>

    <div>
        <span>Occupied</span>
        <h2><?= $occupiedRooms ?></h2>
    </div>
</div>

        </section>


        <!-- =========================
             QUICK ACTIONS
        ========================= -->

        <section class="section">

    <div class="section-title">

        <div>
            <h2>Quick Actions</h2>
            <p>Manage your rental properties</p>
        </div>

    </div>


    <div class="quick-actions">

        <!-- My Properties -->
        <button onclick="showScreen('properties')">

            <i class="fa-solid fa-house"></i>

            <div>
                <strong>My Properties</strong>
                <span>View and edit properties</span>
            </div>

            <i class="fa-solid fa-chevron-right arrow"></i>

        </button>


        <!-- Manage Rooms -->
        <button onclick="showScreen('rooms')">

            <i class="fa-solid fa-door-open"></i>

            <div>
                <strong>Manage Rooms</strong>
                <span>Availability and room details</span>
            </div>

            <i class="fa-solid fa-chevron-right arrow"></i>

        </button>


         <!-- Manage Rooms -->
        <button onclick="showScreen('bookingrequest')">

            <i class="fa-solid fa-door-open"></i>

            <div>
                <strong>Booking Request</strong>
                <span>View new booking requests</span>
            </div>

            <i class="fa-solid fa-chevron-right arrow"></i>

        </button>


        <!-- Add Property -->
        <button onclick="openAddProperty()">

            <i class="fa-solid fa-plus"></i>

            <div>
                <strong>Add Property</strong>
                <span>Add a new rental property</span>
            </div>

            <i class="fa-solid fa-chevron-right arrow"></i>

        </button>


        <!-- My Tenants -->
        <button onclick="showScreen('tenants')">

            <i class="fa-solid fa-users"></i>

            <div>
                <strong>My Tenants</strong>
                <span>View and manage your tenants</span>
            </div>

            <i class="fa-solid fa-chevron-right arrow"></i>

        </button>


        <!-- Transactions -->
        <button onclick="showScreen('transactions')">

            <i class="fa-solid fa-money-bill-transfer"></i>

            <div>
                <strong>Transactions</strong>
                <span>View payments and transactions</span>
            </div>

            <i class="fa-solid fa-chevron-right arrow"></i>

        </button>


        <!-- Maintenance -->
        <button onclick="showScreen('maintenance')">

            <i class="fa-solid fa-screwdriver-wrench"></i>

            <div>
                <strong>Maintenance</strong>
                <span>Manage maintenance requests</span>
            </div>

            <i class="fa-solid fa-chevron-right arrow"></i>

        </button>


    </div>

</section>


        <!-- =========================
             MY PROPERTIES PREVIEW
        ========================= -->

        <section class="section">

            <div class="section-title">

                <div>
                    <h2>My Properties</h2>
                    <p>Recently managed properties</p>
                </div>

                <button class="view-all"
                        onclick="showScreen('properties')">
                    View All
                </button>

            </div>


           <div class="property-list">

<?php
$propertyQuery = "
    SELECT *
    FROM properties
    WHERE owner_id = $owner_id
    ORDER BY id DESC
";

$propertyResult = $conn->query($propertyQuery);


?>
<?php
if ($propertyResult && $propertyResult->num_rows > 0):

    while ($property = $propertyResult->fetch_assoc()):

        $status = strtolower($property['status'] ?? 'pending');

        if ($status === 'approved') {
            $statusClass = 'approved';
            $statusText = 'Approved';
        } elseif ($status === 'suspended') {
            $statusClass = 'suspended';
            $statusText = 'Suspended';
        } else {
            $statusClass = 'pending';
            $statusText = 'Pending';
        }

        $image = !empty($property['image'])
            ? $property['image']
            : 'images/bg.jpg';
?>

    <div class="property-card">

        <img src="<?= htmlspecialchars($image) ?>"
             onerror="this.src='images/bg.jpg';">

        <div class="property-info">

            <div class="property-top">

                <div>

                    <h3>
                        <?= htmlspecialchars($property['property_name']) ?>
                    </h3>

                    <p>
                        <i class="fa-solid fa-location-dot"></i>
                        <?= htmlspecialchars($property['location']) ?>
                    </p>

                </div>

                <span class="status <?= $statusClass ?>">
                    <?= htmlspecialchars($statusText) ?>
                </span>

            </div>


            <div class="property-details">

                <div>
                    <span>Monthly Price</span>

                    <strong>
                        ₱<?= number_format((float)$property['price'], 2) ?>
                    </strong>
                </div>

                <div>
                    <span>Total Rooms</span>

                    <strong>
                        <?= (int)$property['total_rooms'] ?>
                    </strong>
                </div>

                <div>
                    <span>Available</span>

                    <strong>
                        <?= (int)$property['available_rooms'] ?>
                    </strong>
                </div>

            </div>


            <div class="property-actions">
<button
    class="secondary-btn"
    onclick="console.log('Property ID:', <?= (int)$property['id'] ?>); editProperty(<?= (int)$property['id'] ?>)">

    <i class="fa-solid fa-pen"></i>
    Edit

</button>


                <button
                    class="primary-btn"
                    onclick="showScreen('rooms')">

                    <i class="fa-solid fa-door-open"></i>
                    Rooms

                </button>

            </div>

        </div>

    </div>

<?php endwhile; ?>
<?php else: ?>

    <p>No properties found.</p>

<?php endif; ?>

</div>

        </section>

    </main>

</section>


<!-- =========================
     PROPERTIES
========================= -->

<section id="properties" class="screen">

    <header class="page-header">

        <button class="back-button"
                onclick="showScreen('dashboard')">

            <i class="fa-solid fa-arrow-left"></i>
            Back

        </button>

        <h1>My Properties</h1>

        <p>Manage your rental accommodations</p>

    </header>


    <main class="content">

<button class="add-property-btn"
        onclick="openAddProperty()">

    <i class="fa-solid fa-plus"></i>
    Add Property

</button>


<?php
$propertyQuery = "
    SELECT *
    FROM properties
    WHERE owner_id = $owner_id
    ORDER BY id DESC
";

$propertyResult = $conn->query($propertyQuery);
?>


<?php if ($propertyResult && $propertyResult->num_rows > 0): ?>

    <?php while ($property = $propertyResult->fetch_assoc()): ?>

    <div class="property-card">

        <!-- Property image -->
        <img src="<?= htmlspecialchars($property['image']) ?>"
             onerror="this.src='images/bg.jpg';">

        <div class="property-info">

            <div class="property-top">

                <div>
                    <h3>
                        <?= htmlspecialchars($property['property_name']) ?>
                    </h3>

                    <p>
                        <i class="fa-solid fa-location-dot"></i>
                        <?= htmlspecialchars($property['location']) ?>
                    </p>
                </div>

                <span class="status approved">
                    <?= htmlspecialchars(ucfirst($property['status'])) ?>
                </span>

            </div>


            <div class="property-details">

                <div>
                    <span>Monthly Price</span>
                    <strong>
                        ₱<?= number_format((float)$property['price'], 2) ?>
                    </strong>
                </div>

                <div>
                    <span>Total Rooms</span>
                    <strong>
                        <?= (int)$property['total_rooms'] ?>
                    </strong>
                </div>

                <div>
                    <span>Available</span>
                    <strong>
                        <?= (int)$property['available_rooms'] ?>
                    </strong>
                </div>

            </div>


            <!-- ACTIONS -->
            <div class="property-actions">

                <button
                    class="secondary-btn"
                    onclick="editProperty(<?= (int)$property['id'] ?>)">

                    <i class="fa-solid fa-pen"></i>
                    Edit

                </button>


                <button
                    class="primary-btn"
                    onclick="showScreen('rooms')">

                    <i class="fa-solid fa-door-open"></i>
                    Rooms

                </button>

            </div>

        </div>

    </div>
<?php endwhile; ?>

<?php else: ?>

    <p>No properties found.</p>

<?php endif; ?>
    </main>

</section>


<!-- =========================
     ROOMS
========================= -->

<section id="rooms" class="screen">

    <header class="page-header">

        <button class="back-button"
                onclick="showScreen('dashboard')">

            <i class="fa-solid fa-arrow-left"></i>
            Back

        </button>

        <h1>Manage Rooms</h1>

        <p>Update room availability and pricing</p>

    </header>


    <main class="content">

      <div class="room-property-header">

    <?php if ($roomsPropertyResult && $roomsPropertyResult->num_rows > 0): ?>

        <?php
        $roomsProperty = $roomsPropertyResult->fetch_assoc();

        $property_id = (int)$roomsProperty['id'];

        $roomCountResult = $conn->query("
            SELECT COUNT(*) AS total
            FROM rooms
            WHERE property_id = $property_id
        ");

        $roomCount = 0;

        if ($roomCountResult) {
            $roomCount = (int)$roomCountResult->fetch_assoc()['total'];
        }
        ?>

        <div>
            <h2>
                <?= htmlspecialchars($roomsProperty['property_name']) ?>
            </h2>

            <p>
                <?= htmlspecialchars($roomsProperty['location']) ?>, Iloilo City
            </p>
        </div>

        <span>
            <?= $roomCount ?> Rooms
        </span>

    <?php else: ?>

        <div>
            <h2>No Property Found</h2>
            <p>You don't have any properties yet.</p>
        </div>

        <span>0 Rooms</span>

    <?php endif; ?>

</div>

       <?php

$roomsQuery = "
    SELECT id, room_number, price, availability
    FROM rooms
    WHERE property_id = $property_id
    ORDER BY id ASC
";

$roomsResult = $conn->query($roomsQuery);

?>

<div class="room-list">

    <?php if ($roomsResult && $roomsResult->num_rows > 0): ?>

        <?php while ($room = $roomsResult->fetch_assoc()): ?>

            <div class="room-card">

                <div class="room-number">

                    <i class="fa-solid fa-door-closed"></i>

                    <div>
                        <strong>
                            <?= htmlspecialchars($room['room_number']) ?>
                        </strong>
                    </div>

                </div>


                <div class="room-price">

                    <span>Monthly Price</span>

                    <strong>
                        ₱<?= number_format((float)$room['price'], 2) ?>
                    </strong>

                </div>


                <div class="room-status
                    <?= $room['availability'] === 'available'
                        ? 'available-status'
                        : 'occupied-status' ?>">

                    <i class="fa-solid fa-circle"></i>

                    <?= ucfirst(htmlspecialchars($room['availability'])) ?>

                </div>


                <button
    class="edit-room"
    onclick="editRoom(
        <?= (int)$room['id'] ?>,
        <?= htmlspecialchars(json_encode($room['room_number']), ENT_QUOTES, 'UTF-8') ?>,
        <?= (float)$room['price'] ?>,
        <?= htmlspecialchars(json_encode($room['availability']), ENT_QUOTES, 'UTF-8') ?>
    )">

    <i class="fa-solid fa-pen"></i>

</button>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p>No rooms found for this property.</p>

    <?php endif; ?>

</div>

    </main>

</section>


<!-- =========================
     MY TENANTS
========================= -->
<section id="tenants" class="screen">

    <header class="page-header">

        <button class="back-button"
                onclick="showScreen('dashboard')">

            <i class="fa-solid fa-arrow-left"></i>
            Back

        </button>

        <h1>My Tenants</h1>

        <p>View and manage your current tenants</p>

    </header>


    <main class="content">

        <!-- Tenant Search -->
        <div class="tenant-search">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                id="tenantSearch"
                placeholder="Search tenants..."
                onkeyup="searchTenants()">

        </div>


        <!-- GET TENANTS -->
        <?php

        $tenantQuery = "
            SELECT
                b.id AS booking_id,
                b.renter_id,
                b.property_id,
                b.room_number,
                b.occupants,
                b.move_in_date,
                b.payment_method,
                b.amount,
                b.status,
                b.payment_status,

                u.fullname,
                u.email,
                u.mobile,

                p.property_name

            FROM bookings b

            INNER JOIN users u
                ON b.renter_id = u.id

            INNER JOIN properties p
                ON b.property_id = p.id

            WHERE b.owner_id = $owner_id

            ORDER BY b.id DESC
        ";

        $tenantResult = $conn->query($tenantQuery);

        if (!$tenantResult) {
            echo "<p style='color:red;'>Tenant Query Error: "
                . htmlspecialchars($conn->error)
                . "</p>";
        }

        ?>


        <!-- DISPLAY TENANTS -->
        <?php if ($tenantResult && $tenantResult->num_rows > 0): ?>

            <div class="tenants-list" id="tenantsList">

                <?php while ($tenant = $tenantResult->fetch_assoc()): ?>

                    <div class="tenant-card">

                        <!-- Avatar -->
                        <div class="tenant-avatar">

                            <i class="fa-solid fa-user"></i>

                        </div>


                        <!-- Tenant Information -->
                        <div class="tenant-info">

                            <div class="tenant-main">

                                <div>

                                    <h3>
                                        <?= htmlspecialchars($tenant['fullname']) ?>
                                    </h3>

                                    <p>

                                        <i class="fa-solid fa-house"></i>

                                        <?= htmlspecialchars($tenant['property_name']) ?>

                                        <?php if (!empty($tenant['room_number'])): ?>

                                            · Room
                                            <?= htmlspecialchars($tenant['room_number']) ?>

                                        <?php endif; ?>

                                    </p>

                                </div>

                                <?php if ($tenant['payment_status'] === 'paid'): ?>

    <span class="tenant-payment-status paid">
        Paid
    </span>

<?php else: ?>

    <span class="tenant-payment-status pending">
        Pending
    </span>

<?php endif; ?>


                                <!-- Booking Status -->
                                <?php
                                $status = strtolower($tenant['status']);
                                ?>

                                <span class="tenant-status <?= htmlspecialchars($status) ?>">

                                    <?= ucfirst(htmlspecialchars($status)) ?>

                                </span>

                            </div>


                            <!-- Tenant Details -->
                            <div class="tenant-details">

                                <div>

                                    <span>Email</span>

                                    <strong>
                                        <?= htmlspecialchars($tenant['email']) ?>
                                    </strong>

                                </div>


                                <div>

                                    <span>Mobile</span>

                                    <strong>
                                        <?= htmlspecialchars($tenant['mobile']) ?>
                                    </strong>

                                </div>


                                <div>

                                    <span>Move-in Date</span>

                                    <strong>
                                        <?= date(
                                            'M d, Y',
                                            strtotime($tenant['move_in_date'])
                                        ) ?>
                                    </strong>

                                </div>


                                <div>

                                    <span>Amount</span>

                                    <strong>
                                        ₱<?= number_format($tenant['amount'], 2) ?>
                                    </strong>

                                </div>

                            </div>


                            <!-- Actions -->
                            <div class="tenant-actions">

                                <button
                                    class="secondary-btn"
                                    onclick="viewTenant(<?= (int)$tenant['booking_id'] ?>)">

                                    <i class="fa-solid fa-eye"></i>
                                    View

                                </button>


                                <button
                                    class="primary-btn"
                                    onclick="messageTenant(<?= (int)$tenant['renter_id'] ?>)">

                                    <i class="fa-solid fa-message"></i>
                                    Message

                                </button>

                            </div>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>


        <?php else: ?>

            <div class="empty-tenants">

                <i class="fa-solid fa-users"></i>

                <h3>No Tenants Yet</h3>

                <p>
                    Renters who book your properties will appear here.
                </p>

            </div>

        <?php endif; ?>

    </main>

</section>

        <!--BOOKING REQUEST-->

<section id="bookingrequest" class="screen">

    <header class="page-header">

        <button class="back-button"
                onclick="showScreen('dashboard')">

            <i class="fa-solid fa-arrow-left"></i>
            Back

        </button>

        <h1>Booking Requests</h1>

        <p>Review booking requests from renters</p>

    </header>


    <main class="content">

        <?php

        $bookingRequestQuery = "
            SELECT
                b.id AS booking_id,
                b.renter_id,
                b.property_id,
                b.room_number,
                b.occupants,
                b.move_in_date,
                b.payment_method,
                b.amount,
                b.status,

                u.fullname,
                u.email,
                u.mobile,

                p.property_name,
                p.location

            FROM bookings b

            INNER JOIN users u
                ON b.renter_id = u.id

            INNER JOIN properties p
                ON b.property_id = p.id

            WHERE b.owner_id = $owner_id
              AND b.status = 'pending'

            ORDER BY b.id DESC
        ";

        $bookingRequestResult =
            $conn->query($bookingRequestQuery);

        ?>


        <?php if ($bookingRequestResult && $bookingRequestResult->num_rows > 0): ?>

            <div class="booking-request-list">

                <?php while ($booking = $bookingRequestResult->fetch_assoc()): ?>

                    <div class="booking-request-card">

                        <!-- HEADER -->

                        <div class="booking-header">

                            <div class="booking-avatar">
                                <i class="fa-solid fa-user"></i>
                            </div>

                            <div class="booking-renter">

                                <h3>
                                    <?= htmlspecialchars($booking['fullname']) ?>
                                </h3>

                                <p>
                                    <?= htmlspecialchars($booking['email']) ?>
                                </p>

                            </div>

                            <span class="booking-status pending">
                                Pending
                            </span>

                        </div>


                        <!-- PROPERTY -->

                        <div class="booking-property">

                            <strong>
                                <?= htmlspecialchars($booking['property_name']) ?>
                            </strong>

                            <p>
                                <i class="fa-solid fa-location-dot"></i>

                                <?= htmlspecialchars($booking['location']) ?>
                            </p>

                        </div>


                        <!-- BOOKING DETAILS -->

                        <div class="booking-details">

                            <div>
                                <span>Room</span>

                                <strong>
                                    <?= htmlspecialchars($booking['room_number']) ?>
                                </strong>
                            </div>


                            <div>
                                <span>Occupants</span>

                                <strong>
                                    <?= htmlspecialchars($booking['occupants']) ?>
                                </strong>
                            </div>


                            <div>
                                <span>Move-in Date</span>

                                <strong>
                                    <?= date(
                                        'M d, Y',
                                        strtotime($booking['move_in_date'])
                                    ) ?>
                                </strong>
                            </div>


                            <div>
                                <span>Amount</span>

                                <strong>
                                    ₱<?= number_format(
                                        $booking['amount'],
                                        2
                                    ) ?>
                                </strong>
                            </div>

                        </div>


                        <!-- PAYMENT -->

                        <div class="booking-payment">

                            <span>Payment Method</span>

                            <strong>
                                <?= htmlspecialchars(
                                    $booking['payment_method']
                                ) ?>
                            </strong>

                        </div>


                        <!-- ACTIONS -->

                        <div class="booking-actions">

                            <button
                                type="button"
                                class="reject-btn"
                                onclick="rejectBooking(<?= (int)$booking['booking_id'] ?>)">

                                <i class="fa-solid fa-xmark"></i>

                                Reject

                            </button>


                            <button
                                type="button"
                                class="approve-btn"
                                onclick="approveBooking(<?= (int)$booking['booking_id'] ?>)">

                                <i class="fa-solid fa-check"></i>

                                Approve

                            </button>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>


        <?php else: ?>

            <div class="empty-booking">

                <i class="fa-solid fa-calendar-check"></i>

                <h3>No Booking Requests</h3>

                <p>
                    New booking requests from renters will appear here.
                </p>

            </div>

        <?php endif; ?>

    </main>

</section>



        <!--TRANSACTION-->


<section id="transactions" class="screen">

    <header class="page-header">

        <button class="back-button"
                onclick="showScreen('dashboard')">

            <i class="fa-solid fa-arrow-left"></i>
            Back

        </button>

        <h1>Transactions</h1>

        <p>View payments and transaction history</p>

    </header>


    <main class="content">

        <?php

        $transactionQuery = "
            SELECT
                b.id AS booking_id,
                b.renter_id,
                b.property_id,
                b.room_number,
                b.move_in_date,
                b.payment_method,
                b.amount,
                b.status,

                u.fullname,

                p.property_name

            FROM bookings b

            INNER JOIN users u
                ON b.renter_id = u.id

            INNER JOIN properties p
                ON b.property_id = p.id

            WHERE b.owner_id = $owner_id

            ORDER BY b.id DESC
        ";

        $transactionResult =
            $conn->query($transactionQuery);

        ?>


        <?php if ($transactionResult && $transactionResult->num_rows > 0): ?>

            <div class="transactions-list">

                <?php while ($transaction = $transactionResult->fetch_assoc()): ?>

                    <?php
                    $status = strtolower($transaction['status']);
                    ?>

                    <div class="transaction-card">

                        <!-- ICON -->

                        <div class="transaction-icon">

                            <i class="fa-solid fa-money-bill-transfer"></i>

                        </div>


                        <!-- INFORMATION -->

                        <div class="transaction-info">

                            <div class="transaction-main">

                                <div>

                                    <h3>
                                        <?= htmlspecialchars(
                                            $transaction['fullname']
                                        ) ?>
                                    </h3>

                                    <p>
                                        <?= htmlspecialchars(
                                            $transaction['property_name']
                                        ) ?>

                                        <?php if (!empty($transaction['room_number'])): ?>

                                            ·
                                            <?= htmlspecialchars(
                                                $transaction['room_number']
                                            ) ?>

                                        <?php endif; ?>

                                    </p>

                                </div>


                                <!-- STATUS -->

                                <span class="transaction-status <?= htmlspecialchars($status) ?>">

                                    <?= ucfirst(
                                        htmlspecialchars($status)
                                    ) ?>

                                </span>

                            </div>


                            <!-- TRANSACTION DETAILS -->

                            <div class="transaction-details">

                                <div>

                                    <span>Amount</span>

                                    <strong>
                                        ₱<?= number_format(
                                            $transaction['amount'],
                                            2
                                        ) ?>
                                    </strong>

                                </div>


                                <div>

                                    <span>Payment Method</span>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $transaction['payment_method']
                                        ) ?>
                                    </strong>

                                </div>


                                <div>

                                    <span>Move-in Date</span>

                                    <strong>
                                        <?= date(
                                            'M d, Y',
                                            strtotime(
                                                $transaction['move_in_date']
                                            )
                                        ) ?>
                                    </strong>

                                </div>


                                <div>

                                    <span>Transaction ID</span>

                                    <strong>
                                        #<?= (int)$transaction['booking_id'] ?>
                                    </strong>

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>


        <?php else: ?>

            <div class="empty-transactions">

                <i class="fa-solid fa-receipt"></i>

                <h3>No Transactions Yet</h3>

                <p>
                    Payments and transactions will appear here.
                </p>

            </div>

        <?php endif; ?>

    </main>

</section>      


        <!--MAINTENANCE REQUESTS-->

<section id="maintenance" class="screen">

    <header class="page-header">

        <button class="back-button" onclick="showScreen('dashboard')">
            <i class="fa-solid fa-arrow-left"></i>
            Back
        </button>

        <h1>Maintenance Requests</h1>
        <p>Manage tenant maintenance requests</p>

    </header>


    <?php

    $maintenanceQuery = "
        SELECT
            m.id AS request_id,
            m.room_number,
            m.issue,
            m.status,
            m.created_at,
            u.fullname

        FROM maintenance_requests m

        INNER JOIN users u
            ON m.renter_id = u.id

        WHERE m.owner_id = $owner_id

        ORDER BY m.id DESC
    ";

    $maintenanceResult = $conn->query($maintenanceQuery);

    ?>


    <div class="maintenance-container">


        <!-- TABS -->

        <div class="maintenance-tabs">

            <button
                class="maintenance-tab active"
                onclick="filterMaintenance('pending', this)">
                Pending
            </button>

            <button
                class="maintenance-tab"
                onclick="filterMaintenance('in_progress', this)">
                In Progress
            </button>

            <button
                class="maintenance-tab"
                onclick="filterMaintenance('completed', this)">
                Completed
            </button>

        </div>


        <!-- REQUESTS -->

        <div class="maintenance-request-list">

            <?php if ($maintenanceResult && $maintenanceResult->num_rows > 0): ?>

                <?php while ($request = $maintenanceResult->fetch_assoc()): ?>

                    <div
                        class="maintenance-request-card"
                        data-status="<?= htmlspecialchars($request['status']) ?>"
                    >

                        <div class="maintenance-request-info">

                            <h3>
                                <?= htmlspecialchars($request['issue']) ?>
                            </h3>

                            <p class="maintenance-tenant">

                                <?= htmlspecialchars($request['fullname']) ?>

                                <span>•</span>

                                Room <?= htmlspecialchars($request['room_number']) ?>

                            </p>

                            <p class="maintenance-date">

                                <?= date(
                                    'M d, Y',
                                    strtotime($request['created_at'])
                                ) ?>

                            </p>

                        </div>


                        <span class="maintenance-status <?= htmlspecialchars($request['status']) ?>">

                            <?php

                            if ($request['status'] === 'in_progress') {

                                echo 'In Progress';

                            } else {

                                echo ucfirst($request['status']);

                            }

                            ?>

                        </span>

                    </div>

                <?php endwhile; ?>


            <?php else: ?>

                <div class="empty-maintenance">

                    <i class="fa-solid fa-screwdriver-wrench"></i>

                    <h3>No Maintenance Requests</h3>

                    <p>
                        Maintenance requests from your tenants
                        will appear here.
                    </p>

                </div>

            <?php endif; ?>

        </div>


        <!-- VIEW ALL -->

        <button
            class="view-all-maintenance"
            onclick="showScreen('allmaintenance')">

            View All Requests

            <i class="fa-solid fa-chevron-right"></i>

        </button>

    </div>

</section>

<!-- =========================
     EDIT PROPERTY
========================= -->

<section id="editProperty" class="screen">

    <header class="page-header">

        <button class="back-button"
                onclick="showScreen('properties')">

            <i class="fa-solid fa-arrow-left"></i>
            Back

        </button>

        <h1>Edit Property</h1>

        <p>Update your property information</p>

    </header>


    <main class="content">

        <form class="edit-form"
              onsubmit="saveProperty(event)">

              <input type="hidden" id="propertyId">


            <div class="form-group">

                <label>Property Name</label>

                <input
                    type="text"
                    id="propertyName"
                    required>

            </div>


            <div class="form-group">

                <label>Location</label>

                <input
                    type="text"
                    id="propertyLocation"
                    required>

            </div>


            <div class="form-group">

                <label>Monthly Price</label>

                <div class="price-input">

                    <span>₱</span>

                    <input
                        type="number"
                        id="propertyPrice"
                        required>

                </div>
            </div>

            <div class="form-group">
    <label>Description</label>
    <textarea
        id="propertyDescription"
        rows="5"></textarea>
</div>

            <!-- AMENITIES -->

            <div class="form-group">

                <label>Amenities</label>

                <div class="amenity-options">

                    <label class="check-option">

                        <input
                            type="checkbox"
                            checked>

                        <span>
                            <i class="fa-solid fa-wifi"></i>
                            Wi-Fi
                        </span>

                    </label>


                    <label class="check-option">

                        <input
                            type="checkbox"
                            checked>

                        <span>
                            <i class="fa-solid fa-snowflake"></i>
                            Air Conditioning
                        </span>

                    </label>


                    <label class="check-option">

                        <input
                            type="checkbox">

                        <span>
                            <i class="fa-solid fa-car"></i>
                            Parking
                        </span>

                    </label>


                    <label class="check-option">

                        <input
                            type="checkbox">

                        <span>
                            <i class="fa-solid fa-shirt"></i>
                            Laundry
                        </span>

                    </label>


                    <label class="check-option">

                        <input
                            type="checkbox">

                        <span>
                            <i class="fa-solid fa-kitchen-set"></i>
                            Kitchen
                        </span>

                    </label>


                    <label class="check-option">

                        <input
                            type="checkbox">

                        <span>
                            <i class="fa-solid fa-bath"></i>
                            Private Bathroom
                        </span>

                    </label>

                </div>

            </div>


            <button
                type="submit"
                class="save-btn">

                <i class="fa-solid fa-check"></i>
                Save Changes

            </button>

        </form>

    </main>

</section>


<!-- =========================
     EDIT ROOM MODAL
========================= -->

<div id="roomModal" class="modal">

    <div class="modal-box">

        <button class="close-modal"
                onclick="closeRoomModal()">

            <i class="fa-solid fa-xmark"></i>

        </button>


        <h2>Edit Room</h2>

        <p>Update room information</p>


        <div class="form-group">

            <label>Room Number</label>

            <input
                type="text"
                id="roomNumber">

        </div>


        <div class="form-group">

            <label>Monthly Price</label>

            <div class="price-input">

                <span>₱</span>

                <input
                    type="number"
                    id="roomPrice">

            </div>

        </div>


        <div class="form-group">

            <label>Availability</label>

            <select id="roomAvailability">

                <option value="available">
                    Available
                </option>

                <option value="occupied">
                    Occupied
                </option>

            </select>

        </div>


        <button
            class="save-btn"
            onclick="saveRoom()">

            <i class="fa-solid fa-check"></i>
            Save Room

        </button>

    </div>

</div>


<!-- =========================
     ADD PROPERTY MODAL
========================= -->

<div id="propertyModal" class="modal">

    <div class="modal-box">

        <button
            type="button"
            class="close-modal"
            onclick="closePropertyModal()">

            <i class="fa-solid fa-xmark"></i>

        </button>


        <h2>Add Property</h2>

        <p>Add a new rental accommodation</p>


        <form action="add_property.php" method="POST">


            <!-- PROPERTY CODE -->

            <div class="form-group">

                <label>Property Code</label>

                <input
                    type="text"
                    name="property_code"
                    placeholder="Example: sunrise01"
                    required>

                <small>
                    A unique code for this property.
                </small>

            </div>


            <!-- PROPERTY NAME -->

            <div class="form-group">

                <label>Property Name</label>

                <input
                    type="text"
                    name="property_name"
                    placeholder="Example: Sunrise Residences"
                    required>

            </div>


            <!-- LOCATION -->

            <div class="form-group">

                <label>Location</label>

                <select
                    name="location"
                    required>

                    <option value="">
                        Select location
                    </option>

                    <option value="Arevalo">
                        Arevalo
                    </option>

                    <option value="City Proper">
                        City Proper
                    </option>

                    <option value="Jaro">
                        Jaro
                    </option>

                    <option value="La Paz">
                        La Paz
                    </option>

                    <option value="Lapuz">
                        Lapuz
                    </option>

                    <option value="Mandurriao">
                        Mandurriao
                    </option>

                    <option value="Molo">
                        Molo
                    </option>

                </select>

            </div>


            <!-- COMPLETE ADDRESS -->

            <div class="form-group">

                <label>Complete Address</label>

                <input
                    type="text"
                    name="address"
                    placeholder="Example: 384 A. Lopez Jaena Street, Jaro, Iloilo City"
                    required>

            </div>


            <!-- PROPERTY TYPE -->

            <div class="form-group">

                <label>Property Type</label>

                <select
                    name="property_type"
                    required>

                    <option value="">
                        Select property type
                    </option>

                    <option value="Boarding House">
                        Boarding House
                    </option>

                    <option value="Apartment">
                        Apartment
                    </option>

                    <option value="Dormitory">
                        Dormitory
                    </option>

                    <option value="Bed Space">
                        Bed Space
                    </option>

                    <option value="Transient">
                        Transient
                    </option>

                    <option value="Boarding House/Transient">
                        Boarding House / Transient
                    </option>

                    <option value="Apartment/Transient">
                        Apartment / Transient
                    </option>

                </select>

            </div>


            <!-- MINIMUM PRICE -->

            <div class="form-group">

                <label>Minimum Price</label>

                <div class="price-input">

                    <span>₱</span>

                    <input
                        type="number"
                        name="price"
                        min="0"
                        step="0.01"
                        placeholder="4500"
                        required>

                </div>

            </div>


            <!-- MAXIMUM PRICE -->

            <div class="form-group">

                <label>
                    Maximum Price
                    <small>(optional)</small>
                </label>

                <div class="price-input">

                    <span>₱</span>

                    <input
                        type="number"
                        name="price_max"
                        min="0"
                        step="0.01"
                        placeholder="8000">

                </div>

            </div>


            <!-- PRICE UNIT -->

            <div class="form-group">

                <label>Price Unit</label>

                <select
                    name="price_unit"
                    required>

                    <option value="month">
                        Per Month
                    </option>

                    <option value="day">
                        Per Day
                    </option>

                    <option value="night">
                        Per Night
                    </option>

                    <option value="day/month">
                        Day / Month
                    </option>

                </select>

            </div>


            <!-- AVAILABLE ROOMS -->

            <div class="form-group">

                <label>Available Rooms</label>

                <input
                    type="number"
                    name="available_rooms"
                    min="0"
                    value="1"
                    required>

            </div>


            <!-- IMAGE -->

            <div class="form-group">

                <label>Image Path</label>

                <input
                    type="text"
                    name="image"
                    placeholder="images/property.jpg"
                    required>

                <small>
                    Example: images/barridabh1.jpg
                </small>

            </div>


            <!-- SUBMIT -->

            <button
                type="submit"
                class="save-btn">

                <i class="fa-solid fa-check"></i>

                Add Property

            </button>


        </form>

    </div>

</div>


<!-- =========================
     BOTTOM NAV
========================= -->

<nav class="bottom-nav">

    <button
        class="nav-active"
        onclick="showScreen('dashboard')">

        <i class="fa-solid fa-chart-line"></i>

        <span>Dashboard</span>

    </button>


    <button
        onclick="showScreen('properties')">

        <i class="fa-solid fa-house"></i>

        <span>Properties</span>

    </button>


    <button
        onclick="showScreen('rooms')">

        <i class="fa-solid fa-door-open"></i>

        <span>Rooms</span>

    </button>


    <button>

        <i class="fa-solid fa-message"></i>

        <span>Messages</span>

    </button>


    <button>

        <i class="fa-solid fa-user"></i>

        <span>Profile</span>

    </button>

</nav>


<script src="owner.js"></script>

</body>

</html>