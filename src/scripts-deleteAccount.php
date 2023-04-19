<?php

if (!isset($_SESSION["session"])) {
    header("Location: login.php");
    die();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    require "vendor/autoload.php";

    $service = new PHPSupabase\Service(
        "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhzZXBqZ3h4b3pleWt0amtiZXdjIiwicm9sZSI6ImFub24iLCJpYXQiOjE2NzYwMzgwODQsImV4cCI6MTk5MTYxNDA4NH0.zusO9r5QquROh2XfQ6CIM0sbL3Re2KPtSOsHK7lsPfc",
        "https://hsepjgxxozeyktjkbewc.supabase.co/rest/v1/"
    );

    $db = $service->initializeDatabase('profiles', 'id');

    try {
        $encoded = json_encode($_SESSION["session"]);
        $php_array = json_decode($encoded, true);
        $data = $db->delete($php_array["id"]);
    } catch (Exception $e) {
        echo $e->getMessage();
        die();
    }

    session_destroy();
    header("Location: login.php");
    die();
}
?>