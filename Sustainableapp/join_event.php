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

/* Check event exists and is approved */
$checkEvent = $conn->prepare("
    SELECT id, event_name, max_participants
    FROM events
    WHERE id = ? AND status = 'Approved'
    LIMIT 1
");
$checkEvent->bind_param("i", $event_id);
$checkEvent->execute();
$eventResult = $checkEvent->get_result();

if (!$eventResult || $eventResult->num_rows === 0) {
    header("Location: eventdetails.php?id=$event_id&join=invalid");
    exit;
}

$eventRow = $eventResult->fetch_assoc();
$eventName = $eventRow['event_name'];
$maxParticipants = (int) $eventRow['max_participants'];

/* Check if user already has record */
$checkJoin = $conn->prepare("
    SELECT id, participation_status
    FROM event_participants
    WHERE event_id = ? AND user_id = ?
    LIMIT 1
");
$checkJoin->bind_param("ii", $event_id, $user_id);
$checkJoin->execute();
$joinResult = $checkJoin->get_result();

if ($joinResult && $joinResult->num_rows > 0) {
    $existingRow = $joinResult->fetch_assoc();
    $currentStatus = $existingRow['participation_status'];

    if ($currentStatus === 'cancelled') {
        /* Check capacity before rejoining */
        $countStmt = $conn->prepare("
            SELECT COUNT(*) AS total_joined
            FROM event_participants
            WHERE event_id = ?
            AND participation_status = 'joined'
        ");
        $countStmt->bind_param("i", $event_id);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $countRow = $countResult->fetch_assoc();
        $currentJoined = (int) $countRow['total_joined'];

        if ($currentJoined >= $maxParticipants) {
            header("Location: eventdetails.php?id=$event_id&join=full");
            exit;
        }

        $rejoinStmt = $conn->prepare("
            UPDATE event_participants
            SET participation_status = 'joined',
                attendance_status = 'pending',
                joined_at = NOW()
            WHERE event_id = ? AND user_id = ?
        ");
        $rejoinStmt->bind_param("ii", $event_id, $user_id);

        if ($rejoinStmt->execute()) {
            header("Location: eventdetails.php?id=$event_id&join=success");
            exit;
        } else {
            header("Location: eventdetails.php?id=$event_id&join=error");
            exit;
        }
    } else {
        header("Location: eventdetails.php?id=$event_id&join=already");
        exit;
    }
}

/* Count joined participants */
$countStmt = $conn->prepare("
    SELECT COUNT(*) AS total_joined
    FROM event_participants
    WHERE event_id = ?
    AND participation_status = 'joined'
");
$countStmt->bind_param("i", $event_id);
$countStmt->execute();
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();

$currentJoined = (int) $countRow['total_joined'];

if ($currentJoined >= $maxParticipants) {
    header("Location: eventdetails.php?id=$event_id&join=full");
    exit;
}

/* Insert new join record */
$insertStmt = $conn->prepare("
    INSERT INTO event_participants (event_id, user_id, joined_at, participation_status, attendance_status)
    VALUES (?, ?, NOW(), 'joined', 'pending')
");
$insertStmt->bind_param("ii", $event_id, $user_id);

if ($insertStmt->execute()) {

    $title = "Event Joined Successfully";
    $message = "You have successfully joined " . $eventName . ".";

    $notifStmt = $conn->prepare("
        INSERT INTO notifications (user_id, event_id, title, message, is_read)
        VALUES (?, ?, ?, ?, 0)
    ");
    $notifStmt->bind_param("iiss", $user_id, $event_id, $title, $message);
    $notifStmt->execute();

    header("Location: eventdetails.php?id=$event_id&join=success");
    exit;
} else {
    header("Location: eventdetails.php?id=$event_id&join=error");
    exit;
}
?>