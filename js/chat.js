const chatContainer = document.getElementById('chat-container');

function openChat(userId, userName, userImage) {
    if (document.getElementById('chat-' + userId)) return;

    const chatBubble = document.createElement('div');
    chatBubble.classList.add('chat-bubble');
    chatBubble.id = 'chat-' + userId;

    chatBubble.innerHTML = `
        <div class="chat-header">
            <img src="${userImage}" alt="${userName}">
            <span>${userName}</span>
            <span class="chat-close">&times;</span>
        </div>
        <div class="chat-body">
            <div class="chat-messages">
                <!-- Mensajes cargados -->
            </div>
            <div class="chat-input">
                <input type="text" placeholder="Escribe tu mensaje...">
                <button><i class="bi bi-send" style="top: 0; right: 0;"></i></button>
            </div>
        </div>
    `;

    chatContainer.appendChild(chatBubble);

    const chatBody = chatBubble.querySelector('.chat-body');
    const chatClose = chatBubble.querySelector('.chat-close');
    const chatHeader = chatBubble.querySelector('.chat-header');

    // Expande el chat inmediatamente
    chatBody.style.display = 'block';
    chatBubble.querySelector('.chat-input').style.display = 'flex'; // mostrar el input

    chatHeader.onclick = () => {
        chatBody.style.display = chatBody.style.display === 'none' ? 'block' : 'none';
    };

    chatClose.onclick = (e) => {
        e.stopPropagation();
        clearInterval(fetchInterval);
        chatContainer.removeChild(chatBubble);
    };

    // Función para obtener mensajes cada 1 segundo
    function fetchMessages() {
        const myId = document.getElementById('userPrincipalId').value;
        const friendImage = userImage;
        const myImage = document.querySelector('#profileIcon img') ? document.querySelector('#profileIcon img').src : 'http://app.bingo.es/src/img/default-avatar.png';
        
        // Función auxiliar para formatear números a dos dígitos
        function pad(n) { return n < 10 ? '0' + n : n; }
        
        let lastDate = ''; // Para agrupar por fecha
        fetch(`../php/getMessages.php?friendId=${userId}&myId=${myId}`)
            .then(response => response.json())
            .then(data => {
                let html = '';
                data.forEach(msg => {
                    const msgDate = new Date(msg.fecha_envio);
                    const formattedDate = pad(msgDate.getDate()) + "/" + pad(msgDate.getMonth()+1) + "/" + msgDate.getFullYear();
                    const formattedTime = pad(msgDate.getHours()) + ":" + pad(msgDate.getMinutes());
                    
                    // Inserta separador si cambia la fecha
                    if(formattedDate !== lastDate) {
                        html += `<div class="message-date">
                                    <hr><span>${formattedDate}</span><hr>
                                 </div>`;
                        lastDate = formattedDate;
                    }
                    
                    if (msg.usuario_emisor_id == myId) {
                        // Mensaje enviado por mí
                        html += `<div class="user-message">
                                    <div class="message-content">
                                        ${msg.mensaje}
                                        <div class="message-time">${formattedTime}</div>
                                    </div>
                                    <img src="${myImage}" alt="yo">
                                 </div>`;
                    } else {
                        // Mensaje recibido
                        html += `<div class="bot-message">
                                    <img src="${friendImage}" alt="amigo">
                                    <div class="message-content">
                                        ${msg.mensaje}
                                        <div class="message-time">${formattedTime}</div>
                                    </div>
                                 </div>`;
                    }
                });
                const messagesContainer = chatBubble.querySelector('.chat-messages');
                messagesContainer.innerHTML = html;
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            })
            .catch(console.error);
    }

    // Inicia la actualización de mensajes cada 1 segundo
    const fetchInterval = setInterval(fetchMessages, 1000);

    // Enviar mensaje
    const sendButton = chatBubble.querySelector('.chat-input button');
    sendButton.addEventListener('click', () => {
        const input = chatBubble.querySelector('.chat-input input');
        const message = input.value.trim();
        if (!message) return;
        fetch('../php/sendMessage.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ friendId: userId, message: message })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                input.value = '';
            }
        })
        .catch(console.error);
    });
}

document.querySelector('.messenger ul').addEventListener('click', (e) => {
    const friendItem = e.target.closest('li[data-user-id]');
    if (!friendItem) return;
    
    const userName = friendItem.querySelector('b').textContent.trim();
    const userImage = friendItem.querySelector('img').src;
    const userId = friendItem.dataset.userId;

    openChat(userId, userName, userImage);
});