<?php
session_start();
require_once '../include/initDB_T.php';
require_once '../include/connection.php';
$_SESSION['event-form_token'] = hash("sha256", bin2hex(random_bytes(16)));

if (isset($_SESSION['event-logged-in'])) {
    header('Location: dashboard.php');
    exit();
}

$error_msg = $_SESSION['event-forgot_password_error_msg'] ?? "";
unset($_SESSION['event-forgot_password_error_msg']);
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

        input[type="text"] {
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

            .btn-primary {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="nav">
        <img class="post-code-logo" src="../assets/img/post-code-logo.png" alt="post-code-logo">
    </div>

    <!-- Forgot Password Form -->
    <div class="container">
        <h1>Forgot Password</h1>
        <form id="form-forgot-password" action="../include/send_password_mail.php" method="post">
            <?php if (!empty($error_msg)): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error_msg[0]); ?></div>
            <?php endif; ?>
            <input type="text" name="email" placeholder="Enter your email" required />
            <button type="submit" class="btn btn-primary my-2">Send Reset Link</button>
        </form>
        Don't have an account? <a href="register.php">Register</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('form-forgot-password').addEventListener('submit', function(event) {
            Swal.fire({
                title: 'Processing...',
                html: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });
        });

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

        <?php if (!empty($_SESSION['event-status_forgot_password'])): ?>
            const forgotPasswordStatuses = <?php echo json_encode($_SESSION['event-status_forgot_password']); ?>;
            forgotPasswordStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast.fire({
                        icon: status.includes('Success') ? "success" : "error",
                        title: status
                    });
                }, index * 5500);
            });
        <?php endif; ?>

        <?php if (!empty($_SESSION['event-status_reset_password'])): ?>
            const resetPasswordStatuses = <?php echo json_encode($_SESSION['event-status_reset_password']); ?>;
            resetPasswordStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast.fire({
                        icon: status.includes('success') ? "success" : "error",
                        title: status
                    });
                }, index * 5500);
            });
        <?php endif; ?>
        <?php unset($_SESSION['event-status_reset_password']); ?>
        <?php unset($_SESSION['event-status_forgot_password']); ?>
    </script>
</body>

</html>