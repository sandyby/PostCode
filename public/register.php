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

$error_msg = $_SESSION['event-register_error_msg'] ?? "";
unset($_SESSION['event-register_error_msg']);
?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Register Account</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            background-color: rgb(240, 240, 240);
        }

        .navbar {
            width: 100%;
            background-color: rgb(215, 215, 215);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            position: fixed;
            top: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }

        .navbar img {
            max-height: 50px;
        }

        .container {
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 20px;
            margin-top: 50px;
        }

        .register-card {
            background-color: #edf1ee;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 25px;
            width: 100%;
            max-width: 400px;
        }

        h1 {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .btn {
            color: black;
        }

        .btn-danger {
            background-color: #d8e1df;
            font-weight: bold;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            margin-top: 20px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .btn-danger:hover {
            background-color: #d8e1df;
            transform: translateY(-5px);
        }

        .btn-danger:active {
            transform: translateY(0);
        }

        .navbar-text {
            cursor: pointer;
            font-weight: bold;
        }

        a {
            text-align: center;
            margin-top: 15px;
            display: block;
            text-decoration: none;
            color: rgb(51, 51, 51);
        }

        a:hover {
            color: rgb(133, 133, 133);
        }

        .error-msg {
            color: #ff3b3b;
            margin-top: 10px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .register-card {
                padding: 20px;
                margin: 10px;
            }

            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <img src="../assets/img/post-code-logo.png" alt="post-code-logo">
        <span class="navbar-text" id="profileButton">
            Hello, Guest!
        </span>
    </div>

    <!-- Registration Form -->
    <div class="container">
        <div class="register-card">
            <h1>Register</h1>
            <form id="form-registrasi" action="../include/register_proses.php" method="post">
                <input type="hidden" name="form_token" value="<?php echo $_SESSION['event-form_token']; ?>">
                <?php if (!empty($error_msg)): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($error_msg[0]); ?></div>
                <?php endif; ?>
                <input type="text" name="username" placeholder="Username" required />
                <input type="text" name="email" placeholder="E-mail" required />
                <input type="password" name="password" placeholder="Password" required />
                <button type="submit" class="btn btn-danger my-2">Register</button>
            </form>
            <a href="login.php">Already have an account? Log In</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('form-registrasi').addEventListener('submit', function(event) {
            Swal.fire({
                title: 'Registering...',
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
            timer: 8000,
            heightAuto: false,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        <?php if (!empty($_SESSION['event-status_verifikasi'])): ?>
            const verificationStatuses = <?php echo json_encode($_SESSION['event-status_verifikasi']); ?>;
            verificationStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast.fire({
                        icon: status.includes('sent') ? "success" : "error",
                        title: status
                    });
                }, index * 8500);
            });
        <?php endif; ?>
        <?php if (!empty($_SESSION['event-status_register'])): ?>
            const verificationStatuses = <?php echo json_encode($_SESSION['event-status_register']); ?>;
            verificationStatuses.forEach((status, index) => {
                setTimeout(() => {
                    Toast.fire({
                        icon: status.includes('sent') ? "success" : "error",
                        title: status
                    });
                }, index * 8500);
            });
        <?php endif; ?>
        <?php unset($_SESSION['event-status_verifikasi']); ?>
        <?php unset($_SESSION['event-status_register']); ?>
    </script>
</body>

</html>