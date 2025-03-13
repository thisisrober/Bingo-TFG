<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}
$myId = $_SESSION['user_id'];
$friendId = isset($_GET['friendId']) ? intval($_GET['friendId']) : 0;
if (!$friendId) {
    echo json_encode([]);
    exit();
}
$stmt = $pdo->prepare("SELECT * FROM mensajes 
    WHERE (usuario_emisor_id = ? AND usuario_receptor_id = ?)
       OR (usuario_emisor_id = ? AND usuario_receptor_id = ?)
    ORDER BY fecha_envio ASC");
$stmt->execute([$myId, $friendId, $friendId, $myId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($messages);
?>
