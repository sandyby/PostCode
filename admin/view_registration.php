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
require_once '../include/initDB_T.php';

if (isset($_GET['event_id'])) {

    $eventId = $_GET['event_id'];

    $eventQuery = $pdo->prepare("SELECT event_name, event_date FROM events WHERE event_id = ?");
    $eventQuery->execute([$eventId]);
    $event = $eventQuery->fetch(PDO::FETCH_ASSOC);
    $eventName = $event['event_name'];

    $stmt = $pdo->prepare("
        SELECT users.user_id, users.username, users.email, regs.registration_date, regs.event_id
        FROM regs
        INNER JOIN users ON regs.user_id = users.user_id
        WHERE regs.event_id = ?");
    $stmt->execute([$eventId]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentDateTime = date('Y-m-d_H-i-s');
    $filename = 'registrantsEvent' . '_' . $eventName . '_' . $currentDateTime . '.csv';
    $upload_path = '../data/registrants/' . $filename;

    $output = fopen($upload_path, 'w');
    fputcsv($output, array('Username', 'Email', 'Registration Date'));

    foreach ($participants as $row) {
        fputcsv($output, array($row['username'], $row['email'], $row['registration_date']));
    }

    fclose($output);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=' . $filename);
    readfile($upload_path);
    exit();
}

?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>View Event Registrants</title>
</head>

<body>
    <div class="container mt-2" style="max-width: 1400px">
        <h1><?php echo htmlspecialchars($event['event_name']); ?></h1>
        <?php if (count($participants) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $participant): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($participant['username']); ?></td>
                            <td><?php echo htmlspecialchars($participant['email']); ?></td>
                            <td><?php echo htmlspecialchars($participant['registration_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No participants registered for this event.</p>
        <?php endif; ?>
        <form method="post">
            <button type="submit" class="btn btn-primary">Export to CSV</button>
        </form>
        <a href="dashboardAdmin.php" class="btn btn-info">Dashboard</a>
    </div>
</body>


</html>