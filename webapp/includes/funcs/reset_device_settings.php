<?php
function reset_device_settings($con, $device_id) {
    $default_last_update = "2022-02-02 22:22:22";
    $default_control_type = 1;
    $default_button_state = 0;
    $default_price_limit = 0;
    $default_cheapest_hours = 0;
    $default_selected_hours = "000000000000000000000000";
    $default_avg_day_hours = 0;
    $default_chp_day_hours = 0;
    $default_exp_day_hours = 0;
    $default_chp_day_thold = 0;
    $default_exp_day_thold = 0;
    $default_time_ranges = "[]";
    $default_output_state = 0;

    $update_esp_table2 = "UPDATE ESPtable2 SET LAST_UPDATE = '$default_last_update', CONTROL_TYPE = '$default_control_type', BUTTON_STATE = '$default_button_state', PRICE_LIMIT = '$default_price_limit', CHEAPEST_HOURS = '$default_cheapest_hours', SELECTED_HOURS = '$default_selected_hours', AVG_DAY_HOURS = '$default_avg_day_hours', CHP_DAY_HOURS = '$default_chp_day_hours', EXP_DAY_HOURS = '$default_exp_day_hours', CHP_DAY_THOLD = '$default_chp_day_thold', EXP_DAY_THOLD = '$default_exp_day_thold', TIME_RANGES = '$default_time_ranges', OUTPUT_STATE = '$default_output_state' WHERE id = '$device_id'";
    
    mysqli_query($con, $update_esp_table2);
}
?>
