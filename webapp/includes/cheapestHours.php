<?php

// Function to get cheapest hours for the current day
function get_cheapest_hours()
{
    include("database_connect.php");

    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        return false;
    }

    $region = $_SESSION['REGION'];

    $device_id = $_SESSION['device_id'];
    $result_cheapest_hours = mysqli_query($con, "SELECT CHEAPEST_HOURS FROM ESPtable2 WHERE id = $device_id");
    $row_cheapest_hours = mysqli_fetch_array($result_cheapest_hours);
    $cheapest_hours = $row_cheapest_hours['CHEAPEST_HOURS'];



    // Get todays prices
    $result_prices = mysqli_query($con, "SELECT * FROM ElectricityPrices WHERE region = '$region'");
    $row = mysqli_fetch_array($result_prices);
    $todays_prices = array();
    for ($i = 0; $i < 24; $i++) {
        $todays_prices[$i] = array('hour' => $i, 'price' => $row['td' . $i]);
    }

    // Sort todays prices
    usort($todays_prices, function ($a, $b) {
        return $a['price'] <=> $b['price'];
    });

    // Get the cheapest hours
    $cheapest_hours_arr = array_slice($todays_prices, 0, $cheapest_hours);

    // Sort cheapest hours by hour key
    usort($cheapest_hours_arr, function ($a, $b) {
        return $a['hour'] <=> $b['hour'];
    });

    // Format the cheapest hours
    $active_hours = "Aktiivseid tunde: $cheapest_hours<br>Aktiivsed tunnid: ";
    foreach ($cheapest_hours_arr as $hour) {
        $start_hour = $hour['hour'];
        $end_hour = $hour['hour'];
        $start_time = str_pad($start_hour, 2, "0", STR_PAD_LEFT) . ":00";
        $end_time = str_pad($end_hour, 2, "0", STR_PAD_LEFT) . ":59";
        $active_hours .= "<strong>{$start_time}-{$end_time}</strong>, ";
    }
    // Else if there are no cheapest hours
    if (empty($cheapest_hours_arr)) {
        $active_hours .= "<strong>-</strong>";
    }
    $active_hours = rtrim($active_hours, ", ");

    return $active_hours;
}

?>