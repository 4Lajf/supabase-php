<?php
session_start();

if (!isset($_SESSION["session"])) {
	header("Location: login.php");
	die();
}
require "vendor/autoload.php";

$service = new PHPSupabase\Service(
	"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhzZXBqZ3h4b3pleWt0amtiZXdjIiwicm9sZSI6ImFub24iLCJpYXQiOjE2NzYwMzgwODQsImV4cCI6MTk5MTYxNDA4NH0.zusO9r5QquROh2XfQ6CIM0sbL3Re2KPtSOsHK7lsPfc",
	"https://hsepjgxxozeyktjkbewc.supabase.co/rest/v1/"
);

function console_log($output, $with_script_tags = true)
{
	$js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) .
		');';
	if ($with_script_tags) {
		$js_code = '<script>' . $js_code . '</script>';
	}
	echo $js_code;
}

$listCitiesQuery = $service->initializeQueryBuilder();

try {
	$listCities = $listCitiesQuery->select('id, state')
		->from('states')
		->join('cities', 'id')
		->execute()
		->getResult();

} catch (Exception $e) {
	echo "An error occured phase 1";
	console_log($e->getMessage());
	die();
}
console_log($listCities);

?>

<!DOCTYPE html>
<main>

	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<link href="/dist/output.css" rel="stylesheet" />
		<link rel="stylesheet" href="https://unpkg.com/@picocss/pico@1.*/css/pico.min.css" />

		<style>
		</style>

	</head>

	<body>
		<h1 class="text-center">Add User</h1>
		<form action="scripts-addUser.php" method="POST" class="auth-form container">
			<p>
				<?php
				if (isset($_SESSION['addUser_error'])) {
					echo '<div class="error">' . $_SESSION['addUser_error'] . '</div>';
					unset($_SESSION['addUser_error']);
				}
				?>
			</p>
			<label for="firstname"> Name </label>
			<input type="text" name="firstname" required autofocus />
			<label for="lastname"> Lastname </label>
			<input type="text" name="lastname" required />
			<label for="birthday"> Birthday </label>
			<input type="date" name="birthday" required />
			<label for="state"> State </label>
			<select name="state" id="state">
				<option value="1">Wielkopolskie</option>
				<option value="2">Zachodniopomorskie</option>
				<option value="3">Śląskie</option>
			</select>
			<div id="search-container">
				<label for="search">Search City:</label>
				<input type="text" id="search" oninput="searchCity()" placeholder="Search for a city...">
				<div id="search-results"></div>
			</div>

			<script>
				let cities = <?php echo json_encode($listCities); ?>;

				document.getElementById('state').addEventListener('change', function () {
					const state = this.value;
					const searchContainer = document.getElementById('search-container');
					const searchResults = document.getElementById('search-results');

					document.getElementById('search').value = '';
				});

				function searchCity() {
					const state = document.getElementById('state').value;
					const searchTerm = document.getElementById('search').value.trim().toLowerCase();
					const searchResults = document.getElementById('search-results');

					if (!searchTerm) {
						searchResults.innerHTML = '';
						return;
					}
					let searchItem = cities.find(item => item['id'] === Number(state));
					const matchedCities = searchItem.cities.filter(city => city.city.toLowerCase().startsWith(searchTerm)).slice(0, 10);

					if (matchedCities.length > 0) {
						searchResults.innerHTML = matchedCities.map(city => `<a class="city-result">${city.city}</a><br>`).join('');
						const cityResults = document.getElementsByClassName('city-result');

						for (let i = 0; i < cityResults.length; i++) {
							cityResults[i].addEventListener('click', function () {
								document.getElementById('search').value = this.textContent;
							});
						}
					} else {
						searchResults.innerHTML = `<p>City <b><i>${searchTerm}</b></i> will be added to the database</p>`;
					}
				}

			</script>

			<button type="submit">Add User</button>
		</form>
		<main>