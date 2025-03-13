<?php
require 'db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado.']);
    exit();
}

$postId = $_GET['post_id'];
$offset = (int)$_GET['offset'];

try {
    $stmt = $pdo->prepare("SELECT c.*, u.nombre, u.foto_perfil FROM comentario c JOIN usuario u ON c.id_usuario = u.id_usuario WHERE c.publicacion_id = ? LIMIT 2 OFFSET ?");
    $stmt->bindParam(1, $postId, PDO::PARAM_INT);
    $stmt->bindParam(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $comentarios = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comentario WHERE publicacion_id = ?");
    $stmt->execute([$postId]);
    $totalComentarios = $stmt->fetchColumn();

    $hasMore = ($offset + 2) < $totalComentarios;

    echo json_encode(['success' => true, 'comments' => $comentarios, 'hasMore' => $hasMore]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error al cargar los comentarios.']);
}
?>
