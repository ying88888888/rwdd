<?php
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int) $_POST['id'];
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $event_location = $_POST['event_location'];
    $event_type = $_POST['eventType'];
    $max_participants = $_POST['max_participants'];
    $description = $_POST['description'];
    $sustainability_goals = $_POST['sustainability_goals'];
    $old_image = $_POST['old_image'];

    $oldEventStmt = $conn->prepare("
        SELECT event_name, event_date, event_time, event_location, status
        FROM events
        WHERE id = ?
        LIMIT 1
    ");
    $oldEventStmt->bind_param("i", $id);
    $oldEventStmt->execute();
    $oldEventResult = $oldEventStmt->get_result();

    if (!$oldEventResult || $oldEventResult->num_rows === 0) {
        die("Event not found.");
    }

    $oldEvent = $oldEventResult->fetch_assoc();
    $currentStatus = $oldEvent['status'];

    $imageName = $old_image;

    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
        $imageName = time() . "_" . basename($_FILES['event_image']['name']);

        move_uploaded_file(
            $_FILES['event_image']['tmp_name'],
            "upload Event/" . $imageName
        );

        if (!empty($old_image) && file_exists("upload Event/" . $old_image)) {
            unlink("upload Event/" . $old_image);
        }
    }

    if ($currentStatus === 'Approved') {
        $sql = "UPDATE events SET
            event_name = '$event_name',
            event_date = '$event_date',
            event_time = '$event_time',
            event_location = '$event_location',
            event_type = '$event_type',
            max_participants = '$max_participants',
            description = '$description',
            sustainability_goals = '$sustainability_goals',
            event_image = '$imageName',
            last_updated_at = NOW(),
            update_notice = 'An approved event was updated by the organizer.',
            show_update_notice = 1
            WHERE id = $id";
    } else {
        $sql = "UPDATE events SET
            event_name = '$event_name',
            event_date = '$event_date',
            event_time = '$event_time',
            event_location = '$event_location',
            event_type = '$event_type',
            max_participants = '$max_participants',
            description = '$description',
            sustainability_goals = '$sustainability_goals',
            event_image = '$imageName'
            WHERE id = $id";
    }

    if (!$conn->query($sql)) {
        die("Failed to update event: " . $conn->error);
    }

    $changes = [];

    if ($oldEvent['event_date'] !== $event_date) {
        $changes[] = "date";
    }

    $oldTime = !empty($oldEvent['event_time']) ? date('H:i', strtotime($oldEvent['event_time'])) : '';
    $newTime = !empty($event_time) ? date('H:i', strtotime($event_time)) : '';

    if ($oldTime !== $newTime) {
        $changes[] = "time";
    }

    if ($oldEvent['event_location'] !== $event_location) {
        $changes[] = "location";
    }

    if (!empty($changes)) {
        if (count($changes) === 1) {
            if ($changes[0] === "date") {
                $notifTitle = "Event Date Changed";
                $notifMessage = "The date for " . $event_name . " has been changed. Please check the updated event details.";
            } elseif ($changes[0] === "time") {
                $notifTitle = "Event Time Changed";
                $notifMessage = "The time for " . $event_name . " has been changed. Please check the updated event details.";
            } else {
                $notifTitle = "Event Location Changed";
                $notifMessage = "The location for " . $event_name . " has been changed. Please check the updated event details.";
            }
        } else {
            $notifTitle = "Event Updated";
            $notifMessage = $event_name . " has updated its " . implode(" and ", $changes) . ". Please check the latest event details.";
        }

        $participantStmt = $conn->prepare("
            SELECT user_id
            FROM event_participants
            WHERE event_id = ?
              AND participation_status = 'joined'
        ");
        $participantStmt->bind_param("i", $id);
        $participantStmt->execute();
        $participantResult = $participantStmt->get_result();

        while ($participant = $participantResult->fetch_assoc()) {
            $target_user_id = (int) $participant['user_id'];

            $notifStmt = $conn->prepare("
                INSERT INTO notifications (user_id, event_id, title, message, is_read)
                VALUES (?, ?, ?, ?, 0)
            ");
            $notifStmt->bind_param("iiss", $target_user_id, $id, $notifTitle, $notifMessage);
            $notifStmt->execute();
        }
    }

    header("Location: event_details.php?id=$id");
    exit;
}
?>