<?php
include("session_test.php");
include("config.php");

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$event = null;

$current_joined = 0;
$spots_left = 0;
$progress_percent = 0;
$hasJoined = false;

if ($event_id > 0) {
    $eventStmt = $conn->prepare("
        SELECT *
        FROM events
        WHERE id = ? AND status = 'Approved'
        LIMIT 1
    ");
    $eventStmt->bind_param("i", $event_id);
    $eventStmt->execute();
    $eventResult = $eventStmt->get_result();

    if ($eventResult && $eventResult->num_rows > 0) {
        $event = $eventResult->fetch_assoc();
    }
}

if ($event) {
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

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

    $current_joined = (int)$countRow['total_joined'];

    $maxParticipants = (int)$event['max_participants'];
    $spots_left = $maxParticipants - $current_joined;

    if ($spots_left < 0) {
        $spots_left = 0;
    }

    if ($maxParticipants > 0) {
        $progress_percent = round(($current_joined / $maxParticipants) * 100);
    }

    /* Check if current user already joined */
    if ($user_id > 0 && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Participant') {
        $joinCheckStmt = $conn->prepare("
            SELECT participation_status
            FROM event_participants
            WHERE event_id = ? AND user_id = ?
            LIMIT 1
        ");
        $joinCheckStmt->bind_param("ii", $event_id, $user_id);
        $joinCheckStmt->execute();
        $joinCheckResult = $joinCheckStmt->get_result();

        if ($joinCheckResult && $joinCheckResult->num_rows > 0) {
            $joinRow = $joinCheckResult->fetch_assoc();

            if ($joinRow['participation_status'] === 'joined') {
                $hasJoined = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EcoEvents | Event Details</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>

<?php include("header.php"); ?>

<main>
    <div class="container">

        <a class="back-link" href="events.php">← Back to Events</a>

        <?php if (isset($_GET['join'])): ?>
            <?php if ($_GET['join'] === 'success'): ?>
                <p style="color: green; font-weight: bold;">You have successfully joined this event.</p>
            <?php elseif ($_GET['join'] === 'already'): ?>
                <p style="color: orange; font-weight: bold;">You already joined this event.</p>
            <?php elseif ($_GET['join'] === 'full'): ?>
                <p style="color: red; font-weight: bold;">Sorry, this event is already full.</p>
            <?php elseif ($_GET['join'] === 'error'): ?>
                <p style="color: red; font-weight: bold;">Something went wrong. Please try again.</p>
            <?php elseif ($_GET['join'] === 'invalid'): ?>
                <p style="color: red; font-weight: bold;">Invalid event.</p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($_GET['cancel'])): ?>
            <?php if ($_GET['cancel'] === 'success'): ?>
                <p style="color: orange; font-weight: bold;">You cancelled your participation.</p>
            <?php elseif ($_GET['cancel'] === 'error'): ?>
                <p style="color: red; font-weight: bold;">Unable to cancel participation.</p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($event): ?>
            <section class="event-details">

                <div class="event-left">

                    <div class="event-image-box">
                        <?php if (!empty($event['event_image'])): ?>
                            <img
                                src="upload Event/<?= rawurlencode($event['event_image']) ?>"
                                alt="<?= htmlspecialchars($event['event_name']) ?>"
                                onerror="this.style.display='none'; this.parentElement.innerHTML='No Image';"
                            >
                        <?php else: ?>
                            <div>No Image</div>
                        <?php endif; ?>
                    </div>

                    <div class="card about-card">
                        <h2>About this Event</h2>
                        <p id="eventDesc">
                            <?= htmlspecialchars($event['description']) ?>
                        </p>
                    </div>

                    <div class="card points-card">
                        <div class="points-icon"></div>
                        <div class="points-text" id="eventPoints">
                            Event Participation
                        </div>
                    </div>
                </div>

                <div class="event-right">
                    <div class="card details-card">

                <div class="detail-row">
                    <div class="detail-icon">📅</div>
                    <div>
                        <div class="detail-label">Date</div>
                        <div class="detail-value" id="eventDate">
                            <?= date("d M Y", strtotime($event['event_date'])) ?>
                        </div>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-icon">⏰</div>
                    <div>
                        <div class="detail-label">Time</div>
                        <div class="detail-value" id="eventTime">
                            <?= date("g:i A", strtotime($event['event_time'])) ?>
                        </div>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-icon">📍</div>
                    <div>
                        <div class="detail-label">Location</div>
                        <div class="detail-value" id="eventLocation">
                            <?= htmlspecialchars($event['event_location']) ?>
                        </div>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-icon">👤</div>
                    <div>
                        <div class="detail-label">Organizer</div>
                        <div class="detail-value" id="eventOrganizer">
                            Organizer
                        </div>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-icon">🏷️</div>
                    <div>
                        <div class="detail-label">Event Category</div>
                        <div class="detail-value" id="eventCategory">
                            <?= htmlspecialchars($event['event_type']) ?>
                        </div>
                    </div>
                </div>
                        <hr class="divider">

                        <div class="capacity">
                            <div class="capacity-top">
                                <span id="eventCount"><?= $current_joined ?></span>/<span id="eventCapacity"><?= (int)$event['max_participants'] ?></span>
                            </div>

                            <div class="capacity-bar">
                                <div class="capacity-fill" id="progressFill" style="width:<?= $progress_percent ?>%"></div>
                            </div>

                            <div class="capacity-bottom" id="spotsText">
                                <?= $spots_left ?> spots available
                            </div>
                        </div>

                        <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Participant'): ?>

                            <?php if ($hasJoined): ?>
                                <button class="btn join-btn" type="button" disabled>
                                    Joined
                                </button>

                                <form action="cancel_event.php" method="POST" style="margin-top:10px;">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <button class="btn cancel-btn" type="submit">
                                        Cancel Participation
                                    </button>
                                </form>
                            <?php else: ?>
                                <form action="join_event.php" method="POST" style="margin-top:14px;">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <button class="btn join-btn" type="submit">
                                        Join this Event
                                    </button>
                                </form>
                            <?php endif; ?>

                        <?php endif; ?>

                    </div>

                    <div class="card goals-card">
                        <h2>Sustainability Goals</h2>
                        <ol id="goalsList">
                            <li><?= htmlspecialchars($event['sustainability_goals']) ?></li>
                        </ol>
                    </div>
                </div>

            </section>
        <?php else: ?>
            <h1>Event not found.</h1>
        <?php endif; ?>

    </div>
</main>

<script src="js/main.js"></script>
</body>
</html>