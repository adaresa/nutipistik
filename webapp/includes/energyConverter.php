<?php

# function to convert float like "150.20" to "0.15"

function convert_unit($str, $energy_type, $vat) {

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

    return $price;
}

?>