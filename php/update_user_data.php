<?php
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $nombre = htmlspecialchars(trim($_POST['nombre'] ?? null));
    $apellidos = htmlspecialchars(trim($_POST['apellidos'] ?? null));
    $biografia = htmlspecialchars(trim($_POST['biografia'] ?? null));
    $fotoPerfil = null;

    // Manejar la subida del archivo de imagen
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['foto_perfil']['tmp_name'];
        $fileName = $_FILES['foto_perfil']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Extensiones permitidas
        $allowedfileExtensions = ['jpg', 'jpeg', 'png'];

        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Definir la ruta de destino donde guardar la imagen en el servidor local
            $uploadFileDir = '../uploads/';
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension; // Crear un nombre único para evitar conflictos
            $dest_path = $uploadFileDir . $newFileName;

            // Crear el directorio si no existe
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            // Mover el archivo subido a la carpeta de destino
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $fotoPerfil = '../uploads/' . $newFileName; // Guardar la ruta relativa para la base de datos
            } else {
                echo json_encode(['success' => false, 'message' => "Hubo un error al mover el archivo de imagen."]);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => "Solo se permiten archivos con extensión JPG, JPEG, o PNG."]);
            exit;
        }
    }

    if ($userId && $nombre && $apellidos) {
        try {
            // Si se subió una nueva foto de perfil, actualizar también la ruta en la base de datos
            if ($fotoPerfil) {
                $stmt = $pdo->prepare("UPDATE usuario SET nombre = ?, apellidos = ?, biografia = ?, foto_perfil = ? WHERE id_usuario = ?");
                $stmt->execute([$nombre, $apellidos, $biografia, $fotoPerfil, $userId]);
            } else {
                $stmt = $pdo->prepare("UPDATE usuario SET nombre = ?, apellidos = ?, biografia = ? WHERE id_usuario = ?");
                $stmt->execute([$nombre, $apellidos, $biografia, $userId]);
            }

            echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>
