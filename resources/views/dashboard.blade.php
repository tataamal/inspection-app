@extends('layout.layout')
@extends('layout.navbar')

@section('header')
  @section('sidenav')
  @endsection
  <!-- Main Content -->
      <main class="flex-1 p-4 space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center bg-white p-4 rounded shadow">
          <div class="flex items-center gap-2">
            <i class="fa-solid fa-user"></i>
            <span class="text-lg font-medium">Halo, {{ session('user_role') }}
          </div>
          <button class="bg-red-500 hover:bg-red-600 p-2 rounded text-white transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg>
          </button>
        </div>

        <!-- Statistik Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-white rounded shadow p-4 text-center">
            <p class="text-gray-600">Total Semua Data Inspeksi</p>
            <p class="text-4xl font-bold text-green-900">{{ $totalInspection }}</p>
          </div>
          <div class="bg-white rounded shadow p-4 text-center">
            <p class="text-gray-600">Jumlah Riwayat Inspeksi</p>
            <p class="text-4xl font-bold text-green-900">365</p>
          </div>
          <div class="bg-white rounded shadow p-4 text-center">
            <p class="text-gray-600">Jumlah Riwayat Reject</p>
            <p class="text-4xl font-bold text-green-900">365</p>
          </div>
        </div>

        <!-- Charts Container -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Grafik Defect Found -->
          <div class="bg-white rounded shadow p-4">
            <h2 class="text-md font-semibold mb-4 text-green-800">Grafik Kategori Defect Found</h2>
            <canvas id="defectChart"></canvas>
          </div>

          <!-- Grafik MRP Paling Sering Inspeksi -->
          <div class="bg-white rounded shadow p-4">
            <h2 class="text-md font-semibold mb-4 text-green-800">MRP Paling Sering Inspeksi</h2>
            <canvas id="mrpChart"></canvas>
          </div>
        </div>

        <!-- Detail Table MRP -->
        <div class="bg-white rounded shadow p-4">
          <h2 class="text-lg font-semibold mb-4 text-green-800">Detail Frekuensi Inspeksi per MRP</h2>
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="bg-green-100">
                <tr>
                  <th class="p-3 border">Ranking</th>
                  <th class="p-3 border">Kode MRP</th>
                  <th class="p-3 border">Jumlah Inspeksi</th>
                  <th class="p-3 border">Persentase</th>
                  <th class="p-3 border">Status Terakhir</th>
                  <th class="p-3 border">Trend</th>
                </tr>
              </thead>
              <tbody id="mrpTableBody">
                <!-- Data akan diisi oleh JavaScript -->
              </tbody>
            </table>
          </div>
        </div>

        <!-- MRP Performance Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded shadow p-4 text-center">
            <i class="fa-solid fa-trophy text-2xl mb-2"></i>
            <p class="text-sm opacity-90">MRP Terbaik</p>
            <p class="text-xl font-bold" id="bestMrp">D23</p>
          </div>
          <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded shadow p-4 text-center">
            <i class="fa-solid fa-check-circle text-2xl mb-2"></i>
            <p class="text-sm opacity-90">Total Inspeksi</p>
            <p class="text-xl font-bold" id="totalInspections">1,247</p>
          </div>
          <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded shadow p-4 text-center">
            <i class="fa-solid fa-clock text-2xl mb-2"></i>
            <p class="text-sm opacity-90">Rata-rata per Hari</p>
            <p class="text-xl font-bold" id="avgPerDay">42</p>
          </div>
          <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded shadow p-4 text-center">
            <i class="fa-solid fa-chart-line text-2xl mb-2"></i>
            <p class="text-sm opacity-90">Efisiensi</p>
            <p class="text-xl font-bold" id="efficiency">87%</p>
          </div>
        </div>
      </main>
    </div>

    <script src="{{ asset('js/Chart.js') }}"></script>
    <script src="{{ asset('js/Sidenav.js') }}"></script>
@endsection