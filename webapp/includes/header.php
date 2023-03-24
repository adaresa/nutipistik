<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Nutipistik</title>

    <!-- Custom styles -->
    <link rel="stylesheet" href="/includes/styles.css" <!-- Bootstrap Core CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="shortcut icon" href="assets/icon/favicon.ico" type="image/x-icon" />

    <!-- MetisMenu CSS -->
    <link href="assets/js/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="assets/css/sb-admin-2.css" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="assets/fonts/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <script src="assets/js/jquery.min.js" type="text/javascript"></script>
    <style>
        .selected-hours label {
            display: inline-block;
            width: 100px;
            height: 50px;
            line-height: 50px;
            text-align: center;
            margin: 5px;
            border-radius: 5px;
            background-color: #f9f9f9;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            user-select: none;
        }

        .selected-hours label:hover {
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);
        }

        .selected-hours input[type="checkbox"] {
            display: none;
        }

        .selected-hours input[type="checkbox"]:checked+span.hour-checkbox {
            background-color: #4CAF50;
            color: #fff;
            border-radius: 5px;
            box-sizing: border-box;
            border: 1px solid #4CAF50;
            /* Add this line */
        }

        .selected-hours label span.hour-checkbox {
            display: block;
            border: 1px solid #ccc;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 5px;
        }

        .smart-plug-indicator {
            font-size: 16px;
            font-weight: bold;
            margin: 10px;
        }

        .smart-plug-indicator span {
            color: #f00;
        }
    </style>




</head>

<body>

    <div id="wrapper">

        <!-- Navigation -->
        <?php if (isset($_SESSION['index'])): ?>
            <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="panel.php">
                        Nutipistik
                        <span id="connection-status" class="smart-plug-indicator" style="margin-left: 10px;">
                            Ühendus: <span class="status-circle"
                                style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-left: 5px;"></span>
                        </span>
                    </a>
                </div>
                <!-- Add the dropdown menu here -->
                <ul class="nav navbar-top-links navbar-right">
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                            <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-user">
                            <li><a href="profile.php"><i class="fa fa-user fa-fw"></i> Profiil</a></li>
                            <li><a href="settings.php"><i class="fa fa-gear fa-fw"></i> Seaded</a></li>
                            <li class="divider"></li>
                            <li><a href="logout.php"><i class="fa fa-sign-out fa-fw"></i> Logi välja</a></li>
                        </ul>
                    </li>
                </ul>
                <!-- End of the dropdown menu -->

                <div class="navbar-default sidebar" role="navigation">
                    <div class="sidebar-nav navbar-collapse">
                        <ul class="nav" id="side-menu">
                            <li>
                                <a href="panel.php"><i class="fa fa-dashboard fa-fw"></i> Juhtpaneel</a>
                            </li>

                            <li>
                                <a href="price.php"><i class="fa fa-bolt fa-fw"></i> Elektrihind</a>
                            </li>
                        </ul>
                    </div>
                    <!-- /.sidebar-collapse -->
                </div>
                <!-- /.navbar-static-side -->
            </nav>
        <?php endif; ?>

        <script>
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



        </script>
        <!-- The End of the Header -->