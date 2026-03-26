<?php
include("session_test.php");
include("config.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: rewards.php");
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Participant') {
    header("Location: rewards.php");
    exit;
}

$participant_id = (int) $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];
$username = $_SESSION['user_fullname'] ?? '';
$reward_id = isset($_POST['reward_id']) ? (int) $_POST['reward_id'] : 0;

if ($reward_id <= 0) {
    header("Location: rewards.php?redeem=invalid");
    exit;
}

/* Get reward info */
$rewardStmt = $conn->prepare("
    SELECT reward_id, reward_name, reward_points, reward_quantity, reward_status
    FROM reward
    WHERE reward_id = ?
    LIMIT 1
");
$rewardStmt->bind_param("i", $reward_id);
$rewardStmt->execute();
$rewardResult = $rewardStmt->get_result();

if (!$rewardResult || $rewardResult->num_rows === 0) {
    header("Location: rewards.php?redeem=invalid");
    exit;
}

$reward = $rewardResult->fetch_assoc();
$rewardName = $reward['reward_name'];
$pointsCost = (int) $reward['reward_points'];
$rewardQuantity = (int) $reward['reward_quantity'];

if ($reward['reward_status'] !== 'Active') {
    header("Location: rewards.php?redeem=invalid");
    exit;
}

if ($rewardQuantity <= 0) {
    header("Location: rewards.php?redeem=invalid");
    exit;
}

/* Get current user points */
$userStmt = $conn->prepare("
    SELECT user_point
    FROM user
    WHERE user_id = ?
    LIMIT 1
");
$userStmt->bind_param("i", $participant_id);
$userStmt->execute();
$userResult = $userStmt->get_result();

if (!$userResult || $userResult->num_rows === 0) {
    header("Location: rewards.php?redeem=invalid");
    exit;
}

$user = $userResult->fetch_assoc();
$currentPoints = (int) $user['user_point'];

if ($currentPoints < $pointsCost) {
    header("Location: rewards.php?redeem=notenough");
    exit;
}

$newPoints = $currentPoints - $pointsCost;

$conn->begin_transaction();

try {
    /* 1. Deduct points */
    $updateUserStmt = $conn->prepare("
        UPDATE user
        SET user_point = ?
        WHERE user_id = ?
    ");
    $updateUserStmt->bind_param("ii", $newPoints, $participant_id);

    if (!$updateUserStmt->execute()) {
        throw new Exception("Failed to update user points");
    }

    /* 2. Reduce reward quantity */
    $updateRewardStmt = $conn->prepare("
        UPDATE reward
        SET reward_quantity = reward_quantity - 1
        WHERE reward_id = ? AND reward_quantity > 0
    ");
    $updateRewardStmt->bind_param("i", $reward_id);

    if (!$updateRewardStmt->execute() || $updateRewardStmt->affected_rows === 0) {
        throw new Exception("Failed to update reward quantity");
    }

    /* 3. Insert redeem history */
    $redeemStmt = $conn->prepare("
        INSERT INTO reward_redemptions
        (reward_id, points_used, redeemed_at, created_at, username, gmail)
        VALUES (?, ?, NOW(), NOW(), ?, ?)
    ");

    /* 4. Insert into pointshistory */
    $negativePoints = -$pointsCost;
    $activity = "Redeemed reward: " . $rewardName;

    $historyStmt = $conn->prepare("
        INSERT INTO pointshistory (user_id, pointsHistory_activity, pointsHistory_points, pointsHistory_time)
        VALUES (?, ?, ?, NOW())
    ");
    $historyStmt->bind_param("isi", $participant_id, $activity, $negativePoints);

    if (!$historyStmt->execute()) {
        throw new Exception("Failed to insert points history");
    }
    
    $redeemStmt->bind_param("iiss", $reward_id, $pointsCost, $username, $user_email);

    if (!$redeemStmt->execute()) {
        throw new Exception("Failed to insert redemption history");
    }

    $conn->commit();
    header("Location: rewards.php?redeem=success");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("Redeem failed: " . $e->getMessage());
}
?>