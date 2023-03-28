<?php

//connect to the database
include("database_connect.php"); //We include the database_connect.php which has the data for the connection to the database

// Check the connection
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

if (isset($_POST['submit'])) {
  $unit_id = $_POST['unitID'];

  // Price Limit
  if (isset($_POST['priceLimit'])) {
    $price_limit = $_POST['priceLimit'];
    // Set minimum price limit to 0 and maximum to 1000
    $price_limit = min(99999, max(0, $price_limit));
    $query = "UPDATE ESPtable2 SET PRICE_LIMIT = '$price_limit' WHERE id = '$unit_id'";
    mysqli_query($con, $query);
  }

  // Switch
  if (isset($_POST['switchState'])) {
    $switch_state = $_POST['switchState'];
    $query = "UPDATE ESPtable2 SET BUTTON_STATE='$switch_state' WHERE id='$unit_id'";
    mysqli_query($con, $query);
  }

  // Cheapest Hours
  if (isset($_POST['cheapHours'])) {
    $cheap_hours = $_POST['cheapHours'];
    // Set cheap hours minimum to 0 and maximum to 24
    $cheap_hours = min(24, max(0, $cheap_hours));
    $query = "UPDATE ESPtable2 SET CHEAPEST_HOURS = '$cheap_hours' WHERE id = '$unit_id'";
    mysqli_query($con, $query);
  }

  // Selected Hours
  if (isset($_POST['selectedHours'])) {
    $selected_hours = $_POST['selectedHours'];
    $hours_binary = '';
    for ($i = 0; $i < 24; $i++) {
      $hours_binary .= in_array($i, $selected_hours) ? '1' : '0';
    }
    $query = "UPDATE ESPtable2 SET SELECTED_HOURS = '$hours_binary' WHERE id = '$unit_id'";
    mysqli_query($con, $query);
  }

  // Smart Hours
  if (isset($_POST['cheapDayHours']) && isset($_POST['expensiveDayHours']) && isset($_POST['cheapDayThreshold']) && isset($_POST['expensiveDayThreshold'])) {
    $average_day_hours = $_POST['averageDayHours'];
    $cheap_day_hours = $_POST['cheapDayHours'];
    $expensive_day_hours = $_POST['expensiveDayHours'];
    $cheap_day_threshold = $_POST['cheapDayThreshold'];
    $expensive_day_threshold = $_POST['expensiveDayThreshold'];

    $query = "UPDATE ESPtable2 SET CHP_DAY_HOURS = '$cheap_day_hours', EXP_DAY_HOURS = '$expensive_day_hours', CHP_DAY_THOLD = '$cheap_day_threshold', EXP_DAY_THOLD = '$expensive_day_threshold' WHERE id = '$unit_id'";
    mysqli_query($con, $query);
  }

}

//go back to the interface
header("location: panel.php");
?>