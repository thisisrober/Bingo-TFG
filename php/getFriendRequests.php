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
        SELECT u.*, s.solicitud_id FROM usuario u
        JOIN solicitudes_amistad s ON u.id_usuario = s.usuario_emisor_id
        WHERE s.usuario_receptor_id = ? AND s.estado = 'pendiente'
    ");
    $stmt->execute([$userId]);
    $friendRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($friendRequests);
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode([]);
}
?>
