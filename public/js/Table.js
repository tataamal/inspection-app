// Generate table data
function generateTableData() {
    const tbody = document.getElementById('dataBody');
    let html = '';
    
    for (let i = 1; i <= 50; i++) {
        const status = i % 2 === 0 ? 'REL' : 'TECO';
        const statusClass = status === 'REL' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800';
        
        html += `
            <tr class="hover:bg-green-50 transition border-b">
                <td class="p-3 w-16">${i}</td>
                <td class="p-3 w-48">000000000${200000 + i}</td>
                <td class="p-3">NIGHTST. SEA SALT W/SELENITE P816N2 PTG</td>
                <td class="p-3 w-20">D23</td>
                <td class="p-3 w-32">34100012${8000 + i}</td>
                <td class="p-3 w-20">2</td>
                <td class="p-3 w-20">PC</td>
                <td class="p-3 w-24">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold ${statusClass}">${status}</span>
                </td>
                <td class="p-3 w-32">
                    <button class="bg-lime-400 hover:bg-lime-500 text-black px-3 py-1 rounded-full font-semibold transition text-xs">Accept</button>
                </td>
            </tr>
        `;
    }
    
    tbody.innerHTML = html;
}

// Search functionality
function initializeSearch() {
    const searchInput = document.getElementById("searchInput");
    const emptyMessage = document.getElementById("emptyMessage");

    searchInput.addEventListener("input", () => {
        const term = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll("#dataBody tr");
        let found = false;

        rows.forEach(row => {
            const match = row.textContent.toLowerCase().includes(term);
            row.style.display = match ? '' : 'none';
            if (match) found = true;
        });

        // Show/hide empty message
        const tableContainer = document.querySelector('.max-h-80');
        if (found) {
            emptyMessage.classList.add('hidden');
            tableContainer.classList.remove('hidden');
        } else {
            emptyMessage.classList.remove('hidden');
            tableContainer.classList.add('hidden');
        }
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    generateTableData();
    initializeSearch();
});
