<?php
session_start();

require_once 'db.php'; // Creates the $conn database connection

if (!isset($_SESSION['user_id'])) {
    header("Location: log_in.html");
    exit;
}

if (!isset($_SESSION['user_type']) ||
    $_SESSION['user_type'] !== 'renter') {
    header("Location: log_in.html");
    exit;
}

$fullname = $_SESSION['fullname'] ?? '';
$email = $_SESSION['email'] ?? '';


function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$sql = "SELECT
            id,
            property_code,
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
            verified
        FROM properties
        WHERE status IN ('active', 'approved')
          AND available_rooms >= 1
          AND total_rooms >=1
        ORDER BY created_at DESC";
        
$propertiesResult = $conn->query($sql);

if (!$propertiesResult) {
    die("SQL Error: " . $conn->error);
}

?>




<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Renter</title>
    <link rel="stylesheet" href="style.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    />
  </head>


  <!--SCREEN_____1 -->

  <body>
    <main class="app">
      <section id="filters" class="screen active">
        <header class="hero">
        
          <h1>Search &amp; Filters</h1>
          <p>Find your perfect rental in Iloilo City</p>
          <label class="search-box"
            ><i class="fa-solid fa-magnifying-glass"></i>
            <input 
            id="locationSearch"
            type="text"
            placeholder="Barangay, street, landmark..."
          /></label>
        </header>

        <div class="content">
          <article class="card">
            <h3>LOCATION</h3>
            <div class="location-options">
              <label>
                <input type="radio" name="loc" value="Iloilo City (All)" checked/>
                <span>Iloilo City (All)</span>
                <b class="selected-label">Selected</b>
              </label>

              <label>
                <input type="radio" name="loc" value="Arevalo"  />
                <span>Arevalo</span>
                <b class="selected-label"></b>
              </label>

              <label>
                <input type="radio" name="loc" value="City Proper" />
                <span>City Proper</span>
                <b class="selected-label"></b>
              </label>

              <label>
                <input type="radio" name="loc" value="Jaro" />
                <span>Jaro</span>
                <b class="selected-label"></b>
              </label>

              <label>
                <input type="radio" name="loc" value="La Paz" />
                <span>La Paz</span>
                <b class="selected-label"></b>
              </label>

              <label>
                <input type="radio" name="loc" value="Lapuz" />
                <span>Lapuz</span>
                <b class="selected-label"></b>
              </label>

              <label>
                <input type="radio" name="loc" value="Mandurriao" />
                <span>Mandurriao</span>
                <b class="selected-label"></b>
              </label>

              <label>
                <input type="radio" name="loc" value="Molo" />
                <span>Molo</span>
                <b class="selected-label"></b>
              </label>
            </div>
          </article>

          <article class="card">
            <div class="card-title-row">
  <h3>MONTHLY BUDGET</h3>
  <strong id="budgetValue">₱500 - ₱10,000</strong>
</div>
            <input
              id="budget"
              type="range"
              min="500"
              max="15000"
              value="10000"
            />
            <div class="range-labels">
              <span>₱500</span><span>₱15,000+</span>
            </div>
          </article>

          <article class="card">
  <h3>PROPERTY TYPE</h3>

  <div class="chips" id="propertyTypes">
    <button  class="selected" onclick="selectPropertyType(this)">Boarding House</button>
    <button onclick="selectPropertyType(this)">Apartment</button>
    <button onclick="selectPropertyType(this)">Dormitory</button>
    <button onclick="selectPropertyType(this)">Bed Space</button>
    <button onclick="selectPropertyType(this)">Transient</button>
  </div>
