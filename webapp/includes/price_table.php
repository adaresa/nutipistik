<?php
include("database_connect.php");

// Make sure the session is started.
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

if (mysqli_connect_errno()) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$device_id = $_SESSION['device_id'];
$result_unit_vat = mysqli_query($con, "SELECT ENERGY_TYPE, VAT FROM ESPtable2 WHERE id = $device_id");
$row_unit_vat = mysqli_fetch_array($result_unit_vat);
$unit = $row_unit_vat['ENERGY_TYPE'];
$vat = $row_unit_vat['VAT'];

$result_prices = mysqli_query($con, "SELECT * FROM ElectricityPrices");

while ($row = mysqli_fetch_array($result_prices)) {
	date_default_timezone_set('Europe/Tallinn');
	$tomorrow_exists = $row['tm0'] != 0;
	$today = date("d.m");
	$tomorrow = date("d.m", strtotime("+1 day"));

	echo "
	<style>
    .sticky-header th {
        position: sticky;
        top: 0;
        background-color: white;
        z-index: 10;
    }
	.equal-width-columns th, .equal-width-columns td {
		width: 33.333%;
	}
	.no-top-side-borders {
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
    }
	.no-top-border {
		border-top: none !important;
	}
    </style>
	
    <table class='table table-striped table-bordered table-hover nopadding equal-width-columns' style='font-size: 22px;'>
        <thead>
		<tr class='no-top-border'>
                <th colspan='3' class='no-top-side-borders' style='text-align: center;'>Elektrihind (€/" . $unit . ")</th>
            </tr>
            <tr class='active sticky-header'>
                <th>CET</td>
                <th>Täna ($today)</td>";
	if ($tomorrow_exists) {
		echo "<th>Homme ($tomorrow)</td>";
	}
	echo "</tr>";
	echo "</thead>

        <tbody>";
	for ($i = 0; $i <= 23; $i++) {
		echo "<tr>";
		if ($i < 10) {
			echo "<td>0" . $i . " - ";
		} else {
			echo "<td>" . $i . " - ";
		}
		if ($i < 9) {
			echo "0" . ($i + 1) . "</td>";
		} else {
			// if ($i == 23), then use 00 instead of 24
			if ($i == 23) {
				echo "00</td>";
			} else {
				echo ($i + 1) . "</td>";
			}
		}

		echo "<td>" . convert_unit($row['td' . $i], $unit, $vat) . "</td>";
		if ($tomorrow_exists) {
			echo "<td>" . convert_unit($row['tm' . $i], $unit, $vat) . "</td>";
		}
		echo "</tr>";
	}
	echo "</tbody>
	</table><br>";
}
?>
