<?php
require_once 'db.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de usuario no proporcionado']);
    exit();
}

$userId = $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM publicaciones WHERE user_id = ? ORDER BY fecha_creacion DESC");
    $stmt->execute([$userId]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($posts);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener las publicaciones del usuario']);
}
?>
