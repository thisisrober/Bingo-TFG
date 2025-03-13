<?php
require_once 'db.php';

// Verificar que el usuario está accediendo a la página con un user_id
if (!isset($_GET['user_id'])) {
    echo "Acceso no permitido.";
    exit();
}

$userId = $_GET['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
                echo "Hubo un error al mover el archivo de imagen.";
            }
        } else {
            echo "Solo se permiten archivos con extensión JPG, JPEG, o PNG.";
        }
    } else {
        // Si no se sube ninguna imagen, usar la imagen por defecto
        $fotoPerfil = '../src/img/default-avatar.png';
    }

    // Actualizar los datos del usuario en la base de datos
    try {
        $stmt = $pdo->prepare("UPDATE usuario SET nombre = ?, apellidos = ?, biografia = ?, foto_perfil = ? WHERE id_usuario = ?");
        $stmt->execute([$nombre, $apellidos, $biografia, $fotoPerfil, $userId]);

        // Redirigir al usuario a la página principal después de completar el perfil
        header("Location: ../main/index.php");
        exit();
    } catch (PDOException $e) {
        echo "Error al actualizar el perfil: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar Perfil - Bingo!</title>
    <link rel="icon" href="../src/img/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/auth.css">
    <style>
        .form-container.complete-profile {
            position: relative;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            padding: 30px;
        }

        .profile-pic-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .profile-pic img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .form-container.complete-profile textarea {
            background-color: #eee;
            border: none;
            margin: 8px 0;
            padding: 10px 15px;
            font-size: 13px;
            border-radius: 8px;
            width: 100%;
            outline: none;
            resize: none;
        }

        #cropperModal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
        }

        .image-container {
            width: 100%;
            height: 400px;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #imageToCrop {
            max-width: 100%;
            max-height: 100%;
        }

        .close-button {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-button:hover {
            color: red;
        }

    </style>
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function () {
                const output = document.getElementById('profilePicPreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</head>
<body>
    <div class="container" id="container">
        <div class="form-container complete-profile">
            <form action="completar_perfil.php?user_id=<?= htmlspecialchars($userId) ?>" method="POST" enctype="multipart/form-data">
                <h1>¡Bienvenido a <b>bingo</b>!</h1>
                <span>Agrega más información para completar tu perfil</span>
                
                <div class="profile-pic-container">
                    <div class="profile-pic">
                        <img id="profilePicPreview" src="../src/img/default-avatar.png">
                    </div>
                    <input type="file" name="foto_perfil" accept="image/*" onchange="showCropper(event)" style="margin-top: 10px;">
                </div>
                <input type="text" name="nombre" placeholder="Nombre">
                <input type="text" name="apellidos" placeholder="Apellidos">
                <textarea name="biografia" placeholder="Biografía (máximo 200 caracteres)" maxlength="200"></textarea>
                <button type="submit">COMPLETAR PERFIL</button>
            </form>
        </div>
        <div id="cropperModal" class="modal">
            <div class="modal-content">
                <span class="close-button" onclick="closeCropperModal()">&times;</span>
                <h2>RECORTA TU FOTO DE PERFIL</h2>
                <div class="image-container">
                    <img id="imageToCrop" src="">
                </div>
                <button id="cropImageButton">GUARDAR</button>
            </div>
        </div>
    </div>
    <script>
        let cropper;
        let croppedImageBlob = null; // Variable global para almacenar el blob de la imagen recortada

        function showCropper(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const imageToCrop = document.getElementById('imageToCrop');
                    imageToCrop.src = e.target.result;
                    document.getElementById('cropperModal').style.display = 'block';

                    if (cropper) {
                        cropper.destroy();
                    }
                    cropper = new Cropper(imageToCrop, {
                        aspectRatio: 1,
                        viewMode: 1,
                    });
                };
                reader.readAsDataURL(file);
            }
        }

        function closeCropperModal() {
            document.getElementById('cropperModal').style.display = 'none';
            if (cropper) {
                cropper.destroy();
            }
        }

        document.getElementById('cropImageButton').addEventListener('click', function () {
            if (cropper) {
                const canvas = cropper.getCroppedCanvas({
                    width: 200,
                    height: 200,
                });
                const profilePicPreview = document.getElementById('profilePicPreview');
                profilePicPreview.src = canvas.toDataURL();

                canvas.toBlob((blob) => {
                    croppedImageBlob = blob; // Almacenar el blob en la variable global
                    closeCropperModal();
                });
            }
        });

        document.querySelector('form').addEventListener('submit', function (event) {
            event.preventDefault(); // Evitar el envío normal del formulario

            const formData = new FormData(this); // 'this' hace referencia al formulario
            if (croppedImageBlob) {
                formData.set('foto_perfil', croppedImageBlob, 'croppedImage.png');
            }

            // Enviar el formulario mediante AJAX
            fetch('completar_perfil.php?user_id=<?= htmlspecialchars($userId) ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    window.location.href = "../main/index.php";
                } else {
                    console.error('Error al actualizar el perfil');
                }
            })
            .catch(error => {
                console.error('Error de red:', error);
            });
        });
    </script>

</body>
</html>