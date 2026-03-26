<?php
include("session_test.php");
include("config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: Participant.php");
    exit;
}

$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;

if ($event_id <= 0) {
    header("Location: my_events.php");
    exit;
}

/* Get event info */
$eventStmt = $conn->prepare("
    SELECT event_name, event_date, event_location
    FROM events
    WHERE id = ?
");

if (!$eventStmt) {
    die("SQL error: " . $conn->error);
}

$eventStmt->bind_param("i", $event_id);
$eventStmt->execute();
$eventResult = $eventStmt->get_result();

if ($eventResult->num_rows === 0) {
    die("Event not found.");
}

$event = $eventResult->fetch_assoc();

/* Get gallery photos */
$photoStmt = $conn->prepare("
    SELECT ep.photo_id, ep.image_path, ep.user_id, u.user_fullname
    FROM event_photos ep
    LEFT JOIN `user` u ON ep.user_id = u.user_id
    WHERE ep.event_id = ?
    ORDER BY ep.photo_id DESC
");

if (!$photoStmt) {
    die("SQL error: " . $conn->error);
}

$photoStmt->bind_param("i", $event_id);
$photoStmt->execute();
$photoResult = $photoStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Gallery</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }

        .gallery-card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            padding-bottom: 12px;
        }

        .gallery-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
        }

        .gallery-info {
            padding: 12px 15px;
        }

        .gallery-info p {
            margin: 6px 0;
            font-size: 14px;
        }

        .empty-gallery {
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-top: 20px;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .gallery-top-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .my-btn {
            display: inline-block;
            padding: 10px 16px;
            background: #2f7d32;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
        }

        .my-btn:hover {
            background: #256528;
        }
    </style>
</head>
<body>

<?php include("header.php"); ?>

<main class="container">
    <h1 class="page-title">Event Gallery</h1>

    <p><strong>Event:</strong> <?= htmlspecialchars($event['event_name']) ?></p>
    <p><strong>Date:</strong> <?= date("d M Y", strtotime($event['event_date'])) ?></p>
    <p><strong>Location:</strong> <?= htmlspecialchars($event['event_location']) ?></p>

    <?php if (isset($_GET['upload']) && $_GET['upload'] === 'success'): ?>
        <div class="success-message">Photo uploaded successfully and added to gallery.</div>
    <?php endif; ?>

    <div class="gallery-top-actions">
        <a href="upload_event_photo.php?event_id=<?= $event_id ?>" class="my-btn">Upload New Photo</a>
        <a href="my_events.php" class="my-btn">Back to My Events</a>
    </div>

    <?php if ($photoResult->num_rows > 0): ?>
        <div class="gallery-grid">
            <?php while ($photo = $photoResult->fetch_assoc()): ?>
                <div class="gallery-card">
                    <img src="<?= htmlspecialchars($photo['image_path']) ?>" alt="Event Photo">
                    <div class="gallery-info">
                      <p><strong>Uploaded by:</strong> <?= htmlspecialchars($photo['user_fullname'] ?? 'Participant') ?></p>
                      <p>
                          <a href="<?= htmlspecialchars($photo['image_path']) ?>" target="_blank" class="my-btn">View Full Image</a>
                      </p>
                  </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-gallery">
            <p>No photos uploaded for this event yet.</p>
        </div>
    <?php endif; ?>
</main>

<?php include("footer.php"); ?>

</body>
</html>