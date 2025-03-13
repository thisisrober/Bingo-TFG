<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $contenido = trim($_POST['contenido']);
    $media_url = null;
    $tipo_media = null; // Permitir que sea NULL
    
    if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
        $file = $_FILES['media'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        $fileType = $file['type'];

        $fileExt = explode('.', $fileName);
        $fileActualExt = strtolower(end($fileExt));

        $allowedImages = array('jpg', 'jpeg', 'png', 'gif');
        $allowedVideos = array('mp4', 'mov', 'avi');

        if (in_array($fileActualExt, $allowedImages)) {
            $tipo_media = 'image';
        } elseif (in_array($fileActualExt, $allowedVideos)) {
            $tipo_media = 'video';
        } else {
            echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido.']);
            exit();
        }

        if ($fileSize < 5000000) { 
            $fileNameNew = uniqid('', true) . "." . $fileActualExt;
            $fileDestination = '../uploads/' . $fileNameNew;
            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                $media_url = 'uploads/' . $fileNameNew; // Guardar la ruta relativa
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al mover el archivo subido.']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Tu archivo es demasiado grande.']);
            exit();
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO publicaciones (user_id, contenido, media_url, tipo_media, fecha_creacion) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $contenido, $media_url, $tipo_media]);
        header('Location: ../main');
        exit();
        // No mostrar el resultado JSON
        // echo json_encode(['success' => true, 'post_id' => $pdo->lastInsertId()]);
        // exit();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
        exit();
    }
}
?>