<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);



$amenityIcons = [
    'wi-fi' => 'wifi.png',
    'wifi' => 'wifi.png', 
    'cctv protected' => 'cctv.png',
    'personal locker' => 'locker.png',
    'cleaning service' => 'cleaningservice.png',
    '300 mbps wi-fi' => 'wifi.png',
    'tv' => 'Tv.png',
    'double deck bed' => 'bed_pillows.png',
    'electric fan' => 'electricfan.png',
    'mattress ready' => 'matt.png',
    'sofa' => 'Sofa.png',
    'bed/pillow' => 'bed_pillows.png',
    'submeter' => 'submeter.png',
    'furnature' => 'furnature.png',
    'basic furniture' => 'furnature.png',
    'air condition' => 'aircon.png',
    'aircondition' => 'aircon.png',
    'motorcycle parking only' => 'garage.png',
    'provision garage' => 'garage.png',
    'security' => 'security.png',
    'laundry' => 'laundry.png',
    'laundry area' => 'laundry.png',
    'dining area' => 'diningaria.png',
    'kitchen' => 'diningaria.png',
    'kitchen area' => 'diningaria.png'
];

session_start();

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['user_type']) ||
    $_SESSION['user_type'] !== 'renter'
) {
    header("Location: log_in.html");
    exit;
}

require_once 'db.php';



// Get the property ID from the URL
$propertyId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$propertyId || $propertyId < 1) {
    exit("Invalid property ID.");
}

