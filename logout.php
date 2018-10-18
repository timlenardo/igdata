<?php
    session_start();
    $_SESSION['logged_in'] = -1;
    $_SESSION['username'] = null;

    header("Location: index.php");
?>
