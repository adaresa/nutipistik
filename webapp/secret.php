<?php
    include("database_connect.php");

    $result = mysqli_query($con, "SELECT * FROM ESPtable2");

    while ($row = mysqli_fetch_array($result)) {
        $_USERNAME = $row['LOGIN_USER'];
        $_PASSWORD = $row['LOGIN_PASS'];
    }
?>
