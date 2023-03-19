<?php
global $update_number;

// Loop through and grab variables from the received URL
foreach ($_REQUEST as $key => $value) {
    // Save the received value to the key variable, save each character after the "&"
    if ($key == "td") {
        $day = $value;
    }
    if ($key == "tm") {
        $day_tomorrow = $value;
    }
    if ($key == "val") {
        $val = $value;
    }
    if ($key == "id") {
        $unit = $value;
    }
    if ($key == "pw") {
        $pass = $value;
    }
    if ($key == "un") {
        $update_number = $value;
    }
	if ($key == "out") {
		$output = $value;
	}

    // Process the received data based on update_number
    if ($update_number == 1) {
        if ($key == "n1") {
            $sent_nr_1 = $value;
        }
    } elseif ($update_number == 2) {
        if ($key == "b1") {
            $ARDUINO_OUTPUT = $value;
        }
    }
}

include("database_connect.php");
include_once('includes/energyConverter.php');

// Check the connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

// If $day is set, update the day
if (isset($day)) {
    mysqli_query($con, "UPDATE ElectricityPrices SET td{$day} = $val WHERE id=$unit AND PASSWORD=$pass");
}

// If $day_tomorrow is set, update the day
if (isset($day_tomorrow)) {
    mysqli_query($con, "UPDATE ElectricityPrices SET tm{$day_tomorrow} = $val WHERE id=$unit AND PASSWORD=$pass");
}

if (isset($output)) {
	mysqli_query($con, "UPDATE ESPtable2 SET OUTPUT_STATE = $output WHERE id=$unit AND PASSWORD=$pass");
}

// Update values in the database based on update_number
if ($update_number == 1) {
    mysqli_query($con, "UPDATE ESPtable2 SET CURRENT_PRICE = $sent_nr_1 WHERE id=$unit AND PASSWORD=$pass");
} elseif ($update_number == 2) {
    mysqli_query($con, "UPDATE ESPtable2 SET ARDUINO_OUTPUT = $ARDUINO_OUTPUT WHERE id=$unit AND PASSWORD=$pass");
}

$result = mysqli_query($con, "SELECT * FROM ESPtable2");
date_default_timezone_set('Europe/Tallinn');

while ($row = mysqli_fetch_array($result)) {
    if ($row['id'] == $unit) {
        $selected_hours = $row['SELECTED_HOURS'];
        $t2 = date("G");
        $selected_hour = substr($selected_hours, $t2, 1);
        $region = $row['REGION'];
        $button_state = $row['BUTTON_STATE'];
        $price_limit = $row['PRICE_LIMIT'];
        $control_type = $row['CONTROL_TYPE'];
        $current_price = convert_unit($row['CURRENT_PRICE'], $row['ENERGY_TYPE'], $row['VAT']);
        $unit = $row['ENERGY_TYPE'];
        $cheapest_hours = $row['CHEAPEST_HOURS'];

        if ($control_type == 1) {
            $result = 'region:' . $region . ',control_type:1,price_limit:' . $price_limit . ',current_price:' . $current_price;
        } elseif ($control_type == 2) {
            $result = 'region:' . $region . ',control_type:2,switch_state:' . $button_state;
        } elseif ($control_type == 3) {
        $result = 'region:' . $region . ',control_type:3,cheapest_hours:' . $cheapest_hours;
        } elseif ($control_type == 4) {
        $result = 'region:' . $region . ',control_type:4,selected_hour:' . $selected_hour;
        }
        echo $result;
    }
}
?>

