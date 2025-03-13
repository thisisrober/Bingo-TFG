<?php
require_once 'db.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth.php");
    exit();
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT u.* FROM usuario u
        JOIN amigos a ON (u.id_usuario = a.usuario1_id OR u.id_usuario = a.usuario2_id)
        WHERE (a.usuario1_id = ? OR a.usuario2_id = ?) AND u.id_usuario != ?
    ");
    $stmt->execute([$userId, $userId, $userId]);
    $directFriends = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($directFriends);
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode([]);
}
?>
