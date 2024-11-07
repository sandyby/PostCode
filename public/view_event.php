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
require_once '../include/connection.php';
require_once '../include/initDB_T.php';

if (!isset($_GET['event_id'])) {
    $_SESSION['event-status_view_event'][] = 'Please select an event to view!';
    header("Location: dashboard.php");
    exit();
}

$eventId = $_GET['event_id'];

$stmt = $pdo->prepare("SELECT e.*, COUNT(r.reg_id) AS participant_count
                                FROM events AS e
                                LEFT JOIN regs r ON e.event_id = r.event_id
                                WHERE e.event_id = :event_id
                                GROUP BY e.event_id");
$stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
$stmt->execute();
$event = $stmt->fetch(PDO::FETCH_ASSOC);

$userId = $_SESSION['event-user_id'];
$checkRegistrationStmt = $pdo->prepare("SELECT * FROM regs WHERE user_id = :user_id AND event_id = :event_id");
$checkRegistrationStmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
$isRegistered = $checkRegistrationStmt->rowCount() > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        $registerStmt = $pdo->prepare("INSERT INTO regs (user_id, event_id) VALUES (:user_id, :event_id)");
        $registerStmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
        $historyStmt = $pdo->prepare("INSERT INTO regs_history (user_id, event_id, action) VALUES (?, ?, 'registered')");
        $historyStmt->execute([$userId, $eventId]);
        $_SESSION['event-status_view_event_register'][] = 'Successfully registered for event: ' . $event['event_name'] . '!';
        header("Location: view_event.php?event_id=" . $eventId);
        exit();
    } elseif (isset($_POST['cancel'])) {
        $cancelStmt = $pdo->prepare("DELETE FROM regs WHERE user_id = :user_id AND event_id = :event_id");
        $cancelStmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
        $historyStmt = $pdo->prepare("INSERT INTO regs_history (user_id, event_id, action) VALUES (?, ?, 'cancelled')");
        $historyStmt->execute([$userId, $eventId]);
        $_SESSION['event-status_cancel_registration'][] = 'Successfully canceled registration for event: ' . $event['event_name'] . '!';
        header("Location: view_event.php?event_id=" . $eventId);
        exit();
    }
}

?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>View Event Details</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            background-color: rgb(240, 240, 240);
            font-family: Arial, sans-serif;
        }

        .nav {
            width: 100%;
            background-color: rgb(215, 215, 215);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            position: fixed;
            top: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .post-code-logo {
            max-height: 50px;
        }

        .container {
            margin-top: 80px;
            padding: 20px;
            max-width: 500px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2rem;
        }

        .event-img img {
            border: 4px solid grey;
            border-radius: 10px;
            object-fit: cover;
            max-width: 100%;
            height: auto;
        }

        p {
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .btn {
            width: 100%;
            margin-top: 10px;
            font-weight: bold;
        }

        .btn-info {
            background-color: #4c8a68f5;
            border: #4c8a68f5;
            width: 100%;
            margin-top: 10px;
        }

        .btn-info:hover{
            background-color: #4c8a68f5;
            border: #4c8a68f5;
            transform: translateY(-5px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }


        @media (min-width: 768px) {
            .btn {
                width: auto;
                margin-right: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="nav">
        <img class="post-code-logo" src="../assets/img/post-code-logo.png" alt="post-code-logo">
    </div>

    <div class="container">
        <div class="text-center" id="event-wrapper">
            <h1><?php echo htmlspecialchars($event['event_name']); ?></h1>
            <img src="../uploads/<?= htmlspecialchars($event['image']) ?>" class="mt-2 mb-5 img img-fluid" width="200" height="200" style="object-fit: cover; border-radius: 10px">
            <p>Date: <?php $date = new DateTime($event['event_date']);
                        $formattedDate = $date->format('d F Y');
                        echo htmlspecialchars($formattedDate); ?></p>
            <p>Time: <?php $time = new DateTime($event['event_time']);
                        $formattedTime = $time->format('H:i');
                        echo htmlspecialchars($formattedTime); ?></p>
            <p>Location: <?php echo htmlspecialchars($event['location']); ?></p>
            <p>Description: <?php echo htmlspecialchars($event['description']); ?></p>
            <p>Participants: <?php echo htmlspecialchars($event['participant_count'] . "/" . $event['max_participants']); ?></p>
            <p>Status: <span class="status status-<?= strtolower($event['status']); ?>">
                    <?php echo htmlspecialchars($event['status']); ?></p>

            <?php if ($event['status'] === 'Open'): ?>
                <?php if ($isRegistered && $_SESSION['event-user_role'] !== 1): ?>
                    <form method="POST">
                        <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                        <button type="submit" class="btn btn-danger" name="cancel"
                            onclick="return confirm('Cancel Your Registration??')">Cancel Registration</button>
                    </form>
                <?php elseif (!$isRegistered && $_SESSION['event-user_role'] !== 1): ?>
                    <form method="POST">
                        <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                        <button type="submit" class="btn btn-primary" name="register">Register</button>
                    </form>
                <?php endif; ?>
            <?php endif ?>
            <a href="dashboard.php" class="btn btn-info mb-3">View All Events</a>
        </div>
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

        <?php if (!empty($_SESSION['event-status_view_event_register'])): ?>
            const registerEventStatuses = <?php echo json_encode($_SESSION['event-status_view_event_register']); ?>;
            registerEventStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast8000.fire({
                        icon: status.includes('Successfully') ? "success" : "error",
                        title: status
                    });
                }, index * 8500);
            });
        <?php endif; ?>

        <?php if (!empty($_SESSION['event-status_cancel_registration'])): ?>
            const cancelRegistrationStatuses = <?php echo json_encode($_SESSION['event-status_cancel_registration']); ?>;
            cancelRegistrationStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast8000.fire({
                        icon: status.includes('Successfully') ? "success" : "error",
                        title: status
                    });
                }, index * 8500);
            });
        <?php endif; ?>
        <?php unset($_SESSION['event-status_view_event_register']); ?>
        <?php unset($_SESSION['event-status_cancel_registration']); ?>
    </script>
</body>

</html>