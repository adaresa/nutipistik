<?php
include("database_connect.php");

if (mysqli_connect_errno()) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$result = mysqli_query($con, "SELECT * FROM ElectricityPrices");

while ($row = mysqli_fetch_array($result)) {
	date_default_timezone_set('Europe/Tallinn');
	$tomorrow_exists = $row['tm0'] != 0;
	$today = date("d.m");
	$tomorrow = date("d.m", strtotime("+1 day"));
    $unit = $row['ENERGY_TYPE'];
    $vat = $row['VAT'];

	echo"
	<style>
	.nopadding {
		display:block;
		height: 580px;
		overflow:auto;
	}
	.nopadding thead th {
		position: sticky;
		top: 0px;
	}
	</style>

	<table style='font-size: 30px;'>
		<thead>
			<tr>
				<th style='padding-left:8px; padding-bottom:10px;'>Elektrihinnad (€/" . $unit . ")</th>
			</tr>
		</thead>
	</table>

	<table class='table nopadding' style='font-size: 30px;'>
		<thead>
			<tr class='active'>
				<th style='padding-right: 120px;'>EET</td>
				<th>Täna ($today)</td>";
				if ($tomorrow_exists) {	echo "<th>Homme ($tomorrow)</td>";}
			echo "</tr>";
		echo"</thead>

		<tbody>";
			for ($i = 0; $i <= 23; $i++) {
				echo "<tr>";
					if ($i < 10) { echo "<td>0".$i." - "; } else { echo "<td>".$i." - "; }
					if ($i < 9) { echo "0".($i+1)."</td>"; } else { echo ($i+1)."</td>"; }

					echo "<td>" . convert_unit($row['td'.$i], $unit, $vat) ."</td>";
					if ($tomorrow_exists) {	echo "<td>" . convert_unit($row['tm'.$i], $unit, $vat) ."</td>";}
				echo "</tr>";
			}
		echo "</tbody>
	</table><br>";
}
?>