// Fetch property information
$stmt = $conn->prepare("
    SELECT *
    FROM properties
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param("i", $propertyId);
$stmt->execute();

$result = $stmt->get_result();
$property = $result->fetch_assoc();
$stmt->close();

if (!$property) {
    exit("Property not found.");
}

// Fetch property owner
$stmt = $conn->prepare("
    SELECT
        u.fullname AS owner_name,
        u.email AS owner_email
    FROM properties p
    JOIN users u ON p.owner_id = u.id
    WHERE p.id = ?
    LIMIT 1
");

$stmt->bind_param("i", $propertyId);
$stmt->execute();

$owner = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch gallery images
$stmt = $conn->prepare("
    SELECT image_path
    FROM property_gallery
    WHERE property_id = ?
    ORDER BY sort_order ASC, id ASC
");
$stmt->bind_param("i", $propertyId);
$stmt->execute();

$galleryResult = $stmt->get_result();
$gallery = [];

while ($row = $galleryResult->fetch_assoc()) {
    $gallery[] = $row['image_path'];
}
$stmt->close();


if (empty($gallery)) {
    $gallery[] = !empty($property['image'])
        ? $property['image']
        : 'images/property-placeholder.png';
}

// Fetch amenities
$stmt = $conn->prepare("
    SELECT amenity_name
    FROM property_amenities
    WHERE property_id = ?
    ORDER BY id ASC
");
$stmt->bind_param("i", $propertyId);
$stmt->execute();

$amenitiesResult = $stmt->get_result();
$amenities = [];

while ($row = $amenitiesResult->fetch_assoc()) {
    $amenities[] = $row['amenity_name'];
}
$stmt->close();

// Fetch house rules
$stmt = $conn->prepare("
    SELECT rule_text
    FROM property_rules
    WHERE property_id = ?
    ORDER BY id ASC
");
$stmt->bind_param("i", $propertyId);
$stmt->execute();

$rulesResult = $stmt->get_result();
$rules = [];

while ($row = $rulesResult->fetch_assoc()) {
    $rules[] = $row['rule_text'];
}
$stmt->close();



// Fetch reviews and reviewer names
$stmt = $conn->prepare("
    SELECT
        pr.rating,
        pr.review_text,
        pr.created_at,
        u.fullname
    FROM property_reviews pr
    INNER JOIN users u ON u.id = pr.renter_id
    WHERE pr.property_id = ?
    ORDER BY pr.created_at DESC
");
$stmt->bind_param("i", $propertyId);
$stmt->execute();

$reviewsResult = $stmt->get_result();
$reviews = [];

while ($row = $reviewsResult->fetch_assoc()) {
    $reviews[] = $row;
}
$stmt->close();

// Calculate rating from actual reviews
$reviewCount = count($reviews);
$totalRating = 0;

foreach ($reviews as $review) {
    $totalRating += (int) $review['rating'];
}

$averageRating = $reviewCount > 0
    ? round($totalRating / $reviewCount, 1)
    : 0;

// Escape text before displaying it in HTML
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Format price
$price = (float) ($property['price'] ?? 0);
$priceMax = isset($property['price_max'])
    && $property['price_max'] !== null
    && $property['price_max'] !== ''
    ? (float) $property['price_max']
    : null;

$priceLabel = '₱' . number_format($price);

if ($priceMax !== null && $priceMax > $price) {
    $priceLabel .= '–₱' . number_format($priceMax);
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renter</title>
   <link rel="stylesheet" href="style.css">
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>
<body>




  <section id="property1" class="screen active">
    <section class="property-shell">
     <header class="topbar1">
    <button class="back-btn1" onclick="history.back()">
        <i class="fa-solid fa-chevron-left"></i> </button> 

    <button
    type="button"
    class="heart"
    onclick="toggleHeart(event, this, <?= (int) $property['id'] ?>)"
>
    ♡
</button>
</header>

      <section class="gallery" aria-label="Property photos">
       <div class="slides">
    <?php foreach ($gallery as $index => $photo): ?>
        <img
            src="<?= e($photo) ?>"
            alt="<?= e($property['property_name']) ?> photo <?= $index + 1 ?>"
            class="slide <?= $index === 0 ? 'active' : '' ?>"
        >
    <?php endforeach; ?>
</div>

<div class="photo-count">
    <span class="photoNumber">1</span> / <?= count($gallery) ?>
</div>

<div class="dots" aria-label="Photo navigation">
    <?php foreach ($gallery as $index => $photo): ?>
        <button
            class="dot <?= $index === 0 ? 'active' : '' ?>"
            data-index="<?= $index ?>"
            aria-label="Show photo <?= $index + 1 ?>"
        ></button>
    <?php endforeach; ?>
</div>
        
      </section>

      <section class="content">
       <h1><?= e($property['property_name']) ?></h1>

<p class="location">
    ⌖ &nbsp; <?= e($property['address'] ?? $property['location'] ?? '') ?>
</p>

<div class="rating">
    <span>
        <?php
        $fullStars = (int) floor($averageRating);
        echo str_repeat('★ ', $fullStars);
        echo str_repeat('☆ ', 5 - $fullStars);
        ?>
    </span>

    <b><?= number_format($averageRating, 1) ?></b>
    <em>(<?= $reviewCount ?>)</em>
</div>

<div class="price">
    <strong><?= e($priceLabel) ?></strong>
    <small>/ <?= e($property['price_unit'] ?? 'month') ?></small>
</div>

        <div class="tags">
         <span>🏠 &nbsp; <?= e($property['property_type'] ?? 'Rental') ?></span>
          <span>🛏️ &nbsp; Single Room</span>
          <span>👤 &nbsp; 1–2 Persons</span>
          <span>📅 &nbsp; Min. 1 Month</span>
        </div>

        <div class="availability">
    <div class="check">✓</div>
    <div>
        <strong><?= (int)$property['available_rooms'] > 0
            ? 'Available Now'
            : 'Fully Occupied' ?></strong>
        <p>
            <?= (int)$property['available_rooms'] ?>
            rooms available · Move-in ready
        </p>
    </div>
    <div class="availability-house">🏠</div>
</div>

      <section class="card amenities">
    <h2>AMENITIES</h2>

    <div class="amenity-list">
    <?php foreach ($amenities as $amenity): ?>
        <?php
            $name = strtolower(trim($amenity));

            // Find the matching icon filename
            $iconFile = $amenityIcons[$name] ?? 'default.png';
        ?>

        <div class="amenity1">
            <img
                src="images/<?= e($iconFile) ?>"
                alt=""
                class="amenity-icon"
            >
            <label><?= e($amenity) ?></label>
        </div>
    <?php endforeach; ?>

    <?php if (empty($amenities)): ?>
        <p>No amenities listed yet.</p>
    <?php endif; ?>
</div>
</section>

<section class="card rules">
    <h2>HOUSE RULES</h2>

    <ul>
        <?php foreach ($rules as $rule): ?>
            <li><?= e($rule) ?></li>
        <?php endforeach; ?>
    </ul>

    <?php if (empty($rules)): ?>
        <p>No house rules listed yet.</p>
    <?php endif; ?>
</section>

        <section class="card owner">
          <h2>PROPERTY OWNER</h2>
          <div class="owner-row">
            <img src="images/pro.png" alt="Property owner">
            <div class="owner-info">
              <?= e($owner['owner_name'] ?? 'Property Owner') ?>
              <p>Member since 2021 · Responds in &lt;2 hours</p>
            </div>
          </div>
          <button class="message-btn" onclick="messageLandlord()">▢ &nbsp; Message landlord</button>
        </section>

        <section class="card reviews">
    <div class="reviews-heading">
        <h2>REVIEWS</h2>

        <div>
            <span class="big-star">★</span>
            <b><?= number_format($averageRating, 1) ?></b>
            <em>(<?= $reviewCount ?> reviews)</em>
        </div>
    </div>

    <?php if (!empty($reviews)): ?>
        <?php foreach ($reviews as $review): ?>
            <?php
            $name = $review['fullname'] ?? 'Renter';
            $initials = '';

            foreach (preg_split('/\s+/', trim($name)) as $part) {
                if ($part !== '') {
                    $initials .= strtoupper(substr($part, 0, 1));
                }
            }

            $initials = substr($initials, 0, 2);
            $rating = (int) $review['rating'];
            ?>

            <article class="review">
                <div class="review-avatar">
                    <?= e($initials) ?>
                </div>

                <div>
                    <h3><?= e($name) ?></h3>

                    <div class="review-meta">
                        <span>
                            <?= str_repeat('★', $rating) ?>
                            <?= str_repeat('☆', 5 - $rating) ?>
                        </span>

                        <?= e(date(
                            'F Y',
                            strtotime($review['created_at'])
                        )) ?>
                    </div>

                    <p><?= e($review['review_text'] ?? '') ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No reviews yet.</p>
    <?php endif; ?>
</section>

<?php
$mapAddress = trim(
    ($property['address'] ?? '') . ', ' .
    ($property['location'] ?? '') . ', Iloilo City'
);

$mapUrl = 'https://www.google.com/maps/search/?api=1&query='
    . urlencode($mapAddress);

$mapEmbedUrl = 'https://maps.google.com/maps?q='
    . urlencode($mapAddress)
    . '&output=embed';
?>

<section class="map-card">
    <iframe
        src="<?= e($mapEmbedUrl) ?>"
        width="100%"
        height="250"
        style="border:0;"
        loading="lazy"
        allowfullscreen
        title="Property location map">
    </iframe>

    <div class="map-label">
        <h2><?= e($property['location'] ?? 'Iloilo City') ?></h2>

        <p>
            <a href="<?= e($mapUrl) ?>"
               target="_blank"
               rel="noopener noreferrer">
                View on Google Maps
            </a>
        </p>
    </div>
</section>
      </section>

      <div class="reserve-bar">
    <button
        type="button"
        class="reserve-btn"
        onclick="openBookingScreen()"
        <?= (int)($property['available_rooms'] ?? 0) < 1
            ? 'disabled'
            : '' ?>
    >
        <?= (int)($property['available_rooms'] ?? 0) > 0
            ? 'Reserve Now'
            : 'Fully Occupied' ?>
    </button>
</div>

    </section>

    <div class="toast" id="toast"></div>

    
       </section>


        <!--SCREEN_____4 -->



      <section id="booking" class="screen">
        <header class="top-header_">

          <div class="title-row1_">
            <button class="icon-btn_" 
            onclick="history.back()">
              <i class="fa-solid fa-chevron-left"></i>
            </button>
            <div>
              <h1>Booking Request</h1>
              <p class="reviewss">Review your reservation details</p>
            </div>
          </div>
        </header>
        <div class="content booking-content">
         <article class="property-summary card">
  <div class="thumb-placeholder">
    <img id="bookingImage" src="images/madu-bh-InY1.jpg" alt="Property Image">
  </div>

  <div>
    <em>♢ Verified</em>
    <h2 id="bookingName">Iny Residences</h2>
    <p id="bookingLocation">⌖ Manduriao, Iloilo City</p>
    <span class="gold">★★★★★</span>
    <span id="bookingRating">4.0 (9)</span>
  </div>
</article>

 <!-- SELECTED ROOM -->

         <article class="card">
    <h3>SELECTED ROOM</h3>

    <div class="select-box" id="roomBox">
        <div>
            <b id="bookingRoom">Select a room</b>
            <span id="bookingOccupants">Choose an available room</span>
        </div>

        <i class="fa-solid fa-chevron-down"></i>
    </div>

    <div class="room-options" id="roomOptions">

        <!-- Rooms will be added here by JavaScript -->


    </div>
</article>
    

<!-- RESERVATION DETAILS -->
<article class="card reservation-details-card">
    <h3>RESERVATION DETAILS</h3>

    <div class="reservation-field">
        <label for="bookingMoveIn">
            <i class="fa-regular fa-calendar"></i>
            Move-in Date
        </label>

        <input
            type="date"
            id="bookingMoveIn"
            required
        >
    </div>

    <div class="reservation-field">
        <label for="bookingPersons">
            <i class="fa-solid fa-users"></i>
            Number of Persons
        </label>

        <select id="bookingPersons" required>
            <option value="1">1 person</option>
            <option value="2">2 persons</option>
            <option value="3">3 persons</option>
            <option value="4">4 persons</option>
            <option value="family">Family</option>
        </select>
    </div>
</article>

         <article class="card">
  <h3>RESERVATION SUMMARY</h3>

  <div class="summary">
    <span>
      Monthly Rent
      <b id="monthlyRent">₱4,500.00</b>
    </span>

    <span>
      Reservation Payment
      <b id="reservationPayment">₱4,500.00</b>
    </span>

    <span>
      Service Fee
      <i>To be disclosed</i>
    </span>

    <hr>

    <strong>
      Due Today
      <b class="gold" id="dueToday">₱4,500.00</b>
    </strong>
  </div>
</article>
          <article class="notice">
            <b>⏰ &nbsp; 48-Hour Response Window</b>
            <p>
              The landlord has 48 hours to confirm or decline your booking
              request. You will be notified via the app once a decision is made.
            </p>
          </article>



            <div class="booking-action">
          <button class="primary" onclick="showScreen('payment')">
            <i class="fa-solid fa-lock"></i> Continue to Payment
          </button>
        </div>
        </div>
      
      </section>

      <section id="payment" class="screen">
        <header class="top-header">
         
          <div class="title-row">
            <button class="icon-btn" onclick="showScreen('booking')">
              <i class="fa-solid fa-chevron-left"></i>
            </button>
            <div>
              <h1>Secure Payment</h1>
              <p>Step 2 of 2</p>
            </div>
          </div>
        </header>
        <div class="content">
          <article class="property-summary card">
  <div class="thumb-placeholder">
    <img id="paymentImage" src="images/madu-bh-InY2.jpg" alt="Property Image">
  </div>

  <div>
    <h2 id="paymentName">Iny Residences</h2>

    <p id="paymentDetails">
      Room A-12 (Single) · Manduriao, Iloilo City
    </p>

    <p>
      Reservation Payment
      <b class="gold" id="paymentPrice">₱4,500.00</b>
    </p>
  </div>
</article>
          <article class="card">
            <h3>PAYMENT METHOD</h3>

<div class="payment-method" data-method="Card">
    <span>
        💵 
        <b>
            Cash
            <small>Pay Here (Cash)</small>
        </b>
    </span>

    <i class="fa-regular fa-circle payment-select-icon"></i>
</div>
            <div class="payment-method selected-method" data-method="GCash">
  <span>
    <img src="images/gcash.png" alt="GCash" class="payment-logo">

    <b>
      GCash
      <small>Mobile payment via GCash</small>
    </b>
  </span>

  <i class="fa-solid fa-circle-dot payment-select-icon"></i>
</div>
           <div class="payment-method" data-method="Maya">
  <span>
    <img src="images/maya.png" alt="Maya" class="payment-logo">

    <b>
      Maya
      <small>Mobile payment via Maya</small>
    </b>
  </span>

  <i class="fa-regular fa-circle payment-select-icon"></i>
</div>
           <div class="payment-method" data-method="Online Banking">
    <span>
        🏦
        <b>
            Online Banking
            <small>BPI, BDO, UnionBank &amp; more</small>
        </b>
    </span>

    <i class="fa-regular fa-circle payment-select-icon"></i>
</div>

<div class="payment-method" data-method="Card">
    <span>
        💳
        <b>
            Debit / Credit Card
            <small>Visa, Mastercard accepted</small>
        </b>
    </span>

    <i class="fa-regular fa-circle payment-select-icon"></i>
</div>


            
             
        <div class="payment-action">
 <button class="gold-button" id="confirmPaymentButton" onclick="submitBooking()">
    <i class="fa-solid fa-lock"></i>
    Confirm Payment
</button>



          <p>
            By proceeding, you agree to Nestify's Terms of Service and Payment
            Policy.
          </p>
        </div>
      </section>

      <section id="success" class="screen">
        <header class="success-hero">
         
          <div class="check">✓</div>
          <h1>Booking Request Submitted!</h1>
          <p>Your reservation is pending landlord confirmation</p>
          <div class="waiting">
            <span></span> Waiting for Landlord Confirmation
          </div>
        </header>
        <div class="success-content">
<div class="image-placeholder success-image">
  <img id="successImage" src="images/madu-bh-InY3.jpg" alt="Property Image">
</div>

<article class="success-card">
  <h2>
    <span id="successName">Iny Residences</span>
    <em>♢ Verified</em>
  </h2>

  <p id="successLocation">⌖ Manduriao, Iloilo City</p>

  <div class="booking-grid">
    <div>
      <small>ROOM</small>
      <b id="successRoom">Room A-12 (Single)</b>
    </div>

    <div>
  <small>MOVE-IN DATE</small>
  <b id="successMoveInDate">September 1, 2026</b>
</div>
    <div>
      <small>RESERVATION PAID</small>
      <b id="successPrice">₱4,500.00</b>
    </div>

    <div>
      <small>PAYMENT METHOD</small>
      <b id="successPaymentMethod">GCash</b>
    </div>
  </div>
</article>
          <button class="primary" onclick="updateMyBooking();showScreen('myBooking')">
            View My Booking
          </button>
          <button class="secondary" onclick="showScreen('filters')">
            Back to Home
          </button>
        </div>
      </section>
    </main>

    <!--VIEW BOOKING-->



    <section id="myBooking" class="screen">

    <header class="top-header_2">
        <div class="title-row_2">
            <button class="icon-btn_2" onclick="showScreen('success')">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <div>
                <h1>My Booking</h1>
                <p>View your reservation details</p>
            </div>
        </div>
    </header>

    <div class="content booking-details-content">

        <article class="property-summary card">

            <div class="thumb-placeholder">
                <img id="myBookingImage"
                     src="images/madu-bh-InY3.jpg"
                     alt="Property Image">
            </div>

            <div>
                <em>♢ Verified</em>

                <h2 id="myBookingName">
                    Iny Residences
                </h2>

                <p id="myBookingLocation">
                    ⌖ Manduriao, Iloilo City
                </p>

                <span class="gold">★★★★★</span>
                <span id="myBookingRating">4.0 (9)</span>
            </div>

        </article>


        <article class="card">

            <h3>BOOKING DETAILS</h3>

            <div class="booking-info">

                <div>
                    <small>BOOKING STATUS</small>
                    <b class="booking-status">
                        Pending Confirmation
                    </b>
                </div>

                <div>
                    <small>ROOM</small>
                    <b id="myBookingRoom">
                        Room A-12 (Single)
                    </b>
                </div>

                <div>
                    <small>OCCUPANTS</small>
                    <b id="myBookingOccupants">
                        1 occupant · 12 sqm
                    </b>
                </div>

                <div>
                    <small>MOVE-IN DATE</small>
                    <b id="myBookingDate">
                        September 1, 2026
                    </b>
                </div>

            </div>

        </article>


        <article class="card">

            <h3>PAYMENT DETAILS</h3>

            <div class="booking-info">

                <div>
                    <small>MONTHLY RENT</small>
                    <b id="myBookingRent">
                        ₱4,500.00
                    </b>
                </div>

                <div>
                    <small>RESERVATION PAYMENT</small>
                    <b id="myBookingPayment">
                        ₱4,500.00
                    </b>
                </div>

                <div>
                    <small>PAYMENT METHOD</small>
                    <b id="myBookingMethod">
                        GCash
                    </b>
                </div>

                <div>
                    <small>AMOUNT PAID</small>
                    <b class="gold" id="myBookingPaid">
                        ₱4,500.00
                    </b>
                </div>

            </div>

        </article>


        <article class="notice">

            <b>
                ⏰ 48-Hour Response Window
            </b>

            <p>
                Your booking request has been submitted.
                The landlord has 48 hours to confirm or decline
                your reservation.
            </p>

        </article>

    </div>

</section>


     <script>
window.currentProperty = <?= json_encode([
    'id' => (int) $property['id'],
    'name' => $property['property_name'],
    'location' => $property['address'] ?? $property['location'] ?? '',
    'price' => (float) ($property['price'] ?? 0),
    'image' => $gallery[0] ?? $property['image'] ?? 'images/property-placeholder.png',
    'rating' => number_format($averageRating, 1) . ' (' . $reviewCount . ')',
    'available_rooms' => (int) ($property['available_rooms'] ?? 0)
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>

<script src="script.js"></script>
    
</body>
</html>