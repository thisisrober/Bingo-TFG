<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit();
}
$myId = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$friendId = isset($data['friendId']) ? intval($data['friendId']) : 0;
$message = isset($data['message']) ? trim($data['message']) : '';

if (!$friendId || !$message) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit();
}
$stmt = $pdo->prepare("INSERT INTO mensajes (usuario_emisor_id, usuario_receptor_id, mensaje) VALUES (?, ?, ?)");
$result = $stmt->execute([$myId, $friendId, $message]);
echo json_encode(['success' => $result]);
?>
