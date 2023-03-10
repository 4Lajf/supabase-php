<?php
session_start();
require "vendor/autoload.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pattern = "/^[a-zA-Z0-9\-\_\.\,\:\;\@\#\!\$\%\&\*\(\)\+\=\?\[\]\{\}\|\\\^\`\~\']+$/";
    $_SESSION["login_error"] = "";
    $email = $_POST["email"];
    $password = $_POST["password"];
    if (empty($email) || empty($password)) {
        $_SESSION["login_error"] = "Please fill in all fields.";
        header("Location: login.php");
        die();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["login_error"] = "Invalid email format.";
        header("Location: login.php");
        die();
    } else {
        $service = new PHPSupabase\Service(
            "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhzZXBqZ3h4b3pleWt0amtiZXdjIiwicm9sZSI6ImFub24iLCJpYXQiOjE2NzYwMzgwODQsImV4cCI6MTk5MTYxNDA4NH0.zusO9r5QquROh2XfQ6CIM0sbL3Re2KPtSOsHK7lsPfc",
            "https://hsepjgxxozeyktjkbewc.supabase.co/auth/v1/"
        );

        $auth = $service->createAuth();

        try {
            $auth->signInWithEmailAndPassword($email, $password);
            $data = $auth->data();

            if (isset($data->access_token)) {
                $userData = $data->user; //get the user data
                header("Location: protected.php");
                $_SESSION["session"] = $userData;
                die();
            } else {
                throw new Exception("Error: no access token set");
            }
        } catch (Exception $e) {
            $_SESSION["login_error"] = $auth->getError();
            header("Location: login.php");
            die();
        }
    }
}
?>