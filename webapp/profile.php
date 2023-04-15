<?php
// Make sure the user is logged in
session_start();
if (!isset($_SESSION["index"])) {
    header("LOCATION:index.php");
    die();
}

$user_id = $_SESSION['user_id'];

include_once "includes/header.php";
include_once "includes/funcs/reset_device_settings.php"
?>

<div class='container content-spacing'>
    <?php
    include "database_connect.php"; // Include data for the connection to the database
    
    // Check the connection
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    // Fetch the username from the users table
    $user_result = mysqli_query(
        $con,
        "SELECT username FROM users WHERE id = '$user_id'"
    );

    $username = "";
    if ($user_row = mysqli_fetch_assoc($user_result)) {
        $username = $user_row["username"];
    } ?>
    <div class='row'>
        <div class='col-lg-12'>
            <h1 class='page-header'>Profiil</h1>
        </div>
    </div>
    <div class='row'>
        <div class='col-lg-12'>
            <div class='panel-body'>
                <form action='profile.php' method='post'>
                    <input type="hidden" name="action" value="">
                    <div class='table-responsive'>
                        <table class='table table-striped table-bordered table-hover' id='dataTables-example'>
                            <thead style='font-size: 22px;'>
                                <tr>
                                    <th>Nimi</th>
                                    <th>Väärtus</th>
                                </tr>
                            </thead>
                            <tbody style='font-size: 30px;'>
                                <tr>
                                    <td>Kasutajanimi</td>
                                    <td>
                                        <?php echo $username; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Praegune parool</td>
                                    <td><input type='password' name='current_password' class='custom-input' required
                                            title='Praegune parool' placeholder='************'>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Uus parool</td>
                                    <td><input type='password' name='new_password' class='custom-input' required
                                            title='Uus parool' placeholder='************'></td>
                                </tr>
                                <tr>
                                    <td>Uuesti uus parool</td>
                                    <td><input type='password' name='confirm_new_password' class='custom-input title='
                                            Uuesti uus parool' placeholder='************' required></td>
                                </tr>
                            </tbody>
                        </table>
                        <div>
                            <input type='submit' name='change_password' value='Muuda parool'
                                onclick="document.querySelector('[name=\'action\']').value='change_password';">
                        </div>
                        <div>
                            <input type='submit' name='delete_account' value='Kustuta konto' class='btn-danger mt-3'
                                onclick="document.querySelector('[name=\'action\']').value='delete_account';">
                        </div>
                        <?php
                        if (isset($_POST["action"])) {
                            $action = $_POST["action"];
                            if ($action === "change_password") {

                                $current_password = $_POST["current_password"];
                                $new_password = $_POST["new_password"];
                                $confirm_new_password = $_POST["confirm_new_password"];

                                // Verify current password
                                $query = "SELECT password FROM users WHERE id = '$user_id'";
                                $result = mysqli_query($con, $query);
                                $row = mysqli_fetch_assoc($result);

                                if (password_verify($current_password, $row["password"])) {
                                    if ($new_password === $confirm_new_password) {
                                        // Update the password in the database
                                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                                        $query = "UPDATE users SET password = '$hashed_password' WHERE username = '$username'";
                                        mysqli_query($con, $query);
                                        echo "<p style='color: green;'>Parool edukalt uuendatud.</p>";
                                    } else {
                                        echo "<p style='color: red;'>Uued paroolid ei ühti. Proovige uuesti.</p>";
                                    }
                                } else {
                                    echo "<p style='color: red;'>Praegune parool on vale. Palun proovige uuesti.</p>";
                                }
                            }

                            if ($action === "delete_account") {
                                // Get all device IDs associated with the user
                                $device_result = mysqli_query($con, "SELECT device_id FROM user_devices WHERE user_id = '$user_id'");
                                $device_ids = array();
                                while ($device_row = mysqli_fetch_assoc($device_result)) {
                                    $device_ids[] = $device_row["device_id"];
                                }

                                // Reset the settings of all devices associated with the user
                                foreach ($device_ids as $device_id) {
                                    reset_device_settings($con, $device_id);
                                }

                                // Delete all rows from user_devices where user has a device
                                $query = "DELETE FROM user_devices WHERE user_id = '$user_id'";
                                mysqli_query($con, $query);
                                
                                // Delete the user from the database
                                $query = "DELETE FROM users WHERE id = '$user_id'";
                                mysqli_query($con, $query);

                                // Destroy the session and redirect to the login page
                                session_destroy();
                                echo "<script>window.location.replace('index.php');</script>";
                                die();
                            }
                        }
                        ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelector('[name="delete_account"]').addEventListener('click', function (event) {
        event.preventDefault();
        if (confirm('Olete kindel, et soovite oma konto kustutada? See tegevus on pöördumatu.')) {
            document.querySelector('[name="action"]').value = 'delete_account';
            this.closest('form').submit();
        }
    });
</script>
<?php
include_once "includes/footer.php";
?>
