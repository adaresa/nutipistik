<?php
session_start();
if (!isset($_SESSION["index"])) {
    header("LOCATION:index.php");
    die();
}

$user_id = $_SESSION['user_id'];

include_once "includes/header.php";
include "database_connect.php";

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
?>

<div class='container content-spacing'>
    <div class='row'>
        <div class='col-lg-12'>
            <h1 class='page-header'>Pistikute haldamine</h1>
        </div>
    </div>

    <div class='row'>
        <div class='col-lg-12'>
            <h3>Sinu pistikud</h3>
            <table class='table table-striped'>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pistiku Nimi</th>
                        <th>Tegevused</th>
                    </tr>
                </thead>
                <tbody id='devices-tbody'>
                    <?php
                    $result = mysqli_query($con, "SELECT * FROM user_devices WHERE user_id = '$user_id' ORDER BY device_order");
                    while ($row = mysqli_fetch_assoc($result)) {
                        $device_id = $row['device_id'];
                        $device_name = $row['device_name'];
                        echo "<tr id='deviceRow{$device_id}'>
                            <td>{$device_id}</td>
                            <td>{$device_name}</td>
                            <td>
                                <a id='editBtn{$device_id}' href='javascript:void(0);' onclick='toggleEditMode({$device_id}, \"{$device_name}\");' class='btn btn-warning'>Muuda Nime</a>
                                <a href='includes/delete_device.php?device_id={$device_id}&user_id={$user_id}' class='btn btn-danger' onclick='return confirm(\"Oled kindel, et soovid pistiku &#39;\" + \"" . $device_name . "\" + \"&#39; kustutada?\");'>Kustuta</a>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class='row'>
        <div class='col-lg-12'>
            <h3>Lisa uus pistik</h3>
            <div id="error-message" class="alert alert-danger" role="alert" style="display:none;"></div>
            <form id='add-device-form'>
                <input type='hidden' name='user_id' value='<?php echo $user_id; ?>'>
                <input type='hidden' name='action' id='action' value='add'>
                <div class='row g-3'>
                    <div class='col'>
                        <div class='form-group'>
                            <label for='device_id'>Pistiku ID</label>
                            <input type='number' class='form-control' name='device_id' id='device_id'
                                placeholder='Sisesta pistiku ID' required required min='1' step='1' , maxlength='11'>
                        </div>
                    </div>
                    <div class='col'>
                        <div class='form-group'>
                            <label for='device_name'>Pistiku nimi</label>
                            <input type='text' class='form-control' name='device_name' id='device_name'
                                placeholder='Sisesta pistiku nimi' required maxlength='20'>
                        </div>
                    </div>
                    <div class='col-auto'>
                        <button type='submit' class='btn btn-primary mt-4'>Lisa pistik</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<?php
include_once "includes/footer.php";
?>

<script>
    function submitAddDeviceForm(e) {
        e.preventDefault();
        const formData = new FormData(document.getElementById("add-device-form"));

        fetch("includes/add_device.php", {
            method: "POST",
            body: formData,
        })
            .then((response) => response.text())
            .then((result) => {
                if (result === "success") {
                    location.reload(); // Reload the page to display the updated device name
                } else {
                    const errorMessage = document.getElementById("error-message");
                    errorMessage.innerText = result;
                    errorMessage.style.display = "block";
                }
            });
    }

    let prevEditBtn = null;

    function toggleEditMode(device_id, device_name) {
        const editBtn = document.getElementById(`editBtn${device_id}`);
        if (editBtn.textContent === "Muuda Nime") {
            editDevice(device_id, device_name);
            editBtn.textContent = "Lõpeta muutmine";
            if (prevEditBtn && prevEditBtn !== editBtn) {
                prevEditBtn.textContent = "Muuda Nime";
            }
            prevEditBtn = editBtn;
        } else {
            resetForm();
            editBtn.textContent = "Muuda Nime";
            prevEditBtn = null;
        }
    }

    function editDevice(device_id, device_name) {
        document.getElementById("device_id").value = device_id;
        document.getElementById("device_name").value = device_name;
        document.getElementById("device_id").readOnly = true;
        document.getElementById("action").value = "update";

        // Change the text for the form header and submit button
        const formHeader = document.querySelector(".row:last-child .col-lg-12 > h3");
        const submitButton = document.querySelector(".col-auto > button");

        formHeader.textContent = "Muuda nime";
        submitButton.textContent = "Muuda nimi";
    }

    function resetForm() {
        document.getElementById("add-device-form").reset();
        document.getElementById("device_id").readOnly = false;
        document.getElementById("action").value = "add";

        // Reset the text for the form header and submit button
        const formHeader = document.querySelector(".row:last-child .col-lg-12 > h3");
        const submitButton = document.querySelector(".col-auto > button");

        formHeader.textContent = "Lisa uus pistik";
        submitButton.textContent = "Lisa pistik";
    }

    $(function () {
        $("#devices-tbody").sortable({
            update: function (event, ui) {
                const deviceOrder = $(this).sortable("toArray").map((rowId) => rowId.replace("deviceRow", ""));
                updateDeviceOrder(deviceOrder);
            },
        });
        $("#devices-tbody").disableSelection();
    });

    function updateDeviceOrder(deviceOrder) {
        const formData = new FormData();
        formData.append('user_id', '<?php echo $user_id; ?>');
        formData.append('device_order', JSON.stringify(deviceOrder));

        fetch("includes/update_device_order.php", {
            method: "POST",
            body: formData,
        })
            .then((response) => response.text())
            .then((result) => {
                if (result !== "success") {
                    alert("Failed to update device order: " + result);
                }
            });
    }


    document.getElementById("add-device-form").addEventListener("submit", submitAddDeviceForm);
    document.getElementById("add-device-form").addEventListener("reset", function () {
        document.getElementById("device_id").readOnly = false;
    });
</script>