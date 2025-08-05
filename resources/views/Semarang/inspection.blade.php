@extends('layout.layout')
@extends('layout.navbar')

@section('header')
@section('sidenav')
@endsection

@php
use Illuminate\Support\Str;
@endphp

<main class="flex-1 p-4 space-y-6">
    <div class="flex justify-between items-center bg-white p-4 rounded shadow">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-user text-xl text-gray-600"></i>
            <span class="text-lg font-medium">Halo, {{ session('username') }}</span>
        </div>
        <button class="bg-red-500 hover:bg-red-600 p-2 rounded text-white transition">
            <i class="fa-solid fa-right-from-bracket"></i>
        </button>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <form action="#" method="POST" enctype="multipart/form-data">
        @csrf
        <!-- Hidden Inputs untuk data yang dibaca dari SAP -->
        <input type="hidden" name="prueflos" value="#">
        <input type="hidden" name="charg" value="#">
        <input type="hidden" name="inspection_date" value="#">
        <input type="hidden" name="unit" value="#">
        <input type="hidden" name="location" value="#">
        <input type="hidden" name="ktexmat" value="#">
        <input type="hidden" name="dispo" value="#">
        <input type="hidden" name="mengeneinh" value="#">
        <input type="hidden" name="lagortchrg" value="#">
        <input type="hidden" name="kdpos" value="#">
        <input type="hidden" name="kdauf" value="#">
        <input type="hidden" name="matnr" value="#">
        <input type="hidden" name="plant" value="3000">

        {{-- Section 1 - Inspection Info --}}
        <div class="bg-white rounded shadow p-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                <!-- Judul & Deskripsi -->
                <div class="mb-2 md:mb-0">
                    <h5 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fa-solid fa-clipboard-check text-blue-600 mr-2"></i> Inspection Detail
                    </h5>
                </div>
                <!-- Tanggal -->
                <div class="text-sm font-bold text-blue-600">
                    <i class="fa-solid fa-calendar mr-1"></i> Date Of Inspection: {{ date('d/m/Y') }}
                </div>
            </div>

            <!-- Baris ke-2: PRUEFLOS dan CHARG -->
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center bg-blue-600 text-white text-sm font-medium px-3 py-2 rounded-full shadow-sm">
                    <i class="fa-solid fa-hashtag mr-1"></i>Number Inspection: #
                </span>
                <span class="inline-flex items-center bg-blue-600 text-white text-sm font-medium px-3 py-2 rounded-full shadow-sm">
                    #
                </span>
            </div>
        </div>

        <!-- Checklist + Ilustrasi dalam 1 Card -->
        <div class="bg-white rounded shadow p-4 mb-4">
            <h6 class="text-base font-semibold mb-4 text-gray-700 flex items-center">
                <i class="fa-solid fa-list-check mr-2 text-gray-600"></i>Inspection Details
            </h6>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                <!-- Checklist Scrollable Grid: 6 Kolom -->
                <div class="md:col-span-8">
                    <div class="flex overflow-x-auto border border-gray-200 shadow-sm rounded-lg gap-3 p-3 bg-gray-50">
                        @php
                        $inspectionItems = [
                            "Dimension", "Oversize", "Undersize", "Misalignment", "Distortion",
                            "Surface", "Roughness", "Color", "Telegraphic and Dislamination", "Dirtiness and Tidiness",
                            "Material Defect", "Wrong Material", "Damage", "Wave", "Hairlaine",
                            "Corrosion", "Chipping", "Operator Error", "Machine Malfunction", "Tool Wear",
                            "Incorrect Setup Mechine"
                        ];

                        // Bagi menjadi grup per 6 item
                        $chunks = array_chunk($inspectionItems, 6);
                        @endphp

                        @foreach($chunks as $groupIndex => $group)
                        <div class="flex-shrink-0 min-w-48">
                            @foreach($group as $index => $item)
                            <div class="flex items-center mb-2">
                                <input class="mr-2 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                        type="checkbox" 
                                        name="inspection_items[]" 
                                        id="item{{ $groupIndex }}_{{ $index }}" 
                                        value="{{ $item }}">
                                <label class="text-sm text-gray-700 cursor-pointer" for="item{{ $groupIndex }}_{{ $index }}">
                                    <i class="fa-solid fa-pencil mr-1"></i>{{ $item }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Details: 4 Kolom -->
                <div class="md:col-span-4">
                    <div class="bg-gray-50 border border-gray-200 shadow-sm rounded-lg p-3 h-auto">
                        <h6 class="text-base font-semibold mb-3 text-gray-700 flex items-center">
                            <i class="fa-solid fa-info-circle mr-2 text-gray-600"></i>Inspection Details:
                        </h6>
                        <div class="mb-3">
                            <label for="causeEffect" class="block text-sm font-semibold text-gray-700 mb-1">
                                <i class="fa-solid fa-exclamation-triangle text-yellow-500 mr-1"></i>Cause Effect
                            </label>
                            <textarea name="cause_effect" 
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                        id="causeEffect" 
                                        rows="2" 
                                        placeholder="Penyebab..."></textarea>
                        </div>
                        <div class="mb-2">
                            <label for="correction" class="block text-sm font-semibold text-gray-700 mb-1">
                                <i class="fa-solid fa-wrench text-green-500 mr-1"></i>Correction
                            </label>
                            <textarea name="correction" 
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                        id="correction" 
                                        rows="2" 
                                        placeholder="Tindakan..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2 - Grid Layout 4 Kotak Atas --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            {{-- Section Image Upload box 1 --}}
            <div class="bg-white rounded shadow p-4">
                <h6 class="text-base font-semibold mb-3 text-gray-700 flex items-center">
                    <i class="fa-solid fa-camera mr-2 text-gray-600"></i>Dokumentasi Gambar
                </h6>
                @php
                    $box1 = ['Tampilan Depan', 'Tampilan Belakang'];
                @endphp

                @foreach ($box1 as $index => $view)
                    @php $slug = Str::slug($view); @endphp
                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-2">
                            <i class="fa-solid fa-image mr-1"></i>{{ $view }}
                        </label>

                        <!-- Tombol Kamera -->
                        <button type="button" 
                                class="w-full px-3 py-2 text-sm border border-gray-300 text-gray-700 bg-white rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                onclick="openCamera('camera_{{ $slug }}', 'canvas_{{ $slug }}', 'preview_{{ $slug }}', 'input_{{ $slug }}', 'captureBtn_{{ $slug }}')">
                            <i class="fa-solid fa-video mr-1"></i> Gunakan Kamera
                        </button>

                        <!-- Video Preview -->
                        <video id="camera_{{ $slug }}" class="mt-2 w-full hidden rounded-md" autoplay playsinline muted style="max-height: 200px;"></video>

                        <!-- Tombol Capture & Batalkan dalam 1 baris -->
                        <div class="flex gap-2 mt-2 w-full">
                            <!-- Tombol Capture -->
                            <button type="button" 
                                    class="flex-1 px-3 py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 hidden" 
                                    id="captureBtn_{{ $slug }}"
                                    onclick="captureImage('camera_{{ $slug }}', 'canvas_{{ $slug }}', 'preview_{{ $slug }}', 'input_{{ $slug }}', 'captureBtn_{{ $slug }}', 'cancelBtn_{{ $slug }}')">
                                <i class="fa-solid fa-camera mr-1"></i> Capture
                            </button>

                            <!-- Tombol Batalkan -->
                            <button type="button" 
                                    class="flex-1 px-3 py-2 text-sm bg-red-600 text-white rounded-md hover:bg-red-700 hidden" 
                                    id="cancelBtn_{{ $slug }}"
                                    onclick="cancelCamera('camera_{{ $slug }}', 'captureBtn_{{ $slug }}', 'cancelBtn_{{ $slug }}')">
                                <i class="fa-solid fa-times mr-1"></i> Batalkan
                            </button>
                        </div>

                        <!-- Tombol Ulangi -->
                        <button type="button" 
                                class="w-full mt-2 px-3 py-2 text-sm border border-red-300 text-red-600 bg-white rounded-md hover:bg-red-50 hidden" 
                                id="retakeBtn_{{ $slug }}"
                                onclick="retakeCapture('camera_{{ $slug }}', 'canvas_{{ $slug }}', 'preview_{{ $slug }}', 'input_{{ $slug }}', 'captureBtn_{{ $slug }}', 'retakeBtn_{{ $slug }}')">
                            <i class="fa-solid fa-redo mr-1"></i> Ulangi
                        </button>

                        <!-- Canvas dan Preview -->
                        <canvas id="canvas_{{ $slug }}" class="hidden"></canvas>
                        <img id="preview_{{ $slug }}" src="" class="mt-2 w-full border rounded-md hidden" style="max-height: 200px;">

                        <!-- Hidden input -->
                        <input type="hidden" id="input_{{ $slug }}" name="camera_{{ $slug }}">
                    </div>
                @endforeach
            </div>

            {{-- Section Image Upload Box 2 --}}
            <div class="bg-white rounded shadow p-4">
                <h6 class="text-base font-semibold mb-3 text-gray-700 flex items-center">
                    <i class="fa-solid fa-camera mr-2 text-gray-600"></i>Dokumentasi Gambar
                </h6>
                @php
                    $box2 = ['Tampilan Atas', 'Tampilan Bawah'];
                @endphp

                @foreach ($box2 as $index => $view)
                    @php $slug = Str::slug($view); @endphp
                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 mb-2">
                            <i class="fa-solid fa-image mr-1"></i>{{ $view }}
                        </label>

                        <!-- Tombol Kamera -->
                        <button type="button" 
                                class="w-full px-3 py-2 text-sm border border-gray-300 text-gray-700 bg-white rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                onclick="openCamera('camera_{{ $slug }}', 'canvas_{{ $slug }}', 'preview_{{ $slug }}', 'input_{{ $slug }}', 'captureBtn_{{ $slug }}')">
                            <i class="fa-solid fa-video mr-1"></i> Gunakan Kamera
                        </button>

                        <!-- Video Preview -->
                        <video id="camera_{{ $slug }}" class="mt-2 w-full hidden rounded-md" autoplay playsinline muted style="max-height: 200px;"></video>

                        <!-- Tombol Capture & Batalkan dalam 1 baris -->
                        <div class="flex gap-2 mt-2 w-full">
                            <!-- Tombol Capture -->
                            <button type="button" 
                                    class="flex-1 px-3 py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 hidden" 
                                    id="captureBtn_{{ $slug }}"
                                    onclick="captureImage('camera_{{ $slug }}', 'canvas_{{ $slug }}', 'preview_{{ $slug }}', 'input_{{ $slug }}', 'captureBtn_{{ $slug }}', 'cancelBtn_{{ $slug }}')">
                                <i class="fa-solid fa-camera mr-1"></i> Capture
                            </button>

                            <!-- Tombol Batalkan -->
                            <button type="button" 
                                    class="flex-1 px-3 py-2 text-sm bg-red-600 text-white rounded-md hover:bg-red-700 hidden" 
                                    id="cancelBtn_{{ $slug }}"
                                    onclick="cancelCamera('camera_{{ $slug }}', 'captureBtn_{{ $slug }}', 'cancelBtn_{{ $slug }}')">
                                <i class="fa-solid fa-times mr-1"></i> Batalkan
                            </button>
                        </div>

                        <!-- Tombol Ulangi -->
                        <button type="button" 
                                class="w-full mt-2 px-3 py-2 text-sm border border-red-300 text-red-600 bg-white rounded-md hover:bg-red-50 hidden" 
                                id="retakeBtn_{{ $slug }}"
                                onclick="retakeCapture('camera_{{ $slug }}', 'canvas_{{ $slug }}', 'preview_{{ $slug }}', 'input_{{ $slug }}', 'captureBtn_{{ $slug }}', 'retakeBtn_{{ $slug }}')">
                            <i class="fa-solid fa-redo mr-1"></i> Ulangi
                        </button>

                        <!-- Canvas dan Preview -->
                        <canvas id="canvas_{{ $slug }}" class="hidden"></canvas>
                        <img id="preview_{{ $slug }}" src="" class="mt-2 w-full border rounded-md hidden" style="max-height: 200px;">

                        <!-- Hidden input -->
                        <input type="hidden" id="input_{{ $slug }}" name="camera_{{ $slug }}">
                    </div>
                @endforeach
            </div>

            {{-- Total Defect Found --}}
            <div class="bg-white rounded shadow p-4">
                <h6 class="text-base font-semibold mb-3 text-gray-700 flex items-center">
                    <i class="fa-solid fa-clipboard-check mr-2 text-gray-600"></i>Total Defect Found
                </h6>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Critical Found</label>
                        <input type="number" 
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                name="aql_critical_found" 
                                placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Major Found</label>
                        <input type="number" 
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                name="aql_major_found" 
                                placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Minor Found</label>
                        <input type="number" 
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                name="aql_minor_found" 
                                placeholder="0">
                    </div>
                </div>
            </div>

            {{-- Total Defect Allowed --}}
            <div class="bg-white rounded shadow p-4">
                <h6 class="text-base font-semibold mb-3 text-gray-700 flex items-center">
                    <i class="fa-solid fa-clipboard-check mr-2 text-gray-600"></i>Total Defect Allowed
                </h6>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Critical allowed</label>
                        <input type="number" 
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                name="aql_critical_allowed" 
                                placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Major allowed</label>
                        <input type="number" 
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                name="aql_major_allowed" 
                                placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Minor allowed</label>
                        <input type="number" 
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                name="aql_minor_allowed" 
                                placeholder="0">
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3 - Kotak Bawah Full Width dengan Height Minimal --}}
        <div class="bg-white rounded shadow p-3 h-16">
            <div class="flex items-center justify-between h-full">
                <div class="flex items-center gap-3">
                    <h6 class="text-sm font-semibold text-gray-700 flex items-center">
                        <i class="fa-solid fa-info-circle text-blue-600 mr-1"></i>Inspection Info
                    </h6>
                    
                    <div class="flex items-center gap-2">
                        <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-3 py-1.5 rounded transition">
                            <i class="fa-solid fa-box mr-1"></i>Unit #
                        </button>
                        <button type="button" class="bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium px-3 py-1.5 rounded transition">
                            <i class="fa-solid fa-location-dot mr-1"></i>Loc #
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="nik_qc" class="text-xs font-semibold text-gray-700 whitespace-nowrap">
                            NIK QC
                        </label>
                        <input type="number" 
                               id="nik_qc_awal" 
                               name="nik_qc_awal" 
                               class="w-24 px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500" 
                               placeholder="Masukkan">
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="button" 
                                class="bg-green-600 hover:bg-green-700 text-white font-medium py-1.5 px-4 text-xs rounded shadow-sm transition duration-200" 
                                onclick="openAcceptModal()">
                            <i class="fa-solid fa-check-circle mr-1"></i>Submit UD
                        </button>
                        <a href="#">
                            <button type="button" 
                                    class="bg-red-600 hover:bg-red-700 text-white font-medium py-1.5 px-4 text-xs rounded shadow-sm transition duration-200">
                                <i class="fa-solid fa-times-circle mr-1"></i>Cancel
                            </button>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert">
            <i class="fa-solid fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif
    </form>
</main>
<script src="{{ asset('js/Sidenav.js') }}"></script>
<script src="{{ asset('js/pop_up_mo.js') }}"></script>
<script src="{{ asset('js/live_camera.js') }}"></script>

@endsection