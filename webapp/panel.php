<?php
// Make sure the user is logged in
session_start();
if (!isset($_SESSION['index'])) {
    header('LOCATION:index.php');
    die();
}

$user_id = $_SESSION['user_id']; // Currently logged in user ID
$device_id = $_SESSION['device_id']; // Currently selected device ID
$region = $_SESSION['REGION'];
$unit = $_SESSION['ENERGY_TYPE'];
$vat = $_SESSION['VAT'];


include_once('includes/header.php');
include_once("database_connect.php");
include_once('includes/energyConverter.php');
include_once('includes/cheapestHours.php');
include_once('includes/smartHours.php');

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
?>

<div class='main-content'>

    <div class='container content-spacing'>
        <div class='row'>
            <div class='col-lg-12'>
                <h1 class='page-header'>Juhtpaneel</h1>
            </div>
        </div>

        <!-- Select the type of control -->
        <div>
            <?php
            $user_devices = mysqli_query($con, "SELECT device_id, device_name FROM user_devices WHERE user_id = '$user_id' ORDER BY device_order");
            // Grab the table out of the database
            $result = mysqli_query($con, "SELECT * FROM ESPtable2 WHERE id = '$device_id'");

            $has_devices = mysqli_num_rows($user_devices) > 0; // Check if the user has any devices
            
            if (!$has_devices) {
                ?>
                <div class='row'>
                    <div class='col-lg-12'>
                        <div class='alert alert-info'>
                            <h4 class='alert-heading'>Pole ühtegi seadet</h4>
                            <p>Sul pole ühtegi pistikut lisatud. Uue seadme lisamiseks järgige juhiseid Pistikute haldamise
                                lehel.</p>
                        </div>
                    </div>
                </div>
                <div class='row'>
                    <div class='col-lg-12'>
                        <a href='devices.php' class='btn btn-primary'>Pistikute haldamine</a>
                    </div>
                </div>
                <?php
            } else {

                //Now we create the table with all the values from the database      
                echo "<table class='table' style='font-size: 30px;'>
            <thead>
                <tr>";
                echo "<th>Pistiku nimi: <select class='deviceSelection' id='deviceSelect'>";
                while ($row = mysqli_fetch_array($user_devices)) {
                    $selected = $row['device_id'] == $device_id ? "selected" : "";
                    echo "<option value='{$row['device_id']}' $selected>{$row['device_name']}</option>";
                }
                echo "</select></th>
                    <th></th>
                </tr>
            </thead>
            
            <tbody>
            <tr class='active'>
                <td>Režiim</td>
                <td>
                    <span style='display: inline-block; vertical-align: middle;'>Pistikupesa olek</span>
                    <button class='infoButton' style='display: inline-block; vertical-align: middle;' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true'
                    title='Pistikupesa olek praeguse juhtimisrežiimiga.<br>Uuendatakse iga ~5 sekundi tagant.'>?</button>
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
                    <option ";
                    if ($control_type == 5) {
                        echo "selected";
                    }
                    echo " value='5'>Targad tunnid</option>
                    <option ";
                    if ($control_type == 6) {
                        echo "selected";
                    }
                    echo " value='6'>Ajaplaan</option>

                
                
                
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

                    $cheap_day_threshold = $row['CHP_DAY_THOLD'];
                    $expensive_day_threshold = $row['EXP_DAY_THOLD'];
                    $cheap_day_hours = $row['CHP_DAY_HOURS'];
                    $expensive_day_hours = $row['EXP_DAY_HOURS'];

                    $current_electricity_price = 0;
                    $average_electricity_price = 0;
                    $price_result = mysqli_query($con, "SELECT CURRENT_PRICE, AVERAGE_PRICE FROM ElectricityPrices WHERE region = '$region'");
                    if ($price_row = mysqli_fetch_array($price_result)) {
                        $current_electricity_price = $price_row["CURRENT_PRICE"];
                        $average_electricity_price = $price_row["AVERAGE_PRICE"];
                    }

                    // Get time ranges
                    $time_ranges = json_decode($row['TIME_RANGES'], false) ?: [];

                    // Filter expired time ranges and update the database if needed
                    $filtered_time_ranges = filter_and_update_time_ranges($con, $unit_id, $time_ranges);



                    echo "<table class='table' style='font-size: 30px;'>
            <thead>
                <tr>
                <th>Juhtimine</th>
                </tr>
            </thead>

            <tbody>
            <tr class='active'>
                    <td>
                        <span id='description' style='display: inline-block; vertical-align: middle;'></span>
                        <button id='infoButton' class='infoButton' style='display: inline-block; vertical-align: middle;' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true'
                        title=''>?</button>";
                    // PRICE LIMIT
                    echo "<p class='small-text' data-control-type='1'>Praegune elektrihind: <strong>" . convert_unit($current_electricity_price, $unit, $vat) . '</strong> €/' . $unit . "</p>";
                    echo "<p class='small-text' data-control-type='1'>Päeva keskmine elektrihind: <strong>" . convert_unit($average_electricity_price, $unit, $vat) . '</strong> €/' . $unit . "</p>";
                    // CHEAPEST HOURS
                    echo "<p class='small-text' data-control-type='3'>" . get_cheapest_hours() . "</p>";
                    // SMART HOURS
                    echo "<p class='small-text' data-control-type='5'>Päeva keskmine elektrihind: <strong>" . convert_unit($average_electricity_price, $unit, $vat) . '</strong> €/' . $unit . "</p>";
                    echo "<p class='small-text' data-control-type='5'>" . get_smart_hours(convert_unit($average_electricity_price, $unit, $vat)) . "</p>";

                    echo "
                    </td>
                </tr>";


                    // Price Limit
                    echo "
                <tr class='success' data-control-type='1'><td>
                <form action='update_values.php' method='post'>
                    <div class='input-group mb-2'>
                        <input type='number' step='0.0001' name='priceLimit' value='$price_limit' class='custom-input' title='priceLimit' />
                        <span class='input-group-text'>€/$unit</span>
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
                    <div class='input-group mb-2'>
                        <input type='number' name='cheapHours' value='$cheap_hours' class='custom-input' title='cheapHours' />
                        <span class='input-group-text'>tundi</span>
                    </div>
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
                    echo "</td></tr>";

                    // Smart Hours
                    echo "
                <tr class='success' data-control-type='5'>
                    <td>
                        <form action='update_values.php' method='post' id='smartHoursForm'>
                            <div class='row gy-3'>
                                <div class='col-6'>
                                    <div>
                                        <label for='cheapDayThreshold' style='display: inline-block; vertical-align: middle;'>Odava päeva lävend:</label>
                                        <button class='infoButton' style='display: inline-block; vertical-align: middle;' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true'
                                        title='Kui päeva keskmine elektrihind on allapool <b>Odava päeva lävendit</b>, on tegemist <b>Odava päevaga</b>.'>?</button>
                                    </div>
                                    
                                    <div class='input-group'>
                                        <input type='number' step='0.0001' name='cheapDayThreshold' value='$cheap_day_threshold' class='custom-input' title='cheapDayThreshold' min='0' id='cheapDayThreshold' />
                                        <span class='input-group-text'>€/$unit</span>
                                    </div>
                                </div>
                                <div class='col-6'>
                                    <div>
                                        <label for='cheapDayHours' style='display: inline-block; vertical-align: middle;'>Odava päeva tundide arv:</label>
                                        <button class='infoButton' style='display: inline-block; vertical-align: middle;' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true'
                                        title='Pistikupesa töötamise aeg <b>Odava päeva</b> korral.'>?</button>
                                    </div>
                                    <div class='input-group'>
                                        <input type='number' name='cheapDayHours' value='$cheap_day_hours' class='custom-input' title='cheapDayHours' min='0' max='24' />
                                        <span class='input-group-text'>tundi</span>
                                    </div>
                                </div>
                                <div class='col-6'>
                                    <div>
                                        <label for='expensiveDayThreshold' style='display: inline-block; vertical-align: middle;'>Kalli päeva lävend:</label>
                                        <button class='infoButton' style='display: inline-block; vertical-align: middle;' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true'
                                        title='Kui päeva keskmine elektrihind on üle <b>Kalli päeva lävendi</b>, on tegemist <b>Kalli päevaga</b>.'>?</button>
                                    </div>
                                    <div class='input-group'>
                                        <input type='number' step='0.0001' name='expensiveDayThreshold' value='$expensive_day_threshold' class='custom-input' title='expensiveDayThreshold' min='0' id='expensiveDayThreshold' />
                                        <span class='input-group-text'>€/$unit</span>
                                    </div>
                                </div>
                                <div class='col-6'>
                                    <div>
                                        <label for='expensiveDayHours' style='display: inline-block; vertical-align: middle;'>Kalli päeva tundide arv:</label>
                                        <button class='infoButton' style='display: inline-block; vertical-align: middle;' data-bs-toggle='tooltip' data-bs-placement='right' data-bs-html='true'
                                        title='Pistikupesa töötamise aeg <b>Kalli päeva</b> korral.'>?</button>
                                    </div>
                                    <div class='input-group'>
                                        <input type='number' name='expensiveDayHours' value='$expensive_day_hours' class='custom-input' title='expensiveDayHours' min='0' max='24' />
                                        <span class='input-group-text'>tundi</span>
                                    </div>
                                </div>
                            </div>
                            <div class='mt-3'>
                                <input type='hidden' name='unitID' value='$unit_id' />
                                <input type='submit' name='submit' value='Salvesta' class='btn btn-primary' />
                            </div>
                        </form>
                    </td>
                </tr>
                ";

                    // Schedule
                    echo "<tr class='success' data-control-type='6'><td>
                <form action='update_values.php' method='post' id='scheduleControlForm' class='row g-3'>
                    <div id='schedule-container' class='col-12'>";
                    foreach ($filtered_time_ranges as $index => $time_range) {
                        $start_time = DateTime::createFromFormat('Y-m-d H:i:s', $time_range->start)->format('d.m.Y H:i');
                        $end_time = DateTime::createFromFormat('Y-m-d H:i:s', $time_range->end)->format('d.m.Y H:i');
                        $unique_id = time() . '-' . $index;
                        echo "<div class='time-range d-flex align-items-center mb-3' id='time-range-$unique_id'>";
                        echo "<input type='text' class='flatpickr-input custom-input me-2' name='from[]' value='$start_time' placeholder='pp.kk.aaaa tt:mm' autocomplete='off'>";
                        echo "<span class='me-2'> - </span>";
                        echo "<input type='text' class='flatpickr-input custom-input me-2' name='to[]' value='$end_time' placeholder='pp.kk.aaaa tt:mm' autocomplete='off'>";
                        echo "<button type='button' class='btn btn-danger remove-time-range' data-target='time-range-$unique_id'>Eemalda</button>";
                        echo "</div>";
                    }
                    echo "</div>
                <div class='col-12'>
                    <button type='button' id='addTimeRange' class='btn btn-primary'>Lisa uus vahemik</button>
                </div>
                <input type='hidden' name='unitID' value='$unit_id' />
                <div class='col-12'>
                    <input type='submit' name='submit_schedule' value='Salvesta' class='btn btn-success' />
                </div>
            </form>";

                    echo "</td></tr>";

                }
                echo "</tbody></table><br>";
            }
            ?>

        </div>

    </div>