</article>

          <article class="card">
            <h3>AMENITIES</h3>
            <div class="amenity">
              <span><i class="fa-solid fa-wifi"></i> Wi-Fi</span
              ><button class="switch on"></button>
            </div>
            <div class="amenity">
              <span><i class="fa-solid fa-snowflake"></i> Air Conditioning</span
              ><button class="switch on"></button>
            </div>
            <div class="amenity">
              <span><i class="fa-solid fa-shirt"></i> Laundry Area</span
              ><button class="switch"></button>
            </div>
            <div class="amenity">
              <span><i class="fa-solid fa-kitchen-set"></i> Kitchen</span
              ><button class="switch"></button>
            </div>
            <div class="amenity">
              <span><i class="fa-solid fa-car"></i> Parking</span
              ><button class="switch"></button>
            </div>
          </article>

          <article class="card compact-card">
            <div>
              <h2>Available Now</h2>
              <p>Show only immediately available rooms</p>
            </div>
            <button class="switch on"></button>
            <hr />
            <div>
              <h2>Smart Match <span class="gold">✦</span></h2>
              <p>AI-powered personalized recommendations</p>
            </div>
            <button class="switch gold-switch"></button>
          </article>

      <div class="apply-filter">
          <button class="primary" onclick="applyFilters()">
            <i class="fa-solid fa-magnifying-glass"></i> Apply Filters
          </button>
        </div>


        </div>
       
      </section>

      <section id="results" class="screen">
        <header class="top-header">
         
          <div class="title-row">
            <button class="icon-btn" onclick="showScreen('filters')">
              <i class="fa-solid fa-chevron-left"></i>
            </button>


             <!--SCREEN_____2 -->  

           <div>
  <h1>Search Results</h1>

  <p>
    <i class="fa-solid fa-location-dot gold"></i>
    <span id="resultLocation">Iloilo City (All)</span>
  </p>

  <div id="resultFilters" class="result-filters">
    <span id="resultPropertyType">All Properties</span>
    <span id="resultBudget">₱500 - ₱8,000</span>
  </div>
</div>
            <button class="filter-btn">
              <i class="fa-solid fa-filter"></i> Filter
            </button>
          </div>
          <div class="view-tabs">
            <button class="active"><i class="fa-solid fa-list"></i> List</button
            ><button><i class="fa-regular fa-map"></i> Map</button>
          </div>
        </header>
        <div class="sort-bar">
        <b><span id="rentalCount">0</span> rentals</b> found <span></span
          ><i class="fa-solid fa-arrow-down-wide-short gold"></i
          ><b>Recommended</b><i class="fa-solid fa-chevron-down"></i>
        </div>


       

        <div class="list-content">

       

<?php if ($propertiesResult && $propertiesResult->num_rows > 0): ?>
    <?php while ($propertyCard = $propertiesResult->fetch_assoc()): ?>

        <?php
        $id = (int) $propertyCard['id'];

        $name = e($propertyCard['property_name'] ?? '');
        $location = e($propertyCard['location'] ?? '');
        $address = e($propertyCard['address'] ?? '');
        $image = e($propertyCard['image'] ?? '');
        $type = e($propertyCard['property_type'] ?? '');

        $price = (float) ($propertyCard['price'] ?? 0);
        $priceMax = $propertyCard['price_max'] ?? null;
        $available = (int) ($propertyCard['available_rooms'] ?? 0);
        $rating = (float) ($propertyCard['rating'] ?? 0);
        $reviews = (int) ($propertyCard['review_count'] ?? 0);

        $priceText = '₱' . number_format($price);

        if ($priceMax !== null && (float) $priceMax > $price) {
            $priceText .= ' - ₱' . number_format((float) $priceMax);
        }

        $unit = (($propertyCard['price_unit'] ?? '') === 'night')
            ? '/night'
            : '/mo';
        ?>
