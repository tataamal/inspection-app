@yield('sidenav')
<div class="flex flex-col lg:flex-row">
    <!-- Sidebar -->
    <aside class="bg-gray-900 text-white w-full lg:w-1/5 p-4 space-y-6 flex flex-col items-center">
        <div class="w-20 h-20 rounded-full bg-white p-2 flex items-center justify-center">
            <img src="{{ asset('images/KMI.png') }}" alt="">
        </div>
      <h1 class="text-center text-lg font-semibold">Product Quality Inspection<br>PT. Kayu Mabel Indonesia</h1>
      <nav class="w-full mt-6 space-y-5">
        <a href="{{ route('dashboard') }}"
          class="flex items-center gap-2 px-4 py-2 rounded-md transition duration-200
                  @if (Request::is('dashboard'))
                    bg-white text-black hover:scale-105 active:scale-95
                  @else
                    bg-gray-800 text-white hover:bg-gray-700
                  @endif">
          <i class="fa-solid fa-house"></i>
          Dashboard
        </a>
        <div class="w-full">
          <button onclick="toggleDropdown('semarang')" 
              class="flex items-center justify-between w-full px-4 py-2 rounded-md transition duration-200
                      @if (Request::is('semarang-pro*'))
                        bg-white text-black hover:scale-105 active:scale-95
                      @else
                        bg-gray-800 text-white hover:bg-gray-700
                      @endif">
              <div class="flex items-center gap-2">
                  <i class="fa-solid fa-magnifying-glass"></i>
                  WC Semarang
              </div>
              <i id="semarang-arrow" class="fa-solid fa-chevron-down transition-transform duration-200
                  @if (Request::is('semarang-pro*')) text-black @else text-white @endif"></i>
          </button>
          
          <div id="semarang-dropdown" class="@if (!Request::is('semarang-pro*')) hidden @endif mt-2 ml-4 space-y-2">
                <a href="{{ route('PRO-semarang') }}" 
                    class="flex items-center gap-2 px-4 py-2 rounded-md transition duration-200 text-sm
                          @if (Request::is('semarang-pro'))
                            bg-white text-black hover:scale-105 active:scale-95
                          @else
                            bg-gray-700 text-white hover:bg-gray-600
                          @endif">
                    <i class="fa-solid fa-list"></i>
                    List PRO
                </a>
                <a href="{{ route('DO-semarang') }}" 
                    class="flex items-center gap-2 px-4 py-2 rounded-md transition duration-200 text-sm
                          @if (Request::is('semarang-do'))
                            bg-white text-black hover:scale-105 active:scale-95
                          @else
                            bg-gray-700 text-white hover:bg-gray-600
                          @endif">
                    <i class="fa-solid fa-clipboard-list"></i>
                    List DO
                </a>
                <a href="#" 
                    class="flex items-center gap-2 px-4 py-2 rounded-md transition duration-200 text-sm
                          @if (Request::is('semarang-activity'))
                            bg-white text-black hover:scale-105 active:scale-95
                          @else
                            bg-gray-700 text-white hover:bg-gray-600
                          @endif">
                    <i class="fa-solid fa-clipboard-list"></i>
                    List Activity
                </a>
            </div>
        </div>
        <div class="w-full">
            <button onclick="toggleDropdown('surabaya')" 
                class="flex items-center justify-between w-full px-4 py-2 rounded-md transition duration-200
                        @if (Request::is('surabaya-pro*'))
                          bg-white text-black hover:scale-105 active:scale-95
                        @else
                          bg-gray-800 text-white hover:bg-gray-700
                        @endif">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    WC Surabaya
                </div>
                <i id="surabaya-arrow" class="fa-solid fa-chevron-down transition-transform duration-200
                    @if (Request::is('surabaya-pro*')) text-black @else text-white @endif"></i>
            </button>
            
            <div id="surabaya-dropdown" class="@if (!Request::is('surabaya-pro')) hidden @endif mt-2 ml-4 space-y-2">
                <a href="{{ route('PRO-surabaya') }}" 
                    class="flex items-center gap-2 px-4 py-2 rounded-md transition duration-200 text-sm
                          @if (Request::is('surabaya-pro'))
                            bg-white text-black hover:scale-105 active:scale-95
                          @else
                            bg-gray-700 text-white hover:bg-gray-600
                          @endif">
                    <i class="fa-solid fa-list"></i>
                    List PRO
                </a>
                <a href="{{ route('DO-surabaya') }}" 
                    class="flex items-center gap-2 px-4 py-2 rounded-md transition duration-200 text-sm
                          @if (Request::is('inspeksi-surabaya/do'))
                            bg-white text-black hover:scale-105 active:scale-95
                          @else
                            bg-gray-700 text-white hover:bg-gray-600
                          @endif">
                    <i class="fa-solid fa-clipboard-list"></i>
                    List DO
                </a>
                <a href="#" 
                    class="flex items-center gap-2 px-4 py-2 rounded-md transition duration-200 text-sm
                          @if (Request::is('inspeksi-surabaya/do'))
                            bg-white text-black hover:scale-105 active:scale-95
                          @else
                            bg-gray-700 text-white hover:bg-gray-600
                          @endif">
                    <i class="fa-solid fa-clipboard-list"></i>
                    List Activity
                </a>
            </div>
        </div>
      </nav>
      <form action="{{ route('logout') }}" method="POST">
      @csrf
      <button class="mt-auto top-1 bg-red-600 hover:bg-red-700 active:scale-95 text-white px-4 py-2 rounded-md flex items-center gap-2">
        <i class="fa-solid fa-power-off"></i>
        Keluar
      </button></form>
    </aside>