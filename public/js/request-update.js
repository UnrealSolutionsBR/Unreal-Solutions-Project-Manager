document.addEventListener('DOMContentLoaded', () => {
    const sendBtn = document.getElementById('upm-send-request');
    const cancelBtn = document.getElementById('upm-cancel-request');
    const modal = document.getElementById('upm-request-modal');

    sendBtn.addEventListener('click', async () => {
        const type = document.getElementById('upm-update-type').value;
        const message = document.getElementById('upm-update-message').value;
        const projectId = new URLSearchParams(window.location.search).get('id');

        if (!message.trim()) {
            alert('Por favor, escribe un mensaje antes de enviar.');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'upm_create_request');
        formData.append('type', type);
        formData.append('message', message);
        formData.append('project_id', projectId);

        const response = await fetch(upm_request_ajax.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        });

        const result = await response.json();
        if (result.success) {
            alert('Solicitud enviada correctamente.');
            modal.classList.add('hidden');
            document.getElementById('upm-update-message').value = '';
        } else {
            alert('Error al enviar la solicitud.');
        }
    });

    cancelBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
    });
});
