<?php

if (isset($_SESSION["email"])) {
	header("Location: protected.php");
	die();
}
?>
<!DOCTYPE html>
<main>

	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<link href="/dist/output.css" rel="stylesheet" />
		<link rel="stylesheet" href="https://unpkg.com/@picocss/pico@1.*/css/pico.min.css" />
	</head>

	<body>
		<h1 class="text-center">Registration is currently disabled</h1>
		<p>
			<?php
			if (isset($_SESSION['register_error'])) {
				echo '<div class="error">' . $_SESSION['register_error'] . '</div>';
				unset($_SESSION['register_error']);
			}
			?>
		</p>
		<form action="scripts-register.php" method="POST" class="auth-form container">
			<label for="username"> Username </label>
			<input type="text" name="username" required autofocus disabled />
			<label for="email"> E-mail </label>
			<input type="text" name="email" required disabled />
			<label for="passowrd"> Password </label>
			<input type="password" name="password" required disabled />
			<label for="confirmPassword"> Confirm Password </label>
			<input type="password" name="confirmPassword" required disabled />
			<button type="submit" disabled>Register</button>
		</form>
	</body>
</main>