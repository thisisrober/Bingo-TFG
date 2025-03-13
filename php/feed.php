<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth.php");
    exit();
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT p.*, u.nombre, u.foto_perfil, 
        (SELECT COUNT(*) FROM reaccion WHERE publicacion_id = p.id) AS likes,
        (SELECT COUNT(*) FROM comentario WHERE publicacion_id = p.id) AS comentarios,
        (SELECT COUNT(*) FROM reaccion WHERE publicacion_id = p.id AND id_usuario = ?) AS user_liked
        FROM publicaciones p
        JOIN usuario u ON p.user_id = u.id_usuario
        ORDER BY p.fecha_creacion DESC
    ");
    $stmt->execute([$userId]);
    $feedPosts = $stmt->fetchAll();

    if (!empty($feedPosts)) {
        foreach ($feedPosts as $post) {
            $fechaCreacion = date('d/m/Y H:i', strtotime($post['fecha_creacion']));
            $userLiked = $post['user_liked'] > 0;
            ?>
            <div class="fb-p1-main">
                <div class="post-title">
                    <img src="<?= htmlspecialchars($post['foto_perfil'] ?: 'images/default-avatar.png') ?>" alt="user picture" class="profile-pic">
                    <div class="post-info">
                        <h3>
                            <?= htmlspecialchars($post['nombre']) ?>
                        </h3>
                        <span><?= $fechaCreacion ?></span>
                    </div>
                </div>
                <p><?= nl2br(htmlspecialchars($post['contenido'])) ?></p>

                <?php if (!empty($post['media_url'])): ?>
                    <div class="post-images">
                        <div class="post-images1">
                            <?php if ($post['tipo_media'] === 'image'): ?>
                                <img src="<?= htmlspecialchars($post['media_url']) ?>" alt="post image">
                            <?php elseif ($post['tipo_media'] === 'video'): ?>
                                <video controls>
                                    <source src="<?= htmlspecialchars($post['media_url']) ?>" type="video/mp4">
                                    Tu navegador no soporta reproducción de vídeo.
                                </video>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="like-comment">
                    <ul>
                        <li>
                            <img src="images/like.svg" alt="like" onclick="handleLike(<?= $post['id'] ?>)" style="width: 24px; height: 24px; cursor: pointer;">
                            <span id="like-count-<?= $post['id'] ?>" style="color: <?= $userLiked ? 'blue' : 'inherit' ?>;"><?= $post['likes'] ?></span>
                        </li>
                        <li>
                            <img src="images/comment.svg" alt="comment" onclick="toggleComments(<?= $post['id'] ?>)" style="width: 36px; height: 36px; cursor: pointer;">
                            <span id="comment-count-<?= $post['id'] ?>"><?= $post['comentarios'] ?></span>
                        </li>
                    </ul>
                </div>
                <div class="comment-section" id="comment-section-<?= $post['id'] ?>" style="display: none;">
                    <form onsubmit="handleComment(event, <?= $post['id'] ?>)">
                        <input type="text" name="comentario" placeholder="Escribe un comentario...">
                        <button type="submit">Comentar</button>
                    </form>
                    <div class="comments-list" id="comments-<?= $post['id'] ?>">
                        <?php
                        $comentariosStmt = $pdo->prepare("SELECT c.*, u.nombre, u.foto_perfil FROM comentario c JOIN usuario u ON c.id_usuario = u.id_usuario WHERE c.publicacion_id = ? LIMIT 2");
                        $comentariosStmt->execute([$post['id']]);
                        $comentarios = $comentariosStmt->fetchAll();
                        foreach ($comentarios as $comentario) {
                            $fechaComentario = date('d/m/Y H:i', strtotime($comentario['fecha_comentario']));
                            ?>
                            <div class="comment">
                                <img src="images/replies.png" style="width: 5%">
                                <img src="<?= htmlspecialchars($comentario['foto_perfil'] ?: 'images/default-avatar.png') ?>" alt="user picture" class="profile-pic">
                                <div class="comment-info">
                                    <h4><?= htmlspecialchars($comentario['nombre']) ?> <span style="font-size: 12px; color: #878787;"><?= $fechaComentario ?></span></h4>
                                    <p><?= nl2br(htmlspecialchars($comentario['texto'])) ?></p>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php if ($post['comentarios'] > 2): ?>
                        <button class="see-more-btn" onclick="loadMoreComments(<?= $post['id'] ?>, 2)">Ver más</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    } else {
        echo "<br><p>No hay publicaciones para mostrar.</p>";
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo "<p>Error al cargar las publicaciones: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>