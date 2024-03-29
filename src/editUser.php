<?php
if (!isset($_SESSION["session"])) {
	header("Location: login.php");
	die();
}

if (isset($_GET['userId'])) {
	$_SESSION['userId'] = $_GET['userId'];
	$userId = $_SESSION['userId'];
} else {
	echo "Missing parameter \"userId\" ";
	die();
}

require "vendor/autoload.php";

$service = new PHPSupabase\Service(
	"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhzZXBqZ3h4b3pleWt0amtiZXdjIiwicm9sZSI6ImFub24iLCJpYXQiOjE2NzYwMzgwODQsImV4cCI6MTk5MTYxNDA4NH0.zusO9r5QquROh2XfQ6CIM0sbL3Re2KPtSOsHK7lsPfc",
	"https://hsepjgxxozeyktjkbewc.supabase.co/rest/v1/"
);

$userDataQuery = $service->initializeQueryBuilder();
$citySateQuery = $service->initializeQueryBuilder();

$firstname = '';
$lastname = '';
$birthday = '';
$state = '';
$city = '';

try {
	$fetchUserData = $userDataQuery
		->select('*')
		->from('users')
		->join('cities', 'id')
		->where('id', "eq.$userId") //eq -> equal
		->execute()
		->getResult();

	$fetchCityState = $citySateQuery->select('*')
		->from('cities')
		->join('states', 'id')
		->execute()
		->getResult();

	for ($i = 0; $i < sizeof($fetchUserData); $i++) {
		$userData = json_decode(json_encode($fetchUserData[$i]), true);
		// Value to search for
		$search_value = $userData['city_id'];

		// Use array_filter to find the record(s)
		$stateId_filtered = array_filter($fetchCityState, function ($user) use ($search_value) {
			return $user->id == $search_value;
		});

		$stateId = array_values($stateId_filtered);

		$cityState = json_decode(json_encode($stateId[0]), true);
		$firstname = $userData['firstName'];
		$lastname = $userData['lastName'];
		$birthday = $userData['birthday'];
		$state = $cityState['state_id'];
		$city = $userData['cities']['city'];
	}
} catch (Exception $e) {
	echo $e->getMessage();
}


$listCitiesQuery = $service->initializeQueryBuilder();
try {
	$listCities = $listCitiesQuery->select('id, state')
		->from('states')
		->join('cities', 'id')
		->execute()
		->getResult();

} catch (Exception $e) {
	echo $e->getMessage();
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
		<h1 class="text-center">Edit User</h1>
		<form action="scripts-editUser.php" method="POST" class="auth-form container">
			<p>
				<?php
				if (isset($_SESSION['editUser_error'])) {
					echo '<div class="error">' . $_SESSION['editUser_error'] . '</div>';
					unset($_SESSION['editUser_error']);
				}
				?>
			</p>
			<label for="firstname"> Name </label>
			<input type="text" name="firstname" autofocus value="<?php echo $firstname ?>" />
			<label for="lastname"> Lastname </label>
			<input type="text" name="lastname" value="<?php echo $lastname ?>" />
			<label for="birthday"> Birthday </label>
			<input type="date" name="birthday" value="<?php echo $birthday ?>" />
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
					disabled value="<?php echo $city ?>">
				<div id="search-results"></div>
			</div>

			<script>
				document.getElementById('state').value = <?php echo $state ?>;
				let cities = <?php echo json_encode($listCities); ?>;

				const citySearch = document.getElementById('city');
				citySearch.disabled = true;
				checkIfSelected();

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
			<button type="submit" onclick="checkSession()">Edit User</button>
		</form>
		<main>