<?php 
    $serverName = 'localhost';
    $databaseName = 'webpro_uts_lect';
    $username = 'root';
    $password = '';

    try {
        $pdo = new PDO("mysql:host=$serverName;dbname=$databaseName", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Koneksi gagal: " . $e->getMessage());
    }
?>