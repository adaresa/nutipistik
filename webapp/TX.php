<?php
global $update_number;

// Loop through and grab variables from the received URL
foreach ($_REQUEST as $key => $value) {
    // Save the received value to the key variable, save each character after the "&"
    if ($key == "id") {
        $unit = $value;
    }
    if ($key == "pw") {
        $pass = $value;
    }
	if ($key == "out") {
		$output = $value;
	}

}

include("database_connect.php");
include_once('includes/energyConverter.php');

// Check the connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}



if (isset($output)) {
	mysqli_query($con, "UPDATE ESPtable2 SET OUTPUT_STATE = $output WHERE id=$unit AND PASSWORD=$pass");
}


$result = mysqli_query($con, "SELECT * FROM ESPtable2");
date_default_timezone_set('Europe/Tallinn');

while ($row = mysqli_fetch_array($result)) {
    if ($row['id'] == $unit) {
        $selected_hours = $row['SELECTED_HOURS'];
        $t2 = date("G");
        $selected_hour = substr($selected_hours, $t2, 1);

        $button_state = $row['BUTTON_STATE'];
        $price_limit = $row['PRICE_LIMIT'];
        $control_type = $row['CONTROL_TYPE'];

        // Get 'CURRENT_PRICE' from the ElectricityPrices table

        $row2 = mysqli_fetch_array($result2);
        $current_price = $row2['CURRENT_PRICE'];

        $cheapest_hours = $row['CHEAPEST_HOURS'];

        $chp_day_hours = $row['CHP_DAY_HOURS'];
        $exp_day_hours = $row['EXP_DAY_HOURS'];
        $chp_day_thold = $row['CHP_DAY_THOLD'];
        $exp_day_thold = $row['EXP_DAY_THOLD'];

        $time_ranges = $row['TIME_RANGES'];

        if ($control_type == 1) {
            $result = ',control_type:1,price_limit:' . $price_limit . ',current_price:' . $current_price;
        } elseif ($control_type == 2) {
            $result = ',control_type:2,switch_state:' . $button_state;
        } elseif ($control_type == 3) {
            $result = ',control_type:3,cheapest_hours:' . $cheapest_hours . ',current_price:' . $current_price;
        } elseif ($control_type == 4) {
            $result = ',control_type:4,selected_hour:' . $selected_hour;
        } elseif ($control_type == 5) {
            $result = ',control_type:5,chp_day_hours:' . $chp_day_hours . ',exp_day_hours:' . $exp_day_hours . 
            ',chp_day_thold:' . $chp_day_thold . ',exp_day_thold:' . $exp_day_thold . ',current_price:' . $current_price . ',average_price:' . $average_price;
        } elseif ($control_type == 6) {
            $result = ',control_type:6,schedule:' . $time_ranges;
        }
        echo $result;
    }
}
?>
