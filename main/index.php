<?php
session_start();
require_once '../php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth.php");
    exit();
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM usuario WHERE id_usuario = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$friendRequests = [];
try {
    $friendRequestsStmt = $pdo->prepare("
    SELECT u.*, s.solicitud_id FROM usuario u
    JOIN solicitudes_amistad s ON u.id_usuario = s.usuario_emisor_id
    WHERE s.usuario_receptor_id = ? AND s.estado = 'pendiente'
    ");
    $friendRequestsStmt->execute([$userId]);
    $friendRequests = $friendRequestsStmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
}

$searchResults = [];
if (isset($_GET['search_query']) && !empty($_GET['search_query'])) {
    $searchQuery = trim($_GET['search_query']);
    try {
        $searchStmt = $pdo->prepare("SELECT * FROM usuario WHERE nombre LIKE ? OR identificador LIKE ?");
        $searchTerm = '%' . $searchQuery . '%';
        $searchStmt->execute([$searchTerm, $searchTerm]);
        $searchResults = $searchStmt->fetchAll();
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}

$feedPosts = [];
try {
    $feedStmt = $pdo->prepare("
        SELECT p.*, u.nombre, u.foto_perfil 
        FROM publicaciones p
        JOIN usuario u ON p.user_id = u.id_usuario
        ORDER BY p.fecha_creacion DESC
    ");
    $feedStmt->execute();
    $feedPosts = $feedStmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../src/img/logo-black.png" type="image/x-icon">
    <title>Inicio - bingo!</title>
    <link rel="stylesheet" href="../styles/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <input type="hidden" id="userPrincipalId" value="<?= $_SESSION['user_id'] ?>">
    <header>
        <div class="header-container">
            <div class="header-wrapper">
                <a href="index.php"><div class="logoBox">
                    <img src="../src/img/logo-black.png" alt="logo">
                </div></a>
                <div class="searchBox">
                    <input type="search" id="searchInput" placeholder="Buscar...">
                    <i class="fas fa-search"></i>
                </div>

                <div id="profileModal" class="modal">
                    <div class="modal-content">
                        <span class="close-button">&times;</span>
                        <div id="profileDetails"></div>
                    </div>
                </div>

                <div class="iconBox1">
                </div>
                <div class="iconBox2">
                    <i class="bi bi-bell"></i>
                    <i class="bi bi-gear"></i>
                    <label id="profileIcon" onclick="openProfileEditModal()">
                        <img src="<?= htmlspecialchars($user['foto_perfil'] ?: 'http://app.bingo.es/src/img/default-avatar.png') ?>" alt="user">
                    </label>
                    <div style="display: flex; justify-content: center; align-items: center; cursor: pointer;" onclick="window.location.href='../php/logout.php'">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div id="searchResults" class="search-results-box"></div>
    <div id="profileDropdown" class="profile-dropdown">
        <button id="logoutButton" class="logout-button">Cerrar sesión</button>
    </div>

    <div class="home">
        <div class="container">
            <div class="home-weapper">
                <div class="home-left">
                    <div class="profile">
                        <img src="<?= htmlspecialchars($user['foto_perfil'] ?: 'http://app.bingo.es/src/img/default-avatar.png') ?>" alt="user">
                        <h3><?= htmlspecialchars($user['nombre'] ?? 'Nombre') ?> <?= htmlspecialchars($user['apellidos'] ?? 'Apellidos') ?></h3>&nbsp;
                        <h4 style="font-size: 12px; color: #878787;">@<?= htmlspecialchars($user['identificador'] ?? 'usuario') ?></h4>
                    </div>
                    <div class="games">
                        <h4 class="mini-headign">Juegos</h4>
                        <label>
                            <img src="images/game.png" alt="game01">
                            <span>Serpiente</span>
                        </label>
                    </div>
                    <div class="explore">
                        <h4 class="mini-headign">Explorar</h4>
                        <a href="#"><i class="fa-solid fa-user-group"></i> Grupos</a>
                        <a href="#"><i class="fa-solid fa-star"></i> Favoritos</a>
                        <a href="#"><i class="fa-solid fa-bookmark"></i> Guardados</a>
                        <a href="#"><i class="fa-solid fa-clock"></i> Eventos</a>
                        <a href="#"><i class="fa-solid fa-flag"></i> Páginas</a>
                        <button class="see-more-btn" style="width: 100%;">Ver más <i class="fa-solid fa-angle-down"></i></button>
                    </div>
                </div>

                <div class="home-center">
                    <div class="home-center-wrapper">

                        <div id="createPost" class="createPost">
                            <h3 class="mini-heading">CREAR PUBLICACIÓN <span id="charCount" class="contador-caracteres" style="font-size: 12px; color: grey;">200/200</span></h3>
                            <form id="postForm" action="../php/crear-publicacion.php" method="POST" enctype="multipart/form-data">
                                <div class="post-text">
                                    <img src="<?= htmlspecialchars($user['foto_perfil'] ?: 'http://app.bingo.es/src/img/default-avatar.png') ?>" alt="user">
                                    <input type="text" id="postInput" name="contenido" placeholder="¿En qué estás pensando en este momento?" maxlength="200">
                                </div>
                                <div class="post-icon">
                                    <label for="galleryInput" style="background: #ffebed; border-radius: 5px; padding: 10px; display: inline-block; cursor: pointer;">
                                        <i style="background: #ff4154; border-radius: 50%; padding: 5px;" class="fa-solid fa-camera"></i>
                                        Galería
                                        <input type="file" id="galleryInput" name="media" accept="image/*" style="display: none;">
                                    </label>&nbsp;&nbsp;
                                    <label for="videoInput" style="background: #ccdcff; border-radius: 5px; padding: 10px; display: inline-block; cursor: pointer;">
                                        <i style="background: #0053ff; border-radius: 50%; padding: 5px;" class="fa-solid fa-video"></i>
                                        Videos
                                        <input type="file" id="videoInput" name="media" accept="video/*" style="display: none;">
                                    </label>
                                    <button type="submit" id="submitPost" class="iconBox2" style="float: right !important;"><i class="bi bi-send" style="top: 0; right: 0;"></i></button>
                                </div>
                            </form>
                        </div>

                        <div id="feedContainer" class="fb-post1">
                            <div class="fb-post1-container">
                                <div class="fb-post1-header">
                                    <ul>
                                        <li class="active">PARA TI</li>
                                        <li>AMIGOS</li>
                                    </ul>
                                </div>
                                <?php include '../php/feed.php'; ?>
                            </div>
                        </div><br><br>

                    </div>
                </div>

                <div class="home-right">
                    <div class="friend-request">
                        <div class="friend">
                            <h3 class="heading">SOLICITUD DE AMISTAD <span>ver todas</span></h3>
                                <?php if (count($friendRequests) > 0): ?>
                                    <?php foreach ($friendRequests as $request): ?>
                                        <ul id="friend-request-<?= htmlspecialchars($request['id_usuario']) ?>">
                                            <li><img src="<?= htmlspecialchars(filter_var($request['foto_perfil'], FILTER_VALIDATE_URL) ? $request['foto_perfil'] : '../uploads/' . $request['foto_perfil'], ENT_QUOTES, 'UTF-8') ?>" alt="user"></li>
                                            <li>
                                                <b><?= htmlspecialchars($request['nombre']) ?></b>
                                                <p>@<?= htmlspecialchars($request['identificador']) ?></p>
                                                <button class="friend-confirm" onclick="handleFriendRequest('accept', <?= htmlspecialchars($request['solicitud_id']) ?>)">ACEPTAR</button>
                                                <button class="friend-remove" onclick="handleFriendRequest('reject', <?= htmlspecialchars($request['solicitud_id']) ?>)">ELIMINAR</button>
                                            </li>
                                        </ul>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No tienes solicitudes de amistad pendientes.</p>
                                <?php endif; ?>
                        </div>
                    </div>

                    <div class="messenger">
                        <div class="messenger-search">
                            <i class="fa-solid fa-user-group"></i>&nbsp;
                            <h4>DIRECTOS</h4>&nbsp;&nbsp;
                            <input type="search" placeholder="Buscar">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <ul>
                        <?php
                        $amigos = [];
                        try {
                            $amigosStmt = $pdo->prepare("
                                SELECT u.* FROM usuario u
                                JOIN solicitudes_amistad s ON (u.id_usuario = s.usuario_emisor_id OR u.id_usuario = s.usuario_receptor_id)
                                WHERE (s.usuario_emisor_id = ? OR s.usuario_receptor_id = ?) AND s.estado = 'aceptada' AND u.id_usuario != ?
                            ");
                            $amigosStmt->execute([$userId, $userId, $userId]);
                            $amigos = $amigosStmt->fetchAll();
                        } catch (PDOException $e) {
                            error_log($e->getMessage());
                        }
                        ?>
                        <?php foreach ($amigos as $amigo): ?>
                            <li data-user-id="<?= htmlspecialchars($amigo['id_usuario']) ?>">
                                <img src="<?= htmlspecialchars($amigo['foto_perfil'] ?: 'http://app.bingo.es/src/img/default-avatar.png') ?>" alt="user">
                                <b><?= htmlspecialchars($amigo['nombre']) ?> </b>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="chat-container" class="chat-container"></div>

    <script src="../js/publicaciones.js"></script>
    <script src="../js/buscador.js"></script>
    <script src="../js/likes_comentarios.js"></script>
    <script src="../js/chat.js"></script>
    <script src="../js/perfil.js"></script>

    <script>
        document.querySelector('.close-button').addEventListener('click', function() {
            document.getElementById('profileModal').style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('profileModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    </script>
    <script>
        const postInput = document.getElementById('postInput');
        const charCount = document.getElementById('charCount');
        const maxChars = 200;

        function updateCharCount() {
            const currentLength = postInput.value.length;
            const remainingChars = maxChars - currentLength;

            charCount.textContent = `${remainingChars}/${maxChars}`;

            if (remainingChars <= 15) {
                charCount.style.color = 'red';
            } else if (remainingChars <= 30) {
                charCount.style.color = 'orange';
            } else {
                charCount.style.color = 'grey';
            }
        }

        postInput.addEventListener('input', updateCharCount);

        updateCharCount();
    </script>
    <!-- Script para manejar en tiempo real (aproximadamente 1 segundo) la actualización de las solicitudes de amistad en el apartado "Solicitudes de amistad" -->
    <script>
        function handleFriendRequest(action, requestId) {
            fetch('../php/solicitudes2.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action, request_id: requestId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    fetchFriendRequests(); // Actualizar la lista de solicitudes de amistad
                    fetchDirectFriends(); // Actualizar la lista de amigos directos
                } else {
                    console.error('Error al procesar la solicitud de amistad:', data.error);
                    alert('Error al procesar la solicitud de amistad.');
                }
            })
            .catch(error => {
                console.error('Error al procesar la solicitud de amistad:', error);
            });
        }

        function fetchFriendRequests() {
            fetch('../php/getFriendRequests.php')
            .then(response => response.json())
            .then(data => {
                const friendRequestContainer = document.querySelector('.friend-request .friend');
                friendRequestContainer.innerHTML = `
                <h3 class="heading">SOLICITUD DE AMISTAD <span>ver todas</span></h3>
                `;
                if (data.length > 0) {
                data.forEach(request => {
                    const requestItem = document.createElement('ul');
                    requestItem.id = `friend-request-${request.id_usuario}`;
                    requestItem.innerHTML = `
                    <li><img src="${request.foto_perfil ? request.foto_perfil : '../src/img/default-avatar.png'}" alt="user"></li>
                    <li>
                        <b>${request.nombre}</b>
                        <p>@${request.identificador}</p>
                        <button class="friend-confirm" onclick="handleFriendRequest('accept', ${request.solicitud_id})">ACEPTAR</button>
                        <button class="friend-remove" onclick="handleFriendRequest('reject', ${request.solicitud_id})">ELIMINAR</button>
                    </li>
                    `;
                    friendRequestContainer.appendChild(requestItem);
                });
                } else {
                friendRequestContainer.innerHTML += '<p>No tienes solicitudes de amistad pendientes.</p>';
                }
            })
            .catch(error => {
                console.error('Error al obtener las solicitudes de amistad:', error);
            });
        }

        function fetchDirectFriends() {
            fetch('../php/getDirectFriends.php')
                .then(response => response.json())
                .then(data => {
                    const directFriendsContainer = document.querySelector('.messenger ul');
                    directFriendsContainer.innerHTML = '';
                    data.forEach(friend => {
                        const friendItem = document.createElement('li');
                        friendItem.setAttribute('data-user-id', friend.id_usuario);
                        friendItem.innerHTML = `
                            <img src="${friend.foto_perfil ? friend.foto_perfil : '../src/img/default-avatar.png'}" alt="user">
                            <b>${friend.nombre}</b>
                        `;
                        directFriendsContainer.appendChild(friendItem);
                    });
                })
                .catch(error => {
                    console.error('Error al obtener los amigos directos:', error);
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            fetchFriendRequests();
            fetchDirectFriends();
            setInterval(fetchFriendRequests, 1000); // Comprobar nuevas solicitudes cada segundo
            setInterval(fetchDirectFriends, 1000); // Comprobar nuevos amigos cada segundo
        });
    </script>

</body>
</html>
