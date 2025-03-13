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
if (!is_array($data) || !isset($data['post_id']) || !is_numeric($data['post_id'])) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Datos inválidos o post_id no enviado.']);
    exit();
}

$postId = $data['post_id'];
$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM reaccion WHERE publicacion_id = ? AND id_usuario = ?");
    $stmt->execute([$postId, $userId]);
    $reaccion = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reaccion) {
        $stmt = $pdo->prepare("DELETE FROM reaccion WHERE id_reaccion = ?");
        $stmt->execute([$reaccion['id_reaccion']]);
        $userLiked = false;
    } else {
        $stmt = $pdo->prepare("INSERT INTO reaccion (fecha_reaccion, id_usuario, publicacion_id) VALUES (CURDATE(), ?, ?)");
        $stmt->execute([$userId, $postId]);
        $userLiked = true;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) AS likes FROM reaccion WHERE publicacion_id = ?");
    $stmt->execute([$postId]);
    $likes = $stmt->fetchColumn();

    ob_clean();
    echo json_encode(['success' => true, 'likes' => $likes, 'userLiked' => $userLiked]);
    exit();
} catch (PDOException $e) {
    error_log($e->getMessage());
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Error al manejar el like.']);
    exit();
}
?>
