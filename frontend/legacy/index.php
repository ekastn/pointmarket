<?php
// Redirect to login if not already there
if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header("Location: login.php");
    exit();
}
?>
