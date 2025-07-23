<?php
require_once '../includes/config.php';
startSession();

// Destroy all session data
session_destroy();

// Redirect to login page
setSuccessMessage('Logout berhasil. Terima kasih!');
header("Location: ../login.php");
exit();
?>
