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

require_once("../include/connection.php");

$sql = "
    SELECT users.user_id, users.username, events.event_name, history.action, history.action_date
    FROM regs_history AS history
    INNER JOIN users ON history.user_id = users.user_id
    INNER JOIN events ON history.event_id = events.event_id
    ORDER BY users.user_id, history.action_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$historyRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

$userHistory = [];
foreach ($historyRecords as $record) {
    $userHistory[$record['user_id']]['username'] = $record['username'];
    $userHistory[$record['user_id']]['records'][] = [
        'event_name' => $record['event_name'],
        'action' => $record['action'],
        'action_date' => $record['action_date']
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $deleteUserId = $_POST['delete_user_id'];
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
        $stmt->execute([$deleteUserId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("DELETE FROM regs_history WHERE user_id = ?");
        $stmt->execute([$deleteUserId]);

        $stmt = $pdo->prepare("DELETE FROM regs WHERE user_id = ?");
        $stmt->execute([$deleteUserId]);

        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$deleteUserId]);

        $pdo->commit();
        $_SESSION['event-status_delete_user'][] = 'Successfully deleted user ' . $row['username'] . '!';
        header("Location: registration_history.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Failed to delete user: " . $e->getMessage());
        $_SESSION['event-status_delete_user'][] = 'Failed deleting user ' . $row['username'] . '! Please try again later';
        header("Location: registration_history.php");
        exit();
    }
}

?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>View Registration History</title>
</head>

<body>
    <div class="container mt-2" style="max-width: 1400px">
        <h1>User Registration History</h1>
        <?php if (count($historyRecords) > 0): ?>
            <?php foreach ($userHistory as $userId => $user): ?>
                <h2><?= htmlspecialchars($user['username']) ?>'s Registration History</h2>
                <form method="POST" id="delete-user-<?= $userId ?>" style="display:inline;">
                    <input type="hidden" name="delete_user_id" value="<?= $userId ?>">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete User??')">Delete User</button>
                </form>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Action</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user['records'] as $record): ?>
                            <tr>
                                <td><?= htmlspecialchars($record['event_name']) ?></td>
                                <td><?= htmlspecialchars($record['action']) ?></td>
                                <td><?= htmlspecialchars($record['action_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach ?>
        <?php else: ?>
            <p>No participants registered for any event yet!</p>
        <?php endif; ?>
        <a href="dashboardAdmin.php" class="btn btn-info">Dashboard</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const Toast8000 = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 8000,
            heightAuto: false,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        <?php if (!empty($_SESSION['event-status_delete_user'])): ?>
            const deleteUserStatuses = <?php echo json_encode($_SESSION['event-status_delete_user']); ?>;
            deleteUserStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast8000.fire({
                        icon: status.includes('Successfully') ? "success" : "error",
                        title: status
                    });
                }, index * 8500);
            });
        <?php endif; ?>
        <?php unset($_SESSION['event-status_delete_user']); ?>
    </script>
</body>

</html>