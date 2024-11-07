<?php
session_start();
require_once 'connection.php';
$verification_code = $_GET['verification_code'];

if (isset($verification_code)) {
    
    $sql = "SELECT * FROM users WHERE verification_code = ?";
    $result = $pdo->prepare($sql);
    $result->execute([$verification_code]);
    $row = $result->fetch(PDO::FETCH_ASSOC);

    if ($row === false) {
        $_SESSION['event-status_verifikasi'][] = 'E-mail verification failed to be delivered! Please try again later';
        error_log("Failed trying to verificate account");
        header('Location: ../public/register.php');
        exit();
    }

    $sql = "UPDATE users SET isVerified = 1, verified_at = NOW() WHERE user_id = ?";
    $result = $pdo->prepare($sql);

    if (!$result->execute([$row['user_id']])) {
        $_SESSION['event-status_verifikasi'][] = 'E-mail verification failed to be delivered! Please try again later';
        error_log("Failed trying to verificate account");
        header('Location: ../public/register.php');
        exit();
    }

    $_SESSION['event-status_verifikasi_2'][] = 'Verification success! Log in to continue';
    header('Location: ../public/login.php');
    exit();
}
