<?php
require 'db.php';
session_start();

header('Content-Type: application/json');
ob_start();

if (!isset($_SESSION['user_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data) || !isset($data['post_id']) || !isset($data['comentario']) || !is_numeric($data['post_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Datos inválidos o campos faltantes.']);
    exit();
}

$postId = $data['post_id'];
$comentario = trim($data['comentario']);
if ($comentario === '') {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'El comentario no puede estar vacío.']);
    exit();
}
$comentario = substr($comentario, 0, 100);
$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("INSERT INTO comentario (texto, fecha_comentario, id_usuario, publicacion_id) VALUES (?, CURDATE(), ?, ?)");
    $stmt->execute([$comentario, $userId, $postId]);

    $userStmt = $pdo->prepare("SELECT nombre, foto_perfil FROM usuario WHERE id_usuario = ?");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    ob_clean();
    echo json_encode(['success' => true, 'user' => $user]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Error al manejar el comentario.']);
}
exit();
?>
