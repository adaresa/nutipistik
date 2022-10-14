<?php
// Make sure the user is logged in
session_start();
if (!isset($_SESSION['index'])) {
	header('LOCATION:index.php');
	die();
}

// This will make the page auto-refresh each 15 seconds
$page = $_SERVER['PHP_SELF'];
$sec = "15";


include_once('includes/header.php'); ?>

<head>
	<!-- This will make the page auto-refresh each $sec seconds -->
	<meta http-equiv="refresh" content="<?php echo $sec ?>;URL='<?php echo $page ?>'">
</head>


<div id="page-wrapper">

	<!-- Current electricity price -->
	<div>
		<?php

		include("database_connect.php");

		if (mysqli_connect_errno()) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}

		$result = mysqli_query($con, "SELECT * FROM ESPtable2"); //table select


		echo 
		"<table class='table' style='font-size: 30px;'>
		<thead>
			<tr>
			<th>Elektrihind</th>	
			</tr>
		</thead>
		
		<tbody>
		<tr class='active'>
			<td>Praegune</td>
		</tr>  
			";


		while ($row = mysqli_fetch_array($result)) {
			echo "<tr class='info'>";
			echo "<td>" . $row['SENT_NUMBER_1'] . " EUR/MWh</td>";
			echo "</tr></tbody>";
		}
		echo "</table><br>";
		?>
	</div>

	<!-- Table with today's electricity prices -->
	<div>
		<?php

		include("database_connect.php");

		if (mysqli_connect_errno()) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}

		$result = mysqli_query($con, "SELECT * FROM ElectricityPrices"); //table select

		while ($row = mysqli_fetch_array($result)) {
			$tomorrow_exists = $row['tm0'] != 0;

			echo"
			<table class='table' style='font-size: 30px;'>
			<thead>
				<tr>
				<th>Elektrihinnad</th>	
				</tr>
			</thead>

			<tbody>
			<tr class='active'>
				<td>CET</td>
				<td>Täna</td>";
				if ($tomorrow_exists) {	echo "<td>Homme</td>";}
			echo "</tr>";

			for ($i = 0; $i <= 23; $i++) {
				echo "<tr'>";
				
				# if ($i < 10), then add a zero in front of the number
				if ($i < 10) {
					echo "<td>0".$i." - ";
				} else {
					echo "<td>".$i." - ";
				}
				if ($i < 9) {
					echo "0".($i+1)."</td>";
				} else {
					echo ($i+1)."</td>";
				}

				# Todays and tomorrows (if exists) prices
				echo "<td>" . $row['td'.$i] . " EUR/MWh</td>";
				if ($tomorrow_exists) {	echo "<td>" . $row['tm'.$i] . " EUR/MWh</td>";}
				echo "</tr>";
			}
			echo "</tbody></table><br>";
			
		}
		?>
	</div>
</div>

<?php include_once('includes/footer.php'); ?>