<div class="rental-card"
     data-id="<?= $id ?>"
     data-location="<?= $location ?>"
     data-price="<?= $price ?>"
     data-type="<?= $type ?>"
     role="link"
     tabindex="0"
    onclick="console.log('Property ID:', this.dataset.id); window.location.href='properties_details.php?id=' + encodeURIComponent(this.dataset.id)"

     onkeydown="if(event.key === 'Enter') window.location.href='properties_details.php?id=<?= $id ?>'">
            <div class="image-placeholder">

                <img src="<?= $image ?>"
                     alt="<?= $name ?>"
                     onerror="this.onerror=null;this.src='images/placeholder.jpg';">

                <span></span>

                <?php if ((int) ($propertyCard['verified'] ?? 0) === 1): ?>
                    <em>Verified</em>
                <?php endif; ?>

                <button class="heart"
                        onclick="toggleHeart(event, this)">♡</button>
            </div>

            <div class="rental-info">

                <h2>
                    <?= $name ?>

                    <strong>
                        <?= e($priceText) ?>
                        <small><?= e($unit) ?></small>
                    </strong>
                </h2>

                <p>⌖ <?= $location ?></p>

                <small><?= $address ?></small>

                <div class="tags">
                    <span><?= $type ?></span>

                    <span class="available">
                        <?= $available ?>
                        Room<?= $available !== 1 ? 's' : '' ?><br>
                        available
                    </span>

                    <span class="rating">
                        <?= str_repeat('★', max(0, min(5, (int) round($rating)))) ?>
                        <br>
                        (<?= $reviews ?>)
                    </span>
                </div>

            </div>
        </div>

    <?php endwhile; ?>

<?php else: ?>

    <p class="no-results">
        No available rental properties found.
    </p>

<?php endif; ?>

 


         


        </div>
      </section>

      <nav class="bottom-nav">

    <button data-screen="filters" onclick="showScreen('filters')">
        <img src="images/home.png">
        <span>Home</span>
    </button>

    <button data-screen="results" onclick="showScreen('results')">
        <img src="images/search.png">
        <span>Search</span>
    </button>

    <button data-screen="saved" onclick="showScreen('saved'); displaySavedProperties()">
        <img src="images/save.png">
        <span>Saved</span>
    </button>

    <button data-screen="messages" onclick="showScreen('messages')">
        <img src="images/message.png">
        <span>Messages</span>
    </button>

    <button data-screen="profile" onclick="showScreen('profile')">
        <img src="images/profile.png">
        <span>Profile</span>
    </button>

</nav>

          

    

<!-- SAVED PROPERTIES SCREEN -->
<section id="saved" class="screen">

    <header class="hero">
    
    <button class="back-button" onclick="showScreen('filters')">
        <i class="fa-solid fa-arrow-left"></i>
        Back
    </button>


        <h1>Saved Properties</h1>
        <p>Your favorite rental accommodations in Iloilo City</p>
    </header>

    <div class="content">

        <article class="card">
            <h3>SAVED RENTALS</h3>

            <div id="savedProperties"></div>
        </article>

    </div>

</section>

<!-- MESSAGES SCREEN -->
<section id="messages" class="screen">
<header class="hero message-hero">

    <button class="back-button" onclick="showScreen('filters')">
        <i class="fa-solid fa-arrow-left"></i>
        Back
    </button>

    <h1>Messages</h1>
    <p>Chat with the Nestify admin</p>

</header>

    <div class="content">

        <article class="card">

            <div class="message-header">
                <div class="admin-avatar">
                    <i class="fa-solid fa-user-tie"></i>
                </div>

                <div>
                    <h3>Nestify Admin</h3>
                    <p>Rental Accommodation Support</p>
                </div>
            </div>

            <div id="messageBox" class="message-box">

                <div class="message received">
                    <span>Hello! How can we help you?</span>
                </div>

            </div>

            <form class="message-form" action="send_message.php" method="POST">

                <input
                    type="text"
                    name="message"
                    placeholder="Type your message..."
                    required
                >

                <button type="submit">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>

            </form>

        </article>

    </div>

</section>

<!--PROFILE-->

<section id="profile" class="screen">

    <div class="profile-header">
        <div class="profile-avatar">
            <i class="fa-solid fa-user"></i>
        </div>

        <h2 id="profileName">
    <?= htmlspecialchars($fullname) ?>
</h2>

<p id="profileEmail">
    <?= htmlspecialchars($email) ?>
