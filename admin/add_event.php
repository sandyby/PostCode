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
require_once '../include/formValidation.php';
require_once '../include/generateFileName.php';

$eventName = $_SESSION['event-add_event_data']['event_name'] ?? '';
$eventDate = $_SESSION['event-add_event_data']['event_date'] ?? '';
$eventTime = $_SESSION['event-add_event_data']['event_time'] ?? '';
$location = $_SESSION['event-add_event_data']['event_location'] ?? '';
$description = $_SESSION['event-add_event_data']['event_description'] ?? '';
$maxParticipants = $_SESSION['event-add_event_data']['event_max_participants'] ?? '';
$createdBy = $_SESSION['event-user_id'];

$error_msg = $_SESSION['event-add_event_error_msg'] ?? [];
unset($_SESSION['event-add_event_data'], $_SESSION['event-add_event_error_msg']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $eventName = htmlspecialchars(preg_replace('/\s+/', ' ', trim($_POST['event_name'])));
    $eventDate = $_POST['event_date'];
    $eventTime = $_POST['event_time'];
    $location = htmlspecialchars($_POST['event_location']);
    $description = htmlspecialchars($_POST['event_description']);
    $maxParticipants = $_POST['event_max_participants'];
    $createdBy = $_SESSION['event-user_id'];

    if (!isValid($eventName) || !isValid($eventDate) || !isValid($eventTime) || !isValid($location) || !isValid($maxParticipants)) {
        $error_msg[] = "Please input a valid event data!";
    }

    if (!isEventName($eventName)) {
        $error_msg[] = "Please enter a valid event name! Allowed symbols: ,.-!&'";
    }

    if (!isDate($eventDate)) {
        $error_msg[] = "Please enter a valid date in the format MM/DD/YYYY.";
    }

    if (!isDigits($maxParticipants)) {
        $error_msg[] = "Please enter a valid number for maximum participants.";
    }

    if (!empty($error_msg)) {
        $_SESSION['event-add_event_data'] = ['event_name' => $eventName, 'event_date' => $eventDate, 'event_time' => $eventTime, 'event_location' => $location, 'event_description' => $description, 'event_max_participants' => $maxParticipants];
        $_SESSION['event-add_event_error_msg'] = $error_msg;
        header("Location: add_event.php");
        exit();
    }

    if (!empty($_FILES['event_image']['name']) && isset($_FILES['event_image']) && $_FILES['event_image']['error'] == UPLOAD_ERR_OK) {
        $file_ext = htmlspecialchars(strtolower(pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION)));
        $file_max_size = $_POST['MAX_FILE_SIZE'] ?? 4000000;
        $file_size = $_FILES['event_image']['size'];
        $allowedTypes = ['jpg', 'jpeg', 'png', 'svg', 'webp', 'bmp', 'gif'];

        if ($file_size > $file_max_size) {
            $_SESSION['event-status_add_event'][] = "File exceeds max file size! Please try again";
            $_SESSION['event-add_event_data'] = ['event_name' => $eventName, 'event_date' => $eventDate, 'event_time' => $eventTime, 'event_location' => $location, 'event_description' => $description, 'event_max_participants' => $maxParticipants];
            header("Location: add_event.php");
            exit();
        }

        if (!in_array($file_ext, $allowedTypes)) {
            $_SESSION['event-status_add_event'][] = "File type incorrect! Please try again";
            $_SESSION['event-add_event_data'] = ['event_name' => $eventName, 'event_date' => $eventDate, 'event_time' => $eventTime, 'event_location' => $location, 'event_description' => $description, 'event_max_participants' => $maxParticipants];
            header("Location: add_event.php");
            exit();
        }

        $uploaded_file_name = generateFileName();
        echo $uploaded_file_name;
        $upload_destination_path = '../uploads/' . $uploaded_file_name;
        echo $upload_destination_path;

        if (!move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_destination_path)) {
            $_SESSION['event-status_add_event'][] = "Failed uploading file! Please try again later";
            header("Location: dashboardAdmin.php");
            exit();
        }
    } elseif (!empty($_FILES['event_image']['name']) && $_FILES['event_image']['error'] === UPLOAD_ERR_FORM_SIZE) {
        $_SESSION['event-status_add_event'][] = "File exceeds max file size! Please try again";
        $_SESSION['event-add_event_data'] = ['event_name' => $eventName, 'event_date' => $eventDate, 'event_time' => $eventTime, 'event_location' => $location, 'event_description' => $description, 'event_max_participants' => $maxParticipants];
        header("Location: add_event.php");
        exit();
    } else {
        $uploaded_file_name = 'postcode_img.png';
    }

    $stmt = $pdo->prepare("INSERT INTO events (event_name, event_date, event_time, location, description, max_participants, image, created_by) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt->execute([$eventName, $eventDate, $eventTime, $location, $description, $maxParticipants, $uploaded_file_name, $createdBy])) {
        $_SESSION['event-status_add_event'][] = "Failed creating event! Please try again later";
        header("Location: dashboardAdmin.php");
        exit();
    }
    $_SESSION['event-status_add_event'][] = "Successfully created event: " . $eventName;
    header("Location: dashboardAdmin.php");
    exit();
}
?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Add Event</title>
</head>

<body>
    <div class="container" style="max-width: 500px">
        <h1>Add Event</h1>
        <form method="post" class="form mt-4" enctype="multipart/form-data" novalidate>
            <?php if (!empty($error_msg)): ?>
                <div class="">
                    <h6 class="error-msg"><?php echo htmlspecialchars($error_msg[0]); ?></h6>
                </div>
            <?php endif; ?>
            <div class="my-2">
                <label for="event_name">Event Name</label>
                <input type="text" name="event_name" class="form-control" id="event_name" value="<?= $eventName; ?>" title="Event name must be 1-50 characters long and can include letters, numbers, spaces, commas, periods, dashes, exclamation marks, ampersands, and apostrophes.">

                <label for="event_date">Event Date</label>
                <input type="date" name="event_date" class="form-control" id="event_date" value="<?= $eventDate; ?>">

                <label for="event_time">Event Time</label>
                <input type="time" name="event_time" class="form-control" id="event_time" value="<?= $eventTime; ?>">

                <label for="event_location">Location</label>
                <input type="text" name="event_location" class="form-control" id="event_location" value="<?= $location; ?>"
                    title="Location must be 1-100 characters long and can include letters, numbers, spaces, commas, periods, dashes, exclamation marks, ampersands, and apostrophes.">

                <label for="event_description">Description</label>
                <textarea name="event_description" class="form-control" id="event_description" title="Description must be 1-500 characters long and can include letters, numbers, spaces, commas, periods, dashes, exclamation marks, ampersands, and apostrophes."><?= $description; ?></textarea>

                <label for="event_max_participants">Maximum Participants</label>
                <input type="number" name="event_max_participants" class="form-control" id="event_max_participants" value="<?= $maxParticipants; ?>">

                <label for="event_image">Event Image</label>
                <input type="file" name="event_image" class="form-control" id="event_image" accept=".jpg, .jpeg, .png, .svg, .webp, .bmp, .gif">

            </div>
            <button type="submit" class="btn btn-primary my-2">Add Event</button>
        </form>
        <a href="../admin/dashboardAdmin.php" class="btn btn-info">Dashboard</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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
        <?php unset($_SESSION['event-status_add_event']); ?>
    </script>
</body>

</html>