<?php
session_start();
require "vendor/autoload.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION["status"] = "";
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $_SESSION["status"] = "Please fill in all fields.";
        header("Location: register.php");
        die();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["status"] = "Invalid email format.";
        header("Location: register.php");
        die();
    } elseif ($password !== $confirmPassword) {
        $_SESSION["status"] = "Passwords do not match.";
        header("Location: register.php");
        die();
    } else {
        $service = new PHPSupabase\Service(
            "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhzZXBqZ3h4b3pleWt0amtiZXdjIiwicm9sZSI6ImFub24iLCJpYXQiOjE2NzYwMzgwODQsImV4cCI6MTk5MTYxNDA4NH0.zusO9r5QquROh2XfQ6CIM0sbL3Re2KPtSOsHK7lsPfc",
            "https://hsepjgxxozeyktjkbewc.supabase.co/auth/v1/"
        );

        $auth = $service->createAuth();

        try {
            $auth->createUserWithEmailAndPassword($email, $password);
            $data = $auth->data();
            $_SESSION["email"] = $email;
            $message = "Registration successful";
            header("Location: login.php");
            die();
        } catch (Exception $e) {
            $_SESSION["status"] = "Registration failed: " + $auth->getError();
            header("Location: register.php");
            die();
        }
    }
}
?>