<?php
session_start();
require "vendor/autoload.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION["register_error"] = "";
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $_SESSION["register_error"] = "Please fill in all fields.";
        header("Location: register.php");
        die();
    } elseif (preg_match($pattern, $username)) {
        $_SESSION["register_error"] = "Username can only contain letters, numbers and special symbols.";
        header("Location: register.php");
        die();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["register_error"] = "Invalid email format.";
        header("Location: register.php");
        die();
    } elseif ($password !== $confirmPassword) {
        $_SESSION["register_error"] = "Passwords do not match.";
        header("Location: register.php");
        die();
    } else {
        $service = new PHPSupabase\Service(
            "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhzZXBqZ3h4b3pleWt0amtiZXdjIiwicm9sZSI6ImFub24iLCJpYXQiOjE2NzYwMzgwODQsImV4cCI6MTk5MTYxNDA4NH0.zusO9r5QquROh2XfQ6CIM0sbL3Re2KPtSOsHK7lsPfc",
            "https://hsepjgxxozeyktjkbewc.supabase.co/auth/v1/"
        );

        $auth = $service->createAuth();

        try {
            $user_metadata = [
                'username' => $username,
            ];
            $auth->createUserWithEmailAndPassword($email, $password, $user_metadata);
            $data = $auth->data();
            if (isset($data->access_token)) {
                $userData = $data->user;
                header("Location: protected.php");
                $_SESSION["session"] = $userData;
                die();
            } else {
                throw new Exception("Error: no access token set");
            }
        } catch (Exception $e) {
            $_SESSION["register_error"] = $auth->getError();
            header("Location: register.php");
            die();
        }
    }
}
?>