<?php

header('X-Content-Type-Options: nosniff');

include("../database_connect.php");

if (isset($_POST['unit_id']) && isset($_POST['control_type'])) {
    $unit_id = $_POST['unit_id'];
    $control_type = $_POST['control_type'];

    $query = "UPDATE ESPtable2 SET CONTROL_TYPE = ? WHERE id = ?";

    if ($stmt = mysqli_prepare($con, $query)) {
        mysqli_stmt_bind_param($stmt, "ii", $control_type, $unit_id);

        if (mysqli_stmt_execute($stmt)) {
            echo "Control Type updated successfully";
        } else {
            echo "Error executing statement: (" . mysqli_stmt_errno($stmt) . ") " . mysqli_stmt_error($stmt);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: (" . mysqli_errno($con) . ") " . mysqli_error($con);
    }
} else {
    echo "Error: unit_id and control_type must be set";
}
?>
