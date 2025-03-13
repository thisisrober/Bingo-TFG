document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const searchResultsBox = document.getElementById('searchResults');
    const profileModal = document.getElementById('profileModal');
    const profileDetails = document.getElementById('profileDetails');
    const closeProfileModalButton = document.querySelector('.close-button');
    const currentUserId = document.getElementById('userPrincipalId').value; // Obtener el ID del usuario actual

    // Búsqueda de usuarios
    searchInput.addEventListener('input', function () {
        const searchQuery = this.value.trim();

        if (searchQuery.length > 0) {
            fetch(`../php/search.php?query=${encodeURIComponent(searchQuery)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la solicitud de búsqueda');
                    }
                    return response.json();
                })
                .then(data => {
                    searchResultsBox.innerHTML = '';

                    if (data.length > 0) {
                        data.forEach(user => {
                            if (user.id_usuario === parseInt(currentUserId)) return; // Evitar que el usuario se busque a sí mismo

                            const userItem = document.createElement('li');
                            userItem.classList.add('search-result-item');

                            userItem.innerHTML = `
                                <img class="result-profile-pic" src="${user.foto_perfil ? user.foto_perfil : '../src/img/default-avatar.png'}" alt="Foto de ${user.nombre}">
                                <span>${user.nombre} <span style="color: grey;">@${user.identificador}</span></span>
                            `;

                            userItem.addEventListener('click', () => {
                                showUserProfile(user.id_usuario);
                                searchResultsBox.style.display = 'none';
                                searchInput.value = '';
                            });

                            searchResultsBox.appendChild(userItem);
                        });

                        searchResultsBox.style.display = 'block';

                        const searchInputRect = searchInput.getBoundingClientRect();
                        searchResultsBox.style.position = 'absolute';
                        searchResultsBox.style.top = `${searchInputRect.bottom + window.scrollY}px`;
                        searchResultsBox.style.left = `${searchInputRect.left + window.scrollX}px`;
                        searchResultsBox.style.width = `${searchInputRect.width}px`;
                    } else {
                        searchResultsBox.style.display = 'none'; // Ocultar si no hay resultados
                    }
                })
                .catch(error => {
                    console.error('Error al obtener los resultados de búsqueda:', error);
                });
        } else {
            searchResultsBox.style.display = 'none';
        }
    });

    function showUserProfile(userId) {
        fetch(`../php/getUserDetails.php?id=${userId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la solicitud de detalles del perfil');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.nombre) {
                    profileDetails.innerHTML = `
                        <div class="profile-header">
                            <img class="profile-details-img" src="${data.foto_perfil ? data.foto_perfil : '../src/img/default-avatar.png'}" alt="Foto de Perfil">
                            <div class="profile-info">
                                <h2>${data.nombre} ${data.apellidos} <span>@${data.identificador}</span></h2>
                                <button id="amistad-btn" class="action-btn amistad-btn">Añadir como amigo</button>
                            </div>
                        </div>
                        <p>${data.biografia}</p>
                        <hr>
                        <div id="userPosts" class="user-posts"></div>
                    `;
                    profileModal.style.display = 'flex';

                    checkFriendRequestStatus(userId);
                    loadUserPosts(userId, data.privacidad);

                    document.getElementById('amistad-btn').addEventListener('click', () => toggleFriendRequest(userId));
                } else {
                    alert('No se encontró información para este usuario.');
                }
            })
            .catch(error => {
                console.error('Error al obtener los detalles del perfil:', error);
            });
    }

    function checkFriendRequestStatus(userId) {
        fetch(`../php/comprobar-solicitudes.php?id=${userId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la solicitud');
                }
                return response.json();
            })
            .then(data => {
                const amistadBtn = document.getElementById('amistad-btn');
                if (data.status === 'pendiente') {
                    amistadBtn.textContent = 'Solicitud enviada';
                    amistadBtn.style.backgroundColor = 'rgba(128, 128, 128, 0.5)'; // Gris más transparente
                    amistadBtn.addEventListener('mouseover', () => {
                        amistadBtn.textContent = 'Cancelar solicitud';
                    });
                    amistadBtn.addEventListener('mouseout', () => {
                        amistadBtn.textContent = 'Solicitud enviada';
                    });
                } else if (data.status === 'recibida') {
                    amistadBtn.textContent = 'Aceptar solicitud';
                    amistadBtn.style.backgroundColor = 'green'; // Verde para aceptar solicitud
                } else if (data.status === 'amigos') {
                    amistadBtn.textContent = 'Eliminar amigo';
                    amistadBtn.style.backgroundColor = 'black'; // Negro para amigos
                    amistadBtn.addEventListener('click', () => toggleFriendRequest(userId, 'remove'));
                } else {
                    amistadBtn.textContent = 'Añadir como amigo';
                    amistadBtn.style.backgroundColor = 'black'; // Negro para añadir amigo
                }
            })
            .catch(error => {
                console.error('Error al verificar el estado de la solicitud de amistad:', error);
            });
    }

    function toggleFriendRequest(userId, actionOverride = null) {
        const amistadBtn = document.getElementById('amistad-btn');
        const action = actionOverride || (amistadBtn.textContent === 'Añadir como amigo' ? 'add' : 'cancel');

        fetch(`../php/manejar-solicitudes-amistad.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ userId, action, request_id: userId })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la solicitud');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                checkFriendRequestStatus(userId);
            } else {
                console.error('Error al procesar la solicitud de amistad:', data.error);
                alert('Error al procesar la solicitud de amistad.');
            }
        })
        .catch(error => {
            console.error('Error al procesar la solicitud de amistad:', error);
        });
    }

    function loadUserPosts(userId, privacidad) {
        if (privacidad === 'privado') {
            fetch(`../php/comprobar-solicitudes.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    // Si no son amigos, muestra mensaje de perfil privado
                    if (data.status !== 'amigos') {
                        document.getElementById('userPosts').innerHTML = '<i class="bi bi-lock-fill"></i> Este perfil es privado.';
                        return;
                    }
                    fetchPosts(userId);
                })
                .catch(error => {
                    console.error('Error al verificar el estado de amistad para publicaciones:', error);
                });
        } else {
            fetchPosts(userId);
        }
    }

    function fetchPosts(userId) {
        fetch(`../php/getUserPosts.php?id=${userId}`)
            .then(response => response.json())
            .then(data => {
                const userPosts = document.getElementById('userPosts');
                userPosts.innerHTML = '';

                if (data.length > 0) {
                    data.slice(0, 3).forEach(post => {
                        const postItem = document.createElement('div');
                        postItem.classList.add('post-item');
                        postItem.innerHTML = `
                            <p>${post.contenido}</p>
                            ${post.media_url ? `<img src="${post.media_url}" alt="Media">` : ''}
                        `;
                        userPosts.appendChild(postItem);
                    });

                    if (data.length > 3) {
                        const seeMoreBtn = document.createElement('button');
                        seeMoreBtn.classList.add('see-more-btn');
                        seeMoreBtn.textContent = 'Ver más';
                        seeMoreBtn.addEventListener('click', () => {
                            userPosts.innerHTML = '';
                            data.forEach(post => {
                                const postItem = document.createElement('div');
                                postItem.classList.add('post-item');
                                postItem.innerHTML = `
                                    <p>${post.contenido}</p>
                                    ${post.media_url ? `<img src="${post.media_url}" alt="Media">` : ''}
                                `;
                                userPosts.appendChild(postItem);
                            });
                        });
                        userPosts.appendChild(seeMoreBtn);
                    }
                } else {
                    userPosts.innerHTML = '<p>Este usuario no ha realizado publicaciones.</p>';
                }
            })
            .catch(error => {
                console.error('Error al obtener las publicaciones del usuario:', error);
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
                            <li><img src="${request.foto_perfil ? '../uploads/' + request.foto_perfil : '../src/img/default-avatar.png'}" alt="user"></li>
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

    closeProfileModalButton.addEventListener('click', function () {
        profileModal.style.display = 'none';
    });

    window.addEventListener('click', function (event) {
        if (event.target === profileModal) {
            profileModal.style.display = 'none';
        }
    });

    // Inicializar solicitudes de amistad
    fetchFriendRequests();
});
