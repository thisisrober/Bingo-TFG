<?php
    $host = 'localhost';
    $dbname = 'bingo_db';
    $username = 'bingo-adm';
    $password = 'C0ntr4$3ñ4';

    try {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4"; // Se agrega charset=utf8mb4
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $pdo = new PDO($dsn, $username, $password, $options);
    } catch (PDOException $e) {
        die("Error en la conexión: " . $e->getMessage());
    }
?>
