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


include_once('includes/header.php');
include_once('includes/energyConverter.php'); ?>

<head>
	<!-- This will make the page auto-refresh each $sec seconds -->
	<meta http-equiv="refresh" content="<?php echo $sec ?>;URL='<?php echo $page ?>'">
</head>




<div id="page-wrapper">

	<!-- Select the type of control -->
	<div>
		<?php

		include("database_connect.php"); // Include data for the connection to the database

		// Check the connection
		if (mysqli_connect_errno()) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}

		// Grab the table out of the database
		$result = mysqli_query($con, "SELECT * FROM ESPtable2");

		//Now we create the table with all the values from the database	  
		echo "<table class='table' style='font-size: 30px;'>
		<thead>
			<tr>
			<th>Juhtpaneel</th>	
			</tr>
		</thead>
		
		<tbody>
		<tr class='active'>
			<td>Tüüp</td>
		</tr>  
			";

		//loop through the table and print the data into the table
		while ($row = mysqli_fetch_array($result)) {
			echo "<tr class='success'><td>"; // <tr class='success'> means that the row will be green 	
			$unit = $row['ENERGY_TYPE']; // Grab the unit of the energy type
			$unit_id = $row['id'];
			$column = "CONTROL_TYPE";
			$control_type = $row['CONTROL_TYPE'];

			echo"
			<form action='panel.php' method='post'>
				<select name='controlType'>
					<option "; if($control_type == 1) { echo "selected"; } echo" value='1'>Piirhind</option>
					<option "; if($control_type == 2) { echo "selected"; } echo" value='2'>Lüliti</option>
					<option "; if($control_type == 3) { echo "selected"; } echo" value='3'>Odavad tunnid</option>

				</select>
				<input type='submit' name='submit' value='Muuda' />
			</form>";

			if(isset($_POST['controlType'])) {
				$control_type = $_POST['controlType'];
				if(!empty($control_type)){
					$query = "UPDATE ESPtable2 SET $column = '$control_type' WHERE id = '$unit_id'";
					$result = mysqli_query($con, $query);
					echo "<meta http-equiv='refresh' content='0'>";
				}
			}
			echo "</td></tr></tbody>";
		}
		echo "</table><br>"; ?>
	</div>

	<!-- Set control parameters -->
	<div>
		<?php
		include("database_connect.php");
		if (mysqli_connect_errno()) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		$result = mysqli_query($con, "SELECT * FROM ESPtable2"); //table select

		while ($row = mysqli_fetch_array($result)) {
			if ($control_type == 1) {
				$text = "Sees kui praegune elektrihind on alla piirhinna";
				$current_price = $row['CURRENT_PRICE'];


				echo "<table class='table' style='font-size: 30px;'>
				<thead>
					<tr>
					<th>Juhtimine</th>	
					</tr>
				</thead>
			
				<tbody>
				<tr class='active'>
					<td>$text<br>Praegune elektrihind: " . convert_unit($current_price) . " €/" . $unit . "</td>
				</tr>  
				";

				echo "<tr class='success'>";
				$column6 = "PRICE_LIMIT";
				$price_limit = $row['PRICE_LIMIT'];

				echo "<td><form action= update_values.php method= 'post'>
					<input type='text' name='value' style='width: 120px;' value=$price_limit  size='15' >
					<input type='hidden' name='unit' style='width: 120px;' value=$unit_id >
					<input type='hidden' name='column' style='width: 120px;' value=$column6 >
					<input type='submit' name='change_but'; text-align:center;' value='Muuda (" . $unit . ")'></form></td>";

				echo "</tr>
						</tbody>";

				echo "</table><br>";
			} 
			else if ($control_type == 2) {
				$text = "Juhtimine läbi lüliti";
				echo "<table class='table' style='font-size: 30px;'>
				<thead>
					<tr>
					<th>Juhtimine</th>	
					</tr>
				</thead>
				
				<tbody>
				<tr class='active'>
					<td>$text</td>
				</tr>  
					";

				echo "<tr class='success'>"; // <tr class='success'> means that the row will be green 		
				$unit_id = $row['id'];
				$column1 = "BUTTON_STATE";
				$current_button_state = $row['BUTTON_STATE'];

				if ($current_button_state == 1) {
					$inv_current_button_state = 0;
					$text_current_button_state = "ON";
					$color_current_button_state = "#6ed829";
				} else {
					$inv_current_button_state = 1;
					$text_current_button_state = "OFF";
					$color_current_button_state = "#e04141";
				}

				echo "<td><form action= update_values.php method= 'post'>
				<input type='hidden' name='value' value=$inv_current_button_state  size='15' >
				<input type='hidden' name='unit' value=$unit_id >
				<input type='hidden' name='column' value=$column1 >
				<input type= 'submit' name= 'change_but' style='font-size: 30px; text-align:center; background-color: $color_current_button_state' value=$text_current_button_state></form></td>";

					echo "</tr>
				</tbody>";

				echo "</table>
				<br>
				";
			}
			else if ($control_type == 3) {
				$i_odavamat_tundi = min(max($row['CHEAPEST_HOURS'], 1), 24);

				if ($i_odavamat_tundi == 1) {
					$text = "Sees päeva $i_odavamat_tundi odavaim tund";
				} else {
					$text = "Sees päeva $i_odavamat_tundi odavamat tundi";
				}

				echo "<table class='table' style='font-size: 30px;'>
				<thead>
					<tr>
					<th>Juhtimine</th>	
					</tr>
				</thead>
			
				<tbody>
				<tr class='active'>
					<td>$text</td>
				</tr>  
				";

				echo "<tr class='success'>";

				$column = "CHEAPEST_HOURS";


				echo "<td><form action= update_values.php method= 'post'>";
					$i_odavamat_tundi = min(max($i_odavamat_tundi, 1), 24); echo"
					<input type='text' name='value' style='width: 120px;' value=$i_odavamat_tundi  size='15' >
					<input type='hidden' name='unit' style='width: 120px;' value=$unit_id >
					<input type='hidden' name='column' style='width: 120px;' value=$column >
					<input type= 'submit' name= 'change_but' style='width: 120px; text-align:center;' value='Muuda'></form></td>";

				echo "</tr>
						</tbody>";

				echo "</table><br>";
			}
			// Selected hours
			else if ($control_type == 4) {
				$text = "-";
				echo "<table class='table' style='font-size: 30px;'>
				<thead>
					<tr>
					<th>Juhtimine</th>	
					</tr>
				</thead>";
				
			
					
					
			}
		}


		?>
	</div>
	
	<!-- State of the output -->
	<div>
		<?php
		include("database_connect.php");

		if (mysqli_connect_errno()) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}

		$result = mysqli_query($con, "SELECT * FROM ESPtable2"); //table select

		echo "<table class='table' style='font-size: 30px;'>
		<thead>
			<tr>
			<th>Väljundi olek</th>	
			</tr>
		</thead>
		
		<tbody>
		<tr class='active'>
			<td>Voolupesa</td>
		</tr>  
			";

		while ($row = mysqli_fetch_array($result)) {

			$cur_ARDUINO_OUTPUT = $row['ARDUINO_OUTPUT'];

			if ($cur_ARDUINO_OUTPUT == 1) {
				$label_ARDUINO_OUTPUT = "label-success";
				$text_ARDUINO_OUTPUT = "Sees";
			} else {
				$label_ARDUINO_OUTPUT = "label-danger";
				$text_ARDUINO_OUTPUT = "Väljas";
			}

			echo "<tr class='info'>";
			echo "<td>
			<span class='label $label_ARDUINO_OUTPUT'>"
				. $text_ARDUINO_OUTPUT . "</td>
			</span>";
			echo "</tr>
		</tbody>";
		}
		echo "</table>";
		?>
	</div>
</div>

<?php include_once('includes/footer.php'); ?>
