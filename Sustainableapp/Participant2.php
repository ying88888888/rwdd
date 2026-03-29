<?php 
include("config.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EcoEvents | Home</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include("header_guest.php"); ?>

<section class="hero">
  <div class="container">
    <div class="hero-text">
      <h1>
        Creating a<br>
        Sustainable Future<br>
        Together
      </h1>
      <p>
        Join sustainable events, earn Green Points,<br>
        and make a positive environmental impact.
      </p>
    </div>

    <div class="hero-image">
      <img src="images/gallery/beach1.jpg" alt="Sustainable Event">
    </div>
  </div>
</section>

<section class="stats">
  <div class="container">
    <div>
      <h2>100k+</h2>
      <p>Participants Joined</p>
    </div>

    <div>
      <h2>1000+</h2>
      <p>Event Hosted</p>
    </div>

    <div>
      <h2>1000+</h2>
      <p>Waste Collected</p>
    </div>

    <div>
      <h2>99%</h2>
      <p>Good Comments</p>
    </div>
  </div>
</section>

<section class="how">
  <div class="container">
    <h2>How the Platform Works?</h2>

    <div class="how-cards">
      <div class="how-card">
        <div class="icon">Icon</div>
        <h3>Browse Events</h3>
        <p>View sustainable event dates, locations, and goals.</p>
      </div>

      <div class="how-card">
        <div class="icon">Icon</div>
        <h3>Earn Green Points</h3>
        <p>Join events and participate to earn Green Points.</p>
      </div>

      <div class="how-card">
        <div class="icon">Icon</div>
        <h3>Make Positive Impact</h3>
        <p>See how your actions help create a greener future.</p>
      </div>
    </div>
  </div>
</section>

<section class="events" id="upcomingEvents">
  <div class="container">
    <div class="guest-login-center">
      <a href="http://localhost/Login/login.html" class="guest-login-big-btn">
        Please Login / Sign Up first
      </a>
    </div>
  </div>
</section>

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