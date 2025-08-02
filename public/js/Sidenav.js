function toggleDropdown(location) {
    const dropdown = document.getElementById(location + '-dropdown');
    const arrow = document.getElementById(location + '-arrow');
    
    // Toggle visibility
    dropdown.classList.toggle('hidden');
    
    // Rotate arrow
    if (dropdown.classList.contains('hidden')) {
        arrow.classList.remove('rotate-180');
    } else {
        arrow.classList.add('rotate-180');
    }
    
    // Close other dropdown if open
    const otherLocation = location === 'semarang' ? 'surabaya' : 'semarang';
    const otherDropdown = document.getElementById(otherLocation + '-dropdown');
    const otherArrow = document.getElementById(otherLocation + '-arrow');
    
    if (!otherDropdown.classList.contains('hidden')) {
        otherDropdown.classList.add('hidden');
        otherArrow.classList.remove('rotate-180');
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const semarangBtn = event.target.closest('[onclick="toggleDropdown(\'semarang\')"]');
    const surabayaBtn = event.target.closest('[onclick="toggleDropdown(\'surabaya\')"]');
    const semarangDropdown = document.getElementById('semarang-dropdown');
    const surabayaDropdown = document.getElementById('surabaya-dropdown');
    
    if (!semarangBtn && !semarangDropdown.contains(event.target)) {
        semarangDropdown.classList.add('hidden');
        document.getElementById('semarang-arrow').classList.remove('rotate-180');
    }
    
    if (!surabayaBtn && !surabayaDropdown.contains(event.target)) {
        surabayaDropdown.classList.add('hidden');
        document.getElementById('surabaya-arrow').classList.remove('rotate-180');
    }
});