<?php
session_start();
if (!isset($_SESSION["email"])) {
    header("Location: login.php");
    die();
}
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
            <?php echo $_SESSION["email"] ?> to the protected page!
        </h1>
        <p class="text-center">This content is only visible to authenticated users.</p>
        <form action="scripts-logout.php" method="POST" class="auth-form container">
            <button type="submit">Logout</button>
        </form>

        <p class="text-center">You can also delete your account here</p>
        <form action="scripts-deleteAcc.php" method="POST" class="auth-form container">
            <button type="submit">Delete Account</button>
        </form>
    </body>
</main>