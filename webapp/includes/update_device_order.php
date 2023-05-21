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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $device_order = $_POST['device_order'];

    if (!isset($user_id) || !isset($device_order)) {
        echo "Missing required data.";
        exit();
    }

    $device_order = json_decode($device_order, true);

    $success = true;
    for ($i = 0; $i < count($device_order); $i++) {
        $device_id = $device_order[$i];
        $order = $i + 1;
        $sql = "UPDATE user_devices SET device_order = '$order' WHERE user_id = '$user_id' AND device_id = '$device_id'";

        if (!mysqli_query($con, $sql)) {
            $success = false;
            break;
        }
    }

    if ($success) {
        echo "success";
    } else {
        echo "Pistikute järjestuse muutmine ebaõnnestus.";
    }
}

mysqli_close($con);
?>
