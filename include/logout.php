<?php
session_start();

if (!isset($_SESSION['event-logged-in']) || $_SESSION['event-logged-in'] !== true) {
    echo "You don't have access to this page!";
    echo '
    <div style="margin: 10px">
        <a href="../public/login.php" style="font-size: 20px">Log In</a>
    </div>
    ';
    exit();
}
$_SESSION['event-status_logout'][] = 'Successfully logged out!';
unset($_SESSION['event-logged-in']);
unset($_SESSION['event-user_id']);
unset($_SESSION['event-username']);
header('Location: ../public/login.php');
exit();
