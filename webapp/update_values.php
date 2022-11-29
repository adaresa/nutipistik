<?php

$value = $_POST['value'];		//Get the value
$unit = $_POST['unit'];			//Get the id of the unit where we want to update the value
$column = $_POST['column'];		//Which coulumn of the database, could be the RECEIVED_BOOL1, etc...
$table = $_POST['table'];		//Which table of the database, could be the RECEIVED_BOOL1, etc...

//connect to the database
include("database_connect.php"); //We include the database_connect.php which has the data for the connection to the database

// Check the connection
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

// If $table is not set
if(!isset($table)){
  // update the value
  mysqli_query($con,"UPDATE ESPtable2 SET $column = $value WHERE id=$unit");
}
else{
  // update the value
  mysqli_query($con,"UPDATE $table SET $column = $value WHERE id=$unit");
}

//go back to the interface
header("location: index.php");
?>