<?php
header('Cache-Control: no-cache');
include_once("../database_connect.php");

if (isset($_POST['unit_id'])) {
    $unit_id = $_POST['unit_id'];

    $result = mysqli_query($con, "SELECT * FROM ESPtable2 WHERE id = '$unit_id'");
    $row = mysqli_fetch_array($result);
    $output_state = $row['OUTPUT_STATE'];

    echo $output_state;
}
?>