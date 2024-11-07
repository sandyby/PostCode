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

?>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Profile</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            background-color: rgb(240, 240, 240);
            font-family: Arial, sans-serif;
        }

        /* Navbar styling */
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

        /* Centered container */
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 75vh;
            padding: 20px;
            margin-top: 80px; /* Offset for fixed navbar */
            max-width: 500px;
        }

        /* Profile text styling */
        h1 {
            text-align: center;
            margin-bottom: 15px;
            font-size: 2rem;
        }

        h2 {
            font-size: 1.5rem;
            color: #41835f;
        }

        p {
            font-size: 1rem;
            color: #666;
            margin-top: 5px;
        }

        /* Button styling */
        .btn-info {
            background-color: #41835ff5;
            color: white;
            font-weight: bold;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            margin-top: 20px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-info:hover {
            background-color: #4c8a68f5;
            transform: translateY(-3px);
        }

        /* Mobile responsive adjustments */
        @media (max-width: 768px) {
            h1 {
                font-size: 1.75rem;
            }

            h2 {
                font-size: 1.25rem;
            }

            p {
                font-size: 0.9rem;
            }

            .btn-info {
                font-size: 1rem;
                padding: 8px 16px;
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
    <div class="container text-center">
        <h1>Hello!</h1>
        <div class="my-2">
            <h2><?= htmlspecialchars($_SESSION['event-username']); ?></h2>
            <p><?= htmlspecialchars($_SESSION['event-email']); ?></p>
        </div>
        <a href="dashboard.php" class="btn btn-info">Back to Dashboard</a>
    </div>
</body>

</html>