</p>
    </div>

    <div class="profile-menu">

       <!-- ACCOUNT -->

<button
    type="button"
    class="profile-item"
    onclick="showScreen('account')">

    <i class="fa-solid fa-user-gear"></i>

    <div>
        <strong>Account</strong>
        <span>Manage your account information</span>
    </div>

    <i class="fa-solid fa-chevron-right profile-arrow"></i>

</button>

<!--MY RENTALS-->

        <button
    type="button"
    class="profile-item"
    onclick="window.location.href='my-rentals.php'">

    <i class="fa-solid fa-house"></i>

    <div>
        <strong>My Rentals</strong>
        <span>View your rental information</span>
    </div>

    <i class="fa-solid fa-chevron-right profile-arrow"></i>

</button>




<!-- MAINTENANCE -->

<button
    type="button"
    class="profile-item"
    onclick="showScreen('maintenance')">

    <i class="fa-solid fa-screwdriver-wrench"></i>

    <div>
        <strong>Maintenance</strong>
        <span>Report and track maintenance requests</span>
    </div>

    <i class="fa-solid fa-chevron-right profile-arrow"></i>

</button>



<!-- REVIEWS -->

<button
    type="button"
    class="profile-item"
    onclick="showScreen('reviews')">

    <i class="fa-solid fa-star"></i>

    <div>
        <strong>Reviews</strong>
        <span>Rate and review your rental experience</span>
    </div>

    <i class="fa-solid fa-chevron-right profile-arrow"></i>

</button>

<!-- ACCOUNT SETTINGS -->

<button
    type="button"
    class="profile-item"
    onclick="showScreen('account-settings')">

    <i class="fa-solid fa-gear"></i>

    <div>
        <strong>Account Settings</strong>
        <span>Manage your account preferences</span>
    </div>

    <i class="fa-solid fa-chevron-right profile-arrow"></i>

</button>


<!-- HELP & SUPPORT -->

<button
    type="button"
    class="profile-item"
    onclick="showScreen('help-support')">

    <i class="fa-solid fa-circle-question"></i>

    <div>
        <strong>Help & Support</strong>
        <span>Get help and find answers to your questions</span>
    </div>

    <i class="fa-solid fa-chevron-right profile-arrow"></i>

</button>

       
    </div>
<button class="logout-btn" onclick="window.location.href='log_in.html'">
    <i class="fa-solid fa-right-from-bracket"></i>
    Log Out
</button>
</section>

<!-- =========================
     ACCOUNT
========================= -->
<section id="account" class="screen">

    <!-- Header -->
    <div class="account-page-header">

        <button
            type="button"
            class="account-back-btn"
            onclick="showScreen('profile')">

            <i class="fa-solid fa-arrow-left"></i>
            <span>Back</span>

        </button>

        <div>
            <h2>Account</h2>
            <p>View your account information</p>
        </div>

    </div>


    <!-- Account Profile Card -->
    <div class="account-profile-card">

        <div class="account-profile-top">

            <div class="account-avatar">
                <i class="fa-solid fa-user"></i>
            </div>

            <div class="account-profile-name">

                <h3>
                    <?= htmlspecialchars($fullname) ?>
                </h3>

                <p>
                    <?= htmlspecialchars($email) ?>
                </p>

                <span class="account-badge">
                    <i class="fa-solid fa-circle-check"></i>
                    Renter Account
                </span>

            </div>

        </div>

    </div>


    <!-- Account Information -->
    <div class="account-section">

        <div class="account-section-title">

            <div class="account-section-icon">
                <i class="fa-solid fa-id-card"></i>
            </div>

            <div>
                <h3>Personal Information</h3>
                <p>Your registered account details</p>
            </div>

        </div>


        <div class="account-info-list">

            <!-- Name -->
            <div class="account-info-item">

                <div class="account-info-icon">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div class="account-info-content">

                    <span>Full Name</span>

                    <strong>
                        <?= htmlspecialchars($fullname) ?>
                    </strong>

                </div>

            </div>


            <!-- Email -->
            <div class="account-info-item">

                <div class="account-info-icon">
                    <i class="fa-solid fa-envelope"></i>
                </div>

                <div class="account-info-content">

                    <span>Email Address</span>

                    <strong>
                        <?= htmlspecialchars($email) ?>
                    </strong>

                </div>

            </div>


            <!-- Account Type -->
            <div class="account-info-item">

                <div class="account-info-icon">
                    <i class="fa-solid fa-user-tag"></i>
                </div>

                <div class="account-info-content">

                    <span>Account Type</span>

                    <strong>Renter</strong>

                </div>

            </div>

        </div>

    </div>


    <!-- Account Security -->
    <div class="account-security-card">

        <div class="security-icon">
            <i class="fa-solid fa-shield-halved"></i>
        </div>

        <div class="security-content">

            <h3>Account Security</h3>

            <p>
                Keep your account information secure.
            </p>

        </div>

        <i class="fa-solid fa-chevron-right security-arrow"></i>

    </div>

