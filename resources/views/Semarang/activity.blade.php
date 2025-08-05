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
                <a href="{{ route('PRO-semarang') }}"><button id="btn-pro"
                  class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center gap-2 
                  @if (Request::is('semarang-pro'))
                    bg-green-300 text-black hover:scale-105 active:scale-95
                  @else
                    bg-gray-100 text-gray-600 hover:bg-gray-200 border border-gray-300
                  @endif">
                  <i class="fa-solid fa-tasks"></i>
                  Task PRO
                </button></a>
                
                <a href="{{ route('DO-semarang') }}"><button id="btn-do"
                  class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center gap-2
                  @if (Request::is('semarang-do'))
                    bg-green-300 text-black hover:scale-105 active:scale-95
                  @else
                    bg-gray-100 text-gray-600 hover:bg-gray-200 border border-gray-300
                  @endif">
                    <i class="fa-solid fa-clipboard-check"></i>
                    Task DO
                </button></a>

                <a href="{{ route('Activity-semarang') }}"><button id="btn-activity"
                  class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center gap-2
                  @if (Request::is('semarang-activity'))
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
    <!-- Inspection Info Card -->
    <div class="bg-green-400 p-4 rounded-lg shadow-lg shadow-green-200 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">PRO yang akan di aktifkan</label>
            <select name="tipe_inspeksi" id="tipe_inspeksi" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600">
                <option value="" >Pilih Nomor PRO</option>
                <option value="Whitewood Inspection" >D22</option>
                <option value="Metal Inspection">D23</option>
                <option value="Metal Inspection">D24</option>
                <option value="Metal Inspection">D25</option>
                <option value="Metal Inspection">D26</option>
                <option value="Metal Inspection">D27</option>
            </select>
        </div>
    </div>

    <!-- Table Container with Fixed Header -->
    <div class="w-full border rounded-lg overflow-hidden bg-white shadow-lg shadow-green-200">
        <!-- Fixed Header -->
        <div class="bg-green-600 text-white">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr>
                        <th class="p-3 border-r border-green-500 w-16">NO</th>
                        <th class="p-3 border-r border-green-500 w-48">Material</th>
                        <th class="p-3 border-r border-green-500">Description</th>
                        <th class="p-3 border-r border-green-500 w-20">MRP</th>
                        <th class="p-3 border-r border-green-500 w-32">Order</th>
                        <th class="p-3 border-r border-green-500 w-20">QTP</th>
                        <th class="p-3 border-r border-green-500 w-20">Unit</th>
                        <th class="p-3 border-r border-green-500 w-24">Status</th>
                        <th class="p-3 w-32">Action</th>
                    </tr>
                </thead>
            </table>
        </div>

        <!-- Scrollable Body -->
        <div class="max-h-80 overflow-y-auto">
            <table class="w-full text-sm text-center">
                <tbody id="dataBody">
                    <!-- Data rows will be generated by JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Empty message (hidden by default) -->
        <div id="emptyMessage" class="hidden p-8 text-center text-gray-500">
            <i class="fa-solid fa-search text-4xl mb-4"></i>
            <p>Tidak ada data yang ditemukan</p>
        </div>
    </div>

    </main>

    <script src="{{ asset('js/Sidenav.js') }}"></script>
    <script src="{{ asset('js/Table.js') }}"></script>
@endsection