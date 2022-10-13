<?php
require_once('secret.php'); // get correct username and password
session_start();
echo isset($_SESSION['index']);
if (isset($_SESSION['index'])) {
	header('LOCATION:panel.php');
	die();
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
						$_SESSION['index'] = true;
						header('LOCATION:panel.php');
						die();
					} { // if username and password are not correct
						echo "<div class='alert alert-danger alert-dismissable fade in'>Username and Password do not match.</div>";
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
				<!-- Submit -->
				<button type="submit" name="submit" class="btn btn-success">Sisene</button>
			</div>
		</div>
	</form>
</div>

<?php include 'includes/footer.php'; ?>