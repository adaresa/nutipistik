<?php
// Make sure the user is logged in
session_start();
if (!isset($_SESSION['index'])) {
    header('LOCATION:index.php');
    die();
}

include_once('includes/header.php');
include_once('includes/energyConverter.php'); ?>

<div id="page-wrapper">

    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Elektrihind</h1>
        </div>
        
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

    function drawChart(hours, todayPrices, tomorrowPrices) {
        const ctx = document.getElementById('todayChart').getContext('2d');

        const todayData = {
            label: "Täna",
            data: todayPrices.prices,
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 2,
            fill: false,
        };

        const tomorrowData = {
            label: "Homme",
            data: tomorrowPrices ? tomorrowPrices.prices : [],
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 2,
            fill: false,
        };

        const config = {
            type: 'line',
            data: {
                labels: hours,
                datasets: [todayData, tomorrowData],
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
                            text: 'Kellaaeg',
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
</script>
<script>
    function initChart(hours, todayPrices, tomorrowPrices) {
        console.log('Hours:', hours);
        console.log('Today Prices:', todayPrices);
        console.log('Tomorrow Prices:', tomorrowPrices);
        drawChart(hours, todayPrices, tomorrowPrices);
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
                initChart(data.hours, data.today_prices, data.tomorrow_prices);
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
    }
</script>


<script>
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