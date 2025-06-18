document.addEventListener('DOMContentLoaded', function () {
    const bell = document.getElementById('upm-notification-toggle');
    const dropdown = document.getElementById('upm-notification-dropdown');

    if (bell && dropdown) {
        bell.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdown.classList.toggle('visible');
        });

        document.addEventListener('click', function () {
            dropdown.classList.remove('visible');
        });
    }
});
