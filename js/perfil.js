document.addEventListener('DOMContentLoaded', function() {
    // Asignar función de cierre al botón de cerrar modal
    document.body.addEventListener('click', function(e){
        if(e.target.classList.contains('close-profile-modal')){
            closeProfileModal();
        }
    });
});

function openProfileEditModal() {
    const modal = document.getElementById('profileModal');
    const userId = document.getElementById('userPrincipalId').value;

    // Obtener los datos del usuario mediante AJAX
    fetch('../php/get_user_data.php?user_id=' + userId)
        .then(response => response.json())
        .then(data => {
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="edit-profile-form">
                        <h1>Edita tu perfil</h1>
                        <span>Puedes editar tu perfil y así tener un look más atractivo.</span><br>
                        <form id="profileForm">
                            <div class="edit-profile-pic-container">
                                <div class="edit-profile-pic">
                                    <img id="profilePhotoPreview" src="${data.foto_perfil}" alt="Vista Previa">
                                </div>
                                <input type="file" id="profilePhoto" accept="image/*">
                            </div>
                            <input type="text" id="profileName" placeholder="Nombre" value="${data.nombre}" required>
                            <input type="text" id="profileSurname" placeholder="Apellidos" value="${data.apellidos}" required>
                            <textarea id="profileDescription" rows="3" placeholder="Biografía (máximo 200 caracteres)" maxlength="200">${data.biografia}</textarea>
                            <div class="edit-profile-actions">
                                <button type="button" onclick="saveProfile()" style="background-color: green;">Aceptar cambios</button>
                                <button type="button" class="close-profile-modal" style="background-color: red;">Cancelar y volver</button>
                            </div>
                        </form>
                    </div>
                </div>`;
            modal.style.display = 'flex';
            addProfilePhotoPreview();
        });
}

function closeProfileModal() {
    const modal = document.getElementById('profileModal');
    modal.style.display = 'none';
}

function addProfilePhotoPreview() {
    const photoInput = document.getElementById('profilePhoto');
    photoInput.addEventListener('change', function(event) {
        const preview = document.getElementById('profilePhotoPreview');
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
}

function saveProfile() {
    const name = document.getElementById('profileName').value.trim();
    const surname = document.getElementById('profileSurname').value.trim();
    const description = document.getElementById('profileDescription').value.trim();
    const userId = document.getElementById('userPrincipalId').value;
    const photoInput = document.getElementById('profilePhoto');
    const profilePhoto = photoInput.files[0];

    if (!name || !surname) {
        alert('Los campos Nombre y Apellidos son obligatorios.');
        return;
    }

    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('nombre', name);
    formData.append('apellidos', surname);
    formData.append('biografia', description);
    if (profilePhoto) {
        formData.append('foto_perfil', profilePhoto);
    }

    // Enviar los datos mediante AJAX para actualizar el perfil
    fetch('../php/update_user_data.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeProfileModal();
            window.location.reload();
        } else {
            alert('Error al actualizar el perfil: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el perfil.');
    });
}
