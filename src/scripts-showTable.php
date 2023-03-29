<?php
session_start();

if (!isset($_SESSION["session"])) {
    header("Location: login.php");
    die();
}

if (isset($_GET['table'])) {
    $_SESSION['showTable'] = $_GET['table'];
    header("Location: protected.php");
} else {
    echo "Missing parameter \"table\" ";
    die();
}
?>