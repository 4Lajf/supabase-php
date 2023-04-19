<?php
if (!isset($_SESSION["session"])) {
	header("Location: login.php");
	die();
}
require "vendor/autoload.php";

$service = new PHPSupabase\Service(
	"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhzZXBqZ3h4b3pleWt0amtiZXdjIiwicm9sZSI6ImFub24iLCJpYXQiOjE2NzYwMzgwODQsImV4cCI6MTk5MTYxNDA4NH0.zusO9r5QquROh2XfQ6CIM0sbL3Re2KPtSOsHK7lsPfc",
	"https://hsepjgxxozeyktjkbewc.supabase.co/rest/v1/"
);

$listCitiesQuery = $service->initializeQueryBuilder();

try {
	$listCities = $listCitiesQuery->select('id, state')
		->from('states')
		->join('cities', 'id')
		->execute()
		->getResult();

} catch (Exception $e) {
	echo "An error occured during loading cities...";
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
		<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>

		<style>
		</style>

	</head>

	<script>
		const SUPABASE_URL = 'https://hsepjgxxozeyktjkbewc.supabase.co'
		const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhzZXBqZ3h4b3pleWt0amtiZXdjIiwicm9sZSI6ImFub24iLCJpYXQiOjE2NzYwMzgwODQsImV4cCI6MTk5MTYxNDA4NH0.zusO9r5QquROh2XfQ6CIM0sbL3Re2KPtSOsHK7lsPfc'
		const _supabase = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY)

		async function checkSession() {
			let session = await _supabase.auth.getSession()
			console.log("session", session)
			let isSession = session.data.session ? true : false;
			console.log(isSession)
			if (isSession === false) {
				window.location.href = '/scripts-logout.php';
			}
		}
		checkSession()
	</script>

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
			<select name="state" id="state" onchange="checkIfSelected()">
				<option value="0">Choose a state</option>
				<option value="1">Wielkopolskie</option>
				<option value="2">Zachodniopomorskie</option>
				<option value="3">Śląskie</option>
			</select>
			<div id="search-container">
				<label for="search">Search City:</label>
				<input type="text" name="city" id="city" oninput="searchCity()" placeholder="Search for a city..."
					disabled>
				<div id="search-results"></div>
			</div>

			<script>
				let cities = <?php echo json_encode($listCities); ?>;
				const citySearch = document.getElementById('city');
				citySearch.disabled = true;

				function checkIfSelected() {
					const stateSelect = document.getElementById('state');
					var stateValue = stateSelect.options[stateSelect.selectedIndex].value;
					console.log(stateValue);
					if (stateValue == 0) {
						citySearch.disabled = true;
					} else {
						citySearch.disabled = false;
					}
				}

				document.getElementById('state').addEventListener('change', function () {
					const state = this.value;
					const searchContainer = document.getElementById('search-container');
					const searchResults = document.getElementById('search-results');

					document.getElementById('city').value = '';
				});

				function searchCity() {
					const state = document.getElementById('state').value;
					const searchTerm = document.getElementById('city').value.trim().toLowerCase();
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
								document.getElementById('city').value = this.textContent;
							});
						}
					} else {
						searchResults.innerHTML = `<p>City <b><i>${searchTerm}</b></i> will be added to the database</p>`;
					}
				}

			</script>

			<button type="submit" onclick="checkSession()">Add User</button>
		</form>
		<main>