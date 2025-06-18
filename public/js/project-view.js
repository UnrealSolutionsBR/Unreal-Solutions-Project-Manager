document.addEventListener('DOMContentLoaded', function () {
    const openBtn = document.getElementById('upm-request-update-btn');
    const modal = document.getElementById('upm-request-modal');
    const closeBtn = document.querySelector('.upm-modal-close');
    const cancelBtn = document.getElementById('upm-cancel-request');

    openBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        modal?.classList.remove('hidden');
    });

    const closeModal = () => modal?.classList.add('hidden');

    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);
});
