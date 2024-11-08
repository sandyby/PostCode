<?php
session_start();

require_once 'connection.php';
require_once 'formValidation.php';
$error_msg = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $reset_password_token = $_POST['reset_password_token'];
    $reset_password_token_hash = hash("sha256", $reset_password_token);
    $new_password = $_POST['new-password'];
    $confirm_new_password = $_POST['confirm-new-password'];

    $sql = "SELECT * FROM users
        WHERE reset_password_token = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$reset_password_token_hash]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row === false) {
        $_SESSION['event-status_reset_password'][] = 'An error occured! Please try again later';
        header("Location: reset_password.php?reset_password_token=" . $reset_password_token);
        exit();
    }

    $user_id = $row['user_id'];

    if (!isValid($new_password) || !isValid($confirm_new_password)) {
        $error_msg[] = 'Please enter a valid input!';
    }

    if (!isPassword($new_password)) {
        $error_msg[] = 'Please enter a valid password! (min. 8 characters, 1 lowercase, 1 uppercase, 1 number, and 1 symbol) Only the following symbols are allowed: @$!%*?&';
    }

    if ($new_password !== $confirm_new_password) {
        $error_msg[] = 'Password don\'t match! Please try again';
    }

    if (!empty($error_msg)) {
        $_SESSION['event-reset_password_error_msg'] = $error_msg;
        header("Location: reset_password.php?reset_password_token=" . $reset_password_token);
        exit();
    }

    $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);

    $sql = "UPDATE users SET password = ?, reset_password_token = NULL, reset_password_token_expiry_date = NULL WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$new_password_hash, $user_id]);

    if (!$stmt) {
        $_SESSION['event-status_reset_password'][] = 'Failed changing password! Please try again later';
        header("Location: reset_password.php");
        exit();
    }

    $_SESSION['event-status_reset_password'][] = 'Password successfully changed!';
    header("Location: ../public/login.php");
    exit();
}
