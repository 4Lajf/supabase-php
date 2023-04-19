<?php

require "vendor/autoload.php";

if (!isset($_SESSION["session"])) {
    header("Location: login.php");
    die();
}

if (isset($_GET['userId'])) {
    $userId = $_GET['userId'];
} else {
    echo "Missing parameter \"userId\" ";
    die();
}

$service = new PHPSupabase\Service(
    "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhzZXBqZ3h4b3pleWt0amtiZXdjIiwicm9sZSI6ImFub24iLCJpYXQiOjE2NzYwMzgwODQsImV4cCI6MTk5MTYxNDA4NH0.zusO9r5QquROh2XfQ6CIM0sbL3Re2KPtSOsHK7lsPfc",
    "https://hsepjgxxozeyktjkbewc.supabase.co/rest/v1/"
);

$db = $service->initializeDatabase('users', 'id');

try {
    $data = $db->delete($userId);
    $_SESSION["userDeletedMsg"] = "User deleted successfuly!";
    header("Location: protected.php");
} catch (Exception $e) {
    echo $e->getMessage();
    die();
}
?>