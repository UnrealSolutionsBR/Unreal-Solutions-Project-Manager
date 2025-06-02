document.addEventListener('DOMContentLoaded', function () {
    const serviceFilter = document.getElementById('filter-service');
    const statusFilter = document.getElementById('filter-status');
    const projects = document.querySelectorAll('.upm-project-invoice-block');

    function applyFilter() {
        const selectedService = serviceFilter.value.toLowerCase();
        const selectedStatus = statusFilter.value.toLowerCase();

        projects.forEach(p => {
            const service = p.getAttribute('data-service').toLowerCase();
            const status = p.getAttribute('data-status').toLowerCase();

            const matchService = selectedService === 'todos' || service === selectedService;
            const matchStatus = selectedStatus === 'todos' || status === selectedStatus;

            p.style.display = matchService && matchStatus ? 'block' : 'none';
        });
    }

    serviceFilter.addEventListener('change', applyFilter);
    statusFilter.addEventListener('change', applyFilter);
});
