<?php
session_start();
require_once '../include/initDB_T.php';
require_once '../include/connection.php';
$_SESSION['event-form_token'] = hash("sha256", bin2hex(random_bytes(16)));

if (isset($_SESSION['event-logged-in']) && $_SESSION['event-user_role'] === 1) {
    header('Location: ../admin/dashboardAdmin.php');
    exit();
} elseif (isset($_SESSION['event-logged-in'])) {
    header('Location: dashboard.php');
    exit();
}

$error_msg = $_SESSION['event-login_error_msg'] ?? "";
unset($_SESSION['event-login_error_msg']);
?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Log In</title>
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            background-color: rgb(240, 240, 240);
        }

        .container {
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 20px;
        }

        .navbar {
            width: 100%;
            padding: 10px;
            background-color: #f8f9fa;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            align-items: center;
            justify-content: center;
        }

        .nav {
            width: 100%;
            background-color: rgb(215, 215, 215);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            position: fixed;
            justify-content: center;
            align-items: center;
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }

        .post-code-logo {
            max-height: 50px;
        }

        .login-card {
            background-color: #0c8040b0;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 25px;
            width: 100%;
            max-width: 400px;
        }

        h1 {
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 2rem;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .btn-primary {
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

        .btn-primary:hover {
            background-color: #4c8a68f5;
            transform: translateY(-5px);
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .error-msg {
            color: #ff3b3bf5;
            margin-top: 10px;
            text-align: center;
            font-size: 1rem;
        }

        a {
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            color: rgb(51, 51, 51);
        }

        a:hover {
            color: rgb(133, 133, 133);
        }

        @media (max-width: 768px) {
            .login-card {
                padding: 20px;
                margin: 10px;
            }

            h1 {
                font-size: 1.5rem;
            }

            .btn-primary {
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
        <h1>Log In</h1>
        <form action="../include/login_proses.php" method="post">
            <input type="hidden" name="form_token" value="<?php echo $_SESSION['event-form_token']; ?>">
            <?php if (!empty($error_msg)): ?>
                <div class="">
                    <h6 class="error-msg"><?php echo htmlspecialchars($error_msg[0]); ?></h6>
                </div>
            <?php endif; ?>
            <input type="text" name="email" placeholder="E-mail" />
            <input type="password" name="password" placeholder="Password" />
            <button type="submit" class="btn btn-primary my-2">Log In</button>
        </form>

        <a href="forgot_password.php">Forgot Password?</a>
        <p>Don't have an account yet?<a href="register.php"> Register</a></p>
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

        <?php if (!empty($_SESSION['event-status_logout'])): ?>
            const logoutStatuses = <?php echo json_encode($_SESSION['event-status_logout']); ?>;
            logoutStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast.fire({
                        icon: status.includes('Successfully') ? "success" : "error",
                        title: status
                    });
                }, index * 8500);
            });
        <?php endif; ?>

        <?php if (!empty($_SESSION['event-status_register'])): ?>
            const registerStatuses = <?php echo json_encode($_SESSION['event-status_register']); ?>;
            registerStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast.fire({
                        icon: status.includes('success') ? "success" : "error",
                        title: status
                    });
                }, index * 3500);
            });
        <?php endif; ?>

        <?php if (!empty($_SESSION['event-status_verifikasi'])): ?>
            const verificationStatuses = <?php echo json_encode($_SESSION['event-status_verifikasi']); ?>;
            verificationStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast8000.fire({
                        icon: status.includes('sent') ? "success" : "error",
                        title: status
                    });
                }, index * 8500);
            });
        <?php endif; ?>

        <?php if (!empty($_SESSION['event-status_verifikasi_2'])): ?>
            const verificationStatuses2 = <?php echo json_encode($_SESSION['event-status_verifikasi_2']); ?>;
            verificationStatuses2.forEach((status, index) => {
                setTimeout(() => {
                    Toast8000.fire({
                        icon: status.includes('success') ? "success" : "error",
                        title: status
                    });
                }, index * 8500);
            });
        <?php endif; ?>

        <?php if (!empty($_SESSION['event-status_reset_password'])): ?>
            const resetPasswordStatuses = <?php echo json_encode($_SESSION['event-status_reset_password']); ?>;
            resetPasswordStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast8000.fire({
                        icon: status.includes('successfully') ? "success" : "error",
                        title: status
                    });
                }, index * 8500);
            });
        <?php endif; ?>

        <?php if (!empty($_SESSION['event-status_forgot_password'])): ?>
            const forgotPasswordStatuses = <?php echo json_encode($_SESSION['event-status_forgot_password']); ?>;
            forgotPasswordStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast8000.fire({
                        icon: status.includes('check') ? "success" : "error",
                        title: status
                    });
                }, index * 8500);
            });
        <?php endif; ?>
        <?php unset($_SESSION['event-status_logout']); ?>
        <?php unset($_SESSION['event-status_register']); ?>
        <?php unset($_SESSION['event-status_verifikasi']); ?>
        <?php unset($_SESSION['event-status_verifikasi_2']); ?>
        <?php unset($_SESSION['event-status_reset_password']); ?>
        <?php unset($_SESSION['event-status_forgot_password']); ?>
    </script>
</body>

</html>