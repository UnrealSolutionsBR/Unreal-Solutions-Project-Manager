document.addEventListener('DOMContentLoaded', function () {
    const statusFilter = document.getElementById('filter-status');
    const serviceFilter = document.getElementById('filter-service');
    const blocks = document.querySelectorAll('.upm-project-invoice-block');

    function filterInvoices() {
        const selectedStatus = statusFilter.value;
        const selectedService = serviceFilter.value;

        blocks.forEach(block => {
            const blockStatuses = block.getAttribute('data-statuses').split(',');
            const blockService = block.getAttribute('data-service');

            const matchesStatus = (selectedStatus === 'todos') || blockStatuses.includes(selectedStatus);
            const matchesService = (selectedService === 'todos') || blockService === selectedService;

            block.style.display = (matchesStatus && matchesService) ? 'block' : 'none';
        });
    }

    if (statusFilter && serviceFilter) {
        statusFilter.addEventListener('change', filterInvoices);
        serviceFilter.addEventListener('change', filterInvoices);
    }
});