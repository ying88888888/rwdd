<?php
include("session_test.php");
include("config.php");

$query = "
SELECT 
    e.id,
    e.event_name,
    e.description,
    e.event_date,
    e.event_location,
    MIN(ep.image_path) AS cover_image,
    COUNT(ep.photo_id) AS total_images,
    MAX(ep.uploaded_at) AS latest_upload
FROM events e
JOIN event_photos ep ON e.id = ep.event_id
GROUP BY e.id, e.event_name, e.description, e.event_date, e.event_location
ORDER BY latest_upload DESC
";

$result = $conn->query($query);

if (!$result) {
    die("SQL error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EcoEvents | Event Gallery</title>
  <link rel="stylesheet" href="css/style.css" />
</head>

<body>

<?php include("header.php"); ?>

<main class="container">

  <h1 class="gallery-title">Event Gallery</h1>
  <hr class="gallery-line" />

  <section class="gallery-grid" id="galleryGrid">
    <?php if ($result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <article class="gallery-card">
          <img
            class="gallery-img"
            src="<?= htmlspecialchars($row['cover_image']) ?>"
            alt="<?= htmlspecialchars($row['event_name']) ?>"
            loading="lazy"
          />

          <div class="gallery-info">
            <div class="gallery-row">
              <h3 class="gallery-card-title"><?= htmlspecialchars($row['event_name']) ?></h3>
            </div>

            <p class="gallery-desc">
              <?= htmlspecialchars(mb_strimwidth($row['description'], 0, 90, '...')) ?>
            </p>

            <div class="gallery-row bottom">
              <span><?= date("d M Y", strtotime($row['event_date'])) ?></span>
              <span><?= htmlspecialchars($row['event_location']) ?></span>
            </div>

            <div class="gallery-row bottom">
              <span><?= (int)$row['total_images'] ?> photos</span>
            </div>

            <a href="gallery_view.php?event_id=<?= (int)$row['id'] ?>" class="gallery-btn">
              View Album
            </a>
          </div>
        </article>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No gallery photos available yet.</p>
    <?php endif; ?>
  </section>

</main>

<?php include("footer.php"); ?>

<script src="js/main.js"></script>
</body>
</html>