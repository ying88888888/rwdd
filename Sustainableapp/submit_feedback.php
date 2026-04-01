<?php
include("session_test.php");
include("config.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    header("Location: Participant.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];

//SHOW FORM WHEN GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;

    if ($event_id <= 0) {
        header("Location: my_events.php?feedback=invalid");
        exit;
    }

    $checkStmt = $conn->prepare("
        SELECT ep.id
        FROM event_participants ep
        JOIN events e ON ep.event_id = e.id
        WHERE ep.event_id = ?
          AND ep.user_id = ?
          AND ep.participation_status != 'cancelled'
          AND (
            e.event_date < CURDATE()
            OR (
                e.event_date = CURDATE()
                AND (e.event_time IS NULL OR e.event_time <= CURTIME())
            )
        )
    ");

    if (!$checkStmt) {
        die("SQL error: " . $conn->error);
    }

    $checkStmt->bind_param("ii", $event_id, $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
    header("Location: my_events.php?feedback=notallowed");
    exit;
    }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Feedback</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include("header.php"); ?>

<div class="page-overlay">
    <div class="feedback-modal">
        <div class="feedback-modal-header">
            <div class="close-box"></div>
            <h1>Submit Event Feedback & Rating</h1>
            <p>Share your experience and earn 5 Green Points!</p>
        </div>

        <div class="feedback-modal-body">
            <form action="submit_feedback.php" method="POST">
                <input type="hidden" name="event_id" value="<?= $event_id ?>">

                <label class="form-label">Rating</label>
                <div class="rating-row">
                    <label>
                        <input type="radio" name="rating" value="1" required>
                        <span class="rating-box"></span>
                    </label>
                    <label>
                        <input type="radio" name="rating" value="2">
                        <span class="rating-box"></span>
                    </label>
                    <label>
                        <input type="radio" name="rating" value="3">
                        <span class="rating-box"></span>
                    </label>
                    <label>
                        <input type="radio" name="rating" value="4">
                        <span class="rating-box"></span>
                    </label>
                    <label>
                        <input type="radio" name="rating" value="5">
                        <span class="rating-box"></span>
                    </label>
                </div>

                <label for="feedback" class="form-label">Your Feedback</label>
                <textarea
                    name="feedback"
                    id="feedback"
                    class="feedback-textarea"
                    placeholder="Share your experience...."
                    required
                ></textarea>

                <div class="feedback-actions">
                    <a href="my_events.php" class="modal-btn cancel-btn">Cancel</a>
                    <button type="submit" class="modal-btn submit-btn">Submit Feedback</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>

</body>
</html>
<?php
    exit;
}


// SAVE WHEN POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
    $rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
    $feedback = trim($_POST['feedback'] ?? '');

    if ($event_id <= 0 || $rating <= 0 || $feedback === '') {
        header("Location: my_events.php?feedback=invalid");
        exit;
    }

    $checkStmt = $conn->prepare("
        SELECT ep.id
        FROM event_participants ep
        JOIN events e ON ep.event_id = e.id
        WHERE ep.event_id = ?
          AND ep.user_id = ?
          AND ep.participation_status != 'cancelled'
          AND (
              e.event_date < CURDATE()
              OR (
                  e.event_date = CURDATE()
                  AND (e.event_time IS NULL OR e.event_time <= CURTIME())
              )
          )
    ");

    if (!$checkStmt) {
        die("SQL error: " . $conn->error);
    }

    $checkStmt->bind_param("ii", $event_id, $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: my_events.php?feedback=notallowed");
        exit;
    }

    $duplicateStmt = $conn->prepare("
    SELECT feedback_id
    FROM feedback
    WHERE event_id = ? AND user_email = ?
    LIMIT 1
    ");

    if (!$duplicateStmt) {
        die("Duplicate check prepare failed: " . $conn->error);
    }

    $duplicateStmt->bind_param("is", $event_id, $user_email);
    $duplicateStmt->execute();
    $duplicateResult = $duplicateStmt->get_result();

    if ($duplicateResult->num_rows > 0) {
        header("Location: my_events.php?feedback=already_submitted");
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO feedback (event_id, user_email, rating, feedback_text)
        VALUES (?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("Feedback insert prepare failed: " . $conn->error);
    }

    $stmt->bind_param("isis", $event_id, $user_email, $rating, $feedback);

    if (!$stmt->execute()) {
        die("Feedback insert failed: " . $stmt->error);
    }

    $points = 5;
    $eventStmt = $conn->prepare("SELECT event_name FROM events WHERE id = ?");
    $eventStmt->bind_param("i", $event_id);
    $eventStmt->execute();
    $eventResult = $eventStmt->get_result();
    $eventRow = $eventResult->fetch_assoc();

    $eventName = $eventRow ? $eventRow['event_name'] : 'Event';

    $activity = "Feedback submitted for " . $eventName;

    $pointsStmt = $conn->prepare("
        INSERT INTO pointshistory (user_id, pointsHistory_activity, pointsHistory_points, pointsHistory_time)
        VALUES (?, ?, ?, NOW())
    ");

    if (!$pointsStmt) {
        die("Points history prepare failed: " . $conn->error);
    }

    $pointsStmt->bind_param("isi", $user_id, $activity, $points);

    if (!$pointsStmt->execute()) {
        die("Points history insert failed: " . $pointsStmt->error);
    }

    $userPointStmt = $conn->prepare("
        UPDATE `user`
        SET user_point = COALESCE(user_point, 0) + ?
        WHERE user_id = ?
    ");

    if (!$userPointStmt) {
        die("User points update prepare failed: " . $conn->error);
    }

    $userPointStmt->bind_param("ii", $points, $user_id);

    if (!$userPointStmt->execute()) {
        die("User points update failed: " . $userPointStmt->error);
    }

    $eventStmt = $conn->prepare("SELECT event_name FROM events WHERE id = ?");
    $eventStmt->bind_param("i", $event_id);
    $eventStmt->execute();
    $eventResult = $eventStmt->get_result();
    $eventRow = $eventResult->fetch_assoc();
    $eventName = $eventRow ? $eventRow['event_name'] : 'the event';

    $notifTitle = "Feedback Submitted Successfully";
    $notifMessage = "You submitted feedback for " . $eventName . " and earned 5 green points.";

    $notifStmt = $conn->prepare("
        INSERT INTO notifications (user_id, event_id, title, message, is_read)
        VALUES (?, ?, ?, ?, 0)
    ");

    if (!$notifStmt) {
        die("Notification prepare failed: " . $conn->error);
    }

    $notifStmt->bind_param("iiss", $user_id, $event_id, $notifTitle, $notifMessage);

    if (!$notifStmt->execute()) {
        die("Notification insert failed: " . $notifStmt->error);
    }

    header("Location: my_events.php?feedback=success");
    exit;
}

header("Location: my_events.php");
exit;
?>