<?php 
include("../database_connect.php");
include("energyConverter.php");
global $con;

// Add these two lines
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'custom_error_log.log');

if (!$con) {
    error_log("Error connecting to the database: " . mysqli_connect_error());
    die("Error connecting to the database: " . mysqli_connect_error());
}

$sql = "SELECT ElectricityPrices.*, ESPtable2.ENERGY_TYPE
        FROM ElectricityPrices
        JOIN ESPtable2 ON ElectricityPrices.id = ESPtable2.id
        WHERE ElectricityPrices.id = 99999";

$result = mysqli_query($con, $sql);

if (!$result) {
    error_log("Query error: " . mysqli_error($con));
    die("Query error: " . mysqli_error($con));
}

$hours = [];
$todayPrices = [];
$tomorrowPrices = [];

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
error_log("Hours: " . print_r($hours, true));
error_log("Today Prices: " . print_r($todayPrices, true));
error_log("Tomorrow Prices: " . print_r($tomorrowPrices, true));
header('Content-Type: application/json');
echo json_encode([
    'hours' => $js_hours,
    'today_prices' => $js_today_prices,
    'tomorrow_prices' => $js_tomorrow_prices,
]);
?>
