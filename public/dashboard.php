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

$currDate = date('Y-m-d');
$currTime = date('H:i:s');
$stmt = $pdo->prepare("SELECT * FROM events
                                WHERE (event_date > :current_date) 
                                OR (event_date = :current_date AND event_time > :current_time)");
$stmt->bindParam(':current_date', $currDate);
$stmt->bindParam(':current_time', $currTime);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <title>PostCode</title>
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

        .post-code-logo {
            max-height: 50px;
            margin-right: 15px;
        }

        .container {
            margin-top: 65px;
            padding: 20px;
            max-width: 1200px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .event-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-5px);
        }

        .event-image {
            border-radius: 10px;
            object-fit: cover;
            width: 100%;
            max-height: 200px;
            max-height: 200px;
            height: 100%;
            margin-bottom: 15px;
        }

        .event-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        .btn-primary {
            background-color: #4c8a68f5;
            border: #4c8a68f5;
            width: 100%;
            margin-top: 10px;
        }

        .btn-primary:hover{
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

            .event-card {
                width: 300px;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="nav">
        <div class="d-flex align-items-center">
            <img class="post-code-logo" src="../assets/img/post-code-logo.png" alt="PostCode Logo">
            <h5 class="text-dark">PostCode</h5>
        </div>

        <?php if ($_SESSION['event-user_role'] !== 1): ?>
        <span class="navbar-text" id="profileButton" style="cursor: pointer;">
            Hello, <?= htmlspecialchars($_SESSION['event-username']); ?>!
        </span>
        <?php elseif ($_SESSION['event-user_role'] === 1): ?>
            <div>
                <a href="../admin/dashboardAdmin.php" class="btn btn-info my-2">Admin Dashboard View</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
    <div class="container">
        <h1>Upcoming Events</h1>

        <div class="event-grid">
            <?php if (empty($events)): ?>
                <p class="text-center">There are no events yet!</p>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <div class="img-wrapper">
                            <img src="../uploads/<?= htmlspecialchars($event['image']) ?>" alt="Event Image" class="event-image"
                                style="<?= $event['image'] === 'postcode_img.png' ? 'opacity: 0.2;' : ''; ?>">
                        </div>
                        <h3><?= htmlspecialchars($event['event_name']); ?></h3>
                        <p><strong>Date:</strong> <?= (new DateTime($event['event_date']))->format('F j, Y'); ?></p>
                        <p><strong>Time:</strong> <?= (new DateTime($event['event_time']))->format('g:i A'); ?></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($event['location']); ?></p>
                        <p><strong>Max Participants:</strong> <?= htmlspecialchars($event['max_participants']); ?></p>
                        <p class="text-success" style="font-weight: bold;">
                            <?= htmlspecialchars($event['status']); ?>
                        </p>

                        <a href="view_event.php?event_id=<?= htmlspecialchars($event['event_id']) ?>"
                            class="btn btn-primary">View Event Details</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4">
            <?php if ($_SESSION['event-user_role'] !== 1): ?>
                <a href="view_registered_events.php" class="btn btn-primary">Show Registered Events</a>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>

        document.getElementById('profileButton').addEventListener('click', function () {
            Swal.fire({
                title: 'Profile Options',
                html: `
                            <a href="view_profile.php" class="btn btn-info w-75 my-2">View Profile</a>
                            <a href="edit_profile.php" class="btn btn-warning w-75 my-2">Edit Profile</a>
                            <a href="../include/logout.php" class="btn btn-danger w-75 my-2">Logout</a>
                        `,
                showConfirmButton: false,
                heightAuto: false
            });
        });

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

        <?php if (!empty($_SESSION['event-status_edit_profile'])): ?>
            const editProfileStatuses = <?php echo json_encode($_SESSION['event-status_edit_profile']); ?>;
            editProfileStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast.fire({
                        icon: status.includes('Successfully') ? "success" : "error",
                        title: status
                    });
                }, index * 3500);
            });
        <?php endif; ?>

        <?php if (!empty($_SESSION['event-status_view_event'])): ?>
            const editProfileStatuses = <?php echo json_encode($_SESSION['event-status_view_event']); ?>;
            editProfileStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast.fire({
                        icon: status.includes('Successfully') ? "success" : "error",
                        title: status
                    });
                }, index * 3500);
            });
        <?php endif; ?>
        <?php unset($_SESSION['event-status_login']); ?>
        <?php unset($_SESSION['event-status_edit_profile']); ?>
        <?php unset($_SESSION['event-status_view_event']); ?>
    </script>
</body>

</html>