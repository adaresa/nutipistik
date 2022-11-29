<?php

//We loop through and grab variables from the received URL
foreach($_REQUEST as $key => $value)
{
	if($key =="id"){
	    $unit = $value;
	}	
	if($key =="pw"){
	    $pass = $value;
	}
    if($key =="up"){
        $output = $value;
    }
    if($key == "arduino"){
        $arduino = $value;
    }
}

include("database_connect.php");


// Check  the connection
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

// if $output is set
if(isset($output)){
    // update the output
    mysqli_query($con,"UPDATE ESPtable2 SET OUTPUT_STATE = $output WHERE id=$unit AND PASSWORD=$pass");
}

if(isset($arduino)){
    // update what state is arduino in
    mysqli_query($con,"UPDATE ESPtable2 SET ARDUINO_OUTPUT = $arduino WHERE id=$unit AND PASSWORD=$pass");
}

//Get all the values form the table on the database
$result = mysqli_query($con,"SELECT * FROM ESPtable2");

while($row = mysqli_fetch_array($result)) {
    if($row['id'] == $unit){
            $output_state = $row['OUTPUT_STATE'];	
            echo "#$output_state";
    }
}
?>
