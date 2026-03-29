<?php
include("config.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EcoEvents | About Us</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php
if (isset($_SESSION['user_id'])) {
    include("header.php");
} else {
    include("header_guest.php");
}
?>

<main class="about-page">

<!-- HERO -->
<section class="about-hero">
  <div class="container">
    <div class="about-hero-content">
      <div class="about-hero-icon"></div>

      <h1>About Sustainable Events</h1>

      <p>
        EcoEvents is a platform that encourages students and communities to
        participate in sustainability-focused events, contribute to environmental
        improvement, and earn green points for their involvement.
      </p>
    </div>
  </div>
</section>

<!-- OUR PLATFORM -->
<section class="about-section">
  <div class="container about-row">

    <div class="about-text-card">
      <h2>Our Platform</h2>
      <p>
        Our platform helps participants discover sustainable events, join activities,
        track their participation, and redeem green points for rewards. It is designed
        to make environmental engagement simple, motivating, and rewarding.
      </p>
    </div>

    <div class="about-image-box">
      <img src="images/about/pingu.jpg" alt="Our Platform">
    </div>

  </div>
</section>

<!-- OUR MISSION -->
<section class="about-section">
  <div class="container about-row reverse">

    <div class="about-image-box">
      <img src="images/about/pingu.jpg" alt="Our Mission">
    </div>

    <div class="about-text-card">
      <h2>Our Mission</h2>
      <p>
        Our mission is to promote sustainability awareness and action by connecting
        people with meaningful environmental events. We aim to inspire positive
        habits and long-term community impact through active participation.
      </p>
    </div>

  </div>
</section>

<!-- COMMITMENT -->
<section class="about-section">
  <div class="container about-row">

    <div class="about-text-card">
      <h2>Sustainability Commitment</h2>
      <p>
        We are committed to supporting eco-friendly initiatives, reducing waste,
        and encouraging greener lifestyles. Through every event and reward, we
        strive to create a stronger culture of environmental responsibility.
      </p>
    </div>

    <div class="about-image-box">
      <img src="images/about/pingu.jpg" alt="Sustainability Commitment">
    </div>

  </div>
</section>

<!-- GREEN POINTS -->
<section class="about-points">
 <div class="points-grid two-cards">

  <div class="points-card">
    <div class="points-circle"></div>
    <h2>+10</h2>
    <p>Attend Event</p>
  </div>

  <div class="points-card">
    <div class="points-circle"></div>
    <h2>+5</h2>
    <p>Submit Feedback</p>
  </div>

</div>
</section>

<!-- REDEEM SECTION -->
<section class="about-redeem">
  <div class="container">

    <h2>Redeem Your Points For:</h2>

    <div class="redeem-preview-grid">

      <a href="rewards.php" class="reward-link">
        <div class="redeem-preview-card">
          <img src="images/recyclebag.png" class="redeem-preview-img" alt="Reusable Bag">
          <h3>Reusable Bag</h3>
          <p>Reduce single-use plastic in daily life.</p>
        </div>
      </a>

    </div>

  </div>
</section>

</main>

<?php include("footer.php"); ?>

<div id="loginRequiredModal" class="modal-overlay">
  <div class="modal-box">
    <h3>Preview not available</h3>
    <p>Please log in or sign up to continue.</p>
    <div class="modal-actions">
      <button id="guestModalClose" class="btn-cancel" type="button">Cancel</button>
      <button id="guestModalLogin" class="btn-confirm" type="button">Login/Sign Up</button>
    </div>
  </div>
</div>

<script src="js/main.js"></script>
</body>
</html>