    @yield('side-admin')
    <div class="w-full">
    <button onclick="toggleDropdown('addQuestion')" 
        class="flex items-center justify-between w-full px-4 py-2 rounded-md transition duration-200
                @if (Request::is('admin/create*'))
                bg-white text-black hover:scale-105 active:scale-95
                @else
                bg-gray-800 text-white hover:bg-gray-700
                @endif">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square"></i>
            Inspection Setting
        </div>
        <i id="addQuestion-arrow" class="fa-solid fa-chevron-down transition-transform duration-200
            @if (Request::is('admin/create*')) text-black @else text-white @endif"></i>
    </button>

    <div id="addQuestion-dropdown" class="@if (!Request::is('admin/create*')) hidden @endif mt-2 ml-4 space-y-2">
        <a href="#"
            class="flex items-center gap-2 px-4 py-2 rounded-md transition duration-200 text-sm
                    @if (Request::is('admin/create') && request('section') === 'header')
                    bg-white text-black hover:scale-105 active:scale-95
                    @else
                    bg-gray-700 text-white hover:bg-gray-600
                    @endif">
            <i class="fa-solid fa-heading"></i>
            Tambah Jenis Inspeksi
        </a>
        <a href="#"
            class="flex items-center gap-2 px-4 py-2 rounded-md transition duration-200 text-sm
                    @if (Request::is('admin/create') && request('section') === 'header')
                    bg-white text-black hover:scale-105 active:scale-95
                    @else
                    bg-gray-700 text-white hover:bg-gray-600
                    @endif">
            <i class="fa-solid fa-heading"></i>
           Header Inspection
        </a>
        <a href="#"
            class="flex items-center gap-2 px-4 py-2 rounded-md transition duration-200 text-sm
                    @if (Request::is('admin/create') && request('section') === 'question')
                    bg-white text-black hover:scale-105 active:scale-95
                    @else
                    bg-gray-700 text-white hover:bg-gray-600
                    @endif">
            <i class="fa-solid fa-circle-question"></i>
            Add Question
        </a>
    </div>
</div>

{{-- Sidebar Menu untuk Mengubah Form --}}
<div class="w-full mt-2">
    <a href="#"
        class="flex items-center gap-2 w-full px-4 py-2 rounded-md transition duration-200
                @if (Request::is('admin/manage-form'))
                bg-white text-black hover:scale-105 active:scale-95
                @else
                bg-gray-800 text-white hover:bg-gray-700
                @endif">
        <i class="fa-solid fa-wrench"></i>
        Manage Form
    </a>
</div>