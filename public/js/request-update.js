function showToast(message, type = 'success') {
    const container = document.getElementById('upm-toast-container');
    const toast = document.createElement('div');
    toast.className = `upm-toast ${type}`;
    toast.innerText = message;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

document.addEventListener('DOMContentLoaded', () => {
    const sendBtn = document.getElementById('upm-send-request');
    const cancelBtn = document.getElementById('upm-cancel-request');
    const modal = document.getElementById('upm-request-modal');

    sendBtn.addEventListener('click', async () => {
        const type = document.getElementById('upm-update-type').value;
        const message = document.getElementById('upm-update-message').value;
        const projectId = document.getElementById('upm-project-id')?.value;

        if (!message.trim()) {
            showToast('Please write a message before sending.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'upm_create_request');
        formData.append('type', type);
        formData.append('message', message);
        formData.append('project_id', projectId);

        try {
            const response = await fetch(upm_request_ajax.ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                showToast('Request sent successfully.', 'success');
                modal.classList.add('hidden');
                document.getElementById('upm-update-message').value = '';
            } else {
                showToast('Error sending request. Please try again.', 'error');
            }
        } catch (error) {
            showToast('Unexpected error occurred.', 'error');
            console.error(error);
        }
    });

    cancelBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
    });
});
