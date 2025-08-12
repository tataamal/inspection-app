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

/* Accordion Animation */
.accordion-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
}

.accordion-content.open {
    max-height: 1000px;
    transition: max-height 0.5s ease-in;
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

.btn-danger {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    transform: translateY(0);
}

.btn-danger:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
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
    max-height: 600px;
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

/* Card hover effects */
.header-card {
    transition: all 0.2s ease-in-out;
}

.header-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

/* Question item hover */
.question-item {
    transition: all 0.15s ease-in-out;
}

.question-item:hover {
    background-color: #f8fafc;
    transform: translateX(4px);
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

/* Stats Cards */
.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 1.5rem;
    color: white;
    position: relative;
    overflow: hidden;
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(-100%);
    transition: transform 0.6s ease;
}

.stats-card:hover::before {
    transform: translateX(0);
}

/* Counter badge */
.counter-badge {
    animation: pulse 2s infinite;
}
</style>

<main class="flex-1 p-4 min-h-screen bg-white rounded-l-3xl shadow-inner flex flex-col">

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="stats-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/80 text-sm">Total Jenis Inspeksi</p>
                    <p class="text-3xl font-bold" id="totalTypes">{{ $inspectionTypes->count() }}</p>
                </div>
                <i class="fa-solid fa-clipboard-list text-3xl text-white/70"></i>
            </div>
        </div>
        <div class="stats-card bg-gradient-to-r from-green-500 to-green-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/80 text-sm">Total Header</p>
                    <p class="text-3xl font-bold" id="totalHeaders">{{ $inspectionTypes->sum(function($type) { return $type->headers->count(); }) }}</p>
                </div>
                <i class="fa-solid fa-list text-3xl text-white/70"></i>
            </div>
        </div>
        <div class="stats-card bg-gradient-to-r from-purple-500 to-purple-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/80 text-sm">Total Pertanyaan</p>
                    <p class="text-3xl font-bold" id="totalQuestions">{{ $inspectionTypes->sum(function($type) { return $type->headers->sum(function($header) { return $header->questions->count(); }); }) }}</p>
                </div>
                <i class="fa-solid fa-question-circle text-3xl text-white/70"></i>
            </div>
        </div>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start right-2 sm:items-center gap-4 mb-6">
        
        <!-- Search and Create Button Container -->
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <!-- Search Input -->
            <div class="search-container relative">
                <i class="search-icon fa-solid fa-search"></i>
                <input type="text" id="searchInput" placeholder="Cari jenis inspeksi..."
                    class="search-input form-input w-full sm:w-64 px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900 text-sm focus:outline-none">
            </div>
            
            <!-- Expand All Button -->
            <button id="expandAllBtn"
                class="btn-info px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex items-center gap-2">
                <i class="fa-solid fa-expand-alt"></i>
                <span>Buka Semua</span>
            </button>

            <a href="{{ route('inspection-types.create') }}"><button id="addInspectionTypeBtn"
            class="btn-primary px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center gap-2">
                <i class="fa-solid fa-puzzle-piece"></i>
                <span>Add Jenis Inspeksi</span>
            </button></a>
        </div>
    </div>

    <!-- Inspection Types List Container -->
    <div class="table-container flex-grow">
        <div class="space-y-4" id="inspectionTypesList">
            
            @forelse($inspectionTypes as $type)
                <!-- Inspection Type Card -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 inspection-type-card" 
                     data-name="{{ strtolower($type->name) }}">
                    <div class="p-4">
                        <!-- Type Header -->
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fa-solid fa-clipboard-check text-blue-600"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">{{ $type->name }}</h2>
                                    <p class="text-sm text-gray-500">
                                        {{ $type->headers->count() }} Header • 
                                        {{ $type->headers->sum(function($header) { return $header->questions->count(); }) }} Pertanyaan
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="counter-badge bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    ID: {{ $type->id }}
                                </span>
                                <button class="toggle-accordion btn-info text-sm bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded focus:outline-none" 
                                        data-target="accordion-{{ $type->id }}">
                                    <i class="fa-solid fa-chevron-down transition-transform duration-200"></i>
                                </button>
                                <a href="{{ route('inspection-types.edit', $type->id) }}" 
                                   class="btn-warning text-sm bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded focus:outline-none" 
                                   title="Edit Jenis Inspeksi">
                                    <i class="fa-solid fa-edit"></i>
                                </a>
                                <a href="#"
                                    class="btn-danger text-sm bg-red-800 hover:bg-red-500 text-white px-3 py-1.5 rounded focus:outline-none">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Headers Accordion -->
                        <div class="accordion-content border-t border-gray-200 pt-3" id="accordion-{{ $type->id }}">
                            @if($type->headers->count() > 0)
                                <div class="space-y-3">
                                    @foreach($type->headers as $headerIndex => $header)
                                        <!-- Header Card -->
                                        <div class="header-card bg-gray-50 border border-gray-200 rounded-lg p-3">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-6 h-6 bg-green-100 rounded flex items-center justify-center">
                                                        <i class="fa-solid fa-list text-green-600 text-xs"></i>
                                                    </div>
                                                    <div>
                                                        <h3 class="font-medium text-gray-900 text-sm">{{ $header->title }}</h3>
                                                        <p class="text-xs text-gray-500">{{ $header->questions->count() }} Pertanyaan</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-0.5 rounded-full">
                                                        #{{ $headerIndex + 1 }}
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <!-- Questions List -->
                                            @if($header->questions->count() > 0)
                                                <div class="space-y-1 ml-8">
                                                    @foreach($header->questions as $questionIndex => $question)
                                                        <div class="question-item group flex items-center justify-between p-2 bg-white rounded border border-gray-100">
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-xs font-medium text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">
                                                                    {{ $questionIndex + 1 }}
                                                                </span>
                                                                <span class="text-sm text-gray-700">{{ $question->question_text }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="ml-8 text-center py-4 text-gray-400 text-sm">
                                                    <i class="fa-solid fa-question-circle mb-1 block"></i>
                                                    Belum ada pertanyaan
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-6 text-gray-400">
                                    <i class="fa-solid fa-list text-2xl mb-2 block"></i>
                                    <p class="text-sm">Belum ada header untuk jenis inspeksi ini</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="bg-white border-2 border-dashed border-gray-300 rounded-xl p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-clipboard-list text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada jenis inspeksi</h3>
                    <p class="text-gray-500 mb-4">Mulai dengan membuat jenis inspeksi pertama Anda.</p>
                    <a href="{{ route('inspection-types.create') }}" 
                       class="btn-primary bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fa-solid fa-plus mr-2"></i>
                        Tambah Jenis Inspeksi
                    </a>
                </div>
            @endforelse
            
            <!-- No Results Message -->
            <div id="noResults" class="hidden bg-white border-2 border-dashed border-gray-300 rounded-xl p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-search text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada hasil ditemukan</h3>
                <p class="text-gray-500">Coba gunakan kata kunci yang berbeda atau periksa ejaan Anda.</p>
            </div>

        </div>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const searchInput = document.getElementById('searchInput');
    const expandAllBtn = document.getElementById('expandAllBtn');
    const inspectionTypesList = document.getElementById('inspectionTypesList');
    const noResults = document.getElementById('noResults');
    
    // State variables
    let allExpanded = false;

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const typeCards = document.querySelectorAll('.inspection-type-card');
        let visibleCount = 0;

        typeCards.forEach(card => {
            const name = card.getAttribute('data-name');
            const isVisible = name.includes(searchTerm);
            
            card.style.display = isVisible ? 'block' : 'none';
            if (isVisible) visibleCount++;
        });

        // Show/hide no results message
        if (visibleCount === 0 && searchTerm !== '') {
            noResults.classList.remove('hidden');
        } else {
            noResults.classList.add('hidden');
        }
    });

    // Expand/Collapse All functionality
    expandAllBtn.addEventListener('click', function() {
        const accordions = document.querySelectorAll('.accordion-content');
        const toggleButtons = document.querySelectorAll('.toggle-accordion');
        
        if (allExpanded) {
            // Collapse all
            accordions.forEach(accordion => {
                accordion.classList.remove('open');
            });
            toggleButtons.forEach(btn => {
                const icon = btn.querySelector('i');
                icon.style.transform = 'rotate(0deg)';
            });
            this.innerHTML = '<i class="fa-solid fa-expand-alt"></i><span>Buka Semua</span>';
            allExpanded = false;
        } else {
            // Expand all
            accordions.forEach(accordion => {
                accordion.classList.add('open');
            });
            toggleButtons.forEach(btn => {
                const icon = btn.querySelector('i');
                icon.style.transform = 'rotate(180deg)';
            });
            this.innerHTML = '<i class="fa-solid fa-compress-alt"></i><span>Tutup Semua</span>';
            allExpanded = true;
        }
    });

    // Individual accordion toggle
    document.addEventListener('click', function(e) {
        if (e.target.closest('.toggle-accordion')) {
            const button = e.target.closest('.toggle-accordion');
            const targetId = button.getAttribute('data-target');
            const accordion = document.getElementById(targetId);
            const icon = button.querySelector('i');
            
            if (accordion.classList.contains('open')) {
                accordion.classList.remove('open');
                icon.style.transform = 'rotate(0deg)';
            } else {
                accordion.classList.add('open');
                icon.style.transform = 'rotate(180deg)';
            }
        }
    });

    // Add loading state to edit buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('a[href*="edit"]') || e.target.closest('a[href*="create"]')) {
            const btn = e.target.closest('a');
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            btn.style.pointerEvents = 'none';
            
            // Reset after a delay (in case navigation fails)
            setTimeout(() => {
                btn.innerHTML = originalContent;
                btn.style.pointerEvents = '';
            }, 3000);
        }
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        // Press 'S' to focus search
        if (e.key === 's' || e.key === 'S') {
            if (e.target.tagName !== 'INPUT') {
                e.preventDefault();
                searchInput.focus();
            }
        }
        
        // Press 'E' to expand/collapse all
        if (e.key === 'e' || e.key === 'E') {
            if (e.target.tagName !== 'INPUT') {
                e.preventDefault();
                expandAllBtn.click();
            }
        }
    });
});
</script>

@endsection