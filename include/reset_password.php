<?php
session_start();

require_once 'connection.php';
$error_msg = $_SESSION['event-reset_password_error_msg'] ?? "";
unset($_SESSION['event-reset_password_error_msg']);

$reset_password_token = $_GET['reset_password_token'];
$reset_password_token_hash = hash("sha256", $reset_password_token);

$sql = "SELECT * FROM users
        WHERE reset_password_token = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$reset_password_token_hash]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row === false) {
    $_SESSION['event-status_reset_password'][] = 'Invalid token! Please use a valid one';
    header("Location: ../public/forgot_password.php");
    exit();
}

if (strtotime($row['reset_password_token_expiry_date']) <= time()) {
    $_SESSION['event-status_reset_password'][] = 'Token has expired! Please request a new one';
    header("Location: ../public/forgot_password.php");
    exit();
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Forgot Password</title>
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
            z-index: 1000;
        }

        .post-code-logo {
            max-height: 50px;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: calc(100vh - 80px); /* Full height minus navbar */
            padding: 20px;
        }

        form {
            width: 100%;
            max-width: 400px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2rem;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .btn-danger {
            background-color: #41835ff5;
            color: black;
            font-weight: bold;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .btn-danger:hover {
            background-color: #4c8a68f5;
            transform: translateY(-5px);
        }

        .error-msg {
            color: #ff3b3b;
            text-align: center;
            margin-top: 10px;
            font-size: 1rem;
        }

        a {
            margin-top: 15px;
            text-decoration: none;
            color: rgb(51, 51, 51);
            display: block;
            text-align: center;
        }

        a:hover {
            color: rgb(133, 133, 133);
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 1.5rem;
            }

            .btn-danger {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="nav">
        <img class="post-code-logo" src="../assets/img/post-code-logo.png" alt="post-code-logo">
    </div>

    <div class="container">
        <h1>Reset Password</h1>
        <form action="reset_password_proses.php" method="post">
            <input type="hidden" name="form_token" value="<?= $_SESSION['event-form_token']; ?>">
            <input type="hidden" name="reset_password_token" value="<?= htmlspecialchars($reset_password_token); ?>">
            <?php if (!empty($error_msg)): ?>
                <div class="">
                    <h6 class="error-msg"><?= htmlspecialchars($error_msg[0]); ?></h6>
                </div>
            <?php endif; ?>
            <input type="password" name="new-password" placeholder="New Password" />
            <input type="password" name="confirm-new-password" placeholder="Confirm New Password" />
            <button type="submit" class="btn btn-danger my-2">Ubah</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 5000,
            heightAuto: false,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        <?php if (!empty($_SESSION['event-status_reset_password'])): ?>
            const resetPasswordStatuses = <?php echo json_encode($_SESSION['event-status_reset_password']); ?>;
            resetPasswordStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast.fire({
                        icon: status.includes('successfully') ? "success" : "error",
                        title: status
                    });
                }, index * 5500);
            });
        <?php endif; ?>
        <?php unset($_SESSION['event-status_reset_password']); ?>
    </script>
</body>

</html>