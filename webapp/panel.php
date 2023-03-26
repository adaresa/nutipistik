<?php
// Make sure the user is logged in
session_start();
if (!isset($_SESSION['index'])) {
    header('LOCATION:index.php');
    die();
}

$device_id = $_SESSION['device_id'];

include_once('includes/header.php');
include_once("database_connect.php");
include_once('includes/energyConverter.php');
include_once('includes/cheapestHours.php');

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
?>

<div id="page-wrapper">

    <div>
        <div class='row'>
            <div class='col-lg-12'>
                <h1 class='page-header'>Juhtpaneel</h1>
            </div>
        </div>

        <!-- Select the type of control -->
        <div>
            <?php

            // Grab the table out of the database
            $result = mysqli_query($con, "SELECT * FROM ESPtable2 WHERE id = '$device_id'");

            //Now we create the table with all the values from the database      
            echo "<table class='table' style='font-size: 30px;'>
            <thead>
                <tr>
                    <th>Seadme ID: " . $device_id . "</th>  
                    <th></th>
                </tr>
            </thead>
            
            <tbody>
            <tr class='active'>
                <td>Režiim</td>
                <td>
                    <span style='display: inline-block; vertical-align: middle;'>Pistikupesa olek</span>
                    <button class='infoButton' style='display: inline-block; vertical-align: middle;' data-toggle='tooltip' data-placement='right'
                    title='Pistikupesa olek praeguse juhtimisrežiimiga. Uuendatakse iga 10 sekundi tagant.'>?</button>
                </td>
            </tr>";

            //loop through the table and print the data into the table
            while ($row = mysqli_fetch_array($result)) {
                echo "<tr class='success'><td>";
                $unit_id = $row['id'];
                $column = "CONTROL_TYPE";
                $control_type = $row['CONTROL_TYPE'];
                $output_state = $row['OUTPUT_STATE'];

                echo "
                <select name='controlType' class='controlType' data-unit-id='$unit_id' title='controlType'>
                    <option ";
                if ($control_type == 1) {
                    echo "selected";
                }
                echo " value='1'>Piirhind</option>
                    <option ";
                if ($control_type == 2) {
                    echo "selected";
                }
                echo " value='2'>Lüliti</option>
                    <option ";
                if ($control_type == 3) {
                    echo "selected";
                }
                echo " value='3'>Odavad tunnid</option>
                    <option ";
                if ($control_type == 4) {
                    echo "selected";
                }
                echo " value='4'>Valitud tunnid</option>
                </select></td>";

                // Output state
                echo "<td>";
                echo "<button type='button' class='btn btn-lg outputStateButton' data-unit-id='{$unit_id}' ";
                echo $output_state ? "style='background-color: green; color: white;'" : "style='background-color: red; color: white;'";
                echo " disabled>";
                echo $output_state ? 'SEES' : 'VÄLJAS';
                echo "</button>";


                echo "</td></tr></tbody>";
            }

            echo "</table><br>"; ?>
        </div>

        <!-- Control parameters -->
        <div>
            <?php
            $result = mysqli_query($con, "SELECT * FROM ESPtable2 WHERE id = '$device_id'");
            while ($row = mysqli_fetch_array($result)) {
                $unit_id = $row['id'];
                $price_limit = $row['PRICE_LIMIT'];
                $switch_state = $row['BUTTON_STATE'];
                $cheap_hours = $row['CHEAPEST_HOURS'];
                $selected_hours = $row['SELECTED_HOURS'];
                $control_type = $row['CONTROL_TYPE'];

                $unit = $row['ENERGY_TYPE'];
                $vat = $row['VAT'];
                $current_electricity_price = 0;
                $average_electricity_price = 0;
                $price_result = mysqli_query($con, "SELECT CURRENT_PRICE, AVERAGE_PRICE FROM ElectricityPrices WHERE id = 99999");
                if ($price_row = mysqli_fetch_array($price_result)) {
                    $current_electricity_price = $price_row["CURRENT_PRICE"];
                    $average_electricity_price = $price_row["AVERAGE_PRICE"];
                }



                echo "<table class='table' style='font-size: 30px;'>
            <thead>
                <tr>
                <th>Juhtimine</th>
                </tr>
            </thead>

            <tbody>
            <tr class='active'>
                    <td>
                        <span id='description'></span>
                        <button id='infoButton' class='infoButton' style= data-toggle='tooltip' data-placement='right'
                        title=''>?</button>";
                // PRICE LIMIT
                echo "<p class='small-text' data-control-type='1'>Praegune elektrihind: <strong>" . convert_unit($current_electricity_price, $unit, $vat) . '</strong> €/' . $unit . "</p>";
                echo "<p class='small-text' data-control-type='1'>Päeva keskmine elektrihind: <strong>" . convert_unit($average_electricity_price, $unit, $vat) . '</strong> €/' . $unit . "</p>";
                // CHEAPEST HOURS
                echo "<p class='small-text' data-control-type='3'>" . get_cheapest_hours() . "</p>";

                echo "
                    </td>
                </tr>";


                // Price Limit
                echo "
                <tr class='success' data-control-type='1'><td>
                <form action='update_values.php' method='post'>
                    <div class='price-input-wrapper'>
                        <input type='number' step='0.001' name='priceLimit' value='$price_limit' class='custom-input' title='priceLimit' />
                        <span class='unit'>€/$unit</span>
                    </div>
                    <input type='hidden' name='unitID' value='$unit_id' />
                    <input type='submit' name='submit' value='Salvesta' />
                </form>
                </td></tr>";

                // Switch State
                echo "
                <tr class='success' data-control-type='2'><td>
                <form action='update_values.php' method='post'>
                <div class='selected-hours'>
                <label>
                <input type='checkbox' name='switchState' id='switchState' value='$switch_state' onchange='updateSwitchState(this)'";
                if ($switch_state) {
                    echo "checked";
                }
                echo " /> <span class='hour-checkbox'><span id='switchStateText'>";
                if ($switch_state) {
                    echo "SEES";
                } else {
                    echo "VÄLJAS";
                }
                echo "</span></span></label></div><input type='hidden' name='unitID' value='$unit_id' />
                <input type='hidden' name='submit' value='Update' />
                </form>
                </td></tr>";



                // Cheap Hours
                echo "<tr class='success' data-control-type='3'><td>
                <form action='update_values.php' method='post'>
                    <input type='number' name='cheapHours' value='$cheap_hours' class='custom-input' title='cheapHours' />
                    <input type='hidden' name='unitID' value='$unit_id' />
                    <input type='submit' name='submit' value='Salvesta' />
                </form>";
                echo "</td></tr>";

                // Selected Hours
                echo "<tr class='success' data-control-type='4'><td>
                <form action='update_values.php' method='post' id='selectedHoursForm'>
                <div class='selected-hours'>";
                for ($i = 0; $i < 24; $i++) {
                    $checked = $selected_hours[$i] == '1' ? 'checked' : '';
                    $time_range = str_pad($i, 2, "0", STR_PAD_LEFT) . ":00 - " . str_pad($i, 2, "0", STR_PAD_LEFT) . ":59";
                    echo "<label><input type='checkbox' name='selectedHours[]' value='$i' $checked onchange='updateSelectedHours(this, $unit_id)' /><span class='hour-checkbox'>{$time_range}</span></label>";
                }
                echo "</div>
                <input type='hidden' name='unitID' value='$unit_id' />
                </form>";

            }
            echo "</tbody></table><br>"; ?>

        </div>

    </div>