</section>

<!-- MAINTENANCE -->
<section id="maintenance" class="screen">

    <!-- HEADER -->
    <div class="account-page-header">

        <button
            type="button"
            class="account-back-btn"
            onclick="showScreen('profile')">

            <i class="fa-solid fa-arrow-left"></i>
            <span>Back</span>

        </button>
        <div>
            <h2>Maintenance</h2>
            <p>Report and track maintenance requests</p>
        </div>

    </div>


    <!-- TABS -->
    <div class="maintenance-tabs">

        <button
            type="button"
            class="maintenance-tab active"
            onclick="showMaintenanceTab('submit', this)">

            Submit Request

        </button>

        <button
            type="button"
            class="maintenance-tab"
            onclick="showMaintenanceTab('requests', this)">

            My Requests

        </button>

    </div>


    <!-- SUBMIT REQUEST -->

    <div
        id="maintenance-submit"
        class="maintenance-tab-content active">

        <div class="maintenance-form-group">

            <label for="maintenanceCategory">
                Select Category
            </label>

            <select
                id="maintenanceCategory"
                name="category">

                <option value="Plumbing">Plumbing</option>
                <option value="Electrical">Electrical</option>
                <option value="Air Conditioning">
                    Air Conditioning
                </option>
                <option value="Furniture">Furniture</option>
                <option value="Appliance">Appliance</option>
                <option value="Internet">
                    Internet / Wi-Fi
                </option>
                <option value="Door / Lock">
                    Door / Lock
                </option>
                <option value="Other">Other</option>

            </select>

        </div>


        <!-- DESCRIPTION -->

        <div class="maintenance-form-group">

            <label for="maintenanceDescription">
                Description
            </label>

            <textarea
                id="maintenanceDescription"
                name="description"
                placeholder="Describe the problem..."></textarea>

        </div>


        <!-- PHOTO -->

        <div class="maintenance-form-group">

            <label>
                Add Photo <span>(optional)</span>
            </label>

            <label
                for="maintenancePhoto"
                class="maintenance-photo-upload">

                <i class="fa-regular fa-image"></i>

                <span>Add Photo</span>

            </label>

            <input
                type="file"
                id="maintenancePhoto"
                name="photo"
                accept="image/*"
                hidden>

        </div>


        <!-- SUBMIT -->

        <button
            type="button"
            class="maintenance-submit-btn">

            Submit Request

        </button>

    </div>


    <!-- MY REQUESTS -->

    <div
        id="maintenance-requests"
        class="maintenance-tab-content">

        <div class="my-maintenance-empty">

            <i class="fa-solid fa-screwdriver-wrench"></i>

            <h3>No Requests Yet</h3>

            <p>
                Your maintenance requests will appear here.
            </p>

        </div>

    </div>

</section>


<!-- REVIEWS -->



