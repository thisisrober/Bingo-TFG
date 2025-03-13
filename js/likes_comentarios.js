function handleLike(postId) {
    fetch('../php/like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ post_id: postId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar el conteo de likes
            const likeCount = document.getElementById(`like-count-${postId}`);
            likeCount.textContent = data.likes;
            likeCount.style.color = data.userLiked ? 'blue' : 'inherit';
        } else {
            console.error('Error al dar like:', data.error);
        }
    })
    .catch(error => {
        console.error('Error al dar like:', error);
    });
}

function handleComment(event, postId) {
    event.preventDefault();
    const form = event.target;
    const comentario = form.comentario.value;

    fetch('../php/comentar.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ post_id: postId, comentario })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Agregar el nuevo comentario a la lista
            const commentsList = document.getElementById(`comments-${postId}`);
            const newComment = document.createElement('div');
            newComment.classList.add('comment');
            newComment.innerHTML = `
                <img src="images/replies.png" style="width: 5%">
                <img src="${data.user.foto_perfil ? data.user.foto_perfil : 'images/default-avatar.png'}" alt="user picture" class="profile-pic">
                <div class="comment-info">
                    <h4>${data.user.nombre} <span style="font-size: 12px; color: #878787;">${new Date().toLocaleString()}</span></h4>
                    <p>${comentario}</p>
                </div>
            `;
            commentsList.insertBefore(newComment, commentsList.firstChild);
            form.comentario.value = '';

            // Actualizar el conteo de comentarios
            const commentCount = document.getElementById(`comment-count-${postId}`);
            commentCount.textContent = parseInt(commentCount.textContent) + 1;
        } else {
            console.error('Error al comentar:', data.error);
        }
    })
    .catch(error => {
        console.error('Error al comentar:', error);
    });
}

function loadMoreComments(postId, offset) {
    fetch(`../php/getComments.php?post_id=${postId}&offset=${offset}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const commentsList = document.getElementById(`comments-${postId}`);
            data.comments.forEach(comment => {
                const commentElement = document.createElement('div');
                commentElement.classList.add('comment');
                commentElement.innerHTML = `
                    <img src="images/replies.png" style="width: 5%">
                    <img src="${comment.foto_perfil ? comment.foto_perfil : 'images/default-avatar.png'}" alt="user picture" class="profile-pic">
                    <div class="comment-info">
                        <h4>${comment.nombre} <span style="font-size: 12px; color: #878787;">${new Date(comment.fecha_comentario).toLocaleString()}</span></h4>
                        <p>${comment.texto}</p>
                    </div>
                `;
                commentsList.appendChild(commentElement);
            });
            if (data.hasMore) {
                const seeMoreBtn = document.querySelector(`.see-more-btn[onclick="loadMoreComments(${postId}, ${offset})"]`);
                seeMoreBtn.setAttribute('onclick', `loadMoreComments(${postId}, ${offset + 2})`);
            } else {
                const seeMoreBtn = document.querySelector(`.see-more-btn[onclick="loadMoreComments(${postId}, ${offset})"]`);
                seeMoreBtn.style.display = 'none';
            }
        } else {
            console.error('Error al cargar más comentarios:', data.error);
        }
    })
    .catch(error => {
        console.error('Error al cargar más comentarios:', error);
    });
}

function toggleComments(postId) {
    const commentSection = document.getElementById(`comment-section-${postId}`);
    if (commentSection.style.display === 'none') {
        commentSection.style.display = 'block';
    } else {
        commentSection.style.display = 'none';
    }
}

function fetchFeed() {
    fetch('../php/feed.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('feedContainer').innerHTML = data;
        })
        .catch(error => {
            console.error('Error al actualizar el feed:', error);
        });
}