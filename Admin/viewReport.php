<?php
include "conn.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Fetch Event Info
$eventSql = "SELECT event_name, event_date FROM events WHERE id = $id";
$eventRes = mysqli_query($dbConn, $eventSql);
$eventData = mysqli_fetch_assoc($eventRes);

// 2. Fetch Waste Rows
$wasteSql = "SELECT * FROM waste_reports WHERE event_id = $id";
$wasteRes = mysqli_query($dbConn, $wasteSql);

// 3. Prepare to get the submission date from the first row
$submissionDate = "N/A";
if (mysqli_num_rows($wasteRes) > 0) {
    // We fetch one row to get the timestamp, then reset the pointer for the table loop
    $firstRow = mysqli_fetch_assoc($wasteRes);
    $submissionDate = date('d M Y, H:i', strtotime($firstRow['created_at']));
    mysqli_data_seek($wasteRes, 0); // Reset the pointer so the while loop starts from the beginning
}

if (!$eventData) {
    header("Location: wasteReport.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Events Admin Page - View Waste Report</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class = "topBar">
        <button class = "hamburger" aria-label="Toggle menu">☰</button>
        <a href="dashboard.php">
            <img class = "logo" src="logo.png" alt="Website Logo">
        </a>
        
        <div class = "topRight">
            <a href="Profile.php">
                <img class = "icon" src="profile.png" alt="Profile icon" >
            </a>

            <a class = "logoutBtn" href="logout.php">Logout</a>
        </div>
    </div>

    <div class = "container">
        <aside class = "sideBar">
            <button class = "closeSideBar">&times;</button>
            <a href = "dashboard.php">Dashboard</a>
            <a href="eventApproval.php">Event Approval</a>
            <a href="userManagement.php">User Management</a>
            <a class = "waste" href="wasteReport.php">Waste Report</a>
            <a href="PointsDistribution.php">Points Distribution</a>
            <a href="OrganizerApproval.php">Event Organizer Approval</a>
            <a href="ViewFeedback.php">View Feedback</a>
            <a href="redeemRewards.php">Rewards</a>
        </aside>

        <div class = "mainContent">
            <div class = "pageHeader">
                <h1>Waste Report</h1>

                <a href="wasteReport.php" class = "backBtn">Back</a>
            </div>

<h2 class="eventTitle"><?php echo htmlspecialchars($eventData['event_name']); ?></h2>

        <div class="reportInfo">
            <div class="infoRow">
                <div class="infoLabel">Event Date:</div>
                    <div class="infoValue">
                        <?php echo date('d M Y', strtotime($eventData['event_date'])); ?>
                    </div>
                </div>
                <div class = "infoRow">
                    <div class = "infoLabel">Submitted At:</div>
                    <div class = "infoValue">
                        <?php echo $submissionDate; ?>
                    </div>
                </div>
            </div>

            <table class="eventTable">
                <tr class="tableHeader">
                    <th>Waste Type</th>
                    <th>Quantity</th>
                    <th>Collected</th>
                    <th>Collection Method</th>
                    <th>Disposal Method</th>
                </tr>
                <?php while ($waste = mysqli_fetch_assoc($wasteRes)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($waste['waste_type']); ?></td>
                    <td><?php echo $waste['quantity']; ?></td>
                    <td><?php echo htmlspecialchars($waste['collected']); ?></td>
                    <td><?php echo htmlspecialchars($waste['collection_method']); ?></td>
                    <td><?php echo htmlspecialchars($waste['disposal_method']); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
            </div>
        </div>
    </div>
</body>

<script>
    // declare a variable
    const menuButton = document.querySelector('.hamburger');
    const sideBar = document.querySelector('.sideBar');

    // Adds click evemt lister, so when we click, it toggles the active class on the sidebar
    menuButton.addEventListener('click', () => {
        // Toggle = if the class not there, it adds it -> sidebar slides in
        // if the class exists, it remove it -> sidebar slides out
        sideBar.classList.toggle('active');
    });

    const closeButton = document.querySelector('.closeSideBar');

    closeButton.addEventListener('click', () => {
        // when user click the close button, it removes the active class from the sidebar -> sidebar hides
        sideBar.classList.remove('active');
    });   
    
</script>

</html>