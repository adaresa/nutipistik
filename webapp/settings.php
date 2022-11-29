<?php
// Make sure the user is logged in
session_start();
if (!isset($_SESSION['index'])) {
	header('LOCATION:index.php');
	die();
}

include_once('includes/header.php'); ?>

<div id='page-wrapper'>
    <?php

    include("database_connect.php"); // Include data for the connection to the database

    // Check the connection
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    // Grab the table out of the database
    $result = mysqli_query($con, "SELECT * FROM ESPtable2");

    while ($row = mysqli_fetch_array($result)) {
        $unit_id = $row['id'];
        $energyType = $row['ENERGY_TYPE'];
        $vat = $row['VAT'];
        $region = $row['REGION'];

        echo"<div>
            <div class='row'>
                <div class='col-lg-12'>
                    <h1 class='page-header'>Seaded</h1>
                </div>
            </div>
            <div class='row'>
                <div class='col-lg-12'>
                    <div class='panel panel-default'>
                        <div class='panel-heading'>
                            Üldine
                        </div>
                        <div class='panel-body
                            <div class='table-responsive'>
                                <table class='table table-striped table-bordered table-hover' id='dataTables-example'>
                                    <thead>
                                        <tr>
                                            <th>Nimi</th>
                                            <th>Valik</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                                        // Riik
                                        echo"<tr>
                                            <td>Riik</td>
                                            <td>
                                                <form action='settings.php' method='post'>
                                                    <select name='region'>
                                                        <option "; if($region == '1') { echo "selected"; } echo" value='1'>Eesti</option>
                                                        <option "; if($region == '2') { echo "selected"; } echo" value='2'>Soome</option>
                                                        <option "; if($region == '3') { echo "selected"; } echo" value='3'>Läti</option>
                                                        <option "; if($region == '4') { echo "selected"; } echo" value='4'>Leedu</option>
                                                    </select>

                                                    <input type='submit' name='change_but' value='Salvesta'>
                                                </form>";

                                                if (isset($_POST['region'])) {
                                                    $region = $_POST['region'];
                                                    $query3 = "UPDATE ESPtable2 SET REGION = '$region' WHERE id = '$unit_id'";
                                                    $result = mysqli_query($con, $query3);
                                                    echo "<meta http-equiv='refresh' content='0'>";
                                                }
                                                echo"
                                            </td>
                                        </tr>";
                                        // Sisalda käibemaks
                                        echo"<tr>
                                            <td>Sisalda käibemaks (%)</td>
                                            <td>
                                                <form action='settings.php' method='post'>
                                                    <input type='text' name='vat' value='$vat'>
                                                    <input type='submit' name='change_but' value='Salvesta'>
                                                </form>";

                                                if (isset($_POST['vat'])) {
                                                    $vat = $_POST['vat'];
                                                    $query2 = "UPDATE ESPtable2 SET VAT = '$vat' WHERE id = '$unit_id'";
                                                    $result = mysqli_query($con, $query2);
                                                    echo "<meta http-equiv='refresh' content='0'>";
                                                }
                                                echo"
                                            </td>
                                        </tr>";
                                        // Elektrihinna ühik
                                        echo"<tr>
                                            <td>Elektrihinna ühik</td>
                                            <td>
                                                <form action='settings.php' method='post'>
                                                    <select name='energyType'>
                                                        <option "; if($energyType == 'kWh') { echo "selected"; } echo" value='kWh'>€/kWh</option>
                                                        <option "; if($energyType == 'MWh') { echo "selected"; } echo" value='MWh'>€/MWh</option>
                                                    </select>

                                                    <input type='submit' name='change_but' value='Salvesta'>
                                                </form>";

                                                if (isset($_POST['energyType'])) {
                                                    $energyType = $_POST['energyType'];
                                                    $query = "UPDATE ESPtable2 SET ENERGY_TYPE = '$energyType' WHERE id = '$unit_id'";
                                                    $result = mysqli_query($con, $query);
                                                    echo "<meta http-equiv='refresh' content='0'>";
                                                }
                                                echo"
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    
                    </div>
                </div>
            </div>
        </div>";
    }
    ?>

</div>

<?php include_once('includes/footer.php'); ?>
 