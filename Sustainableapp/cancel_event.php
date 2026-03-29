<?php
include("session_test.php");
include("config.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: events.php");
    exit;
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Participant') {
    header("Location: events.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;

if ($event_id <= 0) {
    header("Location: events.php");
    exit;
}

$stmt = $conn->prepare("
    UPDATE event_participants
    SET participation_status = 'cancelled'
    WHERE event_id = ?
    AND user_id = ?
    AND participation_status = 'joined'
");
$stmt->bind_param("ii", $event_id, $user_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    header("Location: eventdetails.php?id=$event_id&cancel=success");
    exit;
} else {
    header("Location: eventdetails.php?id=$event_id&cancel=error");
    exit;
}
?>