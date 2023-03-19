<?php
require_once('database_connect.php');
session_start();

if (isset($_SESSION['index'])) {
    header('LOCATION:panel.php');
    die();
}

if (isset($_COOKIE['remember'])) {
    $token = $_COOKIE['remember'];
    $query = "SELECT * FROM users WHERE token = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['index'] = true;
        $_SESSION['device_id'] = $user['device_id'];
        header('LOCATION:panel.php');
        die();
    }
}

include 'includes/header.php'; ?>

<div class="col-md-4 col-md-offset-4">
    <form class="form loginform" method="POST">
        <div class="login-panel panel panel-default">
            <div class="panel-heading">Logi sisse</div>

            <div class="panel-body">
                <?php
                if (isset($_POST['submit'])) {
                    $username = $_POST['username'];
                    $password = $_POST['password'];

                    $query = "SELECT * FROM users WHERE username = ?";
                    $stmt = $con->prepare($query);
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $user = $result->fetch_assoc();
                        if (password_verify($password, $user['password'])) {
                            if (isset($_POST['remember'])) {
                                $token = bin2hex(random_bytes(16));
                                $query = "UPDATE users SET token = ? WHERE id = ?";
                                $stmt = $con->prepare($query);
                                $stmt->bind_param("si", $token, $user['id']);
                                $stmt->execute();

                                setcookie('remember', $token, time() + (86400 * 30), "/");
                            }

                            $_SESSION['index'] = true;
                            $_SESSION['device_id'] = $user['device_id'];
                            header('LOCATION:panel.php');
                            die();
                        } else {
                            echo "<div class='alert alert-danger alert-dismissable fade in'>Vale kasutajanimi või parool.</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger alert-dismissable fade in'>Vale kasutajanimi või parool.</div>";
                    }
                }


                ?>
                <div class="form-group">
                    <label for="username">Kasutajanimi:</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="pwd">Parool:</label>
                    <input type="password" class="form-control" id="pwd" name="password" required>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox" id="remember" name="remember">Jäta mind meelde</label>
                </div>
                <button type="submit" name="submit" class="btn btn-success">Sisene</button>
            </div>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>