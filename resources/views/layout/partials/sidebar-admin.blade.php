    @yield('side-admin')
    <div class="w-full">
    <a href="{{ route('inspection-types.index') }}"><button
        class="flex items-center justify-between w-full px-4 py-2 rounded-md transition duration-200
                @if (Request::is('inspection-types*'))
                bg-white text-black hover:scale-105 active:scale-95
                @else
                bg-gray-800 text-white hover:bg-gray-700
                @endif">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square"></i>
            Kelola Checklist Inspeksi
        </div>
    </button></a>
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