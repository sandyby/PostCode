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
$userId = $_SESSION['event-user_id'];

$stmt = $pdo->prepare("SELECT e.* FROM events e
                            JOIN regs r ON e.event_id = r.event_id
                            WHERE r.user_id = :user_id");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$registeredEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel'])) {
    $eventId = $_POST['event_id'];

    $cancelStmt = $pdo->prepare("DELETE FROM regs WHERE user_id = :user_id AND event_id = :event_id");
    $cancelStmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
    $_SESSION['event-status_cancel_registration'][] = 'Successfully canceled the registration!';
    header("Location: view_registered_events.php");
    exit();
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Your Registered Events</title>
    <style>
        html,
        body {
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
            z-index: 1000;
        }

        .post-code-logo {
            max-height: 50px;
        }

        .container {
            margin-top: 100px;
            /* Offset for fixed navbar */
            padding: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .event-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        .event-wrapper {
            background-color: rgb(215, 215, 215);
            border-radius: 10px;
            padding: 20px;
            width: 100%;
            max-width: 400px;
            color: black;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .event-wrapper:hover {
            transform: translateY(-5px);
        }

        .event-details {
            width: 100%;
            text-align: center;
        }

        .event-details img {
            border: 4px solid whitesmoke;
            border-radius: 10px;
            object-fit: cover;
            width: 100%;
            max-width: 250px;
            height: 150px;
        }

        .btn-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            width: 180px;
        }

        .btn-primary {
            background-color: #4c8a68f5;
            border: #4c8a68f5;
            width: 100%;
            margin-top: 10px;
        }

        .btn-primary:hover {
            background-color: #4c8a68f5;
            border: #4c8a68f5;
            transform: translateY(-5px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }


        .btn-danger {
            flex: 1;
            max-width: 180px;
            font-weight: bold;
        }

        .btn-info {
            background-color: #91a2af;
            border: #91a2af;
            width: 180px;
            margin-top: 10px;
        }

        .btn-info:hover {
            background-color: #4c8a68f5;
            border: #4c8a68f5;
            transform: translateY(-5px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }


        @media (min-width: 768px) {
            .event-wrapper {
                width: 300px;
            }

            .btn-primary,
            .btn-danger {
                width: auto;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar/Header -->
    <div class="nav">
        <img class="post-code-logo" src="../assets/img/post-code-logo.png" alt="post-code-logo">
    </div>

    <!-- Main Content -->
    <div class="container">
        <h1>Your Registered Events</h1>
        <div class="event-grid">
            <?php if (empty($registeredEvents)): ?>
                <p class="text-center">No Events Registered Yet.</p>
            <?php else: ?>
                <?php foreach ($registeredEvents as $event): ?>
                    <div class="event-wrapper">
                        <h2 class="text-center"><?php echo htmlspecialchars($event['event_name']); ?></h2>
                        <div class="event-details">
                            <img src="../uploads/<?= htmlspecialchars($event['image']) ?>" alt="Event Image">
                        </div>
                        <p><strong>Date:</strong> <?php echo (new DateTime($event['event_date']))->format('F j, Y'); ?></p>
                        <p><strong>Time:</strong> <?php echo (new DateTime($event['event_time']))->format('g:i A'); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>

                        <div class="btn-container">
                            <a href="view_event.php?event_id=<?= htmlspecialchars($event['event_id']) ?>"
                                class="btn btn-primary">View Event Details</a>

                            <form method="POST">
                                <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                <button type="submit" name="cancel" class="btn btn-danger"
                                    onclick="return confirm('Cancel Your Registration?')">
                                    Cancel Registration
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-info">Back to Dashboard</a>
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

        <?php if (!empty($_SESSION['event-status_cancel_registration'])): ?>
            const registerEventStatuses = <?php echo json_encode($_SESSION['event-status_cancel_registration']); ?>;
            registerEventStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast8000.fire({
                        icon: status.includes('Successfully') ? "success" : "error",
                        title: status
                    });
                }, index * 8500);
            });
        <?php endif; ?>
        <?php unset($_SESSION['event-status_cancel_registration']); ?>
    </script>
</body>

</html>