<!-- TOOD: make addUser add users to auth table
add security to dangerous acrions
merge users and profiles table
avoid multiple ORs in forms
do historyback instsed of header location -->

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
$encoded = json_encode($_SESSION["session"]);
$php_array = json_decode($encoded, true);
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
        <h1 class="text-center">Welcome
            <?php echo $php_array["user_metadata"]["username"] ?> to the protected page!
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
        ?>
        <h2>List of users:</h2>
        <?php
        echo "<table>";
        echo "<tr><th>Imię</th><th>Nazwisko</th><th>Data Urodzenia</th><th>Miasto</th><th>Województwo</th><td>Akcje</td></tr>";
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

                // Use array_filter to find the record(s)
                $stateId_filtered = array_filter($fetchCityState, function ($user) use ($search_value) {
                    return $user->id == $search_value;
                });

                $stateId = array_values($stateId_filtered);

                $cityState = json_decode(json_encode($stateId[0]), true);
                echo "<tr><td>{$userData['firstName']}</td><td>{$userData['lastName']}</td><td>{$userData['birthday']}</td><td>{$userData['cities']['city']}</td><td>{$cityState['states']['state']}</td><td><a href=\"scripts-deleteUser.php?userId={$userData['id']}\">Usuń</a></td></tr>";
            }

            echo "</table>";
        } catch (Exception $e) {
            console_log($e->getMessage());
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

                    echo "<tr><td>{$cityData['id']}</td><td>{$cityData['state_id']}</td><td>{$cityData['city']}<td><a href=\"scripts-deleteCity.php?cityId={$cityData['id']}\">Usuń</a></td></tr>";
                }

                echo "</table>";
            } catch (Exception $e) {
                console_log($e->getMessage());
            }
        }
        unset($_SESSION['showTable']);
        ?>

        <p class="text-center">This content is only visible to authenticated users.</p>
        <div class="auth-form container">
            <a href="scripts-showTable.php?table=cities">
                <button>Show cities</button>
            </a>
        </div>
        <div class="auth-form container">
            <a href="addUser.php">
                <button>Add User</button>
            </a>
        </div>
        <form action="scripts-logout.php" method="POST" class="auth-form container">
            <button type="submit">Logout</button>
        </form>

        <p class="text-center">You can also delete your account here</p>
        <form action="scripts-deleteAccount.php" method="POST" class="auth-form container">
            <button type="submit">Delete Account</button>
        </form>
    </body>
</main>