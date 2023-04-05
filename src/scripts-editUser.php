<?php

require "vendor/autoload.php";
if (!isset($_SESSION["session"])) {
    header("Location: login.php");
    unset($_SESSION['userId']);
    die();
}

function console_log($output, $with_script_tags = true)
{
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) .
        ');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}

$firstname = $_POST["firstname"];
$lastname = $_POST["lastname"];
$birthday = $_POST["birthday"];
$state = $_POST["state"];
$city = $_POST["city"];
$pattern = '/^[\p{L}]+$/u';
//polskie litery...
$userId = $_SESSION['userId'];
if (empty($firstname) || empty($lastname) || empty($state) || empty($city) || $state == 0) {
    $_SESSION["editUser_error"] = "Please fill in all fields.";
    echo "<script> history.back(); </script>";
    die();
}
// if (!preg_match($pattern, $firstname) || !preg_match($pattern, $lastname) || !preg_match($pattern, $city)) {
//     $_SESSION["editUser_error"] = "Firstname, lastname and city can only contain letters";
//     header("Location: editUser?userId=$userId.php");
//     unset($_SESSION['userId']);
//     die();
// }

$service = new PHPSupabase\Service(
    "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhzZXBqZ3h4b3pleWt0amtiZXdjIiwicm9sZSI6ImFub24iLCJpYXQiOjE2NzYwMzgwODQsImV4cCI6MTk5MTYxNDA4NH0.zusO9r5QquROh2XfQ6CIM0sbL3Re2KPtSOsHK7lsPfc",
    "https://hsepjgxxozeyktjkbewc.supabase.co/rest/v1/"
);

$findCityQuery = $service->initializeQueryBuilder();

try {
    $fetchCity = $findCityQuery->select('id, city')
        ->from('cities')
        ->where('city', "eq.$city") //eq -> equal
        ->execute()
        ->getResult();

} catch (Exception $e) {
    echo "An error occured phase 1";
    console_log($e->getMessage());
    unset($_SESSION['userId']);
    die();
}

if (!isset($fetchCity[0]->city)) {
    $addCityQuery = $service->initializeDatabase('cities', 'id');

    $new = [
        'city' => $city,
        'state_id' => $state,
    ];

    try {
        $data = $addCityQuery->insert($new);
    } catch (Exception $e) {
        echo $e->getMessage();
        echo "An error occured phase 2";
    }

    try {
        $fetchCity = $findCityQuery->select('id, city')
            ->from('cities')
            ->where('city', "eq.$city") //eq -> equal
            ->execute()
            ->getResult();

    } catch (Exception $e) {
        echo "An error occured phase 3";
        console_log($e->getMessage());
        unset($_SESSION['userId']);
        die();
    }
}

$editUserQuery = $service->initializeDatabase('users', 'id');

$update = [
    'city_id' => $fetchCity[0]->id,
    'firstName' => $firstname,
    'lastName' => $lastname,
    'birthday' => $birthday,
];
try {
    $data = $editUserQuery->update($userId, $update);
    $_SESSION['editMessage'] = 'User edited successfuly!';
    header("Location: protected.php");
} catch (Exception $e) {
    echo "An error occured phase 4";
    echo $e->getMessage();
    unset($_SESSION['userId']);
    die();
}
unset($_SESSION['userId']);
?>