@extends('layout.layout')
@extends('layout.navbar')

@section('header')
@section('sidenav')
@endsection
    <main class="flex-1 p-4 min-h-screen bg-white rounded-l-3xl shadow-inner flex flex-col">

    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-4 rounded shadow mb-4">
        <div class="flex items-center gap-2">
        <i class="fa-solid fa-user text-xl text-gray-600"></i>
        <span class="text-lg font-medium">Halo, Username</span>
        </div>
        <button class="bg-red-500 hover:bg-red-600 p-2 rounded text-white transition">
        <i class="fa-solid fa-power-off"></i>
        </button>
    </div>

    <div class="flex justify-between items-center mb-4 flex-wrap gap-4">
        <div class="flex items-center gap-4 flex-wrap">
            <h2 class="text-xl font-bold text-green-800">Data Riwayat Inspeksi</h2>
            
            <!-- Navigation Buttons -->
            <div class="flex gap-2">
                <a href="{{ route('PRO-surabaya') }}"><button id="btn-pro"
                  class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center gap-2 
                  @if (Request::is('surabaya-pro'))
                    bg-green-300 text-black hover:scale-105 active:scale-95
                  @else
                    bg-gray-100 text-gray-600 hover:bg-gray-200 border border-gray-300
                  @endif">
                  <i class="fa-solid fa-tasks"></i>
                  Task PRO
                </button></a>
                
                <a href="{{ route('DO-surabaya') }}"><button id="btn-do"
                  class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center gap-2
                  @if (Request::is('surabaya-do'))
                    bg-green-300 text-black hover:scale-105 active:scale-95
                  @else
                    bg-gray-100 text-gray-600 hover:bg-gray-200 border border-gray-300
                  @endif">
                    <i class="fa-solid fa-clipboard-check"></i>
                    Task DO
                </button></a>

                <a href="{{ route('Activity-surabaya') }}"><button id="btn-activity"
                  class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center gap-2
                  @if (Request::is('surabaya-activity'))
                    bg-green-300 text-black hover:scale-105 active:scale-95
                  @else
                    bg-gray-100 text-gray-600 hover:bg-gray-200 border border-gray-300
                  @endif">
                    <i class="fa-solid fa-clipboard-check"></i>
                    Activity
                </button></a>
            </div>
        </div>
        
        <!-- Search Input -->
        <div class="relative w-64">
            <input type="text" id="searchInput" placeholder="Anda Mau Cari Apa?" 
                   class="w-full px-4 py-2 border rounded-full shadow focus:outline-none focus:ring-2 focus:ring-green-500" />
            <i class="fa fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        </div>
    </div>

    <!-- Table Container -->
    <div class="w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
        <!-- Scrollable Container -->
        <div class="max-h-80 overflow-y-auto">
            <table class="w-full text-sm">
                <!-- Fixed Header -->
                <thead class="bg-green-600 text-white sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-3 text-center font-semibold border-r border-green-500" style="width: 80px;">NO</th>
                        <th class="px-4 py-3 text-center font-semibold border-r border-green-500" style="width: 200px;">Kode PO</th>
                        <th class="px-4 py-3 text-center font-semibold border-r border-green-500">Kode Item</th>
                        <th class="px-4 py-3 text-center font-semibold border-r border-green-500" style="width: 150px;">Buyer</th>
                        <th class="px-4 py-3 text-center font-semibold" style="width: 120px;">Action</th>
                    </tr>
                </thead>
                
                <!-- Table Body -->
                <tbody id="dataBody">
                    @foreach ($data as $index => $row)
                    <tr class="border-b border-gray-100 hover:bg-green-50 transition-colors {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                        <td class="px-4 py-3 text-center font-medium border-r border-gray-100" style="width: 80px;">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 text-center font-medium border-r border-gray-100" style="width: 200px;">{{ $row->LS_VBELN }}</td>
                        <td class="px-4 py-3 text-center font-medium border-r border-gray-100">{{ $row->LS_POSNR }}</td>
                        <td class="px-4 py-3 text-center font-medium border-r border-gray-100" style="width: 150px;">{{ $row->LS_KUNAG }}</td>
                        <td class="px-4 py-3 text-center" style="width: 120px;">
                            <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-1.5 rounded-md font-medium text-xs transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                                ACCEPT
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="emptyMessage" class="hidden">
            <div class="p-12 text-center text-gray-500 bg-gray-50">
                <div class="flex flex-col items-center">
                    <i class="fa-solid fa-inbox text-4xl mb-4 text-gray-300"></i>
                    <p class="text-lg font-semibold mb-2">Tidak ada data yang ditemukan</p>
                    <p class="text-sm text-gray-400">Silakan coba lagi atau periksa filter pencarian</p>
                </div>
            </div>
        </div>

    </main>

    <script src="{{ asset('js/Sidenav.js') }}"></script>
    <script src="{{ asset('js/Table.js') }}"></script>
@endsection