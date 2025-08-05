@extends('layout.layout')
@extends('layout.navbar')

@section('header')
    @section('sidenav')
    @endsection
    
    {{-- Container utama dengan padding responsif dan posisi di tengah --}}
    <main class="w-full max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
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
        {{-- Header Halaman --}}
        <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
            <div class="flex items-center gap-4 flex-wrap">
                <h1 class="text-xl md:text-2xl font-bold text-gray-800">Formulir Jenis Inspeksi</h1>
                
                {{-- Tombol Kembali --}}
                <a href="#" {{-- Ganti dengan URL atau route yang sesuai --}}
                class="px-4 py-2 rounded-md text-sm font-medium right-1 transition-all duration-200 flex items-center gap-2 
                        bg-gray-100 text-gray-600 hover:bg-gray-200 border border-gray-300">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali
                </a>
            </div>
        </div>

        {{-- Form Container dalam bentuk Kartu --}}
        <form id="inspectionForm" method="POST" action="#">
            @csrf
            <div class="bg-white rounded-lg shadow-lg shadow-green-200 overflow-hidden">
                
                {{-- Header Kartu Form --}}
                <div class="bg-green-600 text-white p-4">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <i class="fa-solid fa-plus-circle"></i>
                        Tambah Jenis Inspeksi Baru
                    </h3>
                </div>

                {{-- Body Kartu Form --}}
                <div class="p-6 md:p-8">
                    
                    {{-- Notifikasi Sukses (jika ada) --}}
                    @if (session('success'))
                        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
                            class="flex justify-between items-center bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md relative mb-6" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                            <button @click="show = false" class="text-green-600 hover:text-green-800" aria-label="Close">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><title>Tutup</title><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>
                    @endif

                    <div class="space-y-6">
                        {{-- Input Nama Jenis Inspeksi --}}
                        <div>
                            <label for="nama_jenis_inspeksi" class="block text-sm font-medium text-gray-700 mb-1">
                                Nama Jenis Inspeksi <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama_jenis_inspeksi" id="nama_jenis_inspeksi"
                                class="block w-full rounded-md shadow-sm p-4 mt-4 border-2 border-green-200 focus:ring-green-500 focus:border-green-500 sm:text-sm @error('nama_jenis_inspeksi') border-red-500 @enderror"
                                placeholder="Contoh: Inspeksi Kualitas Awal"
                                value=""
                                required>
                            @error('nama_jenis_inspeksi')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Input Deskripsi --}}
                        <div>
                            <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">
                                Deskripsi
                            </label>
                            <textarea id="deskripsi" name="deskripsi" rows="4"
                                    class="block w-full border-2 border-green-200 rounded-md shadow-sm p-4 mt-4 mb-20 focus:ring-green-500 focus:border-green-500 sm:text-sm @error('deskripsi') border-red-500 @enderror"
                                    placeholder="Jelaskan tujuan dan ruang lingkup dari jenis inspeksi ini."></textarea>
                            @error('deskripsi')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Aksi Form --}}
                    <div class="flex justify-end gap-4 mt-8 pt-5 border-t border-gray-200">
                        <a href="#" {{-- Ganti dengan URL atau route yang sesuai --}}
                        class="px-6 py-2 rounded-md text-sm font-medium transition-all duration-200 
                                bg-gray-200 text-gray-700 hover:bg-gray-300 border border-gray-300">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 rounded-md text-sm font-medium transition-all duration-200 
                                    bg-green-600 text-white hover:bg-green-700 flex items-center gap-2">
                            <i class="fa-solid fa-save"></i>
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>
@endsection