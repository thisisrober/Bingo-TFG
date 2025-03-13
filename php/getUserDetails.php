<?php
require_once 'db.php';

header('Content-Type: application/json');

$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($userId > 0) {
    try {
        $stmt = $pdo->prepare("SELECT nombre, apellidos, identificador, foto_perfil, biografia FROM usuario WHERE id_usuario = ?");
        $stmt->execute([$userId]);
        $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userDetails) {
            echo json_encode($userDetails);
        } else {
            echo json_encode([]);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode([]);
}
?>
