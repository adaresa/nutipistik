<?php

//connect to the database
include("database_connect.php"); //We include the database_connect.php which has the data for the connection to the database

// Check the connection
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$unit_id = $_POST['unitID'];

if (isset($_POST['submit'])) {

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

// Schedule
if (isset($_POST['submit_schedule'])) {

  function convertDateFormat($date)
  {
    if (empty($date)) {
      return '';
    }
    $dateTime = DateTime::createFromFormat('d.m.Y H:i', $date);
    return $dateTime->format('Y-m-d H:i:s');
  }

  $new_time_ranges = array();
  if (isset($_POST['from']) && isset($_POST['to'])) {
      $start_times = $_POST['from'];
      $end_times = $_POST['to'];
      // Loop through each start and end time and create a new time range object
      for ($i = 0; $i < count($start_times); $i++) {
          if (!empty($start_times[$i]) && !empty($end_times[$i])) {
              $start_time = convertDateFormat($start_times[$i]);
              $end_time = convertDateFormat($end_times[$i]);
              $new_time_range = new stdClass();
              $new_time_range->start = $start_time;
              $new_time_range->end = $end_time;
              array_push($new_time_ranges, $new_time_range);
          }
      }
  }

  // Update the TIME_RANGES column in the database with the new time ranges
  $new_time_ranges_json = json_encode($new_time_ranges);
  $query = "UPDATE ESPtable2 SET TIME_RANGES = '$new_time_ranges_json' WHERE id = '$unit_id'";
  mysqli_query($con, $query);
}


//go back to the interface
header("location: panel.php");
?>