<section id="reviews" class="screen">

    <!-- HEADER -->
    <div class="review-page-header">

        <button
            type="button"
            class="account-back-btn"
            onclick="showScreen('profile')">

            <i class="fa-solid fa-arrow-left"></i>
            <span>Back</span>

        </button>

        <div>
            <h2>Reviews</h2>
            <p>Rate and review your rental experience</p>
        </div>

    </div>


    <!-- RATE YOUR STAY -->

    <div class="review-page-content">

        <h3 class="review-page-title">
            Rate Your Stay
        </h3>

        <p class="review-page-description">
            Help improve the rental experience
            by leaving a review.
        </p>


        <!--  / PROPERTY -->

        <div class="review-rating-item">

            <span class="review-rating-label">
                Owner/Property
            </span>

            <div
                class="review-star-group"
                data-type="owner">

                <button type="button" onclick="setReviewRating(this, 1)">★</button>
                <button type="button" onclick="setReviewRating(this, 2)">★</button>
                <button type="button" onclick="setReviewRating(this, 3)">★</button>
                <button type="button" onclick="setReviewRating(this, 4)">★</button>
                <button type="button" onclick="setReviewRating(this, 5)">★</button>

            </div>

        </div>


        <!-- CLEANLINESS -->

        <div class="review-rating-item">

            <span class="review-rating-label">
                Cleanliness
            </span>

            <div
                class="review-star-group"
                data-type="cleanliness">

                <button type="button" onclick="setReviewRating(this, 1)">★</button>
                <button type="button" onclick="setReviewRating(this, 2)">★</button>
                <button type="button" onclick="setReviewRating(this, 3)">★</button>
                <button type="button" onclick="setReviewRating(this, 4)">★</button>
                <button type="button" onclick="setReviewRating(this, 5)">★</button>

            </div>

        </div>


        <!-- COMMUNICATION -->

        <div class="review-rating-item">

            <span class="review-rating-label">
                Communication
            </span>

            <div
                class="review-star-group"
                data-type="communication">

                <button type="button" onclick="setReviewRating(this, 1)">★</button>
                <button type="button" onclick="setReviewRating(this, 2)">★</button>
                <button type="button" onclick="setReviewRating(this, 3)">★</button>
                <button type="button" onclick="setReviewRating(this, 4)">★</button>
                <button type="button" onclick="setReviewRating(this, 5)">★</button>

            </div>

        </div>


        <!-- OVERALL EXPERIENCE -->

        <div class="review-rating-item">

            <span class="review-rating-label">
                Overall Experience
            </span>

            <div
                class="review-star-group"
                data-type="overall">

                <button type="button" onclick="setReviewRating(this, 1)">★</button>
                <button type="button" onclick="setReviewRating(this, 2)">★</button>
                <button type="button" onclick="setReviewRating(this, 3)">★</button>
                <button type="button" onclick="setReviewRating(this, 4)">★</button>
                <button type="button" onclick="setReviewRating(this, 5)">★</button>

            </div>

        </div>


        <!-- COMMENT -->

        <textarea
            id="reviewComment"
            class="review-comment-box"
            placeholder="Add a comment (optional)"></textarea>


        <!-- SUBMIT -->

        <button
            type="button"
            class="review-submit-button"
            onclick="submitReview()">

            Submit Review

        </button>

    </div>

</section>


<!-- ACCOUNT SETTINGS -->

