<?php

require "vendor/autoload.php";

if (!isset($_SESSION["session"])) {
    header("Location: login.php");
    die();
}

if (isset($_GET['cityId'])) {
    $cityId = $_GET['cityId'];
} else {
    echo "Missing parameter \"cityId\" ";
    die();
}

$service = new PHPSupabase\Service(
    "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhzZXBqZ3h4b3pleWt0amtiZXdjIiwicm9sZSI6ImFub24iLCJpYXQiOjE2NzYwMzgwODQsImV4cCI6MTk5MTYxNDA4NH0.zusO9r5QquROh2XfQ6CIM0sbL3Re2KPtSOsHK7lsPfc",
    "https://hsepjgxxozeyktjkbewc.supabase.co/rest/v1/"
);

$db = $service->initializeDatabase('cities', 'id');
$cityDb = $service->initializeDatabase('users', 'id');
$usersFromCityQuery = $service->initializeQueryBuilder();

try {
    $fetchUsersFromCity = $usersFromCityQuery->select('id')
        ->from('users')
        ->where('city_id', "eq.$cityId") //eq -> equal
        ->execute()
        ->getResult();

} catch (Exception $e) {
    echo "An error occured during loading cities...";
    echo $e->getMessage();
    die();
}

try {
    $data = $cityDb->delete($fetchUsersFromCity[0]->id);
} catch (Exception $e) {
    echo "An error occured phase2";
    echo $e->getMessage();
    die();
}

try {
    $data = $db->delete($cityId);
    $_SESSION["cityDeleteMsg"] = "City deleted successfuly!";
    header("Location: protected.php");
} catch (Exception $e) {
    echo "An error occured phase3";
    echo $e->getMessage();
    die();
}
?>