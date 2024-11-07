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
require_once '../include/formValidation.php';

$error_msg = $_SESSION['event-edit_profile_error_msg'] ?? [];
unset($_SESSION['event-edit_profile_error_msg']);

$user_id = htmlspecialchars($_SESSION['event-user_id']);
$username = htmlspecialchars($_SESSION['event-username']);
$email = htmlspecialchars($_SESSION['event-email']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = htmlspecialchars($_POST['username']);
    $new_email = htmlspecialchars($_POST['email']);
    $old_password_input = htmlspecialchars($_POST['old_password']);
    $new_password = htmlspecialchars($_POST['new_password']);

    if (!isValid($new_username) || !isValid($new_email) || !isValid($old_password_input)) {
        $error_msg[] = 'Please enter a valid input!';
    }
    if (!isUsername($new_username)) {
        $error_msg[] = 'Please enter a valid username! (3-16 characters, only . and _ symbols are allowed)';
    }
    if (!isEmail($new_email)) {
        $error_msg[] = 'Please enter a valid e-mail!';
    }
    if (containAdmin($new_username)) {
        $error_msg[] = 'Username can\'t contain the word \'admin\'';
    }

    if (!empty($error_msg)) {
        $_SESSION['event-edit_profile_error_msg'] = $error_msg;
        header("Location: ../public/edit_profile.php");
        exit();
    }

    if ($username !== $new_username) {
        $sql1 = "SELECT * FROM users
        WHERE username = ?";
        $stmt = $pdo->prepare($sql1);
        $stmt->execute([$new_username]);
        $row1 = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row1) {
            $_SESSION['event-edit_profile_error_msg'][] = 'Username is already taken!';
            header("Location: ../public/edit_profile.php");
            exit();
        }
    }

    if ($email !== $new_email) {
        $sql2 = "SELECT * FROM users
        WHERE email = ?";
        $stmt = $pdo->prepare($sql2);
        $stmt->execute([$new_email]);
        $row2 = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row2) {
            $_SESSION['event-edit_profile_error_msg'][] = 'E-mail is already registered!';
            header("Location: ../public/edit_profile.php");
            exit();
        }
    }

    $en_pass_old = password_hash($old_password_input, PASSWORD_BCRYPT);

    $stmt1 = $pdo->prepare("SELECT password FROM users WHERE user_id = :user_id");
    $stmt1->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt1->execute();

    $old_password_in_db = $stmt1->fetch(PDO::FETCH_ASSOC)['password'];

    if (!password_verify($old_password_input, $old_password_in_db)) {
        $_SESSION['event-edit_profile_error_msg'][] = 'Old password is incorrect! Please try again';
        header("Location: ../public/edit_profile.php");
        exit();
    } else {


        if (empty($new_password)) {
            $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE user_id = :user_id");
        } else {
            if (!isPassword($new_password)) {
                $_SESSION['event-edit_profile_error_msg'][] = 'Please enter a valid password! (min. 8 characters, 1 lowercase, 1 uppercase, 1 number, and 1 symbol) Only the following symbols are allowed: @$!%*?&';
                header("Location: ../public/edit_profile.php");
                exit();
            }
            $en_pass_new = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email, password = :password WHERE user_id = :user_id");
            $stmt->bindParam(':password', $en_pass_new, PDO::PARAM_STR);
        }
        $stmt->bindParam(':username', $new_username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $new_email, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['event-username'] = $new_username;
        $_SESSION['event-email'] = $new_email;
        $_SESSION['event-status_edit_profile'][] = 'Successfully edited profile!';
        header('Location: dashboard.php');
        exit();
    }
}

?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit Profile</title>
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
            justify-content: center;
            align-items: center;
            text-align: center;
            top: 0;
        }

        .post-code-logo {
            max-height: 50px;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 80vh;
            padding: 20px;
            margin-top: 80px;
            max-width: 500px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2rem;
        }

        label {
            font-size: 1rem;
            color: #333;
            margin-top: 10px;
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
            padding: 10px;
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

        .btn-backdash {
            background-color: #9cb6a8;
            color: white;
            font-weight: bold;
            padding: 10px;
            width: 100%;
            border-radius: 8px;
            margin-top: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .btn-backdash:hover {
            background-color: #9cb6a8;
            transform: translateY(-3px);
        }

        .error-msg {
            color: #ff3b3bf5;
            text-align: center;
            font-size: 1rem;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 1.75rem;
            }

            label {
                font-size: 0.9rem;
            }

            input[type="text"],
            input[type="password"] {
                font-size: 0.9rem;
            }

            .btn-primary, .btn-backdash {
                font-size: 0.9rem;
                padding: 8px;
            }
        }
    </style>
</head>

<body>
    <div class="nav">
        <img class="post-code-logo" src="../assets/img/post-code-logo.png" alt="post-code-logo">
    </div>

    <div class="container">
        <h1>Edit Profile</h1>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <?php if (!empty($error_msg)): ?>
                <div class="error-msg">
                    <h6><?php echo htmlspecialchars($error_msg[0]); ?></h6>
                </div>
            <?php endif; ?>
            <label>Username</label>
            <input type="text" name="username" placeholder="Username" value="<?= $username; ?>" />
            <label>Email</label>
            <input type="text" name="email" placeholder="E-mail" value="<?= $email; ?>" />
            <label>Old Password</label>
            <input type="password" name="old_password" placeholder="Current Password" />
            <label>New Password (optional)</label>
            <input type="password" name="new_password" placeholder="New Password" />
            <button type="submit" class="btn btn-primary my-2">Save</button>
        </form>
        <a href="dashboard.php" class="btn btn-backdash">Back to Dashboard</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>