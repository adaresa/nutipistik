<?php
session_start();
if (!isset($_SESSION["index"])) {
    header("LOCATION:index.php");
    die();
}

$user_id = $_POST['user_id'];
$device_id = $_POST['device_id'];
$device_pass = $_POST['device_pass'];
$device_name = $_POST['device_name'];

include "../database_connect.php";

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    // Updating device name
    if ($action == "update") {
        $update_sql = "UPDATE user_devices SET device_name = '$device_name' WHERE user_id = '$user_id' AND device_id = '$device_id'";
        $update_result = mysqli_query($con, $update_sql);

        if ($update_result) {
            echo "success";
        } else {
            echo "Pistiku nime muutmine ebaõnnestus.";
        }
    // Adding new device
    } else {
        // Check if device_id already exists
        $check_device_id = "SELECT device_id FROM user_devices WHERE device_id = '$device_id'";
        $check_result = mysqli_query($con, $check_device_id);

        if (mysqli_num_rows($check_result) > 0) {
            echo "Pistik ID-ga $device_id on juba registreeritud.";
        } elseif (strlen($device_name) > 20) {
            echo "Pistiku nimi ei tohi olla pikem kui 20 tähemärki.";
        } else {
            // Check if device_pass is correct
            $check_device_pass = "SELECT * FROM ESPtable2 WHERE id = '$device_id' AND PASSWORD = '$device_pass'";

            if (mysqli_num_rows(mysqli_query($con, $check_device_pass)) == 0) {
                echo "Pistik ID-ga $device_id ei eksisteeri või parool on vale.";
                die();
            }

            $sql = "INSERT INTO user_devices (user_id, device_id, device_name) VALUES ('$user_id', '$device_id', '$device_name')";
            $result = mysqli_query($con, $sql);

            if ($result) {
                // Set device_id to session if it is not set
                if ($_SESSION['device_id'] == 0) {
                    $_SESSION['device_id'] = $device_id;
                    // Update selected_device_id in the users table
                    $update_selected_device = "UPDATE users SET selected_device_id = '$device_id' WHERE id = '$user_id'";
                    mysqli_query($con, $update_selected_device);
                }
                echo 'success';
            } else {
                echo "Uue pistiku lisamine ebaõnnestus.";
            }
        }
    }
}

mysqli_close($con);

?>
