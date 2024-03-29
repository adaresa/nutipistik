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
    <?php
    $result = mysqli_query($con, "SELECT * FROM user_devices WHERE user_id = '$user_id' ORDER BY device_order");
    $num_rows = mysqli_num_rows($result);
    if ($num_rows > 0) {
        ?>
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
        <?php
    } else { // If the user has no devices
        ?>
        <div class='row'>
            <div class='col-lg-12'>
                <div class='alert alert-info'>
                    <h4 class='alert-heading'>Kuidas saada oma seadme ID ja parool:</h4>
                    <p>Uue seadme lisamiseks vajate seadme ID-d ja parooli, mis on iga seadme jaoks unikaalsed. Selle teabe leiate, kui ühendate seadme Wi-Fi võrku (see kuvatakse Wi-Fi halduri konfiguratsiooniportaalis).</p>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
    <div class='row'>
        <div class='col-lg-12'>
            <h3>Lisa uus pistik</h3>
            <div id="error-message" class="alert alert-danger" role="alert" style="display:none;"></div>
            <form id='add-device-form'>
                <input type='hidden' name='user_id' value='<?php echo $user_id; ?>'>
                <input type='hidden' name='action' id='action' value='add'>
                <div class='row g-3'>
                    <div class='col-3'>
                        <div class='form-group'>
                            <label for='device_id'>ID</label>
                            <input type='number' class='form-control' name='device_id' id='device_id'
                                placeholder='Sisesta pistiku ID' required min='1' step='1'
                                oninput='limitInput(this, 99999999999);'>
                        </div>
                    </div>
                    <div class='col-3' id='device_pass_col'>
                        <div class='form-group' id='device_pass_form_group'>
                            <label for='device_id'>Parool</label>
                            <input type='text' class='form-control' name='device_pass' id='device_pass'
                                placeholder='Sisesta pistiku parool' required min='1' maxlength='10'>
                        </div>
                    </div>
                    <div class='col-4'>
                        <div class='form-group'>
                            <label for='device_name'>Nimi</label>
                            <input type='text' class='form-control' name='device_name' id='device_name'
                                placeholder='Sisesta pistiku nimi' required maxlength='40'>
                        </div>
                    </div>
                    <div class='col-2'>
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
    // Set ID input limit to 11 characters
    function limitInput(input, maxValue) {
        if (parseInt(input.value) > maxValue) {
            input.value = maxValue;
        }
    }

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
    let passwordFieldAdded = true;
    let devicePassCol;

    function toggleEditMode(device_id, device_name) {
        const editBtn = document.getElementById(`editBtn${device_id}`);
        const parentDevicePassCol = document.getElementById("device_pass_col");

        if (editBtn.textContent === "Muuda Nime") {
            if (prevEditBtn && prevEditBtn !== editBtn) {
                prevEditBtn.textContent = "Muuda Nime";
                resetForm(false);
            } else {
                resetForm(true);
            }

            editDevice(device_id, device_name);
            editBtn.textContent = "Lõpeta muutmine";

            if (!devicePassCol) {
                devicePassCol = parentDevicePassCol;
            }

            if (parentDevicePassCol) { // Check if parentDevicePassCol is not null
                parentDevicePassCol.parentElement.removeChild(parentDevicePassCol);
            }
            passwordFieldAdded = false;
            prevEditBtn = editBtn;
        } else {
            resetForm(true);
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
        const formHeader = document.querySelector(".container .row:nth-child(3) .col-lg-12 > h3");
        const submitButton = document.querySelector(".container .row:nth-child(3) .col-2 > button");

        formHeader.textContent = "Muuda nime";
        submitButton.textContent = "Muuda nimi";
    }

    function resetForm(addPasswordField) {
        document.getElementById("add-device-form").reset();
        document.getElementById("device_id").readOnly = false;
        document.getElementById("action").value = "add";

        // Reset the text for the form header and submit button
        const formHeader = document.querySelector(".container .row:nth-child(3) .col-lg-12 > h3");
        const submitButton = document.querySelector(".container .row:nth-child(3) .col-2 > button");

        formHeader.textContent = "Lisa uus pistik";
        submitButton.textContent = "Lisa pistik";

        // Add the 'col-3' div containing the password form group back to the DOM
        if (!passwordFieldAdded && addPasswordField) {
            const formRow = document.querySelector(".container .row:nth-child(3) .g-3");
            formRow.insertBefore(devicePassCol, formRow.children[1]);
            passwordFieldAdded = true;
        }
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