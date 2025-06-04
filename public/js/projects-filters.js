document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.upm-filters form');
    const container = document.getElementById('upm-project-cards');

    if (!form || !container) return;

    form.addEventListener('change', function (e) {
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        fetch(upm_ajax.url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=upm_filter_projects&${params.toString()}`
        })
        .then(res => res.text())
        .then(html => {
            container.innerHTML = html;
        });
    });
});
