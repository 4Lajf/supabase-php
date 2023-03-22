<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    require "vendor/autoload.php";
    function console_log($output, $with_script_tags = true)
    {
        $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) .
            ');';
        if ($with_script_tags) {
            $js_code = '<script>' . $js_code . '</script>';
        }
        echo $js_code;
    }

    $service = new PHPSupabase\Service(
        "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhzZXBqZ3h4b3pleWt0amtiZXdjIiwicm9sZSI6ImFub24iLCJpYXQiOjE2NzYwMzgwODQsImV4cCI6MTk5MTYxNDA4NH0.zusO9r5QquROh2XfQ6CIM0sbL3Re2KPtSOsHK7lsPfc",
        "https://hsepjgxxozeyktjkbewc.supabase.co/rest/v1/"
    );

    $db = $service->initializeDatabase('profiles', 'id');

    try {
        $encoded = json_encode($_SESSION["session"]);
        $php_array = json_decode($encoded, true);
        $data = $db->delete($php_array["id"]); //the parameter ('1') is the product id
    } catch (Exception $e) {
        echo $e->getMessage();
        die();
    }

    session_destroy();
    header("Location: login.php");
    die();
}
?>