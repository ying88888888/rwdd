<?php
include("session_test.php");
include("config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: Participant.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

/* -------------------------
   SHOW FORM WHEN GET
-------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;

    if ($event_id <= 0) {
        header("Location: my_events.php?upload=invalid");
        exit;
    }

    $checkStmt = $conn->prepare("
        SELECT ep.id
        FROM event_participants ep
        JOIN events e ON ep.event_id = e.id
        WHERE ep.event_id = ?
          AND ep.user_id = ?
          AND ep.participation_status != 'cancelled'
          AND CONCAT(e.event_date, ' ', COALESCE(e.event_time, '23:59:59')) < NOW()
    ");

    if (!$checkStmt) {
        die("SQL error: " . $conn->error);
    }

    $checkStmt->bind_param("ii", $event_id, $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: my_events.php?upload=notallowed");
        exit;
    }
    $eventStmt = $conn->prepare("
    SELECT e.event_name, e.event_date, u.user_fullname AS organizer_name
    FROM events e
    LEFT JOIN `user` u ON e.organizer_id = u.user_id
    WHERE e.id = ?
");

if (!$eventStmt) {
    die("SQL error: " . $conn->error);
}

$eventStmt->bind_param("i", $event_id);
$eventStmt->execute();
$eventResult = $eventStmt->get_result();
$eventData = $eventResult->fetch_assoc();

$event_name = $eventData['event_name'] ?? 'Event Name';
$event_date = !empty($eventData['event_date']) ? date("d M Y", strtotime($eventData['event_date'])) : 'Event Date';
$organizer_name = $eventData['organizer_name'] ?? 'Organizer';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Event Photo</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .page-overlay {
            min-height: 100vh;
            background: rgba(0,0,0,0.18);
            padding: 40px 20px;
        }

        .upload-modal {
            max-width: 790px;
            margin: 30px auto;
            background: #7f7f7f;
            color: #fff;
            border-radius: 4px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .upload-header {
            padding: 24px 34px;
            border-bottom: 1px solid rgba(255,255,255,0.4);
            position: relative;
        }

        .upload-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 500;
            color: #fff;
        }

        .close-box {
            position: absolute;
            top: 18px;
            right: 18px;
            width: 24px;
            height: 24px;
            background: #e5e5e5;
            border-radius: 2px;
        }

        .upload-body {
            padding: 24px 34px 28px;
        }

        .event-info {
            margin-bottom: 24px;
            line-height: 2;
            font-size: 16px;
        }

        .event-info strong {
            display: inline-block;
            width: 130px;
            font-weight: 500;
        }

        .section-title {
            font-size: 18px;
            margin: 22px 0 14px;
            color: #fff;
        }

        .upload-dropzone {
            background: #efefef;
            padding: 30px 20px;
            text-align: center;
            border-radius: 2px;
            margin-bottom: 18px;
            color: #111;
        }

        .upload-dropzone p {
            margin: 0 0 14px;
            font-size: 18px;
            line-height: 1.4;
        }

        .browse-btn {
            display: inline-block;
            padding: 8px 26px;
            background: #bdbdbd;
            color: #111;
            border-radius: 2px;
            font-weight: 500;
            cursor: pointer;
        }

        .hidden-file {
            display: none;
        }

        .file-note {
            margin-top: 10px;
            font-size: 14px;
            color: #f0f0f0;
        }

        .upload-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            padding: 20px 34px 30px;
            border-top: 1px solid rgba(255,255,255,0.4);
        }

        .modal-btn {
            min-width: 150px;
            padding: 10px 18px;
            border: none;
            border-radius: 2px;
            background: #e5e5e5;
            color: #111;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }
    </style>
</head>
<body>

<?php include("header.php"); ?>

<div class="page-overlay">
    <div class="upload-modal">
        <div class="upload-header">
            <h1>Upload Event Photo</h1>
            <div class="close-box"></div>
        </div>

        <form action="upload_event_photo.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="event_id" value="<?= $event_id ?>">

            <div class="upload-body">
                <div class="event-info">
                    <div><strong>Event Name:</strong> <?= htmlspecialchars($event_name ?? 'Event Name') ?></div>
                    <div><strong>Event Date:</strong> <?= htmlspecialchars($event_date ?? 'Event Date') ?></div>
                    <div><strong>Organizer:</strong> <?= htmlspecialchars($organizer_name ?? 'Organizer') ?></div>
                </div>

                <div class="section-title">Upload your Photo here</div>

                <div class="upload-dropzone">
                    <p>Drag and drop your photo here or click Browse<br>to select file (JPG, PNG, GIF, etc)</p>
                    <label for="image" class="browse-btn">Browse</label>
                    <input type="file" name="image" id="image" class="hidden-file" accept=".jpg,.jpeg,.png,.gif" required>
                    <div class="file-note" id="file-name">No file chosen</div>
                </div>
            </div>

            <div class="upload-actions">
                <button type="submit" class="modal-btn">Upload Photo</button>
                <a href="gallery_view.php?event_id=<?= $event_id ?>" class="modal-btn">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include("footer.php"); ?>

<script>
document.getElementById('image').addEventListener('change', function () {
    const fileName = this.files.length ? this.files[0].name : 'No file chosen';
    document.getElementById('file-name').textContent = fileName;
});
</script>

</body>
</html>
<?php
exit;
}

/* -------------------------
   SAVE WHEN POST
-------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;

    if ($event_id <= 0) {
        header("Location: my_events.php?upload=invalid");
        exit;
    }

    $checkStmt = $conn->prepare("
        SELECT ep.event_id
        FROM event_participants ep
        JOIN events e ON ep.event_id = e.id
        WHERE ep.event_id = ?
          AND ep.user_id = ?
          AND ep.participation_status != 'cancelled'
          AND CONCAT(e.event_date, ' ', COALESCE(e.event_time, '23:59:59')) < NOW()
    ");

    if (!$checkStmt) {
        die("SQL error: " . $conn->error);
    }

    $checkStmt->bind_param("ii", $event_id, $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: my_events.php?upload=notallowed");
        exit;
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
        header("Location: upload_event_photo.php?event_id=$event_id&upload=nofile");
        exit;
    }

    $uploadDir = "uploads/images/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES['image']['name']);
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($extension, $allowed)) {
        header("Location: upload_event_photo.php?event_id=$event_id&upload=type");
        exit;
    }

    $newName = uniqid() . "_" . preg_replace("/[^A-Za-z0-9._-]/", "_", $fileName);
    $targetPath = $uploadDir . $newName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        header("Location: upload_event_photo.php?event_id=$event_id&upload=error");
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO event_photos (event_id, user_id, image_path)
        VALUES (?, ?, ?)
    ");

    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    $stmt->bind_param("iis", $event_id, $user_id, $targetPath);

    if ($stmt->execute()) {
        header("Location: gallery_view.php?event_id=$event_id&upload=success");
    } else {
        header("Location: upload_event_photo.php?event_id=$event_id&upload=dberror");
    }

    exit;
}

header("Location: my_events.php");
exit;
?>