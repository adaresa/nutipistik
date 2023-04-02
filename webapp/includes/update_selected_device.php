<?php
session_start();
include_once("../database_connect.php");

if (isset($_POST['device_id'])) {
    $_SESSION['device_id'] = $_POST['device_id'];
    # Update the selected_device_id to device_id in the users table
    $user_id = $_SESSION['user_id'];
    $device_id = $_POST['device_id'];

    $query = "UPDATE users SET selected_device_id = '$device_id' WHERE id = '$user_id'";
    $result = mysqli_query($con, $query);

    http_response_code(200);
} else {
    http_response_code(400);
}
?>
