<?php
// Make sure the user is logged in
session_start();
if (!isset($_SESSION["index"])) {
    header("LOCATION:index.php");
    die();
}

$device_id = $_SESSION["device_id"];

include_once "includes/header.php";
?>

<div id='page-wrapper'>
    <?php
    include "database_connect.php"; // Include data for the connection to the database
    
    // Check the connection
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    // Grab the table out of the database
    $result = mysqli_query($con, "SELECT * FROM ESPtable2 WHERE id = '$device_id'");

    while ($row = mysqli_fetch_array($result)) {
        $unit_id = $row["id"];
        $energyType = $row["ENERGY_TYPE"];
        $vat = $row["VAT"];
        $region = $row["REGION"];

        echo "<div>
            <div class='row'>
                <div class='col-lg-12'>
                    <h1 class='page-header'>Seaded</h1>
                </div>
            </div>
            <div class='row'>
                <div class='col-lg-12'>

                        <div class='panel-body'>
                        <form action='settings.php' method='post'>
                        <div class='table-responsive'>
                            <table class='table table-striped table-bordered table-hover' id='dataTables-example'>
                                <thead style='font-size: 22px;'>
                                    <tr>
                                        <th>Nimi</th>
                                        <th>Väärtus</th>
                                    </tr>
                                </thead>
                                <tbody style='font-size: 30px;'>";
        // Riik
        echo "
                                    <tr>
                                        <td>
                                            <span style='display: inline-block; vertical-align: middle;'>Riik</span>
                                            <button class='infoButton' type='button' style='display: inline-block; vertical-align: middle;' data-toggle='tooltip' data-placement='right'
                                            title='Vali riik, kus asub seade. See määrab ka elektrihinna päritolu.'>?</button>
                                        </td>
                                        
                                        <td>
                                            <select name='region' class='controlType' title='region'>
                                                <option ";
        if ($region == "1") {
            echo "selected";
        }
        echo " value='1'>Eesti</option>
                                                <option ";
        if ($region == "2") {
            echo "selected";
        }
        echo " value='2'>Soome</option>
                                                <option ";
        if ($region == "3") {
            echo "selected";
        }
        echo " value='3'>Läti</option>
                                                <option ";
        if ($region == "4") {
            echo "selected";
        }
        echo " value='4'>Leedu</option>
                                            </select>
                                        </td>
                                    </tr>";
        // Sisalda käibemaks
        echo "
                                    <tr>
                                        <td>Sisalda käibemaks (%)</td>
                                        <td>
                                            <input type='number' name='vat' value='$vat' class='custom-input' title='vat'>
                                        </td>
                                    </tr>";
        // Elektrihinna ühik
        echo "
                                    <tr>
                                        <td>Elektrihinna ühik</td>
                                        <td>
                                            <select name='energyType' class='controlType' title='energyType'>
                                                <option ";
        if ($energyType == "kWh") {
            echo "selected";
        }
        echo " value='kWh'>€/kWh</option>
                                                <option ";
        if ($energyType == "MWh") {
            echo "selected";
        }
        echo " value='MWh'>€/MWh</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                       
                        <input type='submit' name='change_but' value='Salvesta'>";
        if (isset($_POST["change_but"])) {
            if (isset($_POST["region"])) {
                $region = $_POST["region"];
                $query3 = "UPDATE ESPtable2 SET REGION = '$region' WHERE id = '$unit_id'";
                $result = mysqli_query($con, $query3);
            }

            if (isset($_POST["vat"])) {
                $vat = $_POST["vat"];
                $query2 = "UPDATE ESPtable2 SET VAT = '$vat' WHERE id = '$unit_id'";
                $result = mysqli_query($con, $query2);
            }

            if (isset($_POST["energyType"])) {
                $energyType = $_POST["energyType"];
                $query = "UPDATE ESPtable2 SET ENERGY_TYPE = '$energyType' WHERE id = '$unit_id'";
                $result = mysqli_query($con, $query);
            }

            echo "<meta http-equiv='refresh' content='0'>";
        }
        echo "</form>
                        </div>
                    
                    </div>
                </div>
            </div>
        </div>";
    }
    ?>

</div>

<?php include_once "includes/footer.php"; ?>
