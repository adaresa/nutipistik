<?php

header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $unit_id = isset($_POST['unit_id']) ? intval($_POST['unit_id']) : 0;
    $hour_value = isset($_POST['hour_value']) ? intval($_POST['hour_value']) : -1;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($unit_id > 0 && $hour_value >= 0 && in_array($action, ['add', 'remove'])) {
        include("../database_connect.php");

        $result = mysqli_query($con, "SELECT SELECTED_HOURS FROM ESPtable2 WHERE id = $unit_id");
        $row = mysqli_fetch_array($result);
        $selected_hours = $row['SELECTED_HOURS'];

        if ($action == 'add') {
            $selected_hours[$hour_value] = '1';
        } else {
            $selected_hours[$hour_value] = '0';
        }

        $updated_selected_hours = mysqli_real_escape_string($con, $selected_hours);
        $query = "UPDATE ESPtable2 SET SELECTED_HOURS = '$updated_selected_hours' WHERE id = $unit_id";
        $update_result = mysqli_query($con, $query);

        if ($update_result) {
            echo "Selected hours updated successfully!";
        } else {
            echo "Error updating selected hours: " . mysqli_error($con);
        }

        mysqli_close($con);
    }
}
?>
