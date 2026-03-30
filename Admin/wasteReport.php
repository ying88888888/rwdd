<?php
include "conn.php";

// 1. Corrected SQL: Fixed the double GROUP BY and added ORDER BY
$sql = "SELECT e.id, e.event_name, e.event_date, MAX(w.created_at) as submission_date 
        FROM events e 
        JOIN waste_reports w ON e.id = w.event_id 
        GROUP BY e.id, e.event_name, e.event_date
        ORDER BY e.event_date DESC";

$result = mysqli_query($dbConn, $sql);

if (!$result) {
    die("Database Error: " . mysqli_error($dbConn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Events Admin Page - Waste Report</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="topBar">
        <button class="hamburger" aria-label="Toggle menu">☰</button>
        <a href="dashboard.php">
            <img class="logo" src="logo.png" alt="Website Logo">
        </a>
        <div class="topRight">
            <a href="Profile.php">
                <img class="icon" src="profile.png" alt="Profile icon" >
            </a>
            <a class="logoutBtn" href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <aside class="sideBar">
            <button class="closeSideBar">&times;</button>
            <a href="dashboard.php">Dashboard</a>
            <a href="eventApproval.php">Event Approval</a>
            <a href="userManagement.php">User Management</a>
            <a class="waste" href="wasteReport.php">Waste Report</a>
            <a href="PointsDistribution.php">Points Distribution</a>
            <a href="OrganizerApproval.php">Event Organizer Approval</a>
            <a href="ViewFeedback.php">View Feedback</a>
            <a href="redeemRewards.php">Rewards</a>
        </aside>

        <div class="mainContent">
            <h1>Waste Report</h1>
            <p class="desc">View waste collection and disposal data submitted by organizers</p>

            <h2 class="sectionTitle">All Waste Reports</h2>

            <table class="eventTable">
                <tr class="tableHeader">
                    <th>Event name</th>
                    <th>Event date</th>
                    <th>Submitted date</th>
                    <th>Action</th>
                </tr>

                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['event_date'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['submission_date'])); ?></td>
                            <td>
                                <button class="viewBtn">
                                    <a href="viewReport.php?id=<?php echo $row['id']; ?>">View Report</a>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No waste reports found.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <script>
        const menuButton = document.querySelector('.hamburger');
        const sideBar = document.querySelector('.sideBar');

        menuButton.addEventListener('click', () => {
            sideBar.classList.toggle('active');
        });

        const closeButton = document.querySelector('.closeSideBar');
        closeButton.addEventListener('click', () => {
            sideBar.classList.remove('active');
        });
    </script>
</body>
</html>