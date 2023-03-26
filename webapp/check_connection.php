<?php
session_start();

header('X-Content-Type-Options: nosniff');
header("Content-Type: text/html; charset=utf-8");

include("database_connect.php");

if (mysqli_connect_errno()) {
    echo json_encode(["status" => "error"]);
    exit();
}

$device_id = $_SESSION['device_id'];

$result = mysqli_query($con, "SELECT * FROM ESPtable2 WHERE id = '$device_id'");

if ($row = mysqli_fetch_array($result)) {
    $last_update = strtotime($row['LAST_UPDATE']);
    $time_difference = time() - $last_update;

    if ($time_difference <= 30) { // Assuming the device sends updates at least every minute
        echo json_encode(["status" => "connected"]);
    } else {
        echo json_encode(["status" => "disconnected"]);
    }
} else {
    echo json_encode(["status" => "unknown"]);
}
?>
