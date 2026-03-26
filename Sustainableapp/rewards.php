<?php
include("session_test.php");
include("config.php");

$participant_id = (int) $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];

/* Get total points from user table */
$pointsStmt = $conn->prepare("
    SELECT user_point
    FROM user
    WHERE user_id = ?
    LIMIT 1
");
$pointsStmt->bind_param("i", $participant_id);
$pointsStmt->execute();
$pointsResult = $pointsStmt->get_result();
$pointsRow = $pointsResult->fetch_assoc();

$totalPoints = (int)($pointsRow['user_point'] ?? 0);

$nextReward = ceil(($totalPoints + 1) / 100) * 100;
$progressPercent = $nextReward > 0 ? ($totalPoints / $nextReward) * 100 : 0;

/* Get redeem history */
$historyStmt = $conn->prepare("
    SELECT r.reward_name, rr.points_used, rr.created_at
    FROM reward_redemptions rr
    JOIN reward r ON rr.reward_id = r.reward_id
    WHERE rr.gmail = ?
    ORDER BY rr.created_at DESC
");
$historyStmt->bind_param("s", $user_email);
$historyStmt->execute();
$historyResult = $historyStmt->get_result();

/* Get rewards */
$rewardsStmt = $conn->prepare("
    SELECT reward_id, reward_name, reward_points, reward_image
    FROM reward
    WHERE reward_status = 'Active'
    ORDER BY reward_points ASC
");
$rewardsStmt->execute();
$rewardsResult = $rewardsStmt->get_result();
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EcoEvents | Rewards</title>
  <link rel="stylesheet" href="css/style.css" />
</head>

<body>

<?php include("header.php"); ?>

<main class="container rewards-page">

  <?php if (isset($_GET['redeem'])): ?>
    <?php if ($_GET['redeem'] === 'success'): ?>
      <div class="success-msg">Reward redeemed successfully.</div>
    <?php elseif ($_GET['redeem'] === 'notenough'): ?>
      <div class="error-msg">Not enough Green Points to redeem this reward.</div>
    <?php elseif ($_GET['redeem'] === 'invalid'): ?>
      <div class="error-msg">Invalid reward selected.</div>
    <?php endif; ?>
  <?php endif; ?>

  <h1 class="rewards-title">Rewards</h1>

  <!-- TOP SUMMARY BAR -->
  <section class="rewards-summary">
    <div class="summary-left">
      <div class="summary-icon"></div>
      <div>
        <div class="summary-label">Your Green Points</div>
        <div class="summary-points" id="userPoints"><?= $totalPoints ?></div>
      </div>
    </div>

    <div class="summary-right">
      <div class="summary-label" id="nextRewardText">Next reward at <?=$nextReward ?> green points</div>
      <div class="summary-progress">
        <div class="summary-bar">
          <div class="summary-fill" id="progressFill" style="width:<?= $progressPercent ?>%"></div>
        </div>
        <div class="summary-count" id="progressText">
          <?= $totalPoints ?>/<?= $nextReward ?>
      </div>
    </div>
  </section>

  <!-- POINTS BREAKDOWN TABLE -->
  <section class="rewards-section">
    <h2 class="section-title">Points Breakdown</h2>

    <div class="table-wrap">
      <table class="rewards-table">
        <thead>
          <tr>
            <th>Event</th>
            <th>Points Earned</th>
            <th>Date Earned</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Community Gardening Day</td>
            <td>30 points</td>
            <td>30 December 2025</td>
          </tr>
          <tr>
            <td>Beach Cleanup</td>
            <td>10 points</td>
            <td>30 November 2025</td>
          </tr>
          <tr>
            <td>Community Gardening Day</td>
            <td>20 points</td>
            <td>3 October 2025</td>
          </tr>
          <tr>
            <td>Art Workshop</td>
            <td>30 points</td>
            <td>10 September 2025</td>
          </tr>
          <tr>
            <td>Beach Cleanup</td>
            <td>10 points</td>
            <td>1 August 2025</td>
          </tr>
          <tr>
            <td>Community Gardening Day</td>
            <td>20 points</td>
            <td>20 July 2025</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>

  <!-- BOTTOM 2 COLUMNS -->
  <section class="rewards-bottom">

    <!-- Redeem Rewards -->
    <div>
      <h2 class="section-title">Redeem Rewards</h2>

<div class="redeem-grid">

<?php if ($rewardsResult->num_rows > 0): ?>
  <?php while ($reward = $rewardsResult->fetch_assoc()): ?>
    <article class="redeem-card">

      <div class="redeem-img">
        <img 
          src="/uploads/<?= rawurlencode($reward['reward_image']) ?>" 
          alt="<?= htmlspecialchars($reward['reward_name']) ?>"
        >
      </div>

      <div class="redeem-cost">
        <?= (int)$reward['reward_points'] ?> points
      </div>

      <div class="redeem-body">
        <h3><?= htmlspecialchars($reward['reward_name']) ?></h3>

        <form action="redeem_reward.php" method="POST" class="redeemForm">
          <input type="hidden" name="reward_id" value="<?= (int)$reward['reward_id'] ?>">
          <button class="redeem-btn" type="button">Redeem</button>
        </form>
      </div>

    </article>
  <?php endwhile; ?>
<?php else: ?>
  <p>No rewards available.</p>
<?php endif; ?>

</div>

      <p class="redeem-note">* Points will be deducted upon redemption, not refundable</p>
    </div>

    <!-- Redeem History -->
    <div>
      <h2 class="section-title">Redeem History</h2>

      <div class="table-wrap">
        <table class="rewards-table">
          <thead>
            <tr>
              <th>Reward</th>
              <th>Points Used</th>
              <th>Date Redeemed</th>
            </tr>
          </thead>
          <tbody id="redeemHistoryBody">

          <?php if ($historyResult->num_rows > 0): ?>
          <?php while ($row = $historyResult->fetch_assoc()): ?>

          <tr>
            <td><?= htmlspecialchars($row['reward_name']) ?></td>
            <td><?= $row['points_used'] ?> points</td>
            <td><?= date("d F Y", strtotime($row['created_at'])) ?></td>
          </tr>

          <?php endwhile; ?>
          <?php else: ?>

          <tr>
            <td colspan="3">No rewards redeemed yet.</td>
          </tr>

          <?php endif; ?>

          </tbody>
        </table>
      </div>
    </div>

  </section>
</main>

<!--for pop up design -->
<div id="redeemModal" class="modal-overlay">
  <div class="modal-box">

    <h3>Confirm Redemption</h3>
    <p>Are you sure you want to redeem this reward?<br></p>

    <div class="modal-actions">
      <button id="cancelRedeem" class="btn-cancel">Cancel</button>
      <button id="confirmRedeem" class="btn-confirm">Yes, Redeem</button>
    </div>

  </div>
</div>

<script>
let selectedForm = null;

const modal = document.getElementById("redeemModal");
const confirmBtn = document.getElementById("confirmRedeem");
const cancelBtn = document.getElementById("cancelRedeem");

document.querySelectorAll(".redeem-btn").forEach(btn=>{
  btn.addEventListener("click",function(){
      selectedForm = this.closest("form");
      modal.style.display = "flex";
  });
});

cancelBtn.onclick = () => modal.style.display = "none";

confirmBtn.onclick = () => {
  if(selectedForm){
    selectedForm.submit();
  }
};

</script>

<?php include("footer.php"); ?>

<script src="js/main.js"></script>
</body>
</html>