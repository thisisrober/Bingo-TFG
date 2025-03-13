<?php
require_once 'db.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth.php");
    exit();
}

$userId = $_SESSION['user_id'];
$friendId = $_GET['id'];

$response = ['status' => 'none'];

try {
    $stmt = $pdo->prepare("SELECT estado FROM solicitudes_amistad WHERE (usuario_emisor_id = ? AND usuario_receptor_id = ?) OR (usuario_emisor_id = ? AND usuario_receptor_id = ?)");
    $stmt->execute([$userId, $friendId, $friendId, $userId]);
    $request = $stmt->fetch();

    if ($request) {
        $response['status'] = $request['estado'];
    } else {
        $stmt = $pdo->prepare("SELECT * FROM amigos WHERE (usuario1_id = ? AND usuario2_id = ?) OR (usuario1_id = ? AND usuario2_id = ?)");
        $stmt->execute([$userId, $friendId, $friendId, $userId]);
        $friendship = $stmt->fetch();

        if ($friendship) {
            $response['status'] = 'amigos';
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
