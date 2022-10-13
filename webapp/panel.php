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
			$unit_id = $row['id'];
			$column = "CONTROL_TYPE";
			$control_type = $row['CONTROL_TYPE'];

			echo"
			<form action='panel.php' method='post'>
				<select name='controlType'>
					<option "; if($control_type == 1) { echo "selected"; } echo" value='1'>Piirhind</option>
					<option "; if($control_type == 2) { echo "selected"; } echo" value='2'>Lüliti</option>
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

		if ($control_type == 1) {
			$text = "Piirhind";
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

			while ($row = mysqli_fetch_array($result)) {

				echo "<tr class='success'>";
				$column6 = "PRICE_LIMIT";
				$current_num_1 = $row['PRICE_LIMIT'];


				echo "<td><form action= update_values.php method= 'post'>
					<input type='text' name='value' style='width: 120px;' value=$current_num_1  size='15' >
					<input type='hidden' name='unit' style='width: 120px;' value=$unit_id >
					<input type='hidden' name='column' style='width: 120px;' value=$column6 >
					<input type= 'submit' name= 'change_but' style='width: 120px; text-align:center;' value='Muuda'></form></td>";

				echo "</tr>
						</tbody>";}

				echo "</table><br>";

		} else if ($control_type == 2) {
			$text = "Lüliti";
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

			while ($row = mysqli_fetch_array($result)) {

				echo "<tr class='success'>"; // <tr class='success'> means that the row will be green 		
				$unit_id = $row['id'];
				$column1 = "BUTTON_STATE";
				$current_bool_1 = $row['BUTTON_STATE'];

				if ($current_bool_1 == 1) {
					$inv_current_bool_1 = 0;
					$text_current_bool_1 = "ON";
					$color_current_bool_1 = "#6ed829";
				} else {
					$inv_current_bool_1 = 1;
					$text_current_bool_1 = "OFF";
					$color_current_bool_1 = "#e04141";
				}

				echo "<td><form action= update_values.php method= 'post'>
			<input type='hidden' name='value' value=$inv_current_bool_1  size='15' >
			<input type='hidden' name='unit' value=$unit_id >
			<input type='hidden' name='column' value=$column1 >
			<input type= 'submit' name= 'change_but' style='font-size: 30px; text-align:center; background-color: $color_current_bool_1' value=$text_current_bool_1></form></td>";

				echo "</tr>
			</tbody>";
			}
			echo "</table>
		<br>
		";
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
			<td>LED tuli</td>
		</tr>  
			";

		while ($row = mysqli_fetch_array($result)) {

			$cur_sent_bool_1 = $row['SENT_BOOL_1'];

			if ($cur_sent_bool_1 == 1) {
				$label_sent_bool_1 = "label-success";
				$text_sent_bool_1 = "Sees";
			} else {
				$label_sent_bool_1 = "label-danger";
				$text_sent_bool_1 = "Väljas";
			}

			echo "<tr class='info'>";
			echo "<td>
			<span class='label $label_sent_bool_1'>"
				. $text_sent_bool_1 . "</td>
			</span>";
			echo "</tr>
		</tbody>";
		}
		echo "</table>";
		?>
	</div>
</div>

<?php include_once('includes/footer.php'); ?>