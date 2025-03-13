<?php
require_once 'db.php';

header('Content-Type: application/json');

if (isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];

    try {
        $stmt = $pdo->prepare("SELECT nombre, apellidos, biografia, foto_perfil FROM usuario WHERE id_usuario = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode($user);
        } else {
            echo json_encode(['error' => 'Usuario no encontrado.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al obtener los datos del usuario: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'ID de usuario no proporcionado.']);
}
?>
