<?php
session_start();

require_once 'connection.php';
require_once 'formValidation.php';

if (!isset($_POST['form_token']) || $_POST['form_token'] !== $_SESSION['event-form_token']) {
    header("Location: ../public/login.php");
    exit();
}

unset($_SESSION['event-form_token']);
$error_msg = [];
$email = htmlspecialchars($_POST['email']);
$password = htmlspecialchars($_POST['password']);

if (!isValid($email) || !isValid($password)) {
    $error_msg[] = 'Please enter a valid input!';
}
if (!isEmail($email)) {
    $error_msg[] = 'Please enter a valid e-mail!';
}

if (!empty($error_msg)) {
    $_SESSION['event-login_error_msg'] = $error_msg;
    header("Location: ../public/login.php");
    exit();
}

$sql = "SELECT * FROM users
            WHERE email = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row === false) {
    $_SESSION['event-login_error_msg'][] = 'User not found!';
    header("Location: ../public/login.php");
    exit();
} else {
    if (!password_verify($password, $row['password'])) {
        $_SESSION['event-login_error_msg'][] = 'Incorrect password! Please try again';
        header("Location: ../public/login.php");
        exit();
    } elseif ($row['isVerified'] === 0) {
        $_SESSION['event-login_error_msg'][] = 'Please check and verify your e-mail first!';
        header("Location: ../public/login.php");
        exit();
    } else {
        $_SESSION['event-logged-in'] = true;
        $_SESSION['event-status_login'][] = 'Successfully logged in!';
        $_SESSION['event-user_id'] = $row['user_id'];
        $_SESSION['event-username'] = $row['username'];
        $_SESSION['event-email'] = $row['email'];
        $_SESSION['event-user_role'] = $row['role_id'];
        if($_SESSION['event-user_role'] === 1){
            header('Location: ../admin/dashboardAdmin.php');
        } else {
            header('Location: ../public/dashboard.php');
        }
        exit();
    }
}
