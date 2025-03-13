<?php
require_once 'db.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth.php");
    exit();
}

$currentUserId = $_SESSION['user_id'];

if (isset($_GET['query'])) {
    $searchQuery = trim($_GET['query']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE (nombre LIKE ? OR identificador LIKE ?) AND id_usuario != ?");
        $searchTerm = '%' . $searchQuery . '%';
        $stmt->execute([$searchTerm, $searchTerm, $currentUserId]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Devolver los resultados en formato JSON
        echo json_encode($resultados);
    } catch (PDOException $e) {
        // Enviar un error
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode([]);
}
?>
