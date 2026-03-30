<?php
include "conn.php";

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = intval($_GET['id']);
    $action = strtolower($_GET['action']);

    // First check current status
    $checkSql = "SELECT user_status FROM `user` WHERE user_id = $id";
    $checkRes = mysqli_query($dbConn, $checkSql);

    if (!$checkRes || mysqli_num_rows($checkRes) == 0) {
        header('HTTP/1.1 404 Not Found');
        die("User not found");
    }

    $currentUser = mysqli_fetch_assoc($checkRes);

    if ($currentUser['user_status'] == 'Pending') {
        header('HTTP/1.1 403 Forbidden');
        die("Cannot modify a Pending user through this interface");
    }

    if ($action == "deactivate") {
        $status = "Deactivated";
    } elseif ($action == "activate") {
        $status = "Active";
    } else {
        header('HTTP/1.1 400 Bad Request');
        die("Invalid action: " . htmlspecialchars($action));
    }

    $sql = "UPDATE `user` SET user_status = '$status' WHERE user_id = $id";

    if (mysqli_query($dbConn, $sql)) {
        echo "Success";
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo "Failed: " . mysqli_error($dbConn);
    }
} else {
    header('HTTP/1.1 400 Bad Request');
    echo "Missing id or action";
}
?>