</div>

<?php include_once('includes/footer.php'); ?>

<script>
    // Update selected hours
    document.getElementById('selectedHoursForm').addEventListener('submit', function (e) {
        const selectedHoursCheckboxes = e.target.querySelectorAll('input[type="checkbox"]');
        let checkedCount = 0;

        selectedHoursCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                checkedCount++;
            }
        });
    });

    // Update switch state
    document.querySelectorAll('.selected-hours label').forEach(function (label) {
        label.addEventListener('click', function (e) {
            if (e.target !== label) return;
            const checkbox = label.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
        });
    });

    // Update switch state
    function updateSwitchState(switchStateInput) {
        const form = switchStateInput.form;
        const unitId = form.elements['unitID'].value;
        const switchState = switchStateInput.checked ? '1' : '0';

        // Update the switch text
        const switchStateText = document.getElementById('switchStateText');
        switchStateText.textContent = switchStateInput.checked ? 'SEES' : 'VÄLJAS';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_values.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                // console.log('Response:', xhr.responseText); // debug
            } else {
                // console.log('Error:', xhr.status, xhr.statusText); // debug
            }
        }
        xhr.send('unitID=' + unitId + '&switchState=' + switchState + '&submit=Update');
    }

    // Update selected hours
    function updateSelectedHours(checkbox, unitId) {
        const isChecked = checkbox.checked;
        const hourValue = checkbox.value;
        const action = isChecked ? 'add' : 'remove';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'includes/update_selected_hours.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                console.log('Response:', xhr.responseText);
            } else {
                console.log('Error:', xhr.status, xhr.statusText);
            }
        }
        xhr.send('unit_id=' + unitId + '&hour_value=' + hourValue + '&action=' + action);
    }

    // update the description, tooltip based on the control type
    function updateDescription(controlType) {
        var description = '';
        var extendedDescription = '';
        switch (controlType) {
            case '1':
                description = 'Piirhind';
                extendedDescription = 'Pistikupesa lülitatakse sisse, kui praegune elektrihind on madalam kui sisestatud hinnapiir.';
                break;
            case '2':
                description = 'Lüliti';
                extendedDescription = 'Pistikupesa saab käsitsi sisse või välja lülitada.';
                break;
            case '3':
                description = 'Odavad tunnid';
                extendedDescription = 'Pistikupesa lülitatakse sisse ainult ööpäeva etteantud arv odavamate tundide jooksul.';
                break;
            case '4':
                description = 'Valitud tunnid';
                extendedDescription = 'Pistikupesa lülitatakse sisse ainult valitud tundide ajal.';
                break;
        }
        document.getElementById('description').textContent = description;
        // Change title of infoButton using bootstrap API
        var infoButton = document.getElementById('infoButton');
        $(infoButton).attr('data-original-title', extendedDescription).tooltip();

    }

    // update the control type
    function updateControlType(unitId, controlType) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'includes/update_control_type.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    // console.log('Response:', xhr.responseText); // debug
                } else {
                    // console.log('Error:', xhr.status, xhr.statusText); // debug
                }
            }
        }
        xhr.send('unit_id=' + unitId + '&control_type=' + controlType);
    }

    // update the visibility of control type specific parameters
    function updateControlParametersVisibility(controlType) {
        document.querySelectorAll('[data-control-type]').forEach(function (row) {
            if (row.getAttribute('data-control-type') === controlType) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // update the switch state
    function updateOutputStateDisplay(unitId) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'includes/update_output_state.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                var outputState = xhr.responseText === '1' ? 'SEES' : 'VÄLJAS';
                var outputStateButton = document.querySelector('.outputStateButton[data-unit-id="' + unitId + '"]');
                outputStateButton.textContent = outputState;
                if (outputState === 'SEES') {
                    outputStateButton.style.backgroundColor = 'green';
                    outputStateButton.style.color = 'white';
                } else {
                    outputStateButton.style.backgroundColor = 'red';
                    outputStateButton.style.color = 'white';
                }
            }
        }
        xhr.send('unit_id=' + unitId);
    }

    // auto update the switch state
    function startAutoUpdateOutputState(unitId, interval) {
        updateOutputStateDisplay(unitId); // Initial update
        setInterval(function () {
            updateOutputStateDisplay(unitId);
        }, interval);
    }

    // when the control type changes, update the control type and control type specific parameters
    document.querySelectorAll('.controlType').forEach(function (element) {
        element.addEventListener('change', function () {
            var unitId = this.getAttribute('data-unit-id');
            var controlType = this.value;
            updateControlType(unitId, controlType);
            updateControlParametersVisibility(controlType);
            updateDescription(controlType); // Update the description when the control type changes
        });
    });

    // initialize the page
    function initializePage() {
        var controlTypeSelect = document.querySelector('.controlType');
        var unitId = controlTypeSelect.getAttribute('data-unit-id');
        var controlType = controlTypeSelect.value;

        updateControlParametersVisibility(controlType);
        updateDescription(controlType);
        startAutoUpdateOutputState(unitId, 2500); // Update every 5000ms (5 seconds)
    }

    initializePage(); // Call the initializePage function

</script>
