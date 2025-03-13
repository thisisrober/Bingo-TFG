<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'php/db.php';

$register_errors = [];
$login_errors = [];
$register_success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        $identificador = trim($_POST['identificador']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($identificador) || empty($email) || empty($password) || empty($confirm_password)) {
            $register_errors[] = "Todos los campos son obligatorios.";
        } elseif ($password !== $confirm_password) {
            $register_errors[] = "Las contraseñas no coinciden.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ? OR identificador = ?");
                $stmt->execute([$email, $identificador]);
                $user = $stmt->fetch();

                if ($user) {
                    $register_errors[] = "El correo electrónico o identificador ya están en uso.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $fecha_creacion = date('Y-m-d');
                    $stmt = $pdo->prepare("INSERT INTO usuario (identificador, email, contraseña, fecha_creacion, privacidad) VALUES (?, ?, ?, ?, 'publico')");
                    $stmt->execute([$identificador, $email, $hashed_password, $fecha_creacion]);
                    $userId = $pdo->lastInsertId();
                    header("Location: php/completar_perfil.php?user_id=$userId");
                    exit();
                }
            } catch (PDOException $e) {
                $register_errors[] = "Error en la base de datos: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['login'])) {
        $login = trim($_POST['email_or_identificador']);
        $password = $_POST['password'];

        try {
            $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ? OR identificador = ?");
            $stmt->execute([$login, $login]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['contraseña'])) {
                $_SESSION['user_id'] = $user['id_usuario'];
                header("Location: main/index.php");
                exit();
            } else {
                $login_errors[] = "Usuario o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            $login_errors[] = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - bingo!</title>
    <link rel="icon" href="src/img/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/auth.css">
</head>
<body>
    <div class="container" id="container">
        <div class="form-container sign-up">
            <?php if ($register_success): ?>
                <div class="alert alert-success">Registro exitoso. Ahora puede iniciar sesión.</div>
            <?php endif; ?>
            <form action="auth.php" method="POST">
                <h1>Crear cuenta</h1>
                <input type="text" name="identificador" placeholder="Usuario" value="<?= htmlspecialchars($_POST['identificador'] ?? '') ?>">
                <input type="email" name="email" placeholder="Correo" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <input type="password" name="password" placeholder="Contraseña">
                <input type="password" name="confirm_password" placeholder="Confirmar Contraseña">
                <?php if (!empty($register_errors)): ?>
                    <div class="alert alert-danger">
                        <?= implode('<br>', $register_errors) ?>
                    </div>
                <?php endif; ?>
                <button type="submit" name="register">REGISTER</button>
            </form>
        </div>
        <div class="form-container sign-in">
            <form action="auth.php" method="POST">
                <h1>Iniciar sesión</h1>
                <span>o utiliza tus datos</span>
                <input type="text" name="email_or_identificador" placeholder="Correo o Usuario" value="<?= htmlspecialchars($_POST['email_or_identificador'] ?? '') ?>">
                <input type="password" name="password" placeholder="Contraseña">
                <?php if (!empty($login_errors)): ?>
                    <div class="alert alert-danger">
                        <?= implode('<br>', $login_errors) ?>
                    </div>
                <?php endif; ?>
                <a href="#">¿Has olvidado tu contraseña?</a>
                <button type="submit" name="login">LOGIN</button>
            </form>
        </div>
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Iniciar sesión</h1>
                    <p>¿Ya tienes una cuenta?</p>
                    <button class="hidden" id="login">LOGIN</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Regístrate</h1>
                    <p>¿Todavía no te has registrado?</p>
                    <button class="hidden" id="register">REGISTER</button>
                </div>
            </div>
        </div>
    </div>

    <script src="js/auth.js"></script>
</body>
</html>
