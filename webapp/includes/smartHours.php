<?php

// Function to get smart cheapest hours for the current day
function get_smart_hours($today_average_price)
{
    include("database_connect.php");
    include_once("includes/energyConverter.php");

    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        return false;
    }

    $device_id = $_SESSION['device_id'];
    $result_smart_hours = mysqli_query($con, "SELECT CHP_DAY_HOURS, EXP_DAY_HOURS, CHP_DAY_THOLD, EXP_DAY_THOLD, REGION FROM ESPtable2 WHERE id = $device_id");
    $row_smart_hours = mysqli_fetch_array($result_smart_hours);

    $chp_day_hours = $row_smart_hours['CHP_DAY_HOURS'];
    $exp_day_hours = $row_smart_hours['EXP_DAY_HOURS'];
    $chp_day_thold = $row_smart_hours['CHP_DAY_THOLD'];
    $exp_day_thold = $row_smart_hours['EXP_DAY_THOLD'];
    $region = $row_smart_hours['REGION'];

    $smart_hours = 0;
    if ($today_average_price <= $chp_day_thold) {
        $smart_hours = $chp_day_hours;
    } else if ($today_average_price >= $exp_day_thold) {
        $smart_hours = $exp_day_hours;
    } else {
        $price_ratio = ($today_average_price - $chp_day_thold) / ($exp_day_thold - $chp_day_thold);
        $smart_hours = (int) round($chp_day_hours + $price_ratio * ($exp_day_hours - $chp_day_hours), 0, PHP_ROUND_HALF_UP);
    }

    // Get todays prices
    $result_prices = mysqli_query($con, "SELECT * FROM ElectricityPrices WHERE region = $region");
    $row = mysqli_fetch_array($result_prices);
    $todays_prices = array();
    for ($i = 0; $i < 24; $i++) {
        $todays_prices[$i] = array('hour' => $i, 'price' => $row['td' . $i]);
    }

    // Sort todays prices by price key
    usort($todays_prices, function ($a, $b) {
        return $a['price'] <=> $b['price'];
    });

    // Get the smart cheapest hours
    $cheapest_hours_arr = array_slice($todays_prices, 0, $smart_hours);

    // Sort smart cheapest hours by hour key
    usort($cheapest_hours_arr, function ($a, $b) {
        return $a['hour'] <=> $b['hour'];
    });

    // Format the smart cheapest hours
    $active_hours = "Aktiivseid tunde: $smart_hours<br>Aktiivsed tunnid: ";
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
