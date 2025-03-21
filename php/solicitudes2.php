<?php
require_once 'db.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth.php");
    exit();
}

$userId = $_SESSION['user_id'];

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action']) && isset($data['request_id'])) {
        $action = $data['action'];
        $requestId = $data['request_id'];
        
        if ($action === 'accept') {
            try {
                // Aceptar solicitud de amistad
                $stmt = $pdo->prepare("UPDATE solicitudes_amistad SET estado = 'aceptada' WHERE solicitud_id = ?");
                if ($stmt->execute([$requestId])) {
                    // Obtener IDs de los usuarios
                    $stmt = $pdo->prepare("SELECT usuario_emisor_id, usuario_receptor_id FROM solicitudes_amistad WHERE solicitud_id = ?");
                    $stmt->execute([$requestId]);
                    $request = $stmt->fetch();

                    if ($request && isset($request['usuario_emisor_id']) && isset($request['usuario_receptor_id'])) {
                        // Insertar en la tabla amigos
                        $stmt = $pdo->prepare("INSERT INTO amigos (usuario1_id, usuario2_id) VALUES (?, ?)");
                        if ($stmt->execute([$request['usuario_emisor_id'], $request['usuario_receptor_id']])) {
                            $response['success'] = true;
                        } else {
                            error_log('Error: No se pudo insertar en la tabla amigos.');
                        }
                    } else {
                        error_log('Error: No se encontraron los IDs de los usuarios en la solicitud.');
                    }
                } else {
                    error_log('Error: No se pudo actualizar el estado de la solicitud de amistad.');
                }
            } catch (PDOException $e) {
                error_log('Error en la base de datos: ' . $e->getMessage());
            }
        } elseif ($action === 'reject') {
            // Rechazar solicitud de amistad
            $stmt = $pdo->prepare("UPDATE solicitudes_amistad SET estado = 'rechazada' WHERE solicitud_id = ?");
            $stmt->execute([$requestId]);
            $response['success'] = true;
        }
    } else {
        error_log('Error: Datos de solicitud incompletos.');
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>