</div>

<?php include_once('includes/footer.php'); ?>

<?php

function filter_and_update_time_ranges($con, $unit_id, $time_ranges)
{
    $currentTime = new DateTime('now', new DateTimeZone('Europe/Tallinn'));
    $filtered_time_ranges = array();

    foreach ($time_ranges as $time_range) {
        $end_time = DateTime::createFromFormat('Y-m-d H:i:s', $time_range->end);
        $end_time_plus_one_minute = clone $end_time;
        $end_time_plus_one_minute->add(new DateInterval('PT1M'));
        if ($end_time_plus_one_minute > $currentTime) {
            $filtered_time_ranges[] = $time_range;
        }
    }

    if (count($filtered_time_ranges) !== count($time_ranges)) {
        $filtered_time_ranges_json = json_encode($filtered_time_ranges);
        $query = "UPDATE ESPtable2 SET TIME_RANGES = '$filtered_time_ranges_json' WHERE id = '$unit_id'";
        mysqli_query($con, $query);
    }

    return $filtered_time_ranges;
}

?>

<script>
    // Change selected device
    var deviceSelectElement = document.getElementById('deviceSelect');
    if (deviceSelectElement) {
        deviceSelectElement.addEventListener('change', function () {
            const deviceId = this.value;
            fetch('includes/update_selected_device.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'device_id=' + deviceId
            }).then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    console.error('Error updating selected device:', response.statusText);
                }
            });
        });
    }


    // Change selected device
    var deviceSelectElement = document.getElementById('deviceSelect');
    if (deviceSelectElement) {
        deviceSelectElement.addEventListener('change', function () {
            const deviceId = this.value;
            fetch('includes/update_selected_device.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'device_id=' + deviceId
            }).then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    console.error('Error updating selected device:', response.statusText);
                }
            });
        });
    }

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
            case '5':
                description = 'Targad tunnid';
                extendedDescription = 'Pistikupesa töötamise aeg sõltub päeva keskmisest elektrihinnast:<br>1. Alla odava päeva lävendi: odava päeva tundide arv.<br>2. Üle kalli päeva lävendi: kalli päeva tundide arv.<br>3. Lävendite vahel: tundide arv arvutatakse lineaarselt.<br>Pistikupesa lülitub sisse ööpäeva odavamate tundide jooksul.';
                break;
            case '6':
                description = 'Ajaplaan';
                extendedDescription = 'Pistikupesa lülitatakse sisse vastavalt ajaplaanile.<br>Aegunud vahemikud eemaldatakse automaatselt.';
        }
        document.getElementById('description').textContent = description;
        // Change title of infoButton using bootstrap API
        var infoButton = document.getElementById("infoButton");
        infoButton.setAttribute("data-bs-original-title", extendedDescription);
        var tooltip = bootstrap.Tooltip.getInstance(infoButton);
        var tooltip = bootstrap.Tooltip.getInstance(infoButton);
        if (!tooltip) {
            tooltip = new bootstrap.Tooltip(infoButton);
        }
        tooltip._fixTitle();
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
        setInterval(function () { updateOutputStateDisplay(unitId); }, interval);
    }

    // validate Smart Hours form
    var smartHoursForm = document.getElementById('smartHoursForm');
    if (smartHoursForm) {
        smartHoursForm.addEventListener('submit', function (event) {
            var cheapDayThreshold = document.getElementById('cheapDayThreshold');
            var expensiveDayThreshold = document.getElementById('expensiveDayThreshold');

            if (parseFloat(cheapDayThreshold.value) >= parseFloat(expensiveDayThreshold.value)) {
                event.preventDefault();
                alert('Odava päeva lävend peab olema madalam kui kalli päeva lävend.');
            }
        });
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


    // Schedule
    let timeRangeIndex = 0;
    document.addEventListener("DOMContentLoaded", function () {
        const existingTimeRanges = document.querySelectorAll(".time-range");
        let maxIndex = -1;
        existingTimeRanges.forEach((timeRange) => {
            const index = parseInt(timeRange.getAttribute("data-index"), 10);
            if (index > maxIndex) {
                maxIndex = index;
            }

            const removeButton = timeRange.querySelector(".remove-time-range");
            if (removeButton) {
                attachRemoveButtonListener(removeButton);
            }
        });
        timeRangeIndex = maxIndex + 1;

        const addTimeRangeButton = document.getElementById("addTimeRange");
        if (addTimeRangeButton) {
            addTimeRangeButton.addEventListener("click", addTimeRange);
        }
    });

    var scheduleControlForm = document.getElementById('scheduleControlForm');
    if (scheduleControlForm) {
        scheduleControlForm.addEventListener('submit', function (event) {
            var timeRanges = document.querySelectorAll('.time-range');
            var hasInvalidRange = false;

            timeRanges.forEach(function (timeRange) {
                var fromInput = timeRange.querySelector('input[name="from[]"]');
                var toInput = timeRange.querySelector('input[name="to[]"]');
                var fromTime = parseDate(fromInput.value);
                var toTime = parseDate(toInput.value);

                timeRange.classList.remove('bg-danger');
                fromInput.classList.remove('bg-danger');
                toInput.classList.remove('bg-danger');

                if (fromInput.value && toInput.value && fromTime >= toTime) {
                    hasInvalidRange = true;
                    fromInput.classList.add('bg-danger');
                    toInput.classList.add('bg-danger');
                }
            });

            if (hasInvalidRange) {
                event.preventDefault();
                alert('Algusaeg ei tohiks tulla enne lõppaega.');
                return;
            }
        });
    }

    function parseDate(str) {
        const [day, month, year, hours, minutes] = str.match(/^(\d{2})\.(\d{2})\.(\d{4})\s(\d{2}):(\d{2})$/).slice(1);
        return new Date(year, month - 1, day, hours, minutes);
    }


    function attachRemoveButtonListener(removeButton) {
        removeButton.addEventListener("click", function (event) {
            const target = event.target.getAttribute("data-target");
            const elementToRemove = document.getElementById(target);
            if (elementToRemove) {
                elementToRemove.remove();
            }
        });
    }


    function addTimeRange() {
        const timeRangeId = Date.now();

        const scheduleContainer = document.getElementById("schedule-container");
        const timeRangeContainer = document.createElement("div");
        timeRangeContainer.classList.add("time-range", "d-flex", "align-items-center", "mb-3");
        timeRangeContainer.id = `time-range-${timeRangeId}`;

        const inputFrom = document.createElement("input");
        inputFrom.type = "text";
        inputFrom.classList.add("flatpickr-input", "custom-input", "me-2");
        inputFrom.name = "from[]";
        inputFrom.setAttribute("placeholder", "pp.kk.aaaa tt:mm");
        inputFrom.setAttribute("autocomplete", "off");
        timeRangeContainer.appendChild(inputFrom);

        const span = document.createElement("span");
        span.classList.add("me-2");
        span.innerHTML = " - ";
        timeRangeContainer.appendChild(span);

        const inputTo = document.createElement("input");
        inputTo.type = "text";
        inputTo.classList.add("flatpickr-input", "custom-input", "me-2");
        inputTo.name = "to[]";
        inputTo.setAttribute("placeholder", "pp.kk.aaaa tt:mm");
        inputTo.setAttribute("autocomplete", "off");
        timeRangeContainer.appendChild(inputTo);

        const removeButton = document.createElement("button");
        removeButton.type = "button";
        removeButton.textContent = "Eemalda";
        removeButton.classList.add("btn", "btn-danger", "remove-time-range");
        removeButton.setAttribute("data-target", `time-range-${timeRangeId}`);
        timeRangeContainer.appendChild(removeButton);

        scheduleContainer.appendChild(timeRangeContainer);

        attachRemoveButtonListener(removeButton);

        flatpickr(inputFrom, {
            enableTime: true,
            dateFormat: "d.m.Y H:i",
            time_24hr: true,
            minDate: "today",
            locale: "et"
        });

        flatpickr(inputTo, {
            enableTime: true,
            dateFormat: "d.m.Y H:i",
            time_24hr: true,
            minDate: "today",
            locale: "et"
        });

        timeRangeIndex++;
    }

    // initialize the page
    function initializePage() {
        var controlTypeSelect = document.querySelector('.controlType');
        // Check if exists first, if exists, then get the attribute, then start the auto update
        if (controlTypeSelect) {
            var unitId = controlTypeSelect.getAttribute('data-unit-id');
            startAutoUpdateOutputState(unitId, 1000); // Update every 1 seconds
        }
        // Do the same for the control type select
        if (controlTypeSelect) {
            var controlType = controlTypeSelect.value;
            updateControlParametersVisibility(controlType);
            updateDescription(controlType);
        }
    }

    initializePage(); // Call the initializePage function

</script>