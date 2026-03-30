<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$organizer_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Event not found.");
}

$event_id = (int) $_GET['id'];

// Check event belongs to organizer
$sql = "SELECT * FROM events WHERE id = ? AND organizer_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("ii", $event_id, $organizer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Event not found or access denied.");
}

$event = $result->fetch_assoc();
$stmt->close();

// Read participant + user data
$sql2 = "
    SELECT 
        ep.user_id,
        ep.participation_status,
        ep.attendance_status,
        u.user_fullname,
        u.user_email,
        u.user_phoneNumber
    FROM event_participants ep
    JOIN user u ON ep.user_id = u.user_id
    WHERE ep.event_id = ?
";

$stmt2 = $conn->prepare($sql2);

if (!$stmt2) {
    die("Prepare failed: " . $conn->error);
}

$stmt2->bind_param("i", $event_id);
$stmt2->execute();
$result2 = $stmt2->get_result();

// Force download as txt file
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="attendance_event_' . $event_id . '.txt"');

// File content
echo "===== ATTENDANCE LIST =====\n\n";
echo "Event Name : " . $event['event_name'] . "\n";
echo "Date       : " . $event['event_date'] . "\n";
echo "Time       : " . $event['event_time'] . "\n";
echo "Location   : " . $event['event_location'] . "\n";
echo "-----------------------------------------------\n\n";

printf("%-5s %-20s %-30s %-12s %-12s\n", "No", "Name", "Email", "Join Status", "Attendance");
echo str_repeat("=", 90) . "\n";

$count = 1;

if ($result2->num_rows > 0) {
    while ($row = $result2->fetch_assoc()) {
        $name = $row['user_fullname'];
        $email = $row['user_email'];
        $join_status = $row['participation_status'];
        $attendance = $row['attendance_status'];

        printf(
            "%-5s %-20s %-30s %-12s %-12s\n",
            $count,
            $name,
            $email,
            $join_status,
            $attendance
        );

        $count++;
    }
} else {
    echo "No participants found.\n";
}

$stmt2->close();
$conn->close();
?>