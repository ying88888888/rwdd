<?php
session_start();
include("config.php");

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

if (!isset($_SESSION['user_role'])) {
    die("Access denied. user_role session not found.");
}

$role = strtolower(trim($_SESSION['user_role']));
if ($role !== 'organizer' && $role !== 'event organizer') {
    die("Access denied. This page is for organizers only.");
}

$organizer_id = (int) $_SESSION['user_id'];

include("header.php");

/*
feedback table:
- feedback_id
- event_id
- user_id
- rating
- feedback_text
- submitted_at

events table:
- id
- organizer_id
- event_name

user table:
- user_id
- user_fullname
- user_email
- user_profilePicture
*/

$sql = "
    SELECT
        f.feedback_id,
        f.event_id,
        f.rating,
        f.feedback_text,
        f.submitted_at,
        e.id AS event_real_id,
        e.event_name,
        e.organizer_id,
        u.user_fullname,
        u.user_email,
        u.user_profilePicture
    FROM feedback f
    INNER JOIN events e ON f.event_id = e.id
    INNER JOIN `user` u ON f.user_email = u.user_email
    WHERE e.organizer_id = ?
    ORDER BY f.submitted_at DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL error: " . $conn->error);
}

$stmt->bind_param("i", $organizer_id);
$stmt->execute();

$stmt->bind_result(
    $feedback_id,
    $event_id,
    $rating,
    $feedback_text,
    $submitted_at,
    $event_real_id,
    $event_name,
    $event_organizer_id,
    $user_fullname,
    $user_email,
    $user_profilePicture
);

$feedbacks = [];

while ($stmt->fetch()) {
    $feedbacks[] = [
        "feedback_id" => $feedback_id,
        "event_id" => $event_id,
        "rating" => $rating,
        "feedback_text" => $feedback_text,
        "submitted_at" => $submitted_at,
        "event_real_id" => $event_real_id,
        "event_name" => $event_name,
        "organizer_id" => $event_organizer_id,
        "user_fullname" => $user_fullname,
        "user_email" => $user_email,
        "user_profilePicture" => $user_profilePicture
    ];
}

/* Summary */
$total = count($feedbacks);
$avg_rating = $total > 0 ? round(array_sum(array_column($feedbacks, "rating")) / $total, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Management</title>
    <link rel="stylesheet" href="CSS/style.css">
    <style>
        .fb-admin-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .fb-stat-box {
            flex: 1;
            min-width: 220px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 18px;
            text-align: center;
        }

        .fb-stat-num {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
        }

        .fb-stat-label {
            margin-top: 6px;
            font-size: 14px;
            color: #6b7280;
        }

        .fb-admin-header {
            background: #f3f4f6;
            font-weight: 700;
        }

        .fb-admin-row {
            display: grid;
            grid-template-columns: 220px 180px 130px 1fr 170px;
            gap: 16px;
            align-items: center;
            padding: 16px 18px;
            border-bottom: 1px solid #e5e7eb;
        }

        .fb-col-name {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .fb-avatar-img {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .fb-name-wrap {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .fb-name-wrap strong,
        .fb-name-wrap span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .feedback-stars {
            color: #f59e0b;
            font-size: 16px;
            letter-spacing: 1px;
        }

        .fb-rating-num {
            margin-left: 6px;
            color: #6b7280;
            font-size: 13px;
        }

        .fb-col-comment {
            color: #374151;
            line-height: 1.5;
            word-break: break-word;
        }

        .fb-col-date {
            font-size: 14px;
            color: #6b7280;
        }

        @media (max-width: 1000px) {
            .fb-admin-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .fb-admin-header {
                display: none;
            }

            .fb-admin-row > div::before {
                display: block;
                font-size: 12px;
                font-weight: 700;
                color: #6b7280;
                margin-bottom: 4px;
                text-transform: uppercase;
                letter-spacing: .04em;
            }

            .fb-col-name::before { content: "Participant"; }
            .fb-col-event::before { content: "Event"; }
            .fb-col-rating::before { content: "Rating"; }
            .fb-col-comment::before { content: "Comment"; }
            .fb-col-date::before { content: "Date"; }
        }
    </style>
</head>
<body >

<div class="Dashboard">
<main class="container profile-page" style="max-width: 1100px;">

    <h1 class="profile-title">Feedback Management</h1>

    <section class="profile-card">

        <div class="fb-admin-stats">
            <div class="fb-stat-box">
                <div class="fb-stat-num"><?php echo $total; ?></div>
                <div class="fb-stat-label">Total Feedback</div>
            </div>

            <div class="fb-stat-box">
                <div class="fb-stat-num">
                    <?php echo $avg_rating; ?>
                    <span style="font-size:20px; color:#f59e0b;">★</span>
                </div>
                <div class="fb-stat-label">Average Rating</div>
            </div>
        </div>

        <div class="profile-block">
            <div class="profile-block-title">All Feedback</div>
            <div class="profile-block-body" style="gap: 0; padding: 0;">

                <?php if (empty($feedbacks)): ?>
                    <div style="padding: 24px; text-align: center; color: #888; font-size: 14px;">
                        No feedback submitted yet for your events.
                    </div>
                <?php else: ?>

                    <div class="fb-admin-row fb-admin-header">
                        <div class="fb-col-name">Participant</div>
                        <div class="fb-col-event">Event</div>
                        <div class="fb-col-rating">Rating</div>
                        <div class="fb-col-comment">Comment</div>
                        <div class="fb-col-date">Date</div>
                    </div>

                    <?php foreach ($feedbacks as $fb): ?>
                        <?php
                            $avatar = !empty($fb["user_profilePicture"])
                                ? "uploads/profile/" . $fb["user_profilePicture"]
                                : "Image/defaultProfile.png";
                        ?>
                        <div class="fb-admin-row">

                            <div class="fb-col-name">
                                <img src="<?php echo htmlspecialchars($avatar); ?>"
                                     alt="Participant"
                                     class="fb-avatar-img"
                                     onerror="this.src='defaultProfile.png'">

                                <div class="fb-name-wrap">
                                    <strong><?php echo htmlspecialchars($fb["user_fullname"]); ?></strong>
                                    <span><?php echo htmlspecialchars($fb["user_email"]); ?></span>
                                </div>
                            </div>

                            <div class="fb-col-event">
                                <?php echo htmlspecialchars($fb["event_name"]); ?>
                            </div>

                            <div class="fb-col-rating">
                                <span class="feedback-stars">
                                    <?php for ($i = 1; $i <= 5; $i++) echo $i <= (int)$fb["rating"] ? "★" : "☆"; ?>
                                </span>
                                <span class="fb-rating-num">(<?php echo (int)$fb["rating"]; ?>)</span>
                            </div>

                            <div class="fb-col-comment">
                                "<?php echo htmlspecialchars($fb["feedback_text"]); ?>"
                            </div>

                            <div class="fb-col-date">
                                <?php echo htmlspecialchars($fb["submitted_at"]); ?>
                            </div>

                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>

            </div>
        </div>

        <div class="profile-actions">
            <a href="dashboard.php" class="profile-action-btn">Back to Dashboard</a>
        </div>

    </section>

</main>
</div>
<?php include("footer.php"); ?>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>