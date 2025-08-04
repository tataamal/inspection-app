@extends('layout.layout')

@section('header')
    <main class="flex-1 p-4 min-h-screen bg-white rounded-l-3xl shadow-inner flex flex-col">

    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-4 rounded shadow mb-4">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-user text-xl text-gray-600"></i>
            <span class="text-lg font-medium">Halo, #</span>
        </div>
        <form method="POST" action="#" class="inline">
            @csrf
            <button type="submit" class="bg-red-500 hover:bg-red-600 p-2 rounded text-white transition">
                <i class="fa-solid fa-power-off"></i>
            </button>
        </form>
    </div>

    <!-- 404 Error Content -->
    <div class="flex-1 flex items-center justify-center">
        <div class="text-center max-w-2xl mx-auto px-4">
            
            <!-- Error Icon and Number -->
            <div class="mb-8">
                <div class="relative">
                    <!-- Large 404 Text -->
                    <h1 class="text-9xl font-bold text-green-100 select-none">404</h1>
                    
                    <!-- Icon overlay -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="bg-green-600 rounded-full p-6 shadow-lg">
                            <i class="fa-solid fa-search text-white text-4xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">
                    Halaman Tidak Ditemukan
                </h2>
                <p class="text-lg text-gray-600 mb-2">
                    Oops! Halaman yang Anda cari tidak dapat ditemukan.
                </p>
                <p class="text-gray-500">
                    Halaman mungkin telah dipindahkan, dihapus, atau URL yang Anda masukkan salah.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <!-- Back Button -->
                <button onclick="history.back()" 
                    class="px-6 py-3 rounded-lg text-sm font-medium transition-all duration-200 
                    bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300 
                    flex items-center gap-2 min-w-[160px] justify-center">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali
                </button>

                <!-- Home Button -->
                <a href="#" 
                    class="px-6 py-3 rounded-lg text-sm font-medium transition-all duration-200 
                    bg-green-600 text-white hover:bg-green-700 
                    flex items-center gap-2 min-w-[160px] justify-center">
                    <i class="fa-solid fa-home"></i>
                    Ke Beranda
                </a>
            </div>

            <!-- Additional Help Links -->
            <div class="mt-12 pt-8 border-t border-gray-200">
                <p class="text-sm text-gray-500 mb-4">Atau coba halaman berikut:</p>
                <div class="flex flex-wrap justify-center gap-4 text-sm">
                    <a href="#" 
                        class="text-green-600 hover:text-green-800 transition-colors flex items-center gap-1">
                        <i class="fa-solid fa-clipboard-check text-xs"></i>
                        Daftar Inspeksi
                    </a>
                    <span class="text-gray-300">|</span>
                    <a href="#" 
                        class="text-green-600 hover:text-green-800 transition-colors flex items-center gap-1">
                        <i class="fa-solid fa-chart-bar text-xs"></i>
                        Laporan
                    </a>
                    <span class="text-gray-300">|</span>
                    <a href="#" 
                        class="text-green-600 hover:text-green-800 transition-colors flex items-center gap-1">
                        <i class="fa-solid fa-user-cog text-xs"></i>
                        Profil
                    </a>
                </div>
            </div>

            <!-- Contact Support (Optional) -->
            <div class="mt-8 p-4 bg-green-50 rounded-lg border border-green-200">
                <p class="text-sm text-green-700">
                    <i class="fa-solid fa-info-circle mr-2"></i>
                    Butuh bantuan? Hubungi administrator sistem untuk mendapatkan dukungan teknis.
                </p>
            </div>

        </div>
    </div>

    <!-- Footer with Error Code (Optional) -->
    <div class="mt-auto pt-8">
        <div class="text-center text-xs text-gray-400">
            <p>Error Code: 404 | {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Request URL: {{ request()->fullUrl() }}</p>
        </div>
    </div>

    </main>

    <style>
        /* Additional CSS for smooth animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-pulse-subtle {
            animation: pulse 2s infinite;
        }

        /* Hover effects for buttons */
        button:hover,
        a:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Custom scrollbar for consistency */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #16a34a;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #15803d;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add fade-in animation to main content
            const mainContent = document.querySelector('main > div');
            if (mainContent) {
                mainContent.classList.add('animate-fade-in-up');
            }

            // Add subtle pulse animation to the icon
            const errorIcon = document.querySelector('.fa-search').parentElement;
            if (errorIcon) {
                errorIcon.classList.add('animate-pulse-subtle');
            }

            // Track 404 errors (optional - for analytics)
            if (typeof gtag !== 'undefined') {
                gtag('event', 'page_not_found', {
                    'event_category': 'Error',
                    'event_label': window.location.pathname,
                    'value': 1
                });
            }

            // Auto-focus on back button for keyboard navigation
            const backButton = document.querySelector('button[onclick="history.back()"]');
            if (backButton) {
                backButton.focus();
            }

            // Add keyboard shortcut for quick navigation
            document.addEventListener('keydown', function(e) {
                // Press 'H' to go to home
                if (e.key === 'h' || e.key === 'H') {
                    const homeLink = document.querySelector('a[href*="dashboard"], a[href="/"]');
                    if (homeLink && !e.ctrlKey && !e.altKey && !e.metaKey) {
                        homeLink.click();
                    }
                }
                
                // Press 'B' to go back
                if (e.key === 'b' || e.key === 'B') {
                    if (!e.ctrlKey && !e.altKey && !e.metaKey) {
                        history.back();
                    }
                }
            });

            // Show keyboard shortcuts hint after 5 seconds
            setTimeout(function() {
                const hint = document.createElement('div');
                hint.className = 'fixed bottom-4 right-4 bg-gray-800 text-white text-xs px-3 py-2 rounded shadow-lg opacity-0 transition-opacity duration-300';
                hint.innerHTML = '<i class="fa-solid fa-keyboard mr-1"></i> Tekan H untuk beranda, B untuk kembali';
                document.body.appendChild(hint);
                
                setTimeout(() => hint.style.opacity = '1', 100);
                setTimeout(() => {
                    hint.style.opacity = '0';
                    setTimeout(() => hint.remove(), 300);
                }, 3000);
            }, 5000);
        });

        // Service Worker registration for offline support (optional)
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(function(error) {
                console.log('Service Worker registration failed:', error);
            });
        }
    </script>
@endsection