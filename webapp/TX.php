<?php
global $update_number;

//We loop through and grab variables from the received the URL
foreach($_REQUEST as $key => $value)  //Save the received value to the key variable. Save each cahracter after the "&"
{
	//Now we detect if we receive the id, the password, unit, or a value to update
	if($key == "td"){
		$day = $value;
	}
	if($key == "tm"){
		$day_tomorrow = $value;
	}
	if($key == "val"){
		$val = $value;
	}

	if($key =="id"){
	$unit = $value;
	}	
	if($key =="pw"){
	$pass = $value;
	}	
	if($key =="un"){
	$update_number = $value;
	}
	
	if($update_number == 1)
	{
		if($key =="n1"){
			$sent_nr_1 = $value;
		}			
	}
	else if($update_number == 2)
	{
		if($key =="b1"){
			$ARDUINO_OUTPUT = $value;
		}			
	}

}


include("database_connect.php");
include_once('includes/energyConverter.php');


// Check  the connection
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

// if $day is set
if(isset($day)){
	// update the day
	mysqli_query($con,"UPDATE ElectricityPrices SET td{$day} = $val WHERE id=$unit AND PASSWORD=$pass");
}

// if $day_tomorrow is set
if(isset($day_tomorrow)){
	// update the day
	mysqli_query($con,"UPDATE ElectricityPrices SET tm{$day_tomorrow} = $val WHERE id=$unit AND PASSWORD=$pass");
}

//Now we update the values in database
if($update_number == 1)	//If the received data is for CURRENT_PRICE, we update that value
	{
		mysqli_query($con,"UPDATE ESPtable2 SET CURRENT_PRICE = $sent_nr_1 WHERE id=$unit AND PASSWORD=$pass");	
	}
else if($update_number == 2)	//If the received data is for ARDUINO_OUTPUT, we update that value
	{
		mysqli_query($con,"UPDATE ESPtable2 SET ARDUINO_OUTPUT = $ARDUINO_OUTPUT WHERE id=$unit AND PASSWORD=$pass");	
	}


date_default_timezone_set('Europe/Tallinn');

$t2 = date("G");
$result2 = mysqli_query($con,"SELECT * FROM SelectedHours");
while($row2 = mysqli_fetch_array($result2)) {
	$selected_hour = $row2['Selected'.$t2];
}

//Get all the values form the table on the database
$result = mysqli_query($con,"SELECT * FROM ESPtable2");

while($row = mysqli_fetch_array($result)) {
if($row['id'] == $unit){
	
	//We update the values for the boolean and numebers we receive from the Arduino, then we echo the boolean
	//and numbers and the text from the database back to the Arduino

	$region = $row['REGION'];

	$button_state = $row['BUTTON_STATE'];	
	
	$price_limit = $row['PRICE_LIMIT'];	

	$control_type = $row['CONTROL_TYPE'];

	$current_price = convert_unit($row['CURRENT_PRICE']);

	$unit = $row['ENERGY_TYPE'];

	$cheapest_hours = $row['CHEAPEST_HOURS'];

	if ($control_type == 1) {
		$result = 'region:'.$region.',control_type:1,price_limit:'.$price_limit.',current_price:'.$current_price;
	} else if ($control_type == 2) {
		$result = 'region:'.$region.',control_type:2,switch_state:'.$button_state;
	} else if ($control_type == 3) {
		$result = 'region:'.$region.',control_type:3,cheapest_hours:'.$cheapest_hours;
	} else if ($control_type == 4) {
		$result = 'region:'.$region.',control_type:4,selected_hour:'.$selected_hour;
	}

	echo $result;
}

}
?>








