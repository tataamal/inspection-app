// Fungsi untuk membuat search berfungsi
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('dataBody');
    
    // Fungsi untuk melakukan pencarian
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const rows = tableBody.getElementsByTagName('tr');
        let visibleRowCount = 0;
        
        // Loop through semua baris dalam tabel
        Array.from(rows).forEach(function(row) {
            const cells = row.getElementsByTagName('td');
            let rowText = '';
            
            // Gabungkan semua text dari setiap cell
            Array.from(cells).forEach(function(cell) {
                // Skip cell yang berisi button (kolom action)
                if (!cell.querySelector('button')) {
                    rowText += cell.textContent.toLowerCase() + ' ';
                }
            });
            
            // Tampilkan atau sembunyikan baris berdasarkan pencarian
            if (searchTerm === '' || rowText.includes(searchTerm)) {
                row.style.display = '';
                visibleRowCount++;
                
                // Update nomor urut untuk baris yang terlihat
                const numberCell = row.cells[0];
                if (numberCell) {
                    numberCell.textContent = visibleRowCount;
                }
            } else {
                row.style.display = 'none';
            }
        });
        
        // Tampilkan pesan jika tidak ada hasil
        showNoResultsMessage(visibleRowCount === 0 && searchTerm !== '');
    }
    
    // Fungsi untuk menampilkan pesan "tidak ditemukan"
    function showNoResultsMessage(show) {
        let noResultsRow = document.getElementById('noResultsRow');
        
        if (show) {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.id = 'noResultsRow';
                noResultsRow.innerHTML = `
                    <td colspan="11" class="p-8 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fa fa-search text-4xl mb-3 text-gray-300"></i>
                            <p class="text-lg font-semibold">Data tidak ditemukan</p>
                            <p class="text-sm">Coba ubah kata kunci pencarian Anda</p>
                        </div>
                    </td>
                `;
                tableBody.appendChild(noResultsRow);
            }
        } else {
            if (noResultsRow) {
                noResultsRow.remove();
            }
        }
    }
    
    // Event listener untuk input pencarian
    searchInput.addEventListener('input', function() {
        performSearch();
    });
    
    // Event listener untuk Enter key
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });
    
    // Fungsi untuk reset pencarian
    function resetSearch() {
        searchInput.value = '';
        performSearch();
    }
    
    // Optional: Tambahkan tombol clear di dalam input
    function addClearButton() {
        const searchContainer = searchInput.parentElement;
        searchContainer.classList.add('relative');
        
        const clearButton = document.createElement('button');
        clearButton.innerHTML = '×';
        clearButton.className = 'absolute right-8 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xl font-bold';
        clearButton.style.display = 'none';
        clearButton.onclick = resetSearch;
        
        searchContainer.appendChild(clearButton);
        
        // Tampilkan/sembunyikan tombol clear
        searchInput.addEventListener('input', function() {
            clearButton.style.display = this.value ? 'block' : 'none';
        });
    }
    
    // Inisialisasi tombol clear
    addClearButton();
});

// Fungsi tambahan untuk pencarian berdasarkan kolom tertentu
function searchByColumn(columnIndex, searchTerm) {
    const tableBody = document.getElementById('dataBody');
    const rows = tableBody.getElementsByTagName('tr');
    
    Array.from(rows).forEach(function(row) {
        const cell = row.cells[columnIndex];
        const cellText = cell ? cell.textContent.toLowerCase() : '';
        
        if (searchTerm === '' || cellText.includes(searchTerm.toLowerCase())) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Fungsi untuk highlight text yang dicari
function highlightSearchTerm(searchTerm) {
    const tableBody = document.getElementById('dataBody');
    const rows = tableBody.getElementsByTagName('tr');
    
    Array.from(rows).forEach(function(row) {
        if (row.style.display !== 'none') {
            const cells = row.getElementsByTagName('td');
            Array.from(cells).forEach(function(cell) {
                if (!cell.querySelector('button') && !cell.querySelector('span')) {
                    let cellHTML = cell.innerHTML;
                    
                    // Remove existing highlights
                    cellHTML = cellHTML.replace(/<mark class="bg-yellow-200">(.*?)<\/mark>/gi, '$1');
                    
                    if (searchTerm && searchTerm.length > 0) {
                        const regex = new RegExp(`(${searchTerm})`, 'gi');
                        cellHTML = cellHTML.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
                    }
                    
                    cell.innerHTML = cellHTML;
                }
            });
        }
    });
}