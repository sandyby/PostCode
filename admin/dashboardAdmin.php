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
$stmt = $pdo->prepare("
    SELECT e.*, COUNT(r.reg_id) AS participant_count
    FROM events e
    LEFT JOIN regs r ON e.event_id = r.event_id
    GROUP BY e.event_id
");

if (!$stmt->execute()) {
    header("Location: dashboardAdmin.php");
    exit();
}

$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($events === false) {
    header("Location: dashboardAdmin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $eventId = $_POST['event_id'];
    $newStatus = $_POST['status'];

    $updateStmt = $pdo->prepare("UPDATE events SET status = :status WHERE event_id = :event_id");
    $updateStmt->bindParam(':status', $newStatus, PDO::PARAM_STR);
    $updateStmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);

    if (!$updateStmt->execute()) {
        $_SESSION['event-status_update_status'][] = 'Failed to update status! Please try again later';
        header("Location: dashboardAdmin.php");
        exit();
    }
    $_SESSION['event-status_update_status'][] = 'Successfully updated status!';
    header("Location: dashboardAdmin.php");
    exit();
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Admin Dashboard</title>
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
            padding: 10px 20px;
            position: fixed;
            top: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .post-code-logo {
            max-height: 50px;
            width: auto;
            margin-right: 15px;
        }

        .user-controls {
            display: flex;
            align-items: center;
        }

        .user-controls h5 {
            margin-right: 15px;
            margin-bottom: 0;
        }

        .btn {
            margin-left: 10px;
        }

        .container {
            margin-top: 100px;
            padding: 20px;
            max-width: 1400px;
        }

        .search-bar-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .input-group {
            width: 300px;
        }

        table {
            width: 100%;
            margin-bottom: 20px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        th,
        td {
            text-align: center;
            vertical-align: middle;
        }

        img {
            object-fit: cover;
            width: 100%;
            max-width: 150px;
            height: 150px;
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
        <div class="logo-container">
            <img class="post-code-logo" src="../assets/img/post-code-logo.png" alt="PostCode Logo">
            <h5 class="text-dark">Admin Dashboard</h5>
        </div>
        <div class="user-controls">
            <h5>Hello, <?= htmlspecialchars($_SESSION['event-username']); ?>!</h5>
            <a href="add_event.php" class="btn btn-secondary">Add Event</a>
            <a href="../public/dashboard.php" class="btn btn-info">User Dashboard View</a>
            <a href="#" class="btn btn-danger" onclick="confirmLogOut(); return false;">Log Out</a>
        </div>
    </div>

    <div class="container">
        <h1 class="text-center">Event List</h1>

        <div class="search-bar-wrapper">
            <form method="GET" class="input-group">
                <input type="text" id="search" name="search" class="form-control" placeholder="Search events...">
                <button type="submit" class="btn btn-success">Search</button>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Image</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Change Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['event_name']); ?></td>
                            <td>
                                <img src="../uploads/<?= htmlspecialchars($event['image']); ?>"
                                    style="<?= $event['image'] === 'postcode_img.png' ? 'opacity: 0.2;' : ''; ?>">
                            </td>
                            <td><?= (new DateTime($event['event_date']))->format('F j, Y'); ?></td>
                            <td><?= (new DateTime($event['event_time']))->format('g:i A'); ?></td>
                            <td><?= htmlspecialchars($event['location']); ?></td>
                            <td><?= htmlspecialchars($event['description']); ?></td>
                            <td class="text-<?= strtolower($event['status']); ?>">
                                <?= htmlspecialchars($event['status']); ?>
                            </td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="event_id" value="<?= $event['event_id']; ?>">
                                    <select name="status" class="form-select">
                                        <option value="Open" <?= $event['status'] === 'Open' ? 'selected' : ''; ?>>Open
                                        </option>
                                        <option value="Closed" <?= $event['status'] === 'Closed' ? 'selected' : ''; ?>>Closed
                                        </option>
                                        <option value="Cancelled" <?= $event['status'] === 'Cancelled' ? 'selected' : ''; ?>>
                                            Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-primary mt-2">Update</button>
                                </form>
                            </td>
                            <td event-data-id="<?= htmlspecialchars($event['event_id']); ?>"
                                event-data-name="<?= htmlspecialchars($event['event_name']); ?>">
                                <a href="edit_event.php?event_id=<?= $event['event_id']; ?>"
                                    class="btn btn-primary">Edit</a>
                                <button class="btn btn-danger delete-confirm" data-id="<?= $event['event_id']; ?>"
                                    data-name="<?= htmlspecialchars($event['event_name']); ?>">Delete</button>
                                <a href="view_registration.php?event_id=<?= $event['event_id']; ?>"
                                    class="btn btn-success mt-2">View Registrations</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <a href="registration_history.php" class="btn btn-info mb-3">View All User Registration History</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmLogOut() {
            Swal.fire({
                title: "Log Out?",
                text: "You will be redirected to log in page!",
                icon: "warning",
                iconColor: "#a8211e",
                heightAuto: false,
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Log Out",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../include/logout.php';
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.delete-confirm').forEach(function (button) {
                button.addEventListener('click', function () {
                    const eventName = this.parentElement.getAttribute('event-data-name');
                    const eventID = this.parentElement.getAttribute('event-data-id');

                    Swal.fire({
                        title: `Delete event: ${eventName}?`,
                        text: "It will be permanently deleted!",
                        icon: "warning",
                        iconColor: "#a8211e",
                        heightAuto: false,
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Delete",
                        cancelButtonText: "Cancel",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `delete_event.php?event_id=${eventID}`;
                        }
                    });
                });
            });
        });

        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            heightAuto: false,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

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

        <?php if (!empty($_SESSION['event-status_login'])): ?>
            const loginStatuses = <?php echo json_encode($_SESSION['event-status_login']); ?>;
            loginStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast.fire({
                        icon: status.includes('Successfully') ? "success" : "error",
                        title: status
                    });
                }, index * 3500);
            });
        <?php endif; ?>

        <?php if (!empty($_SESSION['event-status_add_event'])): ?>
            const addEventStatuses = <?php echo json_encode($_SESSION['event-status_add_event']); ?>;
            addEventStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast8000.fire({
                        icon: status.includes('Successfully') ? "success" : "error",
                        title: status
                    });
                }, index * 8500);
            });
        <?php endif; ?>

        <?php if (!empty($_SESSION['event-status_edit_event'])): ?>
            const editEventStatuses = <?php echo json_encode($_SESSION['event-status_edit_event']); ?>;
            editEventStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast.fire({
                        icon: status.includes('Successfully') ? "success" : "error",
                        title: status
                    });
                }, index * 3500);
            });
        <?php endif; ?>

        <?php if (!empty($_SESSION['event-status_delete_event'])): ?>
            const deleteEventStatuses = <?php echo json_encode($_SESSION['event-status_delete_event']); ?>;
            deleteEventStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast.fire({
                        icon: status.includes('Successfully') ? "success" : "error",
                        title: status
                    });
                }, index * 3500);
            });
        <?php endif; ?>

        <?php if (!empty($_SESSION['event-status_update_status'])): ?>
            const updateStatusStatuses = <?php echo json_encode($_SESSION['event-status_update_status']); ?>;
            updateStatusStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast.fire({
                        icon: status.includes('Successfully') ? "success" : "error",
                        title: status
                    });
                }, index * 3500);
            });
        <?php endif; ?>
        <?php unset($_SESSION['event-status_login']); ?>
        <?php unset($_SESSION['event-status_add_event']); ?>
        <?php unset($_SESSION['event-status_edit_event']); ?>
        <?php unset($_SESSION['event-status_delete_event']); ?>
        <?php unset($_SESSION['event-status_update_status']); ?>
    </script>
</body>

</html>