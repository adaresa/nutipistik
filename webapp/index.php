<?php
require_once('secret.php'); // get correct username and password
session_start();
echo isset($_SESSION['index']);

if (isset($_SESSION['index'])) {
	header('LOCATION:panel.php');
	die();
}

# Check if user has selected to remember the login
if (isset($_COOKIE['remember'])) {
	# Check if the cookie is valid
	if ($_COOKIE['remember'] == '123') {
		# Set the session
		$_SESSION['index'] = true;
		# Redirect to the panel
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
					if ($username == $_USERNAME && $password == $_PASSWORD) {
						if (isset($_POST['remember'])) {
							$token = '123';
							# Set the cookie
							setcookie('remember', $token, time() + (86400 * 30), "/"); # 30 days							
						}

						$_SESSION['index'] = true;
						header('LOCATION:panel.php');
						die();
					} { // if username and password are not correct
						echo "<div class='alert alert-danger alert-dismissable fade in'>Vale kasutajanimi või parool.</div>";
					}
				}
				?>
				<!-- Username -->
				<div class="form-group">
					<label for="username">Kasutajanimi:</label>
					<input type="text" class="form-control" id="username" name="username" required>
				</div>
				<!-- Password -->
				<div class="form-group">
					<label for="pwd">Parool:</label>
					<input type="password" class="form-control" id="pwd" name="password" required>
				</div>
				<!-- Remember me -->
				<div class="checkbox">
					<label><input type="checkbox" id="remember" name="remember">Jäta mind meelde</label>
				</div>
				<!-- Submit -->
				<button type="submit" name="submit" class="btn btn-success">Sisene</button>
			</div>
		</div>
	</form>
</div>

<?php include 'includes/footer.php'; ?>