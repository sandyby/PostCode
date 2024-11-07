<?php
session_start();

if ($_SESSION['event-user_role'] !== 1 || !isset($_SESSION['event-logged-in']) || $_SESSION['event-logged-in'] !== true) {
    echo "You don't have access to this page!";
    echo '
        <div style="margin: 10px">
            <a href="../public/login.php" style="font-size: 20px">Log In</a>
        </div>
        ';
    exit();
}

require_once '../include/connection.php';

if (!isset($_GET['list_id'])) {
    header("Location: ../admin/dashboardAdmin.php");
}
$user_id = $_SESSION['event-user_id'];
$event_id = $_GET['event_id'];

$stmt = $pdo->prepare("SELECT event_name FROM events WHERE event_id = :event_id");
$stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
if (!$stmt->execute()) {
    $_SESSION['event-status_delete_event'][] = "An error occured! Please try again later";
    header("Location: dashboardAdmin.php");
    exit();
}

$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result === false) {
    $_SESSION['event-status_delete_event'][] = "An error occured! Please try again later";
    header("Location: dashboardAdmin.php");
    exit();
}

$stmt = $pdo->prepare("DELETE FROM events WHERE event_id = :event_id");
$stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
if (!$stmt->execute()) {
    $_SESSION['event-status_delete_event'][] = "An error occured! Please try again later";
    header("Location: dashboardAdmin.php");
    exit();
}

$_SESSION['event-status_delete_event'][] = 'Successfully deleted event: ' . $result['event_name'];
header('Location: dashboardAdmin.php');
exit();
