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

    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
        <div class="flex items-center gap-4 flex-wrap">
            <h2 class="text-xl font-bold text-yellow-800">Edit Form Inspeksi</h2>
            <a href="#" 
               class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center gap-2 
               bg-gray-100 text-gray-600 hover:bg-gray-200 border border-gray-300">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <form action="#" method="POST" id="inspectionForm">
        @csrf
        @method('PUT')

        <!-- Tipe Inspeksi -->
        <div class="bg-white p-4 rounded-lg shadow-lg shadow-yellow-200 mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Inspeksi</label>
            <input type="text" name="tipe_inspeksi" value="#" readonly
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600">
        </div>

        <!-- Pertanyaan -->
        <div class="bg-white rounded-lg shadow-lg shadow-yellow-200 overflow-hidden">
            <div class="bg-yellow-600 text-white p-4">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-question"></i>
                    Daftar Pertanyaan
                </h3>
            </div>

            <div class="p-6 space-y-6">
                {{-- @foreach($questions as $i => $q) --}}
                    <input type="hidden" name="question_ids[]" value="#">
                    <div class="question-item border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                            <input type="text" name="sections[]" value="#"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pertanyaan</label>
                            <input type="text" name="questions[]" value="#"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        </div>
                    </div>
                {{-- @endforeach --}}
            </div>

            <div class="flex justify-end gap-4 mt-6 p-6">
                <a href="#"
                   class="px-6 py-2 rounded-md text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 border border-gray-300">
                   Batal
                </a>
                <button type="submit" 
                    class="px-6 py-2 rounded-md text-sm font-medium bg-yellow-600 text-white hover:bg-yellow-700">
                    <i class="fa-solid fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</main>

@endsection