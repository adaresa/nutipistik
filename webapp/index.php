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
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['device_id'] = $user['selected_device_id'];
        $_SESSION['REGION'] = $user['REGION'];
        $_SESSION['ENERGY_TYPE'] = $user['ENERGY_TYPE'];
        $_SESSION['VAT'] = $user['VAT'];
        header('LOCATION:panel.php');
        die();
    }
}

include 'includes/header.php';

$register_alert = '';
$login_alert = '';

if (isset($_POST['register'])) {
    $reg_username = $_POST['reg-username'];
    $reg_password = $_POST['reg-password'];
    $reg_confirm_password = $_POST['reg-confirm-password'];

    if ($reg_password === $reg_confirm_password) {
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("s", $reg_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $hashed_password = password_hash($reg_password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, password) VALUES (?, ?)";
            $stmt = $con->prepare($query);
            $stmt->bind_param("ss", $reg_username, $hashed_password);
            $stmt->execute();

            $login_alert = "<div class='alert alert-success alert-dismissable fade show'>Registreerimine õnnestus. Palun logi sisse.</div>";
        } else {
            $register_alert = "<div class='alert alert-danger alert-dismissable fade show'>Kasutajanimi on juba kasutusel. Palun proovige uuesti.</div>";
        }
    } else {
        $register_alert = "<div class='alert alert-danger alert-dismissable fade show'>Paroolid ei kattu. Palun proovige uuesti.</div>";
    }
}

?>

<div class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="col-md-4 col-lg-4 col-sm-12">
        <div>
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
                                    $_SESSION['user_id'] = $user['id'];
                                    $_SESSION['device_id'] = $user['selected_device_id'];
                                    $_SESSION['REGION'] = $user['REGION'];
                                    $_SESSION['ENERGY_TYPE'] = $user['ENERGY_TYPE'];
                                    $_SESSION['VAT'] = $user['VAT'];
                                    header('LOCATION:panel.php');
                                    die();
                                } else {
                                    echo "<div class='alert alert-danger alert-dismissable fade show'>Vale kasutajanimi või parool.</div>";
                                }
                            } else {
                                echo "<div class='alert alert-danger alert-dismissable fade show'>Vale kasutajanimi või parool.</div>";
                            }
                        }

                        echo $login_alert;
                        ?>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="username" name="username"
                                placeholder="Kasutajanimi" required>
                            <label for="username">Kasutajanimi</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="pwd" name="password" placeholder="Parool"
                                required>
                            <label for="pwd">Parool</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Jäta mind meelde</label>
                        </div>
                        <button type="submit" name="submit" class="btn btn-primary w-100 mb-2">Sisene</button>
                        <a href="#" class="btn btn-secondary w-100" id="register-btn">Registreeri</a>
                    </div>
                </div>
            </form>
            <form class="form d-none" id="register-form" method="POST">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        Registreerimine
                    </div>
                    <div class="card-body">
                        <?php echo $register_alert; ?>
                        <!-- Registration form fields go here -->
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="reg-username" name="reg-username"
                                placeholder="Username" required>
                            <label for="reg-username">Kasutajanimi</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="reg-pwd" name="reg-password"
                                placeholder="Password" required>
                            <label for="reg-pwd">Parool</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="reg-confirm-pwd" name="reg-confirm-password"
                                placeholder="Confirm Password" required>
                            <label for="reg-confirm-pwd">Kinnita parool</label>
                        </div>
                        <button type="submit" name="register" class="btn btn-primary w-100">Registreeri</button>
                        <a href="#" class="btn btn-secondary w-100 mt-2" id="back-to-login">Tagasi sisselogimisele</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.getElementById('register-btn').addEventListener('click', function () {
        document.querySelector('.loginform').classList.add('d-none');
        document.getElementById('register-form').classList.remove('d-none');
    });

    document.getElementById('back-to-login').addEventListener('click', function () {
        document.getElementById('register-form').classList.add('d-none');
        document.querySelector('.loginform').classList.remove('d-none');
    });

    <?php if ($register_alert !== ''): ?>
        document.querySelector('.loginform').classList.add('d-none');
        document.getElementById('register-form').classList.remove('d-none');
    <?php endif; ?>
</script>
<?php include 'includes/footer.php';?>
