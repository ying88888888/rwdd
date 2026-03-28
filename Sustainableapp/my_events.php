<?php
include("session_test.php");
include("config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: Participant.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT 
        ep.participation_status,
        ep.attendance_status,
        e.id AS event_id,
        e.event_name,
        e.description,
        e.event_date,
        e.event_time,
        e.event_location,
        e.organizer_id,
        u.user_fullname AS organizer_name
    FROM event_participants ep
    JOIN events e ON ep.event_id = e.id
    LEFT JOIN `user` u ON e.organizer_id = u.user_id
    WHERE ep.user_id = ?
      AND ep.participation_status != 'cancelled'
    ORDER BY e.event_date DESC
");

if (!$stmt) {
    die("SQL error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Events</title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<?php include("header.php"); ?>

<main class="container">

<!-- this one for showing submitting again noti -->
<?php if (isset($_GET['feedback'])): ?>

    <?php if ($_GET['feedback'] === 'success'): ?>
        <div class="success-message">
            ✅ Feedback submitted successfully! +5 points added.
        </div>

    <?php elseif ($_GET['feedback'] === 'already_submitted'): ?>
        <div class="success-message">
            ⚠️ You have already submitted feedback for this event.
        </div>

    <?php elseif ($_GET['feedback'] === 'notallowed'): ?>
        <div class="success-message">
            ❌ You are not allowed to submit feedback for this event.
        </div>

    <?php elseif ($_GET['feedback'] === 'invalid'): ?>
        <div class="success-message">
            ❌ Failed to submit feedback. Please try again.
        </div>

    <?php endif; ?>

<?php endif; ?>

<h1 class="page-title">My Events</h1>

<?php if (isset($_GET['upload']) && $_GET['upload'] === 'success'): ?>
<div class="success-message">
    Photo uploaded successfully!
</div>
<?php endif; ?>

<div class="my-tabs">
    <button class="my-tab active" data-filter="all">All</button>
    <button class="my-tab" data-filter="upcoming">Upcoming</button>
    <button class="my-tab" data-filter="ongoing">Ongoing</button>
    <button class="my-tab" data-filter="completed">Completed</button>
</div>

<section class="my-grid">

<?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>

        <?php
        $currentTimestamp = time();
        $eventTimestamp = strtotime($row['event_date'] . ' ' . (!empty($row['event_time']) ? $row['event_time'] : '23:59:59'));

        if ($eventTimestamp < $currentTimestamp) {
            $displayStatus = "completed";
        } elseif ($row['event_date'] === date("Y-m-d")) {
            $displayStatus = "ongoing";
        } else {
            $displayStatus = "upcoming";
        }
        ?>

        <article class="my-card" data-status="<?= $displayStatus ?>">

            <div class="my-img">
                <span class="my-badge"><?= ucfirst($displayStatus) ?></span>
            </div>

            <div class="my-body">

                <h2><?= htmlspecialchars($row['event_name']) ?></h2>
                <p><?= htmlspecialchars($row['description']) ?></p>

                <ul class="my-meta">
                    <li><strong>Date:</strong> <?= date("d M Y", strtotime($row['event_date'])) ?></li>
                    <li><strong>Location:</strong> <?= htmlspecialchars($row['event_location']) ?></li>
                    <li><strong>Organizer:</strong> <?= htmlspecialchars($row['organizer_name'] ?? 'Organizer') ?></li>
                </ul>

                <a href="eventdetails.php?id=<?= (int)$row['event_id'] ?>" class="my-btn">View Details</a>

                <?php if ($displayStatus === 'completed'): ?>

                    <button 
                        type="button"
                        class="my-btn"
                        onclick="openFeedbackModal(<?= (int)$row['event_id'] ?>, '<?= htmlspecialchars(addslashes($row['event_name'])) ?>')">
                        Submit Feedback
                    </button>

                    <button 
                        type="button"
                        class="my-btn"
                        onclick="openUploadModal(<?= (int)$row['event_id'] ?>, '<?= htmlspecialchars(addslashes($row['event_name'])) ?>', '<?= date("d M Y", strtotime($row['event_date'])) ?>', '<?= htmlspecialchars(addslashes($row['organizer_name'] ?? 'Organizer')) ?>')">
                        Upload Photo
                    </button>

                <?php else: ?>

                    <button class="my-btn disabled-btn" disabled>Submit Feedback</button>
                    <button class="my-btn disabled-btn" disabled>Upload Photo</button>

                <?php endif; ?>

                <a href="gallery_view.php?event_id=<?= (int)$row['event_id'] ?>" class="my-btn">
                    Gallery
                </a>

            </div>
        </article>

    <?php endwhile; ?>
<?php else: ?>
    <p>No events found.</p>
<?php endif; ?>

</section>

</main>

<!-- Feedback Modal -->
<div id="feedbackModal" class="custom-modal">
    <div class="custom-modal-box feedback-box">
        <span class="modal-close" onclick="closeFeedbackModal()">&times;</span>
        <h2>Submit Event Feedback & Rating</h2>
        <p class="modal-subtitle">Share your experience and earn 5 Green Points!</p>

        <form action="submit_feedback.php" method="POST">
            <input type="hidden" name="event_id" id="feedback_event_id">

            <label>Rating</label>
            <div class="star-rating">
            <input type="radio" name="rating" id="star5" value="5" required>
            <label for="star5">★</label>

            <input type="radio" name="rating" id="star4" value="4">
            <label for="star4">★</label>

            <input type="radio" name="rating" id="star3" value="3">
            <label for="star3">★</label>

            <input type="radio" name="rating" id="star2" value="2">
            <label for="star2">★</label>

            <input type="radio" name="rating" id="star1" value="1">
            <label for="star1">★</label>
        </div>

            <label for="feedback_text">Your Feedback</label>
            <textarea name="feedback" id="feedback_text" placeholder="Share your experience..." required></textarea>

            <div class="modal-actions">
                <button type="button" class="modal-btn" onclick="closeFeedbackModal()">Cancel</button>
                <button type="submit" class="modal-btn primary-btn">Submit Feedback</button>
            </div>
        </form>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="custom-modal">
    <div class="custom-modal-box upload-box">
        <span class="modal-close" onclick="closeUploadModal()">&times;</span>
        <h2>Upload Event Photo</h2>

        <div class="upload-event-info">
            <p><strong>Event Name:</strong> <span id="upload_event_name"></span></p>
            <p><strong>Event Date:</strong> <span id="upload_event_date"></span></p>
            <p><strong>Organizer:</strong> <span id="upload_organizer"></span></p>
        </div>

        <form action="upload_event_photo.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="event_id" id="upload_event_id">

            <label>Upload your Photo here</label>
            <div class="upload-area">
                <input type="file" name="image" id="upload_image" accept=".jpg,.jpeg,.png,.gif" required>
            </div>

            <div class="modal-actions">
                <button type="button" class="modal-btn" onclick="closeUploadModal()">Cancel</button>
                <button type="submit" class="modal-btn primary-btn">Upload Photo</button>
            </div>
        </form>
    </div>
</div>

<?php include("footer.php"); ?>

<script>
function openFeedbackModal(eventId, eventName) {
    document.getElementById('feedback_event_id').value = eventId;
    document.getElementById('feedbackModal').style.display = 'flex';
}

function closeFeedbackModal() {
    document.getElementById('feedbackModal').style.display = 'none';
}

function openUploadModal(eventId, eventName, eventDate, organizerName) {
    document.getElementById('upload_event_id').value = eventId;
    document.getElementById('upload_event_name').textContent = eventName;
    document.getElementById('upload_event_date').textContent = eventDate;
    document.getElementById('upload_organizer').textContent = organizerName;
    document.getElementById('uploadModal').style.display = 'flex';
}

function closeUploadModal() {
    document.getElementById('uploadModal').style.display = 'none';
}

window.onclick = function(event) {
    const feedbackModal = document.getElementById('feedbackModal');
    const uploadModal = document.getElementById('uploadModal');

    if (event.target === feedbackModal) {
        closeFeedbackModal();
    }

    if (event.target === uploadModal) {
        closeUploadModal();
    }
};
</script>

<script src="js/main.js"></script>

</body>
</html>