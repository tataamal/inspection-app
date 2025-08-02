$(document).ready(function () {
        let table = $('#inspectionTable').DataTable({
            scrollY: '440px',
            scrollX: true,
            scrollCollapse: false,
            paging: false,
            ordering: false,
            order: [[0, 'asc']],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
            },
            dom: 'rt'
        });

        // Global Search
        $('#globalSearch').on('keyup', function () {
            table.search(this.value).draw();
        });

        // Filter MRP
        $('#dispoFilter').on('change', function () {
            table.column(3).search(this.value).draw();
        });

        // Filter Storage
        $('#storageFilter').on('change', function () {
            table.column(8).search(this.value).draw();
        });
    });