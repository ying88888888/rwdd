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
    ORDER BY ep.uploaded_at DESC
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
    </style>
</head>
<body>

<?php include("header.php"); ?>

<main class="container">
    <h1 class="page-title">Event Gallery</h1>

    <p><strong>Event:</strong> <?= htmlspecialchars($event['event_name']) ?></p>
    <p><strong>Date:</strong> <?= date("d M Y", strtotime($event['event_date'])) ?></p>
    <p><strong>Location:</strong> <?= htmlspecialchars($event['event_location']) ?></p>

<?php if ($photoResult->num_rows > 0): ?>
    <?php
    $photos = [];
    while ($photo = $photoResult->fetch_assoc()) {
        $photos[] = $photo;
    }
    ?>

    <div class="album-slider">
        <div class="slider-card">
            <img id="sliderImage" src="<?= htmlspecialchars($photos[0]['image_path']) ?>" alt="Event Photo">

            <?php if (count($photos) > 1): ?>
                <button class="slider-arrow left" id="prevBtn">&#10094;</button>
                <button class="slider-arrow right" id="nextBtn">&#10095;</button>
            <?php endif; ?>

            <div class="gallery-info">
                <p>
                    <strong>Uploaded by:</strong>
                    <span id="sliderUploader"><?= htmlspecialchars($photos[0]['user_fullname'] ?? 'Participant') ?></span>
                </p>
                <p>
                    <a id="fullImageLink" href="<?= htmlspecialchars($photos[0]['image_path']) ?>" target="_blank" class="my-btn">
                        View Full Image
                    </a>
                </p>
            </div>
        </div>
    </div>

    <div class="slider-counter">
        Photo <span id="currentIndex">1</span> of <span id="totalPhotos"><?= count($photos) ?></span>
    </div>

    <script>
        const photos = <?= json_encode($photos, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        let currentPhotoIndex = 0;

        const sliderImage = document.getElementById('sliderImage');
        const sliderUploader = document.getElementById('sliderUploader');
        const fullImageLink = document.getElementById('fullImageLink');
        const currentIndexText = document.getElementById('currentIndex');

        function updateSlider() {
            sliderImage.src = photos[currentPhotoIndex].image_path;
            sliderUploader.textContent = photos[currentPhotoIndex].user_fullname ?? 'Participant';
            fullImageLink.href = photos[currentPhotoIndex].image_path;
            currentIndexText.textContent = currentPhotoIndex + 1;
        }

        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                currentPhotoIndex--;
                if (currentPhotoIndex < 0) {
                    currentPhotoIndex = photos.length - 1;
                }
                updateSlider();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                currentPhotoIndex++;
                if (currentPhotoIndex >= photos.length) {
                    currentPhotoIndex = 0;
                }
                updateSlider();
            });
        }
    </script>

<?php else: ?>
    <div class="empty-gallery">
        <p>No photos uploaded for this event yet.</p>
    </div>
<?php endif; ?>
</main>

<?php include("footer.php"); ?>

</body>
</html>