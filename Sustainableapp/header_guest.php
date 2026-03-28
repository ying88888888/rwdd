<?php
$current = basename($_SERVER['PHP_SELF']);

function active($page, $current) {
  return $page === $current ? 'active' : '';
}
?>

<header class="navbar">
  <div class="container">

    <div class="logo">
      <a href="Participant2.php">
        <img src="images/logo.png" alt="EcoEvents Logo">
      </a>
    </div>

    <nav class="nav-links">
      <a href="Participant2.php" class="<?= active('Participant2.php', $current) ?>">Home</a>
      <a href="about.php" class="<?= active('about.php', $current) ?>">About Us</a>

      <a href="#" class="guest-locked">Events</a>
      <a href="#" class="guest-locked">Rewards</a>
      <a href="#" class="guest-locked">Event Gallery</a>
    </nav>

    <div class="nav-right">
      <a href="#" class="notification-link guest-locked" aria-label="Notifications">
        <img src="images/notification-bell.png" alt="Notifications" class="notification-bell-icon">
        <span class="notification-dot"></span>
      </a>

      <a href="#" class="profile-wrapper guest-locked">
        <img src="images/profileicon.png" alt="Profile" class="profile-icon">
        <span class="nav-profile-name">Profile</span>
      </a>

      <a href="http://localhost/Login/login.html" class="btn">Login</a>
    </div>

  </div>
</header>