<?php
global $update_number;
/*This file should receive a link somethong like this: http://noobix.000webhostapp.com/TX.php?unit=1&b1=1
If you paste that link to your browser, it should update b1 value with this TX.php file. Read more details below.
The ESP will send a link like the one above but with more than just b1. It will have b1, b2, etc...
*/

//We loop through and grab variables from the received the URL
foreach($_REQUEST as $key => $value)  //Save the received value to the hey variable. Save each cahracter after the "&"
{
	//Now we detect if we recheive the id, the password, unit, or a value to update
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

}//End of foreach


include("database_connect.php"); 	//We include the database_connect.php which has the data for the connection to the database
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


//In case that you need the time from the internet, use this line
date_default_timezone_set('Europe/Tallinn');

$t2 = date("G"); 	//This will return 1 as 1
$result2 = mysqli_query($con,"SELECT * FROM SelectedHours");
while($row2 = mysqli_fetch_array($result2)) {
	$selected_hour = $row2['Selected'.$t2];
}

//Get all the values form the table on the database
$result = mysqli_query($con,"SELECT * FROM ESPtable2");	//table select is ESPtable2, must be the same on yor database

//Loop through the table and filter out data for this unit id equal to the one taht we've received. 
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

}// End of the while loop
?>








