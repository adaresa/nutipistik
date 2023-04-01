<?php
include("../database_connect.php");
include("energyConverter.php");

// Make sure the session is started.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$device_id = $_SESSION['device_id'];

// Fetch region from ESPtable2 table
$result_region = mysqli_query($con, "SELECT REGION FROM ESPtable2 WHERE id = $device_id");
$row_region = mysqli_fetch_array($result_region);
$region = $row_region['REGION'];

$sql = "SELECT ElectricityPrices.*, ESPtable2.ENERGY_TYPE, ESPtable2.VAT
        FROM ElectricityPrices
        JOIN ESPtable2 ON ElectricityPrices.region = '$region'
        WHERE ESPtable2.id = $device_id";

$result = mysqli_query($con, $sql);

error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'custom_error_log.log');


if (!$result) {
    error_log("Query error: " . mysqli_error($con));
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Query error: ' . mysqli_error($con)]);
    exit();
}

// Check if the query returned any rows.
if (mysqli_num_rows($result) == 0) {
    error_log("No rows found for device_id: $device_id");
    header('Content-Type: application/json');
    echo json_encode(['error' => "No data found for device_id: $device_id"]);
    exit();
}

$hours = [];
$todayPrices = [];
$tomorrowPrices = [];

// Initialize variables with default values.
$unit = '';
$vat = 0;
$tomorrow_exists = false;

while ($row = mysqli_fetch_array($result)) {
    date_default_timezone_set('Europe/Tallinn');
    error_log("Row: " . print_r($row, true));
    $tomorrow_exists = $row['tm0'] != 0;
    $unit = $row['ENERGY_TYPE'];
    $vat = $row['VAT'];

    for ($i = 0; $i <= 23; $i++) {
        $hours[] = ($i < 10 ? '0' . $i : $i) . ':00';
        $todayPrices[] = convert_unit($row['td' . $i], $unit, $vat);
        if ($tomorrow_exists) {
            $tomorrowPrices[] = convert_unit($row['tm' . $i], $unit, $vat);
        }
    }
}

$js_hours = $hours;
$js_today_prices = ['unit' => $unit, 'prices' => $todayPrices];
$js_tomorrow_prices = $tomorrow_exists ? ['unit' => $unit, 'prices' => $tomorrowPrices] : null;

header('Content-Type: application/json');
echo json_encode([
    'hours' => $js_hours,
    'today_prices' => $js_today_prices,
    'tomorrow_prices' => $js_tomorrow_prices,
    'today_date' => date('d.m', strtotime('today')),
    'tomorrow_date' => date('d.m', strtotime('tomorrow')),
]);
?>
