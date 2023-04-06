<?php
session_start();
if (!isset($_SESSION["index"])) {
    header("LOCATION:index.php");
    die();
}

include "../database_connect.php";

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

if (isset($_GET["device_id"]) && isset($_GET["user_id"])) {
    $selected_device_id = $_SESSION['device_id']; // Currently selected device ID
    $device_id = $_GET['device_id'];
    $user_id = $_GET['user_id'];

    $sql = "DELETE FROM user_devices WHERE user_id = '$user_id' AND device_id = '$device_id'";

    if (mysqli_query($con, $sql)) {
        // Check if the deleted device is the currently selected device
        if ($device_id == $selected_device_id) {
            // Select the first device in the user_devices table ordered by device_order
            $query = "SELECT device_id FROM user_devices WHERE user_id = '$user_id' ORDER BY device_order LIMIT 1";
            $result = mysqli_query($con, $query);

            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $new_selected_device_id = $row['device_id'];
            } else {
                $new_selected_device_id = 0;
            }

            // Update selected_device_id in the users table
            $update_selected_device = "UPDATE users SET selected_device_id = '$new_selected_device_id' WHERE id = '$user_id'";
            mysqli_query($con, $update_selected_device);

            // Update the selected_device_id in the session
            $_SESSION['device_id'] = $new_selected_device_id;
        }

        header("Location: ../devices.php");
    } else {
        echo "Error: " . mysqli_error($con);
    }
}

mysqli_close($con);
?>
