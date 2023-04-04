<?php

// Loop through and grab variables from the received URL
foreach ($_REQUEST as $key => $value) {
    if ($key == "id") {
        $unit = $value;
    }
    if ($key == "pw") {
        $pass = $value;
    }
}

include("database_connect.php");

// Check the connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

// If $pass is set, update the LAST_UPDATE
if (isset($unit)) {
    mysqli_query($con, "UPDATE ESPtable2 SET LAST_UPDATE = NOW() WHERE id=$unit");
}

// Get all the values from the table in the database
$result = mysqli_query($con, "SELECT * FROM ESPtable2");

while ($row = mysqli_fetch_array($result)) {
    if ($row['id'] == $unit) {
        $output_state = $row['OUTPUT_STATE'];
        echo "#$output_state";
    }
}

?>
