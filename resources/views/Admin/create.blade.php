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
            <h2 class="text-xl font-bold text-green-800">Form Question Inspeksi</h2>
            
            <!-- Back Button -->
            <button onclick="history.back()" 
                class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center gap-2 
                bg-gray-100 text-gray-600 hover:bg-gray-200 border border-gray-300">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </button>
        </div>
        
        <!-- Admin Add Question Button (only visible for admin) -->
        <div class="flex gap-2">
            <button id="addQuestionBtn" 
                class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center gap-2 
                bg-blue-500 text-white hover:bg-blue-600 border">
                <i class="fa-solid fa-plus"></i>
                Tambah Pertanyaan
            </button>
        </div>
    </div>

    <form id="inspectionForm " method="POST" action="#">
        @csrf
        <!-- Inspection Info Card -->
        <div class="bg-white p-4 rounded-lg shadow-lg shadow-green-200 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Inspeksi</label>
                <select name="tipe_inspeksi" id="tipe_inspeksi" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600">
                    <option value="" >Pilih Jenis Inspeksi</option>
                    <option value="Whitewood Inspection" >Whitewood Inspection</option>
                    <option value="Metal Inspection">Metal Inspection</option>
                </select>
            </div>
        </div>

        <!-- Questions Form -->
        <div class="bg-white rounded-lg shadow-lg shadow-green-200 overflow-hidden">
            <!-- Form Header -->
            <div class="bg-green-600 text-white p-4">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-question"></i>
                    Daftar Pertanyaan Inspeksi
                </h3>
            </div>

            <!-- Form Body -->
            <div class="p-6">
                <div id="questionsContainer" class="space-y-6">
                    
                    {{-- form akan dibuat dari javascript --}}                  
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end gap-4 mt-6">
                    <button type="button" id="btnReset"
                        class="px-6 py-2 rounded-md text-sm font-medium transition-all duration-200 
                        bg-red-900 text-white hover:bg-red-200 border border-red-300">
                        Reset Formulir
                    </button>
                    <button type="submit" 
                        class="px-6 py-2 rounded-md text-sm font-medium transition-all duration-200 
                        bg-green-600 text-white hover:bg-green-700 flex items-center gap-2">
                        <i class="fa-solid fa-save"></i>
                        Simpan Inspeksi
                    </button>
                </div>
            </div>
        </div>
    </form>
    </main>

    <script src="{{ asset('js/form-question.js') }}"></script>
@endsection