<?php
    // Start the session to ensure we can access and destroy it
    session_start();

    // Unset all session variables to remove all data from the session
    $_SESSION = array();

    // Destroy the session itself
    session_destroy();

    // Redirect the user to the login page
    header("Location: user_login.php");
    exit;
?>
