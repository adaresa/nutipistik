<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Nutipistik</title>

    <!-- jQuery -->
    <script src="assets/js/jquery.min.js?v=<?php echo filemtime('assets/js/jquery.min.js'); ?>"
        type="text/javascript"></script>


    <!-- Custom styles -->
    <link rel="stylesheet" href="/includes/styles.css?v=<?php echo filemtime('includes/styles.css'); ?>" />

    <!-- Bootstrap Core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>

    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/icon/favicon.ico" type="image/x-icon" />

    <!-- MetisMenu CSS -->
    <link
        href="assets/js/metisMenu/metisMenu.min.css?v=<?php echo filemtime('assets/js/metisMenu/metisMenu.min.css'); ?>"
        rel="stylesheet">


    <!-- Custom Fonts -->
    <link
        href="assets/fonts/font-awesome/css/font-awesome.min.css?v=<?php echo filemtime('assets/fonts/font-awesome/css/font-awesome.min.css'); ?>"
        rel="stylesheet" type="text/css">

</head>

<style>
    .tooltip {
        font-size: 14px;
    }

    .tooltip-inner {
        max-width: 300px;
        /* Update this value according to your needs */
    }

    .content-spacing {
        margin-top: 2rem;
        /* Adjust the value as needed */
    }
</style>

<body>



    <!-- Navigation -->
    <?php if (isset($_SESSION["index"])): ?>
        <nav class="navbar navbar-expand-lg navbar-light bg-light" role="navigation">
            <div class="container-fluid">
                <a class="navbar-brand" href="panel.php">
                    <img src="assets/icon/favicon.ico" width="30" height="30" class="d-inline-block align-top"
                        alt="Nutipistik logo">
                    Nutipistik
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div id="connection-status" class="navbar-brand ms-3" data-bs-toggle="tooltip" data-bs-placement="bottom"
                    title="Kui ühendus on roheline, siis on nutipistik ühendatud juhtpaneeliga.">
                    Ühendus:
                    <span class="status-circle"
                        style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-left: 5px;">
                    </span>
                </div>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="panel.php"><i class="fa fa-dashboard fa-fw"></i> Juhtpaneel</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="price.php"><i class="fa fa-bolt fa-fw"></i> Elektrihind</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-user fa-fw"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="profile.php"><i class="fa fa-user fa-fw"></i>
                                        Profiil</a></li>
                                <li><a class="dropdown-item" href="settings.php"><i class="fa fa-gear fa-fw"></i>
                                        Seaded</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fa fa-sign-out fa-fw"></i> Logi
                                        välja</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

    <?php endif; ?>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // JavaScript code to periodically check the smart plug's connection status
            function updateConnectionStatus() {
                $.getJSON("check_connection.php", function (data) {
                    var statusCircle = $("#connection-status .status-circle");

                    if (data.status === "connected") {
                        statusCircle.css("background-color", "green");
                    } else if (data.status === "disconnected") {
                        statusCircle.css("background-color", "red");
                    } else {
                        statusCircle.css("background-color", "grey");
                    }
                });
            }

            // Call the function initially
            updateConnectionStatus();

            // Update the status every 2 seconds
            setInterval(updateConnectionStatus, 2000);
        });
    </script>