<section id="account-settings" class="screen">

    <!-- HEADER -->
    <div class="account-settings-header">

        <button
            type="button"
            class="account-back-btn"
            onclick="showScreen('profile')">

            <i class="fa-solid fa-arrow-left"></i>
            <span>Back</span>

        </button>

        <div>
            <h2>Account Settings</h2>
            <p>Manage your account preferences</p>
        </div>

    </div>


    <!-- SETTINGS -->

    <div class="account-settings-content">


        <!-- PERSONAL INFORMATION -->

        <div class="account-settings-card">

            <div class="account-settings-icon">
                <i class="fa-solid fa-user"></i>
            </div>

            <div class="account-settings-text">

                <strong>Personal Information</strong>

                <span>
                    Update your name, email, and mobile number
                </span>

            </div>

            <i class="fa-solid fa-chevron-right account-settings-arrow"></i>

        </div>


        <!-- PASSWORD -->

        <div class="account-settings-card">

            <div class="account-settings-icon">
                <i class="fa-solid fa-lock"></i>
            </div>

            <div class="account-settings-text">

                <strong>Password</strong>

                <span>
                    Change your account password
                </span>

            </div>

            <i class="fa-solid fa-chevron-right account-settings-arrow"></i>

        </div>


        <!-- NOTIFICATIONS -->

        <div class="account-settings-card">

            <div class="account-settings-icon">
                <i class="fa-solid fa-bell"></i>
            </div>

            <div class="account-settings-text">

                <strong>Notifications</strong>

                <span>
                    Manage your notification preferences
                </span>

            </div>

            <i class="fa-solid fa-chevron-right account-settings-arrow"></i>

        </div>


        <!-- PRIVACY -->

        <div class="account-settings-card">

            <div class="account-settings-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>

            <div class="account-settings-text">

                <strong>Privacy</strong>

                <span>
                    Manage your privacy settings
                </span>

            </div>

            <i class="fa-solid fa-chevron-right account-settings-arrow"></i>

        </div>


    </div>

</section>


<!-- HELP & SUPPORT -->

<section id="help-support" class="screen">

    <!-- HEADER -->

    <div class="help-support-header">

        <button
            type="button"
            class="account-back-btn"
            onclick="showScreen('profile')">

            <i class="fa-solid fa-arrow-left"></i>
            <span>Back</span>

        </button>

        <div>
            <h2>Help & Support</h2>
            <p>We're here to help you</p>
        </div>

    </div>


    <!-- SUPPORT OPTIONS -->

    <div class="help-support-content">


        <!-- FAQ -->

        <div class="help-support-card">

            <div class="help-support-icon">
                <i class="fa-solid fa-circle-question"></i>
            </div>

            <div class="help-support-text">

                <strong>Frequently Asked Questions</strong>

                <span>
                    Find answers to common questions
                </span>

            </div>

            <i class="fa-solid fa-chevron-right help-support-arrow"></i>

        </div>


        <!-- CONTACT SUPPORT -->

        <div class="help-support-card">

            <div class="help-support-icon">
                <i class="fa-solid fa-headset"></i>
            </div>

            <div class="help-support-text">

                <strong>Contact Support</strong>

                <span>
                    Get assistance from the Nestify support team
                </span>

            </div>

            <i class="fa-solid fa-chevron-right help-support-arrow"></i>

        </div>


        <!-- REPORT PROBLEM -->

        <div class="help-support-card">

            <div class="help-support-icon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <div class="help-support-text">

                <strong>Report a Problem</strong>

                <span>
                    Tell us about an issue with the website
                </span>

            </div>

            <i class="fa-solid fa-chevron-right help-support-arrow"></i>

        </div>


        <!-- TERMS -->

        <div class="help-support-card">

            <div class="help-support-icon">
                <i class="fa-solid fa-file-lines"></i>
            </div>

            <div class="help-support-text">

                <strong>Terms & Conditions</strong>

                <span>
                    Read Nestify's terms and conditions
                </span>

            </div>

            <i class="fa-solid fa-chevron-right help-support-arrow"></i>

        </div>


        <!-- PRIVACY -->

        <div class="help-support-card">

            <div class="help-support-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>

            <div class="help-support-text">

                <strong>Privacy Policy</strong>

                <span>
                    Learn how we protect your information
                </span>

            </div>

            <i class="fa-solid fa-chevron-right help-support-arrow"></i>

        </div>


    </div>


    <!-- VERSION -->

    <div class="help-support-version">
        Nestify
        <span>Version 1.0</span>
    </div>

</section>




    <script src="script.js"></script>
  </body>
</html>