<?php
session_start();
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
		<h1 class="text-center">Login</h1>
		<form action="scripts-login.php" method="POST" class="auth-form container">
			<p>
				<?php if (isset($_SESSION["status"])) {
					echo $_SESSION["status"];
				} ?>
			</p>
			<label for="email"> Email </label>
			<input type="text" name="email" />
			<label for="password"> Password </label>
			<input type="password" name="password" />
			<button type="submit">Login</button>
		</form>
	</body>
</main>