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
	<?php

	include("database_connect.php");

	if (mysqli_connect_errno()) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	$result = mysqli_query($con, "SELECT * FROM ESPtable2"); //table select


	echo "<table class='table' style='font-size: 30px;'>
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
		echo "</tr>
	</tbody>";
	}
	echo "</table>
<br>
";
	?>
</div>

<?php include_once('includes/footer.php'); ?>