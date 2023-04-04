<?php
session_start();
if (!isset($_SESSION["index"])) {
    header("LOCATION:index.php");
    die();
}

$user_id = $_POST['user_id'];
$device_id = $_POST['device_id'];
$device_name = $_POST['device_name'];

include "../database_connect.php";

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    if ($action == "update") {
        $update_sql = "UPDATE user_devices SET device_name = '$device_name' WHERE user_id = '$user_id' AND device_id = '$device_id'";
        $update_result = mysqli_query($con, $update_sql);

        if ($update_result) {
            echo "success";
        } else {
            echo "Pistiku nime muutmine ebaõnnestus.";
        }

    } else {
        $user_id = $_POST['user_id'];
        $device_id = $_POST['device_id'];
        $device_name = $_POST['device_name'];

        // Check if device_id already exists
        $check_device_id = "SELECT * FROM ESPtable2 WHERE id = '$device_id'";
        $check_result = mysqli_query($con, $check_device_id);

        if (mysqli_num_rows($check_result) > 0) {
            echo "Pistik ID-ga $device_id on juba registreeritud.";
        } elseif (strlen($device_name) > 20) {
            echo "Pistiku nimi ei tohi olla pikem kui 20 tähemärki.";
        } else {
            $sql1 = "INSERT INTO ESPtable2 (id) VALUES ('$device_id')";
            $sql2 = "INSERT INTO user_devices (user_id, device_id, device_name) VALUES ('$user_id', '$device_id', '$device_name')";

            $result1 = mysqli_query($con, $sql1);
            $result2 = mysqli_query($con, $sql2);

            if ($result1 && $result2) {
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
