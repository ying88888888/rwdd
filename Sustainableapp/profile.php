<?php
include("session_test.php");
include("config.php");

$user_id = (int) $_SESSION['user_id'];

/* Get logged-in user profile */
$userStmt = $conn->prepare("
    SELECT user_fullname, user_email, user_role, user_registerDate, user_point
    FROM `user`
    WHERE user_id = ?
    LIMIT 1
");

if (!$userStmt) {
    die("Prepare failed (user query): " . $conn->error);
}

$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userResult = $userStmt->get_result();

if (!$userResult || $userResult->num_rows === 0) {
    die("User profile not found.");
}

$user = $userResult->fetch_assoc();
$totalPoints = (int) ($user['user_point'] ?? 0);

/* Total events joined */
$joinedStmt = $conn->prepare("
    SELECT COUNT(*) AS total_joined
    FROM event_participants
    WHERE user_id = ? AND participation_status = 'joined'
");

if (!$joinedStmt) {
    die("Prepare failed (joined query): " . $conn->error);
}

$joinedStmt->bind_param("i", $user_id);
$joinedStmt->execute();
$joinedResult = $joinedStmt->get_result();
$joinedRow = $joinedResult->fetch_assoc();
$totalJoined = (int) ($joinedRow['total_joined'] ?? 0);

/* Total completed events */
$completedStmt = $conn->prepare("
    SELECT COUNT(*) AS total_completed
    FROM event_participants
    WHERE user_id = ? AND participation_status = 'completed'
");

if (!$completedStmt) {
    die("Prepare failed (completed query): " . $conn->error);
}

$completedStmt->bind_param("i", $user_id);
$completedStmt->execute();
$completedResult = $completedStmt->get_result();
$completedRow = $completedResult->fetch_assoc();
$totalCompleted = (int) ($completedRow['total_completed'] ?? 0);

$totalPhotos = 0;

$displayName = htmlspecialchars($user['user_fullname']);
$displayEmail = htmlspecialchars($user['user_email']);
$displayRole = htmlspecialchars(ucfirst($user['user_role']));
$displayCreatedAt = date("d F Y", strtotime($user['user_registerDate']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EcoEvents | Profile</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>

<?php include("header.php"); ?>

<main class="container profile-page">
  <h1 class="profile-title">Profile</h1>

  <section class="profile-card">
    <div class="profile-top">
      <div class="profile-avatar">
        <img src="images/profileicon.png" alt="Profile">
      </div>
      <div class="profile-name"><?= $displayName ?></div>
    </div>

    <div class="profile-block">
      <div class="profile-block-title">Account Details</div>
      <div class="profile-block-body">
        <div class="profile-row"><span>Name:</span><span><?= $displayName ?></span></div>
        <div class="profile-row"><span>Email:</span><span><?= $displayEmail ?></span></div>
        <div class="profile-row"><span>Role:</span><span><?= $displayRole ?></span></div>
        <div class="profile-row"><span>Joined Date:</span><span><?= $displayCreatedAt ?></span></div>
      </div>
    </div>

    <div class="profile-grid">
      <div class="profile-block">
        <div class="profile-block-title">Notification Settings</div>
        <div class="profile-block-body">
          <label class="profile-check">
            <input type="checkbox" checked />
            <span>Email Notifications</span>
          </label>

          <label class="profile-check">
            <input type="checkbox" />
            <span>System Alerts</span>
          </label>
        </div>
      </div>

      <div class="profile-block">
        <div class="profile-block-title">Participation Summary</div>
        <div class="profile-block-body">
          <div class="profile-row"><span>Total events joined:</span><span id="profileJoined"><?= $totalJoined ?></span></div>
          <div class="profile-row"><span>Completed events count:</span><span id="profileCompleted"><?= $totalCompleted ?></span></div>
          <div class="profile-row"><span>Submitted photos count:</span><span id="profilePhotos"><?= $totalPhotos ?></span></div>
          <div class="profile-row"><span>Total Green Points:</span><span id="profileTotalPoints"><?= $totalPoints ?></span></div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include("footer.php"); ?>

<script src="js/main.js"></script>
</body>
</html>