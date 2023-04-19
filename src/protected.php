<!-- TOOD: make addUser add users to auth table
merge users and profiles table
Make Hide Cities actually hide cities -->

<?php
//Jeżeli sesja nie istnieje (użytkownik nie jest zalogowany) nie pokazuj strony
if (!isset($_SESSION["session"])) {
    header("Location: login.php");
    die();
}

//Załaduj biblioteke Supabase
require "vendor/autoload.php";
$service = new PHPSupabase\Service(
    "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhzZXBqZ3h4b3pleWt0amtiZXdjIiwicm9sZSI6ImFub24iLCJpYXQiOjE2NzYwMzgwODQsImV4cCI6MTk5MTYxNDA4NH0.zusO9r5QquROh2XfQ6CIM0sbL3Re2KPtSOsHK7lsPfc",
    "https://hsepjgxxozeyktjkbewc.supabase.co/rest/v1/"
);

//Przygotuj dane do użycia dalej
$encoded = json_encode($_SESSION["session"]);
$userData = json_decode($encoded, true);
?>

<!DOCTYPE html>
<main>

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link href="/dist/output.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://unpkg.com/@picocss/pico@1.*/css/pico.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    </head>

    <script>
        //Podczas pisania tej aplikacji, niestety zauważyżyłem że biblioteka PHP dla Supabase nie jest wystarczająco rozwinięta
        //Stąd nie mogłem użyć jej do weryfikacji sesji użytkownika przy wynonywaniu akcji które wymagałyby uwierzytelnienia
        //np. dodawanie / usuwanie użytkowników

        //Utworzenie klienta JS Supabase
        const SUPABASE_URL = 'https://hsepjgxxozeyktjkbewc.supabase.co'
        const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhzZXBqZ3h4b3pleWt0amtiZXdjIiwicm9sZSI6ImFub24iLCJpYXQiOjE2NzYwMzgwODQsImV4cCI6MTk5MTYxNDA4NH0.zusO9r5QquROh2XfQ6CIM0sbL3Re2KPtSOsHK7lsPfc'
        const _supabase = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY)

        //Przekazanie sesji z klienta PHP Supabase do klienta JS
        //Uwierzytelnianie jest teraz obsługiwane przez blibiotekę JS
        async function loadSession() {
            const access_token = <?php echo json_encode($userData["access_token"]); ?>;
            const refresh_token = <?php echo json_encode($userData["refresh_token"]); ?>;

            const getSession = await _supabase.auth.setSession({
                access_token,
                refresh_token
            })
        }

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
        loadSession()
    </script>

    <body>
        <h1 class="text-center">Welcome
            <?php echo $userData["user"]["user_metadata"]["username"] ?> to the protected page!
        </h1>
        <?php
        if (isset($_SESSION['userDeletedMsg'])) {
            echo '<h2>' . $_SESSION['userDeletedMsg'] . '</h2>';
            unset($_SESSION['userDeletedMsg']);
        }

        if (isset($_SESSION['cityDeleteMsg'])) {
            echo '<h2>' . $_SESSION['cityDeleteMsg'] . '</h2>';
            unset($_SESSION['cityDeleteMsg']);
        }

        if (isset($_SESSION['editMessage'])) {
            echo '<h4>' . $_SESSION['editMessage'] . '</h4>';
            unset($_SESSION['editMessage']);
        }

        if (isset($_SESSION['addMessage'])) {
            echo '<h4>' . $_SESSION['addMessage'] . '</h4>';
            unset($_SESSION['addMessage']);
        }
        ?>
        <h2>List of users:</h2>
        <?php
        echo "<table>";
        echo "<tr><th>Imię</th><th>Nazwisko</th><th>Data Urodzenia</th><th>Miasto</th><th>Województwo</th><td>Akcje</td><td></td></tr>";
        $userDataQuery = $service->initializeQueryBuilder();
        $citySateQuery = $service->initializeQueryBuilder();

        try {
            $fetchUserData = $userDataQuery->select('*')
                ->from('users')
                ->join('cities', 'id')
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

                $stateId_filtered = array_filter($fetchCityState, function ($user) use ($search_value) {
                    return $user->id == $search_value;
                });

                $stateId = array_values($stateId_filtered);

                $cityState = json_decode(json_encode($stateId[0]), true);
                echo "<tr><td>{$userData['firstName']}</td><td>{$userData['lastName']}</td><td>{$userData['birthday']}</td><td>{$userData['cities']['city']}</td><td>{$cityState['states']['state']}</td><td><a href=\"scripts-deleteUser.php?userId={$userData['id']}\" onclick=\"checkSession()\">Usuń</a></td><td><a href=\"editUser.php?userId={$userData['id']}\" onclick=\"checkSession()\">Edytuj</a></td></tr>";
            }

            echo "</table>";
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        ?>

        <?php
        if (isset($_SESSION['showTable'])) {
            echo "<table>";
            echo "<tr><th>id</th><th>state_id</th><th>city</th><td>Akcje</td></tr>";
            $citiesQuery = $service->initializeQueryBuilder();

            try {
                $fetchCities = $citiesQuery->select('*')
                    ->from('cities')
                    ->join('states', 'id')
                    ->execute()
                    ->getResult();

                for ($i = 0; $i < sizeof($fetchCities); $i++) {
                    $cityData = json_decode(json_encode($fetchCities[$i]), true);

                    echo "<tr><td>{$cityData['id']}</td><td>{$cityData['state_id']}</td><td>{$cityData['city']}<td><a href=\"scripts-deleteCity.php?cityId={$cityData['id']}\" onclick=\"checkSession()\">Usuń</a></td></tr>";
                }

                echo "</table>";
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        ?>

        <p class="text-center">This content is only visible to authenticated users.</p>
        <div class="auth-form container">
            <a href="scripts-showTable.php?table=cities">
                <button onclick="checkSession()">
                    <?php if (isset($_SESSION['showTable'])) {
                        echo 'Hide Cities';
                    } else {
                        echo 'Show Cities';
                    }
                    unset($_SESSION['showTable']);
                    ?>
                </button>
            </a>
        </div>
        <div class="auth-form container">
            <a href="addUser.php" onclick="checkSession()">
                <button onclick="checkSession()">Add User</button>
            </a>
        </div>
        <form action="scripts-logout.php" method="POST" class="auth-form container">
            <button type="submit">Logout</button>
        </form>

        <p class="text-center">You can also delete your account here</p>
        <form action="scripts-deleteAccount.php" method="POST" class="auth-form container">
            <button type="submit" onclick="checkSession()">Delete Account</button>
        </form>
    </body>
</main>