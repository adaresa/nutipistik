<?php
// Make sure the user is logged in
session_start();
if (!isset($_SESSION["index"])) {
    header("LOCATION:index.php");
    die();
}

$user_id = $_SESSION['user_id']; // Currently logged in user ID

include_once "includes/header.php";
?>

<div class='container content-spacing'>
    <?php
    include "database_connect.php"; // Include data for the connection to the database
    
    // Check the connection
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    // Grab the table out of the database
    $result = mysqli_query($con, "SELECT * FROM users WHERE id = '$user_id'");

    while ($row = mysqli_fetch_array($result)) {
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
                                            <button class='infoButton' type='button' style='display: inline-block; vertical-align: middle;' data-bs-toggle='tooltip' data-bs-placement='right'
                                            title='Vali riik, kus asub seade. See määrab ka elektrihinna päritolu.'>?</button>
                                        </td>
                                        
                                        <td>
                                            <select name='region' class='controlType' title='region'>
                                                <option ";
        if ($region == "ee") {
            echo "selected";
        }
        echo " value='ee'>Eesti</option>
                                                <option ";
        if ($region == "fi") {
            echo "selected";
        }
        echo " value='fi'>Soome</option>
                                                <option ";
        if ($region == "lt") {
            echo "selected";
        }
        echo " value='lt'>Läti</option>
                                                <option ";
        if ($region == "lv") {
            echo "selected";
        }
        echo " value='lv'>Leedu</option>
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
                $query3 = "UPDATE users SET REGION = '$region' WHERE id = '$user_id'";
                $result = mysqli_query($con, $query3);
                $_SESSION['REGION'] = $region;
            }

            if (isset($_POST["vat"])) {
                $vat = $_POST["vat"];
                $query2 = "UPDATE users SET VAT = '$vat' WHERE id = '$user_id'";
                $result = mysqli_query($con, $query2);
                $_SESSION['VAT'] = $vat;
            }

            if (isset($_POST["energyType"])) {
                $energyType = $_POST["energyType"];
                $query = "UPDATE users SET ENERGY_TYPE = '$energyType' WHERE id = '$user_id'";
                $result = mysqli_query($con, $query);
                $_SESSION['ENERGY_TYPE'] = $energyType;
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
