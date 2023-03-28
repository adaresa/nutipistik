<?php
// Make sure the user is logged in
session_start();
if (!isset($_SESSION['index'])) {
    header('LOCATION:index.php');
    die();
}

include_once('includes/header.php');
include_once('includes/energyConverter.php');
include_once('database_connect.php');

// Fetch unit, vat from ESPtable2 table
$device_id = $_SESSION['device_id'];
$result_unit_vat = mysqli_query($con, "SELECT ENERGY_TYPE, VAT FROM ESPtable2 WHERE id = $device_id");
$row_unit_vat = mysqli_fetch_array($result_unit_vat);
$unit = $row_unit_vat['ENERGY_TYPE'];
$vat = $row_unit_vat['VAT'];

// Fetch current_price, daily average from ElectricityPrices table
$result_prices = mysqli_query($con, "SELECT CURRENT_PRICE, AVERAGE_PRICE FROM ElectricityPrices");
$row = mysqli_fetch_array($result_prices);
$current_price = convert_unit($row['CURRENT_PRICE'], $unit, $vat);
$average_price = convert_unit($row['AVERAGE_PRICE'], $unit, $vat);
?>

<div class='container content-spacing'>

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Elektrihind</h1>
        </div>

        <div>

            <table class="table" style="font-size: 30px;">
                <tbody>
                    <tr>
                        <td>Praegune elektrihind: <strong>
                                <?php echo $current_price; ?>
                            </strong> €/
                            <?php echo $unit; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Päeva keskmine elektrihind: <strong>
                                <?php echo $average_price; ?>
                            </strong> €/
                            <?php echo $unit; ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Button to switch between table and line chart -->
            <button id="toggleView" class="btn btn-primary">Näita graafikut</button>

            <!-- Table with today's and tomorrow's electricity prices -->
            <div id="priceTable" style="display: block;">
                <?php include('includes/price_table.php'); ?>
            </div>

            <!-- Line chart with today's and tomorrow's electricity prices -->
            <div id="priceChart" class="chart-container" style="display: none;">
                <canvas id="todayChart"></canvas>
            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

    function drawChart(hours, todayPrices, tomorrowPrices, todayDate, tomorrowDate) {
        const ctx = document.getElementById('todayChart').getContext('2d');

        const todayData = {
            label: `Täna (${todayDate})`,
            data: todayPrices.prices,
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 2,
            fill: false,
        };

        const tomorrowData = {
            label: `Homme (${tomorrowDate})`,
            data: tomorrowPrices ? tomorrowPrices.prices : [],
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 2,
            fill: false,
        };

        const datasets = [todayData];

        if (tomorrowPrices) {
            datasets.push(tomorrowData);
        }

        const config = {
            type: 'bar',
            data: {
                labels: hours,
                datasets: datasets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: `Elektrihind (€/${todayPrices.unit})`,
                    },
                    tooltip: {
                        callbacks: {
                            title: function (context) {
                                const label = context[0].label;
                                const hour = label.split(':')[0];
                                return `${hour}:00-${hour}:59`;
                            },
                            label: function (context) {
                                const value = context.parsed.y;
                                return `${value.toFixed(3)} €/${todayPrices.unit}`;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Kellaaeg (CET)',
                        },
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Hind',
                        },
                    },
                },
            },
        };

        new Chart(ctx, config);
    }

    function initChart(hours, todayPrices, tomorrowPrices, todayDate, tomorrowDate) {
        drawChart(hours, todayPrices, tomorrowPrices, todayDate, tomorrowDate);
    }


    if (document.readyState === 'complete') {
        fetchData();
    } else {
        document.addEventListener('DOMContentLoaded', fetchData);
    }

    function fetchData() {
        fetch('includes/chart_data.php')
            .then(response => response.json())
            .then(data => {
                initChart(data.hours, data.today_prices, data.tomorrow_prices, data.today_date, data.tomorrow_date);
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const toggleButton = document.getElementById('toggleView');

        toggleButton.addEventListener('click', function () {
            const priceTable = document.getElementById('priceTable');
            const priceChart = document.getElementById('priceChart');

            if (priceTable.style.display === 'none') {
                priceTable.style.display = 'block';
                priceChart.style.display = 'none';
                toggleButton.textContent = 'Näita graafikut';
            } else {
                priceTable.style.display = 'none';
                priceChart.style.display = 'block';
                toggleButton.textContent = 'Näita tabelit';
            }
        });
    });
</script>

<?php include_once('includes/footer.php'); ?>
