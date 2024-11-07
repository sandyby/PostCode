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

if (!isset($_GET['event_id'])) {
    $_SESSION['event-status_edit_event'][] = "Please select an event to be edited first!";
    header("Location: dashboardAdmin.php");
    exit();
}

require_once '../include/connection.php';
require_once '../include/formValidation.php';
require_once '../include/generateFileName.php';

$error_msg = $_SESSION['event-edit_event_error_msg'] ?? [];
unset($_SESSION['event-edit_event_error_msg']);

$eventId = $_GET['event_id'];
$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = :event_id");
$stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
$stmt->execute();
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if ($stmt->rowCount() === 0) {
    $_SESSION['event-status_edit_event'][] = "Event not found! Make sure the selected event exists";
    header("Location: dashboardAdmin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $eventName = preg_replace('/\s+/', ' ', trim($_POST['event_name']));
    $eventName = $_POST['event_name'];
    $eventDate = $_POST['event_date'];
    $eventTime = $_POST['event_time'];
    $location = $_POST['event_location'];
    $description = $_POST['event_description'];
    $maxParticipants = $_POST['event_max_participants'];


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
        $_SESSION['event-edit_event_error_msg'] = $error_msg;
        header("Location: edit_event.php?event_id=" . $eventId);
        exit();
    }

    $stmt = $pdo->prepare("SELECT image FROM events WHERE event_id = :event_id");
    $stmt->bindParam(':event_id', $eventId, PDO::PARAM_STR);
    if (!$stmt->execute()) {
        $_SESSION['event-status_edit_event'][] = "An error occured! Please try again later";
        header("Location: edit_event.php?event_id=" . $eventId);
        exit();
    }
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result === false) {
        $_SESSION['event-status_edit_event'][] = "An error occured! Please try again later";
        header("Location: edit_event.php?event_id=" . $eventId);
        exit();
    }

    $old_image = (isset($result['image']) && $result['image'] !== 'postcode_img.png') ? $result['image'] : 'postcode_img.png';

    $imgChanged = isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK && $_FILES['event_image']['name'] !== $old_image;
    $file_max_size = $_POST['MAX_FILE_SIZE'] ?? 4000000;
    $file_size = $_FILES['event_image']['size'];

    if ($imgChanged) {
        $file_ext = strtolower(pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'svg', 'webp', 'bmp', 'gif'];

        if ($file_size > $file_max_size) {
            $_SESSION['event-status_edit_event'][] = "File exceeds max file size! Please try again";
            header("Location: edit_event.php?event_id=" . $eventId);
            exit();
        }

        if (!in_array($file_ext, $allowedTypes)) {
            $_SESSION['event-status_edit_event'][] = "File type incorrect! Please try again";
            header("Location: edit_event.php?event_id=" . $eventId);
            exit();
        }

        if ($old_image !== 'postcode_img.png' && file_exists("../uploads/$old_image")) {
            unlink("../uploads/$old_image");
        }

        $uploaded_file_name = generateFileName();
        $upload_destination_path = "../uploads/$uploaded_file_name";
        if (!move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_destination_path)) {
            $_SESSION['event-status_edit_event'][] = "Failed uploading file! Please try again later";
            header("Location: edit_event.php?event_id=" . $eventId);
            exit();
        }
    } elseif ($_FILES['event_image']['error'] === UPLOAD_ERR_FORM_SIZE) {
        $_SESSION['event-status_edit_event'][] = "File exceeds max file size! Please try again";
        header("Location: edit_event.php?event_id=" . $eventId);
        exit();
    } else {
        $uploaded_file_name = $old_image;
    }

    $stmt = $pdo->prepare("UPDATE events SET event_name = :event_name, event_date = :event_date, event_time = :event_time, location = :location, description = :description, max_participants = :max_participants, image = :image WHERE event_id = :event_id");

    if (!$stmt->execute([
        ':event_name' => $eventName,
        ':event_date' => $eventDate,
        ':event_time' => $eventTime,
        ':location' => $location,
        ':description' => $description,
        ':max_participants' => $maxParticipants,
        ':image' => $uploaded_file_name,
        ':event_id' => $eventId
    ])) {
        $_SESSION['event-status_edit_event'][] = "Failed editing event! Please try again later";
        header("Location: dashboardAdmin.php");
        exit();
    }

    $_SESSION['event-status_edit_event'][] = "Successfully edited event: " . $eventName . '!';
    header("Location: dashboardAdmin.php");
    exit();
}
?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit Event</title>
</head>

<body>
    <div class="container" style="max-width: 500px">
        <h1>Edit Event</h1>
        <form method="post" class="form mt-4" enctype="multipart/form-data">
            <?php if (!empty($error_msg)): ?>
                <div class="">
                    <h6 class="error-msg"><?= htmlspecialchars($error_msg[0]); ?></h6>
                </div>
            <?php endif; ?>
            <div class="my-2">
                <label for="event_name">Event Name</label>
                <input type="text" name="event_name" class="form-control" id="event_name" value="<?= htmlspecialchars($event['event_name']); ?>" title="Event name must be 1-50 characters long and can include letters, numbers, spaces, commas, periods, dashes, exclamation marks, ampersands, and apostrophes.">

                <label for="event_date">Event Date</label>
                <input type="date" name="event_date" class="form-control" id="event_date" value="<?= htmlspecialchars($event['event_date']); ?>">

                <label for="event_time">Event Time</label>
                <input type="time" name="event_time" class="form-control" id="event_time" value="<?= htmlspecialchars($event['event_time']); ?>">

                <label for="event_location">Location</label>
                <input type="text" name="event_location" class="form-control" id="event_location" value="<?= htmlspecialchars($event['location']); ?>" title="Location must be 1-100 characters long and can include letters, numbers, spaces, commas, periods, dashes, exclamation marks, ampersands, and apostrophes.">

                <label for="event_description">Description</label>
                <textarea name="event_description" class="form-control" id="event_description" title="Description must be 1-500 characters long and can include letters, numbers, spaces, commas, periods, dashes, exclamation marks, ampersands, and apostrophes."><?= htmlspecialchars($event['description']); ?></textarea>

                <label for="event_max_participants">Maximum Participants</label>
                <input type="number" name="event_max_participants" class="form-control" id="event_max_participants" value="<?= htmlspecialchars($event['max_participants']); ?>">

                <label for="event_image">Event Image</label>
                <input type="file" name="event_image" class="form-control" id="event_image" accept="image/*">

            </div>
            <button type="submit" class="btn btn-primary my-2">Save Changes</button>
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
            timer: 3000,
            heightAuto: false,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        <?php if (!empty($_SESSION['event-status_edit_event'])): ?>
            const editEventStatuses = <?php echo json_encode($_SESSION['event-status_edit_event']); ?>;
            editEventStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast8000.fire({
                        icon: status.includes('Successfully') ? "success" : "error",
                        title: status
                    });
                }, index * 8500);
            });
        <?php endif; ?>
        <?php unset($_SESSION['event-status_edit_event']); ?>
    </script>
</body>

</html>