<?php

# function to convert float like "150.20" to "0.15"

function convert_unit($str) {

    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    include("database_connect.php");

    // Grab the table out of the database
    $result = mysqli_query($con, "SELECT * FROM ESPtable2");

    while ($row = mysqli_fetch_array($result)) {
        $vat = $row['VAT'];
        $energy_type = $row['ENERGY_TYPE'];

        $str = $str * (1 + ($vat / 100));

        if ($energy_type == "kWh") {
            # convert EUR/MWh to EUR/kWh
            $price = $str / 1000;
            # round price to 3 decimals after comma
            $price = round($price, 3);
        }
        # else price is already in €/MWh
        else {
            $price = $str;
        }
    }

    return $price;
}

?>