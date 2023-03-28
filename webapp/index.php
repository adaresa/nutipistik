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

<div class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="col-md-4 col-lg-4 col-sm-12 mx-auto">
        <form class="form loginform" method="POST">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    Logi sisse
                </div>
                <div class="card-body">
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
                                echo "<div class='alert alert-danger alert-dismissable fade show'>Vale kasutajanimi või parool.</div>";
                            }
                        } else {
                            echo "<div class='alert alert-danger alert-dismissable fade show'>Vale kasutajanimi või parool.</div>";
                        }
                    }
                    ?>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Kasutajanimi" required>
                        <label for="username">Kasutajanimi</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="pwd" name="password" placeholder="Parool" required>
                        <label for="pwd">Parool</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Jäta mind meelde</label>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary w-100">Sisene</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
