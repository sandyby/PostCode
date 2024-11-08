<?php
define("DATABASE_NAME", "webpro_uts_lect");
define("USERS_TABLE_NAME", "users");
define("EVENTS_TABLE_NAME", "events");
define("REGS_TABLE_NAME", "regs");
define("REGS_HISTORY_TABLE_NAME", "regs_history");

function createDatabase(): void
{
  $servername = "localhost";
  $username = "root";
  $password = "";
  try {
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "CREATE DATABASE IF NOT EXISTS " . DATABASE_NAME;
    $conn->query($sql);
  } catch (PDOException $e) {
    error_log($sql . "<br>" . $e->getMessage());
  }
  $conn = null;
}

function createTableUsers(): void
{
  $servername = "localhost";
  $username = "root";
  $password = "";
  try {
    $conn = new PDO("mysql:host=$servername;dbname=" . DATABASE_NAME, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS " . USERS_TABLE_NAME . "(
            user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(32) UNIQUE NOT NULL,
            email VARCHAR(128) UNIQUE NOT NULL,
            password VARCHAR(128),
            role_id INT DEFAULT 0, 
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            verification_code VARCHAR(64) UNIQUE DEFAULT NULL,
            isVerified BIT DEFAULT 0,
            verified_at TIMESTAMP NULL DEFAULT NULL,
            reset_password_token VARCHAR(64) UNIQUE DEFAULT NULL,
            reset_password_token_expiry_date TIMESTAMP NULL DEFAULT NULL
        )";
    $conn->exec($sql);
  } catch (PDOException $e) {
    error_log($sql . "<br>" . $e->getMessage());
  }
  $conn = null;
}

function createTableEvents(): void
{
  $servername = "localhost";
  $username = "root";
  $password = "";
  try {
    $conn = new PDO("mysql:host=$servername;dbname=" . DATABASE_NAME, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS " . EVENTS_TABLE_NAME . "(
            event_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            event_name VARCHAR(255) NOT NULL,
            event_date DATE NOT NULL,
            event_time TIME NOT NULL,
            location VARCHAR(255) NOT NULL,
            description VARCHAR(255),
            image VARCHAR(255) DEFAULT 'postcode_img.png',
            max_participants INT NOT NULL,
            created_by INT UNSIGNED, 
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('Open', 'Closed', 'Cancelled') DEFAULT 'Open',
            FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
        )";

    $conn->exec($sql);
  } catch (PDOException $e) {
    error_log($sql . "<br>" . $e->getMessage());
  }
  $conn = null;
}

function createTableRegistrations(): void
{
  $servername = "localhost";
  $username = "root";
  $password = "";
  try {
    $conn = new PDO("mysql:host=$servername;dbname=" . DATABASE_NAME, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS " . REGS_TABLE_NAME . "(
            reg_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED,
            event_id INT UNSIGNED,
            registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
            UNIQUE(user_id, event_id)
        )";

    $conn->exec($sql);
  } catch (PDOException $e) {
    error_log($sql . "<br>" . $e->getMessage());
    echo $sql . "<br>" . $e->getMessage();
  }
  $conn = null;
}

function createTableRegistration_History(): void
{
  $servername = "localhost";
  $username = "root";
  $password = "";
  try {
    $conn = new PDO("mysql:host=$servername;dbname=" . DATABASE_NAME, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS " . REGS_HISTORY_TABLE_NAME . "(
            history_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            event_id INT UNSIGNED NOT NULL,
            action ENUM('registered', 'cancelled') NOT NULL,
            action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
            FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
        )";

    $conn->exec($sql);
  } catch (PDOException $e) {
    error_log($sql . "<br>" . $e->getMessage());
    echo $sql . "<br>" . $e->getMessage();
  }
  $conn = null;
}

function createAdminUser()
{
  $servername = "localhost";
  $username = "root";
  $password = "";
  try {
    $conn = new PDO("mysql:host=$servername;dbname=" . DATABASE_NAME, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $adminUsername = 'admin123';
    $adminEmail = 'admin123@gmail.com';
    $adminPassword = password_hash('password123', PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
    $stmt->execute(['username' => $adminUsername, 'email' => $adminEmail]);

    if ($stmt->fetchColumn() == 0) {
      $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id, isVerified) VALUES (:username, :email, :password, 1, 1)");
      $stmt->execute([
        'username' => $adminUsername,
        'email' => $adminEmail,
        'password' => $adminPassword
      ]);
    }
  } catch (PDOException $e) {
    error_log("Error: <br>" . $e->getMessage());
  }
  $conn = null;
}

function createTestUser()
{
  $servername = "localhost";
  $username = "root";
  $password = "";
  try {
    $conn = new PDO("mysql:host=$servername;dbname=" . DATABASE_NAME, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $userUsername = 'user123';
    $userEmail = 'user123@gmail.com';
    $userPassword = password_hash('passworduser123', PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
    $stmt->execute(['username' => $userUsername, 'email' => $userEmail]);

    if ($stmt->fetchColumn() == 0) {
      $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id, isVerified, verification_code) VALUES (:username, :email, :password, 2, 1, 'aaa')");
      $stmt->execute([
        'username' => $userUsername,
        'email' => $userEmail,
        'password' => $userPassword
      ]);
    }
  } catch (PDOException $e) {
    error_log("Error: <br>" . $e->getMessage());
  }
  $conn = null;
}

createDatabase();
createTableUsers();
createTableEvents();
createTableRegistrations();
createTableRegistration_History();
createAdminUser();
createTestUser();
