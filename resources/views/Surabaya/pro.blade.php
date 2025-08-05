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
        <span class="text-lg font-medium">Halo, {{ session('sap_user') }}</span>
        </div>
        <button class="bg-red-500 hover:bg-red-600 p-2 rounded text-white transition">
        <i class="fa-solid fa-power-off"></i>
        </button>
    </div>

    <div class="flex justify-between items-center mb-4 flex-wrap gap-4">
        <div class="flex items-center gap-4 flex-wrap">
            <h2 class="text-xl font-bold text-green-800">Tabel Inspeksi Surabaya</h2>
            
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

    <!-- Scrollable Body -->
    <div class="max-h-96 mt-5 overflow-y-auto border border-gray-200 rounded-lg">
        <table class="w-full text-sm text-left text-gray-600">
            <!-- Table Header -->
            <thead class="bg-green-700 text-gray-50 uppercase text-xs font-semibold sticky top-0 z-10">
                <tr class="border-b border-gray-200">
                    <th class="p-3 w-12 text-center">No</th>
                    <th class="p-3 w-24">Material</th>
                    <th class="p-3 min-w-48">Deskripsi Material</th>
                    <th class="p-3 w-20">Dispo</th>
                    <th class="p-3 w-24">Order</th>
                    <th class="p-3 w-20 text-right">Qty</th>
                    <th class="p-3 w-16 text-center">Unit</th>
                    <th class="p-3 w-20 text-center">Status</th>
                    <th class="p-3 w-24">Batch</th>
                    <th class="p-3 w-20">Storage</th>
                    <th class="p-3 w-20 text-center">Action</th>
                </tr>
            </thead>
            
            <!-- Table Body -->
            <tbody id="dataBody" class="bg-white">
                @foreach ($data as $index => $row)
                    @php
                        $statusClass = $row->STATS === 'TECO' 
                            ? 'bg-green-100 text-green-800 border-green-200' 
                            : 'bg-yellow-100 text-yellow-800 border-yellow-200';
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors duration-150 border-b border-gray-100">
                        <td class="p-3 text-center font-medium">{{ $index + 1 }}</td>
                        <td class="p-3 font-mono text-xs">{{ $row->MATNR }}</td>
                        <td class="p-3 max-w-xs">
                            <div class="truncate" title="{{ $row->KTEXTMAT }}">
                                {{ $row->KTEXTMAT }}
                            </div>
                        </td>
                        <td class="p-3 font-mono text-xs">{{ $row->DISPO }}</td>
                        <td class="p-3 font-mono text-xs">{{ $row->AUFNR }}</td>
                        <td class="p-3 text-right font-semibold">{{ number_format($row->LMENGEZUB, 0, ',', '.') }}</td>
                        <td class="p-3 text-center text-xs font-medium">
                            {{ $row->MENGENEINH === 'ST' ? 'PC' : $row->MENGENEINH }}
                        </td>
                        <td class="p-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold border {{ $statusClass }}">
                                {{ $row->STATS }}
                            </span>
                        </td>
                        <td class="p-3 font-mono text-xs">{{ $row->CHARG }}</td>
                        <td class="p-3 font-mono text-xs">{{ $row->LAGORTCHRG }}</td>
                        <td class="p-3 text-center">
                            @if ($row->STATS === 'TECO')
                                <button class=" bg-yellow-500 hover:bg-yellow-600text-white px-3 py-1.5 rounded-md font-medium text-xs transition-colors duration-150 shadow-sm">
                                    UNTECO
                                </button>
                            @else
                                <a href="{{ route('inspection-surabaya', $row->AUFNR) }}"></a><button class=" bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-md font-medium text-xs transition-colors duration-150 shadow-sm">
                                    ACCEPT
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

      <!-- Empty message -->
      <div id="emptyMessage" class="hidden p-8 text-center text-gray-500">
          <i class="fa-solid fa-search text-4xl mb-4"></i>
          <p>Tidak ada data yang ditemukan</p>
      </div>
  </div>

    </main>

    <script src="{{ asset('js/Sidenav.js') }}"></script>
    <script src="{{ asset('js/Table.js') }}"></script>
@endsection