@extends('layout.layout')
@extends('layout.navbar')

@section('header')
@section('sidenav')
@endsection

<style>
/* Modal Animations */
.modal-overlay {
    transition: all 0.3s ease-in-out;
    backdrop-filter: blur(4px);
}

.modal-overlay.hidden {
    opacity: 0;
    visibility: hidden;
}

.modal-overlay:not(.hidden) {
    opacity: 1;
    visibility: visible;
}

.modal-content {
    transform: scale(0.95) translateY(-20px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-overlay:not(.hidden) .modal-content {
    transform: scale(1) translateY(0);
}

/* Button Hover Effects */
.btn-primary {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    transform: translateY(0);
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-success {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    transform: translateY(0);
}

.btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
}

.btn-secondary {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-warning {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    transform: translateY(0);
}

.btn-warning:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4);
}

.btn-info {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    transform: translateY(0);
}

.btn-info:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
}

.btn-danger {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    transform: translateY(0);
}

.btn-danger:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
}

/* Input Focus Effects */
.form-input {
    transition: all 0.2s ease-in-out;
}

.form-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Table Styling */
.table-container {
    max-height: 400px;
    overflow-y: auto;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.table-container::-webkit-scrollbar {
    width: 8px;
}

.table-container::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.table-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.table-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Search Input Styling */
.search-container {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.search-input {
    padding-left: 40px;
}

/* Table Row Hover */
tbody tr {
    transition: background-color 0.15s ease-in-out;
}

tbody tr:hover {
    background-color: #f8fafc;
}

/* Loading State */
.loading {
    opacity: 0.6;
    pointer-events: none;
}
</style>

<main class="flex-1 p-4 min-h-screen bg-white rounded-l-3xl shadow-inner flex flex-col">

    <!-- Row: Select & Tambah Jenis Inspeksi -->
    <div class="grid grid-cols-12 gap-4 mb-6">
        <div class="col-span-9">
            <label for="inspection_type_select" class="block text-2xl font-medium text-gray-700 mb-2">Kelola Data Jenis Inspeksi</label>
            <select id="inspection_type_select" name="inspection_type_select"
                class="form-input w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600 focus:outline-none">
                <option value="">Pilih Jenis Inspeksi</option>
                @foreach($inspectionTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-3 flex items-end">
            <button id="btnOpenModal"
                class="btn-primary w-full px-3 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <i class="fa-solid fa-plus text-xs"></i>
                Tambah Jenis Inspeksi
            </button>
        </div>
    </div>

    <!-- Modal Tambah Jenis Inspeksi -->
    <div id="inspectionModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="modal-content bg-white w-full max-w-md mx-4 p-6 rounded-lg shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Tambahkan Jenis Inspeksi</h3>
                <button id="btnCloseModal" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <i class="fa-solid fa-times text-lg"></i>
                </button>
            </div>
            <form id="addInspectionTypeForm" method="POST" action="{{ route('inspection-types.store',) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Jenis Inspeksi</label>
                    <input type="text" name="name" required
                        class="form-input w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900 focus:outline-none"
                        placeholder="Masukkan nama jenis inspeksi">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" id="btnCancelModal"
                        class="btn-secondary px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Batal
                    </button>
                    <button type="submit"
                        class="btn-success px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <i class="fa-solid fa-save mr-2"></i>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Jenis Inspeksi -->
    <div id="editInspectionModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
        <div class="modal-content bg-white w-full max-w-md mx-4 p-6 rounded-lg shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Edit Jenis Inspeksi</h3>
                <button id="btnCloseEditModal" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <i class="fa-solid fa-times text-lg"></i>
                </button>
            </div>
            <form id="editInspectionTypeForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Jenis Inspeksi</label>
                    <input type="text" name="name" id="editNameInput" required
                        class="form-input w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900 focus:outline-none"
                        placeholder="Masukkan nama jenis inspeksi">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" id="btnCancelEditModal"
                        class="btn-secondary px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Batal
                    </button>
                    <button type="submit"
                        class="btn-success px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <i class="fa-solid fa-save mr-2"></i>
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        
        <!-- Search and Create Button Container -->
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <!-- Search Input -->
            <div class="search-container relative">
                <i class="search-icon fa-solid fa-search"></i>
                <input type="text" id="searchInput" placeholder="Cari jenis inspeksi..."
                    class="search-input form-input w-full sm:w-64 px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900 text-sm focus:outline-none">
            </div>
            <a href="{{ route('inspection-types.index') }}">
                <button class="btn-secondary px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 flex items-center gap-2">
                    <i class="fa-solid fa-backward-step"></i>
                    Kembali
                </button>
            </a>
        </div>
    </div>

    <!-- Tabel Daftar Inspeksi -->
    <div class="table-container flex-grow">
        <table class="w-full divide-y divide-gray-200" id="inspectionTable">
            <thead class="bg-gray-100 text-gray-700 sticky top-0 z-10 border-2 border-gray-200 shadow-sm shadow-gray-500">
                <tr>
                    <th class="px-3 py-4 text-left text-md font-medium w-16">No</th>
                    <th class="px-4 py-4 text-left text-md font-medium">Data Jenis Inspeksi yang Telah Ditambahkan</th>
                    <th class="px-3 py-4 text-center text-md font-medium w-48">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                @forelse ($inspectionTypes as $index => $type)
                    <tr class="inspection-row" data-name="{{ strtolower($type->name) }}">
                    <td class="px-3 py-4 text-sm text-gray-900 text-center font-medium border-2 border-gray-100">{{ $index + 1 }}</td>
                    <td class="px-4 py-4 text-sm text-gray-900 font-medium border-2 border-gray-100">{{ $type->name }}</td>
                    <td class="px-3 py-4 border-2 border-gray-100">
                        <div class="flex justify-center gap-3">
                            <!-- Fixed Edit Button -->
                            <button onclick="openEditModal({{ $type->id }}, '{{ $type->name }}')"
                                class="btn-warning text-md bg-yellow-400 hover:bg-yellow-500 text-white px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-1"
                                title="Edit">
                                <i class="fa-solid fa-pen-to-square m-1"></i>
                            </button>
                            <button onclick="showDeleteModal({{ $type->id }}, '{{ $type->name }}')"
                                class="btn-danger text-md bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1"
                                title="Hapus">
                                <i class="fa-solid fa-trash m-1"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                    <tr id="emptyRow">
                        <td colspan="3" class="text-center py-8 text-gray-500">
                            <i class="fa-solid fa-inbox text-3xl mb-2 block text-gray-300"></i>
                            Belum ada data inspeksi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- No Results Message -->
        <div id="noResults" class="hidden text-center py-8 text-gray-500">
            <i class="fa-solid fa-search text-3xl mb-2 block text-gray-300"></i>
            Tidak ada hasil yang ditemukan.
        </div>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal Elements - Add
    const modal = document.getElementById('inspectionModal');
    const btnOpenModal = document.getElementById('btnOpenModal');
    const btnCloseModal = document.getElementById('btnCloseModal');
    const btnCancelModal = document.getElementById('btnCancelModal');

    // Modal Elements - Edit
    const editModal = document.getElementById('editInspectionModal');
    const btnCloseEditModal = document.getElementById('btnCloseEditModal');
    const btnCancelEditModal = document.getElementById('btnCancelEditModal');
    const editForm = document.getElementById('editInspectionTypeForm');
    const editNameInput = document.getElementById('editNameInput');

    // Search Elements
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');
    const noResults = document.getElementById('noResults');

    // Add Modal Functions
    function openModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            const nameInput = modal.querySelector('input[name="name"]');
            if (nameInput) nameInput.focus();
        }, 100);
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        
        const form = document.getElementById('addInspectionTypeForm');
        if (form) form.reset();
    }

    // Edit Modal Functions
    function openEditModal(id, name) {
        // Set form action URL
        editForm.action = `/inspection-types/${id}`;
        
        // Set input value
        editNameInput.value = name;
        
        // Show modal with same animation as add modal
        editModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Focus on input after animation
        setTimeout(() => {
            editNameInput.focus();
            editNameInput.select(); // Select text for easy editing
        }, 100);
    }

    function closeEditModal() {
        editModal.classList.add('hidden');
        document.body.style.overflow = '';
        
        // Reset form
        editForm.reset();
    }

    // Make openEditModal globally available
    window.openEditModal = openEditModal;

    // Add Modal Event Listeners
    btnOpenModal.addEventListener('click', openModal);
    btnCloseModal.addEventListener('click', closeModal);
    btnCancelModal.addEventListener('click', closeModal);

    // Edit Modal Event Listeners
    btnCloseEditModal.addEventListener('click', closeEditModal);
    btnCancelEditModal.addEventListener('click', closeEditModal);

    // Close modals on overlay click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });

    editModal.addEventListener('click', function(e) {
        if (e.target === editModal) closeEditModal();
    });

    // Close modals on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (!modal.classList.contains('hidden')) {
                closeModal();
            }
            if (!editModal.classList.contains('hidden')) {
                closeEditModal();
            }
        }
    });

    // Search Function (unchanged)
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.inspection-row');
        const emptyRow = document.getElementById('emptyRow');
        let visibleRows = 0;

        rows.forEach((row) => {
            const name = row.getAttribute('data-name');
            const isVisible = name.includes(searchTerm);
            
            row.style.display = isVisible ? '' : 'none';
            
            if (isVisible) {
                visibleRows++;
                const numberCell = row.querySelector('td:first-child');
                if (numberCell) {
                    numberCell.textContent = visibleRows;
                }
            }
        });

        if (emptyRow) {
            emptyRow.style.display = 'none';
        }

        if (visibleRows === 0 && searchTerm !== '') {
            noResults.classList.remove('hidden');
            tableBody.style.display = 'none';
        } else {
            noResults.classList.add('hidden');
            tableBody.style.display = '';
            
            if (visibleRows === 0 && searchTerm === '' && emptyRow) {
                emptyRow.style.display = '';
            }
        }
    }

    // Search Event Listeners
    searchInput.addEventListener('input', performSearch);
    searchInput.addEventListener('keyup', performSearch);

    // Form submission with loading state - Add Form
    const addForm = document.getElementById('addInspectionTypeForm');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            const submitBtn = addForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Menyimpan...';
                submitBtn.disabled = true;
            }
        });
    }

    // Form submission with loading state - Edit Form
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            const submitBtn = editForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Mengupdate...';
                submitBtn.disabled = true;
            }
        });
    }

    // Add ripple effect to buttons
    function createRipple(event) {
        const button = event.currentTarget;
        const circle = document.createElement('span');
        const diameter = Math.max(button.clientWidth, button.clientHeight);
        const radius = diameter / 2;

        circle.style.width = circle.style.height = `${diameter}px`;
        circle.style.left = `${event.clientX - button.offsetLeft - radius}px`;
        circle.style.top = `${event.clientY - button.offsetTop - radius}px`;
        circle.classList.add('ripple');

        const ripple = button.getElementsByClassName('ripple')[0];
        if (ripple) {
            ripple.remove();
        }

        button.appendChild(circle);
    }

    // Add ripple effect to all buttons
    document.querySelectorAll('button, .btn-warning, .btn-info, .btn-danger').forEach(button => {
        button.addEventListener('click', createRipple);
    });
});
// Delete Modal Function (Modern Modal Style)
function showDeleteModal(id, name) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50';
    modal.innerHTML = `
        <div class="modal-content bg-white w-full max-w-md mx-4 p-6 rounded-lg shadow-2xl transform scale-95 transition-all duration-300">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Hapus</h3>
                    <p class="text-sm text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                </div>
                <button class="ml-auto text-gray-400 hover:text-gray-600 focus:outline-none" onclick="closeDeleteModal(this)">
                    <i class="fa-solid fa-times text-lg"></i>
                </button>
            </div>
            <div class="mb-6">
                <p class="text-gray-600">
                    Apakah Anda yakin ingin menghapus jenis inspeksi:
                </p>
                <p class="font-semibold text-gray-900 mt-2 p-3 bg-gray-50 rounded-md border-l-4 border-red-400">
                    "${name}"
                </p>
                <p class="text-sm text-red-600 mt-3 flex items-center">
                    <i class="fa-solid fa-warning mr-2"></i>
                    Data yang terkait juga akan ikut terhapus!
                </p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDeleteModal(this)" 
                    class="btn-secondary px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                    <i class="fa-solid fa-times mr-2"></i>
                    Batal
                </button>
                <button type="button" onclick="executeDelete(${id}, this)" 
                    class="btn-danger px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200">
                    <i class="fa-solid fa-trash mr-2"></i>
                    Hapus Sekarang
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    // Animate modal in
    setTimeout(() => {
        const modalContent = modal.querySelector('.modal-content');
        modalContent.style.transform = 'scale(1)';
        modalContent.style.opacity = '1';
    }, 10);
    
    // Close on overlay click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeDeleteModal(modal);
    });
    
    // Close on Escape key
    const escHandler = (e) => {
        if (e.key === 'Escape') {
            closeDeleteModal(modal);
            document.removeEventListener('keydown', escHandler);
        }
    };
    document.addEventListener('keydown', escHandler);
    
    // Make it globally accessible
    window.currentDeleteModal = modal;
}

// Close Delete Modal Function
function closeDeleteModal(element) {
    const modal = element.closest ? element.closest('.modal-overlay') : window.currentDeleteModal;
    if (modal) {
        const modalContent = modal.querySelector('.modal-content');
        modalContent.style.transform = 'scale(0.95)';
        modalContent.style.opacity = '0.6';
        
        setTimeout(() => {
            if (document.body.contains(modal)) {
                document.body.removeChild(modal);
            }
            document.body.style.overflow = '';
        }, 200);
    }
}

// Execute Delete Function
function executeDelete(id, buttonElement) {
    const modal = buttonElement.closest('.modal-overlay');
    const deleteBtn = buttonElement;
    
    // Show loading state
    deleteBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Menghapus...';
    deleteBtn.disabled = true;
    deleteBtn.classList.add('opacity-75', 'cursor-not-allowed');
    
    // Disable cancel button
    const cancelBtn = modal.querySelector('.btn-secondary');
    if (cancelBtn) {
        cancelBtn.disabled = true;
        cancelBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
    
    // Create and submit delete form
    setTimeout(() => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/inspection-types/${id}`;
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        // Add method override
        const methodOverride = document.createElement('input');
        methodOverride.type = 'hidden';
        methodOverride.name = '_method';
        methodOverride.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodOverride);
        document.body.appendChild(form);
        form.submit();
    }, 500); // Small delay for better UX
}

// Keep the old confirmDelete function as fallback (but remove from button calls)
function confirmDelete(id, name) {
    if (confirm(`Apakah Anda yakin ingin menghapus jenis inspeksi "${name}"?\n\nTindakan ini tidak dapat dibatalkan.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/inspection-types/${id}`;
        form.style.display = 'none';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const methodOverride = document.createElement('input');
        methodOverride.type = 'hidden';
        methodOverride.name = '_method';
        methodOverride.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodOverride);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

